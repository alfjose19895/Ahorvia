<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Budgets\UpsertMonthlyBudgetAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use App\Services\Budget\BudgetMonitorService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private UpsertMonthlyBudgetAction $upsertMonthlyBudgetAction;

    private RecordExpenseAction $recordExpenseAction;

    private BudgetMonitorService $budgetMonitorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->upsertMonthlyBudgetAction = $this->app->make(UpsertMonthlyBudgetAction::class);
        $this->recordExpenseAction = $this->app->make(RecordExpenseAction::class);
        $this->budgetMonitorService = $this->app->make(BudgetMonitorService::class);
    }

    public function test_budget_execution_calculates_spent_and_triggers_80_90_100_percent_thresholds(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $date = CarbonImmutable::create(2026, 5, 10);

        $account = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Débito',
            'type' => AccountType::Checking,
            'opening_balance' => '5000.0000',
        ]);

        $catAlimentos = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Supermercado y Alimentos',
            'type' => CategoryType::Expense,
            'slug' => 'alimentos',
        ]);

        $catServicios = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Servicios Básicos',
            'type' => CategoryType::Expense,
            'slug' => 'servicios',
        ]);

        // Presupuesto: Alimentos = $1000.00, Servicios = $500.00 (Total = $1500.00)
        $budget = $this->upsertMonthlyBudgetAction->execute($user, [
            'year' => 2026,
            'month' => 5,
            'lines' => [
                ['category_id' => $catAlimentos->id, 'limit_amount' => '1000.0000'],
                ['category_id' => $catServicios->id, 'limit_amount' => '500.0000'],
            ],
        ]);

        $this->assertSame('1500.0000', (string) $budget->total_limit);

        // Sin gastos: umbral 0%
        $eval0 = $this->budgetMonitorService->evaluate($budget);
        $this->assertSame('0.0000', $eval0->totalSpent);
        $this->assertSame(0, $eval0->consumptionPercent);
        $this->assertSame(0, $eval0->highestThresholdReached);

        // Gasto 1: Alimentos $820.00 (82% de $1000.00 -> alerta 80%)
        $this->recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $catAlimentos->id,
            'amount' => '820.0000',
            'entry_date' => $date,
            'description' => 'Mercado de la quincena',
        ]);

        $eval80 = $this->budgetMonitorService->evaluate($budget);
        $lineAlimentos80 = collect($eval80->lines)->firstWhere('category_id', $catAlimentos->id);
        $this->assertNotNull($lineAlimentos80);
        $this->assertSame('820.0000', $lineAlimentos80['spent_amount']);
        $this->assertSame(82, $lineAlimentos80['consumption_percent']);
        $this->assertSame(80, $lineAlimentos80['alert_threshold']);

        // Gasto 2: Alimentos $110.00 adicionales (Total alimentos = $930.00 -> 93% -> alerta 90%)
        $this->recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $catAlimentos->id,
            'amount' => '110.0000',
            'entry_date' => $date,
            'description' => 'Compras extras',
        ]);

        $eval90 = $this->budgetMonitorService->evaluate($budget);
        $lineAlimentos90 = collect($eval90->lines)->firstWhere('category_id', $catAlimentos->id);
        $this->assertNotNull($lineAlimentos90);
        $this->assertSame(93, $lineAlimentos90['consumption_percent']);
        $this->assertSame(90, $lineAlimentos90['alert_threshold']);

        // Gasto 3: Servicios $550.00 (110% de $500.00 -> alerta 100%)
        $this->recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $catServicios->id,
            'amount' => '550.0000',
            'entry_date' => $date,
            'description' => 'Luz y gas mensual',
        ]);

        $eval100 = $this->budgetMonitorService->evaluate($budget);
        $lineServicios = collect($eval100->lines)->firstWhere('category_id', $catServicios->id);
        $this->assertNotNull($lineServicios);
        $this->assertSame(110, $lineServicios['consumption_percent']);
        $this->assertSame(100, $lineServicios['alert_threshold']);
        $this->assertSame(100, $eval100->highestThresholdReached);
    }
}
