<?php

declare(strict_types=1);

namespace Tests\Feature\Modules;

use App\Actions\Accounts\CreateAccountAction;
use App\Enums\AccountType;
use App\Livewire\Debts\CardsIndex;
use App\Livewire\Debts\DebtsIndex;
use App\Models\Debt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DebtsAndCardsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_debt_and_record_payment_with_breakdown(): void
    {
        $user = User::factory()->create();
        $createAccountAction = $this->app->make(CreateAccountAction::class);

        $bank = $createAccountAction->execute($user, [
            'name' => 'Cuenta Nómina',
            'type' => AccountType::Checking,
            'opening_balance' => '5000.00',
        ]);

        // 1. Crear deuda / préstamo
        Livewire::actingAs($user)
            ->test(DebtsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Préstamo Personal Auto')
            ->set('debtType', 'bank_loan')
            ->set('creditorName', 'Banco Pichincha')
            ->set('originalPrincipal', '4000.00')
            ->set('interestRateAnnual', '11.5')
            ->set('disbursementAccountId', $bank->id)
            ->call('saveDebt')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $debt = Debt::query()->ownedBy($user)->where('name', 'Préstamo Personal Auto')->first();
        $this->assertNotNull($debt);

        // 2. Registrar abono separando capital ($300), interés ($40) y comisiones ($5)
        Livewire::actingAs($user)
            ->test(DebtsIndex::class)
            ->call('openPaymentModal', $debt->id)
            ->set('sourceAccountId', $bank->id)
            ->set('paymentDate', now()->toDateString())
            ->set('principalAmount', '300.00')
            ->set('interestAmount', '40.00')
            ->set('feeAmount', '5.00')
            ->set('reference', 'TRANSFER-9012')
            ->call('savePayment')
            ->assertHasNoErrors()
            ->assertSet('showPaymentModal', false);

        // Verificar que el saldo pendiente se redujo en $300 (4000 - 300 = 3700)
        Livewire::actingAs($user)
            ->test(DebtsIndex::class)
            ->assertSee('$ 3,700.00')
            ->assertSee('$ 300.00') // capital amortizado
            ->assertSee('$ 45.00'); // intereses + comisiones pagados
    }

    public function test_user_can_create_card_and_pay_card_without_double_counting(): void
    {
        $user = User::factory()->create();
        $createAccountAction = $this->app->make(CreateAccountAction::class);

        $checking = $createAccountAction->execute($user, [
            'name' => 'Cuenta Débito',
            'type' => AccountType::Checking,
            'opening_balance' => '3000.00',
        ]);

        // 1. Crear Tarjeta de Crédito con cupo $2500 y saldo actual $600
        Livewire::actingAs($user)
            ->test(CardsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Visa Infinite')
            ->set('bankName', 'Santander')
            ->set('creditLimit', '2500.00')
            ->set('openingBalance', '600.00')
            ->set('cutoffDay', 15)
            ->set('paymentDueDay', 30)
            ->call('saveCard')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $card = Debt::query()->ownedBy($user)->where('name', 'Visa Infinite')->first();
        $this->assertNotNull($card);

        // 2. Pagar la tarjeta $400 desde la cuenta de débito
        Livewire::actingAs($user)
            ->test(CardsIndex::class)
            ->call('openPayModal', $card->id)
            ->set('sourceAccountId', $checking->id)
            ->set('paymentDate', now()->toDateString())
            ->set('principalAmount', '400.00')
            ->set('interestAmount', '0.00')
            ->set('feeAmount', '0.00')
            ->call('savePayment')
            ->assertHasNoErrors()
            ->assertSet('showPayModal', false);

        // El saldo pendiente de la tarjeta ahora es 600 - 400 = 200, y el cupo disponible es 2500 - 200 = 2300
        Livewire::actingAs($user)
            ->test(CardsIndex::class)
            ->assertSee('$ 200.00')
            ->assertSee('$ 2,300.00');
    }
}
