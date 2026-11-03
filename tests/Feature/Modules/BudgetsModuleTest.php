<?php

declare(strict_types=1);

namespace Tests\Feature\Modules;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Livewire\Budgets\BudgetsIndex;
use App\Models\Category;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_budget_limits_navigate_months_and_see_threshold_badges(): void
    {
        $now = CarbonImmutable::create(2026, 6, 15);
        $this->travelTo($now);

        $user = User::factory()->create();
        $createAccountAction = $this->app->make(CreateAccountAction::class);
        $recordExpenseAction = $this->app->make(RecordExpenseAction::class);

        $account = $createAccountAction->execute($user, [
            'name' => 'Cuenta Débito',
            'type' => AccountType::Checking,
            'opening_balance' => '5000.00',
        ]);

        $catFood = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Alimentación',
            'slug' => 'alimentacion',
            'type' => CategoryType::Expense,
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $catTransport = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Transporte',
            'slug' => 'transporte',
            'type' => CategoryType::Expense,
            'color' => '#3B82F6',
            'is_active' => true,
        ]);

        // 1. Configurar presupuesto para Junio 2026: Alimentación $500, Transporte $200
        Livewire::actingAs($user)
            ->test(BudgetsIndex::class)
            ->call('openManageModal')
            ->set("limits.{$catFood->id}", '500.00')
            ->set("limits.{$catTransport->id}", '200.00')
            ->call('saveBudget')
            ->assertHasNoErrors()
            ->assertSet('showManageModal', false)
            ->assertSee('Alimentación')
            ->assertSee('Transporte');

        // 2. Navegar meses
        Livewire::actingAs($user)
            ->test(BudgetsIndex::class)
            ->call('previousMonth')
            ->assertSet('selectedMonth', 5)
            ->assertSet('selectedYear', 2026)
            ->call('nextMonth')
            ->assertSet('selectedMonth', 6)
            ->assertSet('selectedYear', 2026);

        // 3. Registrar gasto de $425 en Alimentación (85% de consumo -> Umbral 80%)
        $recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $catFood->id,
            'amount' => '425.00',
            'entry_date' => '2026-06-16',
            'description' => 'Mercado supermercado',
        ]);

        Livewire::actingAs($user)
            ->test(BudgetsIndex::class)
            ->assertSee('Consumo al 85%')
            ->assertSee('Alerta 80%');

        // 4. Registrar gasto adicional de $100 (Total $525 de $500 -> Excedido al 105%)
        $recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $catFood->id,
            'amount' => '100.00',
            'entry_date' => '2026-06-18',
            'description' => 'Cena restaurante',
        ]);

        Livewire::actingAs($user)
            ->test(BudgetsIndex::class)
            ->assertSee('Límite excedido');
    }
}
