<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Budgets\UpsertMonthlyBudgetAction;
use App\Actions\Savings\CreateSavingsGoalAction;
use App\Actions\Savings\RecordSavingsContributionAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\ObligationStatus;
use App\Enums\SavingsContributionType;
use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;
use App\Exceptions\Finance\InsufficientAvailableFundsException;
use App\Models\Category;
use App\Models\ScheduledObligation;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use App\Services\Savings\SavingsCapacityService;
use App\Services\Savings\SavingsPlannerService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartSavingsPlannerTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private CreateSavingsGoalAction $createSavingsGoalAction;

    private RecordSavingsContributionAction $recordSavingsContributionAction;

    private RecordIncomeAction $recordIncomeAction;

    private UpsertMonthlyBudgetAction $upsertMonthlyBudgetAction;

    private AccountBalanceService $accountBalanceService;

    private SavingsPlannerService $savingsPlannerService;

    private SavingsCapacityService $savingsCapacityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->createSavingsGoalAction = $this->app->make(CreateSavingsGoalAction::class);
        $this->recordSavingsContributionAction = $this->app->make(RecordSavingsContributionAction::class);
        $this->recordIncomeAction = $this->app->make(RecordIncomeAction::class);
        $this->upsertMonthlyBudgetAction = $this->app->make(UpsertMonthlyBudgetAction::class);
        $this->accountBalanceService = $this->app->make(AccountBalanceService::class);
        $this->savingsPlannerService = $this->app->make(SavingsPlannerService::class);
        $this->savingsCapacityService = $this->app->make(SavingsCapacityService::class);
    }

    public function test_savings_goal_6_month_plan_calculates_quotas_and_recalculates_on_missed_contributions(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $start = CarbonImmutable::create(2026, 1, 1);
        $target = CarbonImmutable::create(2026, 7, 1); // 6 meses

        $goal = $this->createSavingsGoalAction->execute($user, [
            'name' => 'Fondo de Emergencia 6M',
            'mode' => SavingsMode::Adjustable,
            'target_amount' => '6000.0000',
            'initial_amount' => '0.0000',
            'duration_months' => 6,
            'start_date' => $start,
            'target_date' => $target,
        ]);

        $this->assertSame(SavingsGoalStatus::Active, $goal->status);

        // Mes 1: 6 meses restantes -> cuota = 6000 / 6 = 1000.0000
        $planMes1 = $this->savingsPlannerService->analyzeGoal($goal, $start);
        $this->assertSame('6000.0000', $planMes1->pendingSavings);
        $this->assertSame(6, $planMes1->remainingMonths);
        $this->assertSame('1000.0000', $planMes1->recommendedMonthlyQuota);
        $this->assertSame(0, $planMes1->completionPercentage);

        // Si el usuario no aportó nada en el mes 1 y estamos en el mes 2 (febrero, 5 meses restantes):
        // Cuota recalculada = 6000 / 5 = 1200.0000 sin modificar el historial previo!
        $asOfMes2 = CarbonImmutable::create(2026, 2, 1);
        $planMes2 = $this->savingsPlannerService->analyzeGoal($goal, $asOfMes2);
        $this->assertSame(5, $planMes2->remainingMonths);
        $this->assertSame('1200.0000', $planMes2->recommendedMonthlyQuota);
        $this->assertTrue($planMes2->requiresAdjustment);
    }

    public function test_savings_contribution_via_transfer_and_reserve_types(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);

        $checkingAccount = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Nómina',
            'type' => AccountType::Checking,
            'opening_balance' => '2000.0000',
        ]);

        $savingsAccount = $this->createAccountAction->execute($user, [
            'name' => 'Bóveda de Ahorro',
            'type' => AccountType::Savings,
            'opening_balance' => '0.0000',
        ]);

        $goal = $this->createSavingsGoalAction->execute($user, [
            'name' => 'Viaje Vacaciones',
            'target_amount' => '1500.0000',
            'linked_account_id' => $savingsAccount->id,
        ]);

        // 1. Aporte por transferencia: $500.00 desde Cuenta Nómina hacia Bóveda de Ahorro
        $transferContrib = $this->recordSavingsContributionAction->execute($user, [
            'savings_goal_id' => $goal->id,
            'contribution_type' => SavingsContributionType::Transfer,
            'amount' => '500.0000',
            'source_account_id' => $checkingAccount->id,
            'destination_account_id' => $savingsAccount->id,
        ]);

        $this->assertNotNull($transferContrib->journal_entry_id);
        $this->assertSame('1500.0000', $this->accountBalanceService->balanceForAccount($checkingAccount));
        $this->assertSame('500.0000', $this->accountBalanceService->balanceForAccount($savingsAccount));
        $this->assertSame('500.0000', $this->savingsPlannerService->recognizedSavingsForGoal($goal));

        // 2. Aporte por reserva interna: apartar $300.00 sobre Cuenta Nómina sin transferir
        $reserveContrib = $this->recordSavingsContributionAction->execute($user, [
            'savings_goal_id' => $goal->id,
            'contribution_type' => SavingsContributionType::Reserve,
            'amount' => '300.0000',
            'account_id' => $checkingAccount->id,
        ]);

        $this->assertNull($reserveContrib->journal_entry_id);
        // El saldo contable bancario sigue siendo 1500.0000 para conciliación
        $this->assertSame('1500.0000', $this->accountBalanceService->balanceForAccount($checkingAccount));
        // Pero la liquidez libre disponible se redujo a 1200.0000 (1500 - 300)
        $this->assertSame('1200.0000', $this->accountBalanceService->availableForAccount($checkingAccount));
        // Total reconocido en la meta: 500 + 300 = 800.0000
        $this->assertSame('800.0000', $this->savingsPlannerService->recognizedSavingsForGoal($goal));

        // Completar la meta con un aporte de 700.0000
        $this->recordSavingsContributionAction->execute($user, [
            'savings_goal_id' => $goal->id,
            'contribution_type' => SavingsContributionType::Reserve,
            'amount' => '700.0000',
            'account_id' => $checkingAccount->id,
        ]);

        $goal->refresh();
        $this->assertSame(SavingsGoalStatus::Completed, $goal->status);
        $this->assertNotNull($goal->completed_at);
        $this->assertSame('1500.0000', $this->savingsPlannerService->recognizedSavingsForGoal($goal));
    }

    public function test_reserve_contribution_fails_if_available_funds_are_insufficient(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);

        $account = $this->createAccountAction->execute($user, [
            'name' => 'Efectivo',
            'type' => AccountType::Cash,
            'opening_balance' => '100.0000',
        ]);

        $goal = $this->createSavingsGoalAction->execute($user, [
            'name' => 'Auto Nuevo',
            'target_amount' => '10000.0000',
        ]);

        $this->expectException(InsufficientAvailableFundsException::class);
        $this->expectExceptionMessage('Fondos disponibles insuficientes');

        $this->recordSavingsContributionAction->execute($user, [
            'savings_goal_id' => $goal->id,
            'contribution_type' => SavingsContributionType::Reserve,
            'amount' => '150.0000',
            'account_id' => $account->id,
        ]);
    }

    public function test_savings_capacity_prevents_double_counting_of_budgeted_obligations(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $now = CarbonImmutable::create(2026, 4, 15);

        $checking = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Nómina',
            'type' => AccountType::Checking,
            'opening_balance' => '1000.0000',
        ]);

        $incomeCategory = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Sueldo',
            'type' => CategoryType::Income,
            'slug' => 'sueldo',
        ]);

        $rentCategory = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Alquiler y Vivienda',
            'type' => CategoryType::Expense,
            'slug' => 'alquiler',
        ]);

        // Registrar ingreso mensual de $4000.00
        $this->recordIncomeAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $incomeCategory->id,
            'amount' => '4000.0000',
            'entry_date' => $now,
            'description' => 'Ingreso mensual',
        ]);

        // Presupuesto mensual de $1200.00 en Alquiler
        $this->upsertMonthlyBudgetAction->execute($user, [
            'year' => 2026,
            'month' => 4,
            'lines' => [
                ['category_id' => $rentCategory->id, 'limit_amount' => '1200.0000'],
            ],
        ]);

        // Obligación programada de alquiler por $1200.00 en la misma categoría
        ScheduledObligation::query()->create([
            'user_id' => $user->id,
            'category_id' => $rentCategory->id,
            'title' => 'Pago de Alquiler depto',
            'amount' => '1200.0000',
            'currency_code' => 'USD',
            'due_date' => '2026-04-28',
            'status' => ObligationStatus::Pending,
            'is_included_in_budget' => true,
        ]);

        // Meta activa con cuota recomendada de $500.00
        $this->createSavingsGoalAction->execute($user, [
            'name' => 'Ahorro Inversión',
            'target_amount' => '3000.0000',
            'duration_months' => 6,
            'start_date' => '2026-04-01',
            'target_date' => '2026-10-01',
        ]);

        $capacity = $this->savingsCapacityService->evaluateForMonth($user, $now);

        // Ingresos esperados: 4000.00
        // Gastos presupuestados: 1200.00
        // Obligaciones NO presupuestadas: 0.00 (el alquiler está en el presupuesto, ¡no se descuenta doble!)
        // Capacidad bruta de ahorro = 4000 - 1200 = 2800.0000
        // Demanda de metas activas = 500.0000 (3000 / 6)
        // Capacidad neta disponible = 2800 - 500 = 2300.0000
        $this->assertSame('4000.0000', $capacity->expectedMonthlyIncome);
        $this->assertSame('1200.0000', $capacity->budgetedExpenses);
        $this->assertSame('0.0000', $capacity->unbudgetedObligations);
        $this->assertSame('1200.0000', $capacity->totalCommitments);
        $this->assertSame('2800.0000', $capacity->grossSavingsCapacity);
        $this->assertSame('500.0000', $capacity->activeGoalsMonthlyDemand);
        $this->assertSame('2300.0000', $capacity->netUnallocatedCapacity);
        $this->assertTrue($capacity->isSustainable);
    }
}
