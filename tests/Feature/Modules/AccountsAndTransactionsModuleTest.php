<?php

declare(strict_types=1);

namespace Tests\Feature\Modules;

use App\Actions\Accounts\CreateAccountAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\EntryStatus;
use App\Livewire\Accounts\AccountsIndex;
use App\Livewire\Transactions\TransactionsIndex;
use App\Models\Account;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountsAndTransactionsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_edit_toggle_and_transfer_between_accounts(): void
    {
        $user = User::factory()->create();

        // 1. Crear primera cuenta de activo (Efectivo con saldo inicial $500)
        Livewire::actingAs($user)
            ->test(AccountsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Billetera Personal')
            ->set('type', 'cash')
            ->set('currency_code', 'USD')
            ->set('opening_balance', '500.00')
            ->call('saveAccount')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $wallet = Account::query()->ownedBy($user)->where('name', 'Billetera Personal')->first();
        $this->assertNotNull($wallet);

        // 2. Crear segunda cuenta (Cuenta Corriente con $1000)
        Livewire::actingAs($user)
            ->test(AccountsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Banco Principal')
            ->set('type', 'checking')
            ->set('currency_code', 'USD')
            ->set('opening_balance', '1000.00')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $bank = Account::query()->ownedBy($user)->where('name', 'Banco Principal')->first();
        $this->assertNotNull($bank);

        // 3. Editar cuenta
        Livewire::actingAs($user)
            ->test(AccountsIndex::class)
            ->call('openEditModal', $wallet->id)
            ->set('editName', 'Billetera Efectivo')
            ->call('updateAccount')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $this->assertSame('Billetera Efectivo', $wallet->fresh()->name);

        // 4. Transferir $150 de Banco a Billetera
        Livewire::actingAs($user)
            ->test(AccountsIndex::class)
            ->call('openTransferModal')
            ->set('transferSourceId', $bank->id)
            ->set('transferDestinationId', $wallet->id)
            ->set('transferAmount', '150.00')
            ->set('transferDescription', 'Retiro cajero para efectivo')
            ->call('executeTransfer')
            ->assertHasNoErrors()
            ->assertSet('showTransferModal', false);

        // 5. Desactivar cuenta
        Livewire::actingAs($user)
            ->test(AccountsIndex::class)
            ->call('toggleStatus', $wallet->id);

        $this->assertFalse($wallet->fresh()->is_active);
    }

    public function test_user_can_record_income_expense_transfer_and_reversal(): void
    {
        $user = User::factory()->create();
        $createAccountAction = $this->app->make(CreateAccountAction::class);

        $account = $createAccountAction->execute($user, [
            'name' => 'Cuenta Nómina',
            'type' => AccountType::Checking,
            'opening_balance' => '500.00',
        ]);

        $savingsAccount = $createAccountAction->execute($user, [
            'name' => 'Cuenta Ahorro',
            'type' => AccountType::Savings,
            'opening_balance' => '100.00',
        ]);

        $incomeCat = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Salario',
            'slug' => 'salario',
            'type' => CategoryType::Income,
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $expenseCat = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Supermercado',
            'slug' => 'supermercado',
            'type' => CategoryType::Expense,
            'color' => '#EF4444',
            'is_active' => true,
        ]);

        // 1. Registrar Ingreso
        Livewire::actingAs($user)
            ->test(TransactionsIndex::class)
            ->call('openCreateModal', 'income')
            ->set('accountId', $account->id)
            ->set('categoryId', $incomeCat->id)
            ->set('amount', '2500.00')
            ->set('description', 'Salario mensual')
            ->call('saveTransaction')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        // 2. Registrar Gasto
        Livewire::actingAs($user)
            ->test(TransactionsIndex::class)
            ->call('openCreateModal', 'expense')
            ->set('accountId', $account->id)
            ->set('categoryId', $expenseCat->id)
            ->set('amount', '320.00')
            ->set('description', 'Compra supermercado quincenal')
            ->call('saveTransaction')
            ->assertHasNoErrors();

        // 3. Registrar Transferencia
        Livewire::actingAs($user)
            ->test(TransactionsIndex::class)
            ->call('openCreateModal', 'transfer')
            ->set('accountId', $account->id)
            ->set('destinationAccountId', $savingsAccount->id)
            ->set('amount', '500.00')
            ->set('description', 'Ahorro programado a cuenta ahorros')
            ->call('saveTransaction')
            ->assertHasNoErrors();

        // 4. Reversar el gasto
        $expenseEntry = JournalEntry::query()
            ->ownedBy($user)
            ->where('description', 'Compra supermercado quincenal')
            ->first();

        $this->assertNotNull($expenseEntry);

        Livewire::actingAs($user)
            ->test(TransactionsIndex::class)
            ->call('openReversalModal', $expenseEntry->id)
            ->set('reversalReason', 'Cobro duplicado por error del datáfono')
            ->call('executeReversal')
            ->assertHasNoErrors()
            ->assertSet('showReversalModal', false);

        $this->assertSame(EntryStatus::Reversed, $expenseEntry->fresh()->status);
        $this->assertNotNull($expenseEntry->fresh()->reversed_by_entry_id);
    }
}
