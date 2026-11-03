<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Actions\Transactions\ReverseJournalEntryAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Exceptions\Finance\FinancialDomainException;
use App\Exceptions\Finance\ImmutableLedgerException;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerImmutabilityAndReversalsTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private RecordExpenseAction $recordExpenseAction;

    private ReverseJournalEntryAction $reverseJournalEntryAction;

    private AccountBalanceService $accountBalanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->recordExpenseAction = $this->app->make(RecordExpenseAction::class);
        $this->reverseJournalEntryAction = $this->app->make(ReverseJournalEntryAction::class);
        $this->accountBalanceService = $this->app->make(AccountBalanceService::class);
    }

    public function test_confirmed_journal_entries_and_lines_cannot_be_physically_deleted_or_mutated(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $account = $this->createAccountAction->execute($user, [
            'name' => 'Caja',
            'type' => AccountType::Cash,
            'opening_balance' => '500.0000',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Transporte',
            'type' => CategoryType::Expense,
            'slug' => 'transporte',
        ]);

        $entry = $this->recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '40.0000',
            'description' => 'Combustible',
        ]);

        // 1. Intentar eliminar físicamente el asiento confirmado
        try {
            $entry->delete();
            $this->fail('Se esperaba ImmutableLedgerException al intentar eliminar el asiento contable.');
        } catch (ImmutableLedgerException $e) {
            $this->assertStringContainsString('No está permitido eliminar físicamente un asiento contable confirmado', $e->getMessage());
        }

        // 2. Intentar eliminar físicamente una línea contable
        /** @var JournalLine $line */
        $line = $entry->lines()->firstOrFail();
        try {
            $line->delete();
            $this->fail('Se esperaba ImmutableLedgerException al intentar eliminar una línea de asiento contable.');
        } catch (ImmutableLedgerException $e) {
            $this->assertStringContainsString('No está permitido eliminar físicamente líneas', $e->getMessage());
        }

        // 3. Intentar alterar el débito/crédito de una línea contable confirmada
        try {
            $line->update(['debit' => '999.0000']);
            $this->fail('Se esperaba ImmutableLedgerException al intentar mutar importes contables de una línea.');
        } catch (ImmutableLedgerException $e) {
            $this->assertStringContainsString('Los importes y cuentas de una línea contable confirmada son inmutables', $e->getMessage());
        }
    }

    public function test_auditable_reversal_nets_balances_and_records_audit_trail(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $account = $this->createAccountAction->execute($user, [
            'name' => 'Billetera',
            'type' => AccountType::Cash,
            'opening_balance' => '300.0000',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Entretenimiento',
            'type' => CategoryType::Expense,
            'slug' => 'entretenimiento',
        ]);

        $entry = $this->recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '80.0000',
            'description' => 'Cine y cena',
        ]);

        $this->assertSame('220.0000', $this->accountBalanceService->balanceForAccount($account));

        // Ejecutar reverso auditable
        $reversalEntry = $this->reverseJournalEntryAction->execute($user, $entry, 'Cobro duplicado por el comercio');

        $this->assertSame(MovementType::Reversal, $reversalEntry->movement_type);
        $this->assertSame($entry->id, $reversalEntry->reversal_of_entry_id);

        $entry->refresh();
        $this->assertSame(EntryStatus::Reversed, $entry->status);
        $this->assertSame($reversalEntry->id, $entry->reversed_by_entry_id);
        $this->assertNotNull($entry->reversed_at);
        $this->assertSame('Cobro duplicado por el comercio', $entry->reversal_reason);

        // El saldo de la cuenta se restauró automáticamente a 300.0000 por partida doble sin borrar filas
        $this->assertSame('300.0000', $this->accountBalanceService->balanceForAccount($account));

        // Los gastos mensuales se netearon a 0.0000
        $metrics = $this->accountBalanceService->consolidatedMetricsForUser($user, CarbonImmutable::now());
        $this->assertSame('0.0000', $metrics['monthly_expenses']);

        // Se registró en audit_logs
        $auditLog = AuditLog::query()
            ->where('event', 'journal_entry.reversed')
            ->where('auditable_id', $entry->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame($user->id, $auditLog->user_id);

        // Intentar reversar el mismo asiento por segunda vez debe fallar
        $this->expectException(FinancialDomainException::class);
        $this->expectExceptionMessage('ya ha sido reversado previamente');

        $this->reverseJournalEntryAction->execute($user, $entry, 'Segundo reverso inválido');
    }

    public function test_journal_entry_idempotency_prevents_duplicate_records(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $account = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Única',
            'type' => AccountType::Checking,
            'opening_balance' => '500.0000',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Consultoría',
            'type' => CategoryType::Income,
            'slug' => 'consultoria',
        ]);

        $idemKey = 'unique_consulting_invoice_4921';

        $entry1 = $this->app->make(RecordIncomeAction::class)->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '150.0000',
            'description' => 'Pago de factura #4921',
            'idempotency_key' => $idemKey,
        ]);

        // Reintento de la misma petición con la misma idempotency_key
        $entry2 = $this->app->make(RecordIncomeAction::class)->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '150.0000',
            'description' => 'Pago de factura #4921 (reintento)',
            'idempotency_key' => $idemKey,
        ]);

        $this->assertSame($entry1->id, $entry2->id);
        $this->assertSame(1, JournalEntry::query()->where('idempotency_key', $idemKey)->count());
        $this->assertSame('650.0000', $this->accountBalanceService->balanceForAccount($account));
    }
}
