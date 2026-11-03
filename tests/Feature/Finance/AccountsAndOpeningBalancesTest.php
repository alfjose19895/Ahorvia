<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Enums\AccountType;
use App\Enums\EntryStatus;
use App\Enums\LedgerNature;
use App\Enums\MovementType;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsAndOpeningBalancesTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private AccountBalanceService $accountBalanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->accountBalanceService = $this->app->make(AccountBalanceService::class);
    }

    public function test_user_can_create_cash_and_bank_accounts_with_opening_balance(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);

        $account = $this->createAccountAction->execute($user, [
            'name' => 'Billetera Principal',
            'type' => AccountType::Cash,
            'opening_balance' => '250.5000',
            'notes' => 'Efectivo disponible en casa',
        ]);

        $this->assertSame(AccountType::Cash, $account->type);
        $this->assertSame(LedgerNature::Asset, $account->ledger_nature);
        $this->assertTrue($account->is_active);
        $this->assertFalse($account->is_system);

        $balance = $this->accountBalanceService->balanceForAccount($account);
        $this->assertSame('250.5000', $balance);

        $entry = JournalEntry::query()
            ->ownedBy($user)
            ->where('movement_type', MovementType::OpeningBalance)
            ->with('lines')
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame(EntryStatus::Posted, $entry->status);
        $this->assertTrue($entry->isBalanced());
        $this->assertCount(2, $entry->lines);

        $accountLine = $entry->lines->firstWhere('account_id', $account->id);
        $this->assertNotNull($accountLine);
        $this->assertSame('250.5000', (string) $accountLine->debit);
        $this->assertSame('0.0000', (string) $accountLine->credit);

        $equityLine = $entry->lines->firstWhere('account_id', '!=', $account->id);
        $this->assertNotNull($equityLine);
        $this->assertSame('0.0000', (string) $equityLine->debit);
        $this->assertSame('250.5000', (string) $equityLine->credit);
    }

    public function test_creating_liability_account_with_opening_balance_credits_liability_and_debits_equity(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);

        $creditCard = $this->createAccountAction->execute($user, [
            'name' => 'Visa Oro',
            'type' => AccountType::CreditCard,
            'credit_limit' => '5000.0000',
            'opening_balance' => '800.0000',
        ]);

        $this->assertSame(AccountType::CreditCard, $creditCard->type);
        $this->assertSame(LedgerNature::Liability, $creditCard->ledger_nature);

        $balance = $this->accountBalanceService->balanceForAccount($creditCard);
        $this->assertSame('800.0000', $balance);

        $available = $this->accountBalanceService->availableForAccount($creditCard);
        $this->assertSame('4200.0000', $available);

        $entry = JournalEntry::query()
            ->ownedBy($user)
            ->where('movement_type', MovementType::OpeningBalance)
            ->with('lines')
            ->first();

        $this->assertNotNull($entry);
        $this->assertTrue($entry->isBalanced());

        $liabilityLine = $entry->lines->firstWhere('account_id', $creditCard->id);
        $this->assertNotNull($liabilityLine);
        $this->assertSame('0.0000', (string) $liabilityLine->debit);
        $this->assertSame('800.0000', (string) $liabilityLine->credit);

        $equityLine = $entry->lines->firstWhere('account_id', '!=', $creditCard->id);
        $this->assertNotNull($equityLine);
        $this->assertSame('800.0000', (string) $equityLine->debit);
        $this->assertSame('0.0000', (string) $equityLine->credit);
    }

    public function test_user_cannot_create_account_with_system_ledger_types(): void
    {
        $user = User::factory()->create();

        $this->expectException(FinancialDomainException::class);
        $this->expectExceptionMessage('reservado para el sistema contable');

        $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Ilegal',
            'type' => AccountType::SystemIncome,
        ]);
    }

    public function test_negative_opening_balance_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->expectException(FinancialDomainException::class);
        $this->expectExceptionMessage('no puede ser un valor negativo');

        $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Inválida',
            'type' => AccountType::Checking,
            'opening_balance' => '-100.0000',
        ]);
    }
}
