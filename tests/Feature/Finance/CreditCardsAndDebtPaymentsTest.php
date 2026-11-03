<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Accounts\CreateAccountAction;
use App\Actions\Debts\CreateDebtAction;
use App\Actions\Debts\RecordDebtPaymentAction;
use App\Actions\Transactions\RecordExpenseAction;
use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\MovementType;
use App\Models\Category;
use App\Models\User;
use App\Services\Debt\DebtBalanceService;
use App\Services\Finance\AccountBalanceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardsAndDebtPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private CreateAccountAction $createAccountAction;

    private CreateDebtAction $createDebtAction;

    private RecordExpenseAction $recordExpenseAction;

    private RecordDebtPaymentAction $recordDebtPaymentAction;

    private AccountBalanceService $accountBalanceService;

    private DebtBalanceService $debtBalanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAccountAction = $this->app->make(CreateAccountAction::class);
        $this->createDebtAction = $this->app->make(CreateDebtAction::class);
        $this->recordExpenseAction = $this->app->make(RecordExpenseAction::class);
        $this->recordDebtPaymentAction = $this->app->make(RecordDebtPaymentAction::class);
        $this->accountBalanceService = $this->app->make(AccountBalanceService::class);
        $this->debtBalanceService = $this->app->make(DebtBalanceService::class);
    }

    public function test_credit_card_purchase_records_expense_and_liability_without_double_counting_upon_payment(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);

        $bankAccount = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Ahorros Principal',
            'type' => AccountType::Savings,
            'opening_balance' => '3000.0000',
        ]);

        $cardDebt = $this->createDebtAction->execute($user, [
            'name' => 'Mastercard Platinum',
            'type' => DebtType::CreditCard,
            'credit_limit' => '4000.0000',
            'original_principal' => '0.0000',
        ]);

        $cardAccount = $cardDebt->liabilityAccount;
        $this->assertNotNull($cardAccount);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Electrónica y Software',
            'type' => CategoryType::Expense,
            'slug' => 'electronica',
        ]);

        // Paso 1: Comprar con la tarjeta de crédito $650.00
        $purchaseEntry = $this->recordExpenseAction->execute($user, [
            'account_id' => $cardAccount->id,
            'category_id' => $category->id,
            'amount' => '650.0000',
            'description' => 'Licencia de software profesional',
        ]);

        $this->assertSame(MovementType::CardPurchase, $purchaseEntry->movement_type);

        // El saldo del pasivo de la tarjeta debe ser 650.00 y el cupo disponible 3350.00
        $this->assertSame('650.0000', $this->debtBalanceService->pendingPrincipal($cardDebt));
        $this->assertSame('3350.0000', $this->debtBalanceService->availableCredit($cardDebt));

        // En el mes actual el gasto es $650.00
        $metricsAfterPurchase = $this->accountBalanceService->consolidatedMetricsForUser($user, CarbonImmutable::now());
        $this->assertSame('650.0000', $metricsAfterPurchase['monthly_expenses']);
        $this->assertSame('3000.0000', $metricsAfterPurchase['total_assets']);
        $this->assertSame('650.0000', $metricsAfterPurchase['total_liabilities']);
        $this->assertSame('2350.0000', $metricsAfterPurchase['net_worth']);

        // Paso 2: Pagar la tarjeta de crédito desde la cuenta de ahorros
        // Total: 665.00 (Principal: 650.00, Interés: 10.00, Comisión: 5.00)
        $payment = $this->recordDebtPaymentAction->execute($user, [
            'debt_id' => $cardDebt->id,
            'source_account_id' => $bankAccount->id,
            'principal_amount' => '650.0000',
            'interest_amount' => '10.0000',
            'fee_amount' => '5.0000',
            'total_amount' => '665.0000',
            'reference' => 'PAGO-TC-001',
        ]);

        $this->assertSame('665.0000', (string) $payment->total_amount);
        $this->assertSame('650.0000', (string) $payment->principal_amount);

        // La tarjeta quedó en saldo $0.0000 y el cupo se restableció a 4000.0000
        $this->assertSame('0.0000', $this->debtBalanceService->pendingPrincipal($cardDebt));
        $this->assertSame('4000.0000', $this->debtBalanceService->availableCredit($cardDebt));

        // La cuenta bancaria se debitó exactamente por los 665.00 (3000 - 665 = 2335)
        $this->assertSame('2335.0000', $this->accountBalanceService->balanceForAccount($bankAccount));

        // REGLA CLAVE DE NO DOBLE CONTEO:
        // Los gastos mensuales totales deben ser 665.00 (650 de la compra original + 10 de interés + 5 de comisión).
        // ¡La compra original NO se contó dos veces al pagar la tarjeta!
        $metricsAfterPayment = $this->accountBalanceService->consolidatedMetricsForUser($user, CarbonImmutable::now());
        $this->assertSame('665.0000', $metricsAfterPayment['monthly_expenses']);
        $this->assertSame('2335.0000', $metricsAfterPayment['total_assets']);
        $this->assertSame('0.0000', $metricsAfterPayment['total_liabilities']);
        $this->assertSame('2335.0000', $metricsAfterPayment['net_worth']);
    }

    public function test_loan_disbursement_and_full_payoff_closes_debt(): void
    {
        $user = User::factory()->create(['currency_code' => 'USD']);

        $checking = $this->createAccountAction->execute($user, [
            'name' => 'Cuenta Depósito',
            'type' => AccountType::Checking,
            'opening_balance' => '100.0000',
        ]);

        $loan = $this->createDebtAction->execute($user, [
            'name' => 'Préstamo de Vehículo',
            'type' => DebtType::BankLoan,
            'original_principal' => '5000.0000',
            'disbursement_account_id' => $checking->id,
        ]);

        $this->assertSame(DebtStatus::Active, $loan->status);
        $this->assertSame('5000.0000', $this->debtBalanceService->pendingPrincipal($loan));
        // La cuenta de depósito aumentó con el desembolso: 100 + 5000 = 5100
        $this->assertSame('5100.0000', $this->accountBalanceService->balanceForAccount($checking));

        // Abonar la totalidad de la deuda
        $this->recordDebtPaymentAction->execute($user, [
            'debt_id' => $loan->id,
            'source_account_id' => $checking->id,
            'principal_amount' => '5000.0000',
            'interest_amount' => '0.0000',
            'fee_amount' => '0.0000',
            'total_amount' => '5000.0000',
        ]);

        $loan->refresh();
        $this->assertSame(DebtStatus::PaidOff, $loan->status);
        $this->assertSame('0.0000', $this->debtBalanceService->pendingPrincipal($loan));
        $this->assertSame('100.0000', $this->accountBalanceService->balanceForAccount($checking));
    }
}
