<?php

declare(strict_types=1);

namespace App\Services\Debt;

use App\Enums\EntryStatus;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Services\Finance\AccountBalanceService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class DebtBalanceService
{
    public function __construct(
        private readonly AccountBalanceService $accountBalanceService,
    ) {}

    /**
     * Obtiene el saldo pendiente de capital directamente desde la cuenta contable de pasivo asociada.
     *
     * @return numeric-string
     */
    public function pendingPrincipal(Debt $debt, ?CarbonImmutable $asOfDate = null): string
    {
        $debt->loadMissing('liabilityAccount');

        if ($debt->liabilityAccount === null) {
            return DecimalMath::ZERO;
        }

        $balance = $this->accountBalanceService->balanceForAccount($debt->liabilityAccount, $asOfDate);

        return DecimalMath::isNegative($balance) ? DecimalMath::ZERO : $balance;
    }

    /**
     * Cupo disponible para tarjetas de crédito según su límite y saldo contable actual.
     *
     * @return numeric-string
     */
    public function availableCredit(Debt $debt): string
    {
        if (! $debt->isCreditCard() || $debt->credit_limit === null) {
            return DecimalMath::ZERO;
        }

        return DecimalMath::sub((string) $debt->credit_limit, $this->pendingPrincipal($debt));
    }

    /**
     * Acumulado histórico de pagos confirmados separando capital, intereses y comisiones.
     *
     * @return array{
     *     principal_paid: numeric-string,
     *     interest_paid: numeric-string,
     *     fees_paid: numeric-string,
     *     total_paid: numeric-string
     * }
     */
    public function paymentBreakdownTotals(Debt $debt): array
    {
        $payments = DebtPayment::query()
            ->ownedBy($debt->user_id)
            ->where('debt_id', $debt->getKey())
            ->where('status', EntryStatus::Posted)
            ->get(['principal_amount', 'interest_amount', 'fee_amount', 'total_amount']);

        $principalPaid = DecimalMath::ZERO;
        $interestPaid = DecimalMath::ZERO;
        $feesPaid = DecimalMath::ZERO;
        $totalPaid = DecimalMath::ZERO;

        foreach ($payments as $payment) {
            $principalPaid = DecimalMath::add($principalPaid, (string) $payment->principal_amount);
            $interestPaid = DecimalMath::add($interestPaid, (string) $payment->interest_amount);
            $feesPaid = DecimalMath::add($feesPaid, (string) $payment->fee_amount);
            $totalPaid = DecimalMath::add($totalPaid, (string) $payment->total_amount);
        }

        return [
            'principal_paid' => $principalPaid,
            'interest_paid' => $interestPaid,
            'fees_paid' => $feesPaid,
            'total_paid' => $totalPaid,
        ];
    }
}
