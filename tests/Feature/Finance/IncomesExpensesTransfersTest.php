<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Actions\Transactions\RecordIncomeAction;
use App\Actions\Transactions\RecordTransferAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Exceptions\Finance\CrossTenantOwnershipException;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Category;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomesExpensesTransfersTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private RecordIncomeAction $recordIncomeAction;

    private RecordExpenseAction $recordExpenseAction;

    private RecordTransferAction $recordTransferAction;

    private AccountBalanceService $accountBalanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->recordIncomeAction = $this->app->make(RecordIncomeAction::class);
        $this->recordExpenseAction = $this->app->make(RecordExpenseAction::class);
        $this->recordTransferAction = $this->app->make(RecordTransferAction::class);
        $this->accountBalanceService = $this->app->make(AccountBalanceService::class);
    }

    public function test_recording_income_updates_account_balance_and_monthly_metrics(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $account = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Sueldo',
            'type' => AccountType::Checking,
            'opening_balance' => '500.0000',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Salario Mensual',
            'type' => CategoryType::Income,
            'slug' => 'salario-mensual',
        ]);

        $entry = $this->recordIncomeAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '3200.5000',
            'description' => 'Pago de nómina quincena 1',
            'reference' => 'NOM-001',
        ]);

        $this->assertSame(MovementType::Income, $entry->movement_type);
        $this->assertSame(EntryStatus::Posted, $entry->status);
        $this->assertTrue($entry->isBalanced());

        $balance = $this->accountBalanceService->balanceForAccount($account);
        $this->assertSame('3700.5000', $balance);

        $metrics = $this->accountBalanceService->consolidatedMetricsForUser($user, CarbonImmutable::now());
        $this->assertSame('3200.5000', $metrics['monthly_income']);
        $this->assertSame('3700.5000', $metrics['total_assets']);
    }

    public function test_recording_expense_decreases_asset_balance_and_tracks_monthly_expense(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $account = $this->createAccountAction->execute($user, [
            'name' => 'Efectivo',
            'type' => AccountType::Cash,
            'opening_balance' => '1000.0000',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Alimentación',
            'type' => CategoryType::Expense,
            'slug' => 'alimentacion',
        ]);

        $entry = $this->recordExpenseAction->execute($user, [
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => '120.7500',
            'description' => 'Compras de supermercado',
        ]);

        $this->assertSame(MovementType::Expense, $entry->movement_type);
        $this->assertTrue($entry->isBalanced());

        $balance = $this->accountBalanceService->balanceForAccount($account);
        $this->assertSame('879.2500', $balance);

        $metrics = $this->accountBalanceService->consolidatedMetricsForUser($user, CarbonImmutable::now());
        $this->assertSame('120.7500', $metrics['monthly_expenses']);
        $this->assertSame('-120.7500', $metrics['monthly_cash_flow']);
    }

    public function test_recording_transfer_moves_funds_between_accounts_without_affecting_monthly_income_or_expenses(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);
        $source = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Corriente',
            'type' => AccountType::Checking,
            'opening_balance' => '1500.0000',
        ]);

        $destination = $this->createAccountAction->execute($user, [
            'name' => 'Billetera Virtual',
            'type' => AccountType::Virtual,
            'opening_balance' => '100.0000',
        ]);

        $entry = $this->recordTransferAction->execute($user, [
            'source_account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'amount' => '400.0000',
            'description' => 'Recarga de billetera virtual',
        ]);

        $this->assertSame(MovementType::Transfer, $entry->movement_type);
        $this->assertTrue($entry->isBalanced());

        $this->assertSame('1100.0000', $this->accountBalanceService->balanceForAccount($source));
        $this->assertSame('500.0000', $this->accountBalanceService->balanceForAccount($destination));

        $metrics = $this->accountBalanceService->consolidatedMetricsForUser($user, CarbonImmutable::now());
        $this->assertSame('0.0000', $metrics['monthly_income']);
        $this->assertSame('0.0000', $metrics['monthly_expenses']);
        $this->assertSame('1600.0000', $metrics['total_assets']);
    }

    public function test_transfer_fails_if_source_and_destination_are_the_same(): void
    {
        $user = User::factory()->create();
        $account = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Única',
            'type' => AccountType::Checking,
            'opening_balance' => '100.0000',
        ]);

        $this->expectException(FinancialDomainException::class);
        $this->expectExceptionMessage('deben ser distintas');

        $this->recordTransferAction->execute($user, [
            'source_account_id' => $account->id,
            'destination_account_id' => $account->id,
            'amount' => '50.0000',
            'description' => 'Autotransferencia inválida',
        ]);
    }

    public function test_cross_tenant_ownership_is_strictly_blocked(): void
    {
        $owner = User::factory()->create(['currency_code' => 'USD']);
        $intruder = User::factory()->create(['currency_code' => 'USD']);

        $intruderAccount = $this->createAccountAction->execute($intruder, [
            'name' => 'Cuenta Ajena',
            'type' => AccountType::Checking,
            'opening_balance' => '200.0000',
        ]);

        $ownerCategory = Category::query()->create([
            'user_id' => $owner->id,
            'name' => 'Categoría del Dueño',
            'type' => CategoryType::Income,
            'slug' => 'cat-dueno',
        ]);

        $this->expectException(CrossTenantOwnershipException::class);

        $this->recordIncomeAction->execute($owner, [
            'account_id' => $intruderAccount->id,
            'category_id' => $ownerCategory->id,
            'amount' => '100.0000',
            'description' => 'Intento de escritura sobre cuenta ajena',
        ]);
    }
}
