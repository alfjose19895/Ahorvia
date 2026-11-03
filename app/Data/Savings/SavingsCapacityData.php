<?php

declare(strict_types=1);

namespace App\Data\Savings;

final readonly class SavingsCapacityData
{
    /**
     * @param  numeric-string  $expectedMonthlyIncome
     * @param  numeric-string  $budgetedExpenses
     * @param  numeric-string  $unbudgetedObligations
     * @param  numeric-string  $totalCommitments
     * @param  numeric-string  $grossSavingsCapacity
     * @param  numeric-string  $activeGoalsMonthlyDemand
     * @param  numeric-string  $netUnallocatedCapacity
     */
    public function __construct(
        public string $currencyCode,
        public string $expectedMonthlyIncome,
        public string $budgetedExpenses,
        public string $unbudgetedObligations,
        public string $totalCommitments,
        public string $grossSavingsCapacity,
        public string $activeGoalsMonthlyDemand,
        public string $netUnallocatedCapacity,
        public bool $isSustainable,
    ) {}
}
