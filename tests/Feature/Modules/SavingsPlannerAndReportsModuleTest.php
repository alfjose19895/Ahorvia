<?php

declare(strict_types=1);

namespace Tests\Feature\Modules;

use App\Actions\Accounts\CreateAccountAction;
use App\Enums\AccountType;
use App\Livewire\Notifications\NotificationsIndex;
use App\Livewire\Reports\ReportsIndex;
use App\Livewire\Savings\SavingsPlannerIndex;
use App\Models\SavingsGoal;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsPlannerAndReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_savings_goal_and_record_contribution(): void
    {
        $now = CarbonImmutable::create(2026, 6, 1);
        $this->travelTo($now);

        $user = User::factory()->create();
        $createAccountAction = $this->app->make(CreateAccountAction::class);

        $checking = $createAccountAction->execute($user, [
            'name' => 'Cuenta Sueldo',
            'type' => AccountType::Checking,
            'opening_balance' => '5000.00',
        ]);

        $savingsAcc = $createAccountAction->execute($user, [
            'name' => 'Cuenta Ahorro Banesco',
            'type' => AccountType::Savings,
            'opening_balance' => '200.00',
        ]);

        // 1. Crear meta a 6 meses: Fondo de Emergencia por $1200 con ahorro inicial de $200
        Livewire::actingAs($user)
            ->test(SavingsPlannerIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Fondo de Emergencia')
            ->set('targetAmount', '1200.00')
            ->set('initialAmount', '200.00')
            ->set('durationMonths', 6)
            ->set('mode', 'adjustable')
            ->set('linkedAccountId', $savingsAcc->id)
            ->call('saveGoal')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $goal = SavingsGoal::query()->ownedBy($user)->where('name', 'Fondo de Emergencia')->first();
        $this->assertNotNull($goal);

        // Cuota inicial: ($1200 - $200) / 6 = $166.6666 ($166.66)
        Livewire::actingAs($user)
            ->test(SavingsPlannerIndex::class)
            ->assertSee('Fondo de Emergencia')
            ->assertSee('$ 166.66')
            ->assertSee('$ 1,000.00'); // pendiente

        // 2. Registrar aporte de $300 mediante transferencia de Cuenta Sueldo a Cuenta Ahorro
        Livewire::actingAs($user)
            ->test(SavingsPlannerIndex::class)
            ->call('openContributionModal', $goal->id)
            ->set('contributionType', 'transfer')
            ->set('sourceAccountId', $checking->id)
            ->set('destinationAccountId', $savingsAcc->id)
            ->set('contributionAmount', '300.00')
            ->set('contributionDate', '2026-06-05')
            ->call('saveContribution')
            ->assertHasNoErrors()
            ->assertSet('showContributionModal', false);

        // Ahora el ahorro reconocido es $200 + $300 = $500, pendiente $700
        Livewire::actingAs($user)
            ->test(SavingsPlannerIndex::class)
            ->assertSee('$ 500.00')
            ->assertSee('$ 700.00');
    }

    public function test_reports_and_notifications_index_render_successfully(): void
    {
        $user = User::factory()->create();

        // 1. Reportes: Cambiar timeframe a 3, 6, 12 meses
        Livewire::actingAs($user)
            ->test(ReportsIndex::class)
            ->assertSee('Reportes y Evolución Financiera')
            ->assertSet('timeframe', 6)
            ->call('setTimeframe', 12)
            ->assertSet('timeframe', 12)
            ->call('setTimeframe', 3)
            ->assertSet('timeframe', 3);

        // 2. Notificaciones: Centro de alertas en tiempo real
        Livewire::actingAs($user)
            ->test(NotificationsIndex::class)
            ->assertSee('Centro de Notificaciones')
            ->assertSee('Protege tus finanzas con 2FA TOTP') // Alerta por 2FA no activo
            ->assertSet('filter', 'all')
            ->call('setFilter', 'info')
            ->assertSet('filter', 'info')
            ->assertSee('Protege tus finanzas con 2FA TOTP');
    }
}
