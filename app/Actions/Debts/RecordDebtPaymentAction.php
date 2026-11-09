<?php

declare(strict_types=1);

namespace App\Actions\Debts;

use App\Data\Finance\JournalLineInputData;
use App\Data\Finance\PostJournalEntryData;
use App\Enums\DebtStatus;
use App\Enums\EntryStatus;
use App\Enums\MovementType;
use App\Exceptions\Finance\FinancialDomainException;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Debt\DebtBalanceService;
use App\Services\Finance\JournalPosterService;
use App\Services\Finance\LedgerBootstrapService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RecordDebtPaymentAction
{
    public function __construct(
        private readonly JournalPosterService $journalPoster,
        private readonly LedgerBootstrapService $ledgerBootstrap,
        private readonly DebtBalanceService $debtBalanceService,
    ) {}

    /**
     * @param  array{
     *     debt_id: int,
     *     source_account_id: int,
     *     payment_date?: CarbonImmutable|string|null,
     *     principal_amount?: string|int|null,
     *     interest_amount?: string|int|null,
     *     fee_amount?: string|int|null,
     *     total_amount?: string|int|null,
     *     reference?: string|null,
     *     notes?: string|null,
     *     idempotency_key?: string|null,
     * }  $attributes
     */
    public function execute(User $user, array $attributes): DebtPayment
    {
        $debtId = (int) $attributes['debt_id'];
        $sourceAccountId = (int) $attributes['source_account_id'];

        $principal = DecimalMath::normalize($attributes['principal_amount'] ?? '0.0000');
        $interest = DecimalMath::normalize($attributes['interest_amount'] ?? '0.0000');
        $fee = DecimalMath::normalize($attributes['fee_amount'] ?? '0.0000');

        if (DecimalMath::isNegative($principal) || DecimalMath::isNegative($interest) || DecimalMath::isNegative($fee)) {
            throw new FinancialDomainException('Los importes de capital, interés o comisiones no pueden ser negativos.');
        }

        $calculatedTotal = DecimalMath::add(DecimalMath::add($principal, $interest), $fee);

        if (isset($attributes['total_amount']) && $attributes['total_amount'] !== '') {
            $providedTotal = DecimalMath::normalize($attributes['total_amount']);
            if (! DecimalMath::equals($providedTotal, $calculatedTotal)) {
                throw new FinancialDomainException(
                    "El total especificado [{$providedTotal}] no coincide con la suma de capital, interés y comisiones [{$calculatedTotal}]."
                );
            }
        }

        if (DecimalMath::compare($calculatedTotal, DecimalMath::ZERO) <= 0) {
            throw new FinancialDomainException('El importe total del abono a deuda debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $user,
            $debtId,
            $sourceAccountId,
            $principal,
            $interest,
            $fee,
            $calculatedTotal,
            $attributes
        ): DebtPayment {
            /** @var Debt $debt */
            $debt = Debt::query()
                ->ownedBy($user)
                ->with('liabilityAccount')
                ->lockForUpdate()
                ->findOrFail($debtId);

            /** @var Account $sourceAccount */
            $sourceAccount = Account::query()
                ->ownedBy($user)
                ->lockForUpdate()
                ->findOrFail($sourceAccountId);

            $liabilityAccount = $debt->liabilityAccount;
            if ($liabilityAccount === null) {
                throw new FinancialDomainException('La deuda no tiene una cuenta de pasivo asociada.');
            }

            $currency = strtoupper($debt->currency_code);
            $paymentDate = isset($attributes['payment_date'])
                ? ($attributes['payment_date'] instanceof CarbonImmutable
                    ? $attributes['payment_date']
                    : CarbonImmutable::parse((string) $attributes['payment_date']))
                : CarbonImmutable::now($user->timezone ?: 'UTC');

            $movementType = $debt->isCreditCard()
                ? MovementType::CardPayment
                : MovementType::DebtPayment;

            $lines = [];

            // 1. Salida de dinero de la cuenta de origen (Crédito total)
            $lines[] = new JournalLineInputData(
                accountId: (int) $sourceAccount->getKey(),
                debit: DecimalMath::ZERO,
                credit: $calculatedTotal,
                memo: "Pago a {$debt->name} desde {$sourceAccount->name}",
            );

            // 2. Abono a capital (Débito al pasivo -> reduce la deuda sin duplicar gastos pasados)
            if (DecimalMath::compare($principal, DecimalMath::ZERO) > 0) {
                $lines[] = new JournalLineInputData(
                    accountId: (int) $liabilityAccount->getKey(),
                    debit: $principal,
                    credit: DecimalMath::ZERO,
                    memo: "Abono a capital: {$debt->name}",
                );
            }

            // 3. Intereses (Débito a gastos con categoría del sistema)
            if (DecimalMath::compare($interest, DecimalMath::ZERO) > 0) {
                $interestCategory = $this->ledgerBootstrap->systemInterestCategory($user);
                $systemExpenseAccount = $this->ledgerBootstrap->systemExpenseAccount($user, $currency);

                $lines[] = new JournalLineInputData(
                    accountId: (int) $systemExpenseAccount->getKey(),
                    debit: $interest,
                    credit: DecimalMath::ZERO,
                    categoryId: (int) $interestCategory->getKey(),
                    memo: "Intereses financieros: {$debt->name}",
                );
            }

            // 4. Comisiones (Débito a gastos con categoría del sistema)
            if (DecimalMath::compare($fee, DecimalMath::ZERO) > 0) {
                $feeCategory = $this->ledgerBootstrap->systemFeeCategory($user);
                $systemExpenseAccount = $this->ledgerBootstrap->systemExpenseAccount($user, $currency);

                $lines[] = new JournalLineInputData(
                    accountId: (int) $systemExpenseAccount->getKey(),
                    debit: $fee,
                    credit: DecimalMath::ZERO,
                    categoryId: (int) $feeCategory->getKey(),
                    memo: "Comisiones bancarias: {$debt->name}",
                );
            }

            $idempotencyKey = isset($attributes['idempotency_key']) && trim((string) $attributes['idempotency_key']) !== ''
                ? trim((string) $attributes['idempotency_key'])
                : null;

            if ($idempotencyKey !== null) {
                $existingEntry = JournalEntry::query()
                    ->ownedBy($user)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingEntry !== null) {
                    $existingPayment = DebtPayment::query()
                        ->ownedBy($user)
                        ->where('journal_entry_id', $existingEntry->getKey())
                        ->first();

                    if ($existingPayment !== null) {
                        return $existingPayment;
                    }
                }
            }

            $entry = $this->journalPoster->post($user, new PostJournalEntryData(
                entryDate: $paymentDate,
                movementType: $movementType,
                description: "Abono a {$debt->name} (Capital: {$principal}, Interés: {$interest}, Comisión: {$fee})",
                lines: $lines,
                currencyCode: $currency,
                reference: isset($attributes['reference']) ? trim((string) $attributes['reference']) : "PAGO-{$debt->getKey()}",
                idempotencyKey: $idempotencyKey,
                metadata: [
                    'debt_id' => $debt->getKey(),
                    'principal' => $principal,
                    'interest' => $interest,
                    'fee' => $fee,
                ],
            ));

            $debtPayment = DebtPayment::query()->create([
                'user_id' => $user->getKey(),
                'debt_id' => $debt->getKey(),
                'source_account_id' => $sourceAccount->getKey(),
                'journal_entry_id' => $entry->getKey(),
                'payment_date' => $paymentDate->toDateString(),
                'total_amount' => $calculatedTotal,
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'fee_amount' => $fee,
                'status' => EntryStatus::Posted,
                'reference' => isset($attributes['reference']) ? trim((string) $attributes['reference']) : null,
                'notes' => isset($attributes['notes']) ? trim((string) $attributes['notes']) : null,
            ]);

            // Comprobar si el saldo de capital quedó totalmente liquidado
            $pendingPrincipal = $this->debtBalanceService->pendingPrincipal($debt);
            if (DecimalMath::compare($pendingPrincipal, DecimalMath::ZERO) <= 0) {
                $debt->forceFill(['status' => DebtStatus::PaidOff])->save();
            }

            return $debtPayment->load(['debt', 'sourceAccount', 'journalEntry']);
        });
    }
}
