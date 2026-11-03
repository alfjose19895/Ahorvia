<?php

declare(strict_types=1);

namespace App\Data\Budget;

final readonly class BudgetExecutionData
{
    /**
     * @param  numeric-string  $totalLimit
     * @param  numeric-string  $totalSpent
     * @param  numeric-string  $remainingAmount
     * @param  list<array{
     *     category_id: int,
     *     category_name: string,
     *     limit_amount: numeric-string,
     *     spent_amount: numeric-string,
     *     remaining_amount: numeric-string,
     *     consumption_percent: int,
     *     alert_threshold: int
     * }>  $lines
     */
    public function __construct(
        public int $budgetId,
        public int $year,
        public int $month,
        public string $currencyCode,
        public string $totalLimit,
        public string $totalSpent,
        public string $remainingAmount,
        public int $consumptionPercent,
        public int $highestThresholdReached,
        public array $lines,
    ) {}
}
