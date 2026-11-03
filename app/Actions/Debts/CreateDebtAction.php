<?php

declare(strict_types=1);

namespace App\Actions\Debts;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\AccountType;
use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\LedgerNature;
use App\Enums\MovementType;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Account;
use App\Models\Debt;
use App\Models\User;
use App\Services\Finance\JournalPosterService;
use App\Services\Finance\LedgerBootstrapService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreateDebtAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
        private readonly LedgerBootstrapService $ledgerBootstrap,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     type: DebtType,
     *     creditor_name?: string|null,
     *     currency_code?: string|null,
     *     original_principal?: string|int|null,
     *     credit_limit?: string|int|null,
     *     interest_rate_annual?: string|int|null,
     *     minimum_payment?: string|int|null,
     *     start_date?: CarbonImmutable|string|null,
     *     due_date?: CarbonImmutable|string|null,
     *     payment_due_day?: int|null,
     *     cutoff_day?: int|null,
     *     budget_category_id?: int|null,
     *     disbursement_account_id?: int|null,
     *     notes?: string|null,
     *     idempotency_key?: string|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): Debt
    {
        /** @var DebtType $type */
        $type = $attributes['type'];
        $currency = strtoupper($attributes['currency_code'] ?? ($user->currency_code ?: 'USD'));

        $principalRaw = $attributes['original_principal'] ?? '0.0000';
        $originalPrincipal = DecimalMath::normalize($principalRaw !== '' ? (string) $principalRaw : '0.0000');

        if (DecimalMath::isNegative($originalPrincipal)) {
            throw new FinancialDomainException('El importe principal de la deuda no puede ser negativo.');
        }

        $creditLimit = null;
        if (isset($attributes['credit_limit']) && $attributes['credit_limit'] !== '') {
            $creditLimit = DecimalMath::normalize($attributes['credit_limit']);
        }

        $interestRate = '0.0000';
        if (isset($attributes['interest_rate_annual']) && $attributes['interest_rate_annual'] !== '') {
            $interestRate = DecimalMath::normalize($attributes['interest_rate_annual']);
        }

        $minPayment = '0.0000';
        if (isset($attributes['minimum_payment']) && $attributes['minimum_payment'] !== '') {
            $minPayment = DecimalMath::normalize($attributes['minimum_payment']);
        }

        $startDate = isset($attributes['start_date'])
            ? ($attributes['start_date'] instanceof CarbonImmutable ? $attributes['start_date']->toDateString() : (string) $attributes['start_date'])
            : now()->toDateString();

        $dueDate = isset($attributes['due_date'])
            ? ($attributes['due_date'] instanceof CarbonImmutable ? $attributes['due_date']->toDateString() : (string) $attributes['due_date'])
            : null;

        return DB::transaction(function () use (
            $user,
            $type,
            $currency,
            $originalPrincipal,
            $creditLimit,
            $interestRate,
            $minPayment,
            $startDate,
            $dueDate,
            $attributes
        ): Debt {
            $accountType = $type === DebtType::CreditCard ? AccountType::CreditCard : AccountType::Loan;

            $liabilityAccount = Account::query()->create([
                'user_id' => $user->getKey(),
                'name' => trim($attributes['name']).' (Pasivo)',
                'type' => $accountType,
                'ledger_nature' => LedgerNature::Liability,
                'currency_code' => $currency,
                'institution_name' => isset($attributes['creditor_name']) ? trim((string) $attributes['creditor_name']) : null,
                'credit_limit' => $creditLimit,
                'interest_rate_annual' => $interestRate,
                'billing_cutoff_day' => $attributes['cutoff_day'] ?? null,
                'payment_due_day' => $attributes['payment_due_day'] ?? null,
                'is_active' => true,
                'is_system' => false,
                'include_in_net_worth' => true,
            ]);

            $debt = Debt::query()->create([
                'user_id' => $user->getKey(),
                'liability_account_id' => $liabilityAccount->getKey(),
                'budget_category_id' => $attributes['budget_category_id'] ?? null,
                'name' => trim($attributes['name']),
                'type' => $type,
                'status' => DebtStatus::Active,
                'creditor_name' => isset($attributes['creditor_name']) ? trim((string) $attributes['creditor_name']) : null,
                'currency_code' => $currency,
                'original_principal' => $originalPrincipal,
                'credit_limit' => $creditLimit,
                'interest_rate_annual' => $interestRate,
                'minimum_payment' => $minPayment,
                'start_date' => $startDate,
                'due_date' => $dueDate,
                'payment_due_day' => $attributes['payment_due_day'] ?? null,
                'cutoff_day' => $attributes['cutoff_day'] ?? null,
                'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : null,
            ]);

            if (DecimalMath::compare($originalPrincipal, DecimalMath::ZERO) > 0) {
                if (isset($attributes['disbursement_account_id'])) {
                    $disbursementAccount = Account::query()
                        ->ownedBy($user)
                        ->findOrFail($attributes['disbursement_account_id']);

                    $lines = [
                        new JournalLineInputData(
                            accountId: (int) $disbursementAccount->getKey(),
                            debit: $originalPrincipal,
                            credit: DecimalMath::ZERO,
                            memo: "Desembolso de deuda {$debt->name}",
                        ),
                        new JournalLineInputData(
                            accountId: (int) $liabilityAccount->getKey(),
                            debit: DecimalMath::ZERO,
                            credit: $originalPrincipal,
                            memo: "Reconocimiento de obligación por desembolso: {$debt->name}",
                        ),
                    ];

                    $movementType = MovementType::LoanDisbursement;
                    $desc = "Desembolso de {$debt->name} en {$disbursementAccount->name}";
                } else {
                    $equityAccount = $this->ledgerBootstrap->systemEquityAccount($user, $currency);

                    $lines = [
                        new JournalLineInputData(
                            accountId: (int) $equityAccount->getKey(),
                            debit: $originalPrincipal,
                            credit: DecimalMath::ZERO,
                            memo: "Contrapartida de saldo inicial pasivo: {$debt->name}",
                        ),
                        new JournalLineInputData(
                            accountId: (int) $liabilityAccount->getKey(),
                            debit: DecimalMath::ZERO,
                            credit: $originalPrincipal,
                            memo: "Saldo inicial de deuda: {$debt->name}",
                        ),
                    ];

                    $movementType = MovementType::OpeningBalance;
                    $desc = "Saldo inicial de deuda {$debt->name}";
                }

                $idempotencyKey = isset($attributes['idempotency_key']) && trim((string) $attributes['idempotency_key']) !== ''
                    ? 'open_debt_'.$debt->getKey().'_'.trim((string) $attributes['idempotency_key'])
                    : 'open_debt_'.$debt->getKey();

                $this->journalPoster->post($user, new PostJournalEntryData(
                    entryDate: CarbonImmutable::parse($startDate),
                    movementType: $movementType,
                    description: $desc,
                    lines: $lines,
                    currencyCode: $currency,
                    reference: "DEBT-{$debt->getKey()}",
                    idempotencyKey: $idempotencyKey,
                    metadata: ['debt_id' => $debt->getKey()],
                ));
            }

            return $debt->load('liabilityAccount');
        });
    }
}
