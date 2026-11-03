<?php

declare(strict_types=1);

namespace App\Data\Finance;

use App\Exceptions\Finance\UnbalancedJournalEntryException;
use App\Support\DecimalMath;

final readonly class JournalLineInputData
{
    /** @var numeric-string */
    public string $debit;

    /** @var numeric-string */
    public string $credit;

    public function __construct(
        public int $accountId,
        string|int $debit = '0.0000',
        string|int $credit = '0.0000',
        public ?int $categoryId = null,
        public ?string $memo = null,
    ) {
        $normalizedDebit = DecimalMath::normalize($debit);
        $normalizedCredit = DecimalMath::normalize($credit);

        if (DecimalMath::isNegative($normalizedDebit) || DecimalMath::isNegative($normalizedCredit)) {
            throw new UnbalancedJournalEntryException('Los débitos y créditos no pueden contener importes negativos.');
        }

        $hasDebit = DecimalMath::compare($normalizedDebit, DecimalMath::ZERO) > 0;
        $hasCredit = DecimalMath::compare($normalizedCredit, DecimalMath::ZERO) > 0;

        if ($hasDebit === $hasCredit) {
            throw new UnbalancedJournalEntryException(
                'Cada línea contable debe registrar exclusivamente un débito mayor a cero o un crédito mayor a cero.'
            );
        }

        $this->debit = $normalizedDebit;
        $this->credit = $normalizedCredit;
    }
}
