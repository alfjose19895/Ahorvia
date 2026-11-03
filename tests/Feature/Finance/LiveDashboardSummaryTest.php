<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Budgets\UpsertMonthlyBudgetAction;
use App\Actions\Debts\CreateDebtAction;
use App\Actions\Savings\CreateSavingsGoalAction;
use App\Actions\Savings\RecordSavingsContributionAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\DebtType;
use App\Enums\SavingsContributionType;
use App\Models\Category;
use App\Models\User;
use App\Services\Finance\DashboardSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveDashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private RecordIncomeAction $recordIncomeAction;

    private RecordExpenseAction $recordExpenseAction;

    private CreateDebtAction $createDebtAction;

    private CreateSavingsGoalAction $createSavingsGoalAction;

    private RecordSavingsContributionAction $recordSavingsContributionAction;

    private UpsertMonthlyBudgetAction $upsertMonthlyBudgetAction;

    private DashboardSummaryService $dashboardSummaryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->recordIncomeAction = $this->app->make(RecordIncomeAction::class);
        $this->recordExpenseAction = $this->app->make(RecordExpenseAction::class);
        $this->createDebtAction = $this->app->make(CreateDebtAction::class);
        $this->createSavingsGoalAction = $this->app->make(CreateSavingsGoalAction::class);
        $this->recordSavingsContributionAction = $this->app->make(RecordSavingsContributionAction::class);
        $this->upsertMonthlyBudgetAction = $this->app->make(UpsertMonthlyBudgetAction::class);
        $this->dashboardSummaryService = $this->app->make(DashboardSummaryService::class);
    }

    public function test_dashboard_summary_aggregates_live_financial_engine_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Alfredo Propietario',
            'currency_code' => 'USD',
        ]);
        $referenceDate = CarbonImmutable::create(2026, 6, 15);
        $this->travelTo($referenceDate);

        // 1. Crear cuentas
        $checking = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Principal',
            'type' => AccountType::Checking,
            'opening_balance' => '2500.0000',
        ]);

        $savingsAccount = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Ahorro',
            'type' => AccountType::Savings,
            'opening_balance' => '1000.0000',
        ]);

        // 2. Crear deuda
        $this->createDebtAction->execute($user, [
            'name' => 'Préstamo Personal',
            'type' => DebtType::PersonalLoan,
            'original_principal' => '1200.0000',
        ]);

        // 3. Registrar ingresos y gastos del mes
        $incomeCategory = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Sueldo Junio',
            'type' => CategoryType::Income,
            'slug' => 'sueldo-junio',
        ]);

        $expenseCategory = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Restaurantes',
            'type' => CategoryType::Expense,
            'slug' => 'restaurantes',
            'color' => '#EF4444',
        ]);

        $this->recordIncomeAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $incomeCategory->id,
            'amount' => '3000.0000',
            'entry_date' => $referenceDate,
            'description' => 'Sueldo de junio',
        ]);

        $this->recordExpenseAction->execute($user, [
            'account_id' => $checking->id,
            'category_id' => $expenseCategory->id,
            'amount' => '400.0000',
            'entry_date' => $referenceDate,
            'description' => 'Cena familiar',
        ]);

        // 4. Meta de ahorro y aporte
        $goal = $this->createSavingsGoalAction->execute($user, [
            'name' => 'Vacaciones Europa',
            'target_amount' => '2000.0000',
            'linked_account_id' => $savingsAccount->id,
            'start_date' => '2026-06-01',
            'target_date' => '2026-12-01',
            'duration_months' => 6,
        ]);

        $this->recordSavingsContributionAction->execute($user, [
            'savings_goal_id' => $goal->id,
            'contribution_type' => SavingsContributionType::Transfer,
            'amount' => '500.0000',
            'source_account_id' => $checking->id,
            'destination_account_id' => $savingsAccount->id,
            'contribution_date' => $referenceDate,
        ]);

        // 5. Presupuesto mensual
        $this->upsertMonthlyBudgetAction->execute($user, [
            'year' => 2026,
            'month' => 6,
            'lines' => [
                ['category_id' => $expenseCategory->id, 'limit_amount' => '600.0000'],
            ],
        ]);

        // Evaluar resumen consolidado
        $summary = $this->dashboardSummaryService->summarizeForUser($user, $referenceDate);

        $this->assertTrue($summary->hasAccounts);
        $this->assertTrue($summary->hasTransactions);

        // Activos totales = Checking (2500 + 3000 - 400 - 500 = 4600) + Savings (1000 + 500 = 1500) = 6100.0000
        $this->assertSame('6100.0000', $summary->totalAssets);
        // Pasivos totales = 1200.0000
        $this->assertSame('1200.0000', $summary->totalLiabilities);
        // Patrimonio neto = 6100 - 1200 = 4900.0000
        $this->assertSame('4900.0000', $summary->netWorth);
        // Ingresos mes = 3000.0000, Gastos mes = 400.0000, Flujo = 2600.0000
        $this->assertSame('3000.0000', $summary->monthlyIncome);
        $this->assertSame('400.0000', $summary->monthlyExpenses);
        $this->assertSame('2600.0000', $summary->monthlyCashFlow);
        // Ahorro reconocido = 500.0000
        $this->assertSame('500.0000', $summary->accumulatedSavings);
        $this->assertSame(1, $summary->activeGoalsCount);
        $this->assertSame(25, $summary->overallGoalsCompletionPercent); // 500 / 2000 = 25%

        // Renderizar /dashboard autenticado
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertOk()
            ->assertSee('Vacaciones Europa')
            ->assertSee('25%')
            ->assertSee('Restaurantes');
    }
}
