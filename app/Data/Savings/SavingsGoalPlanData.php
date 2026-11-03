<?php

declare(strict_types=1);

namespace App\Data\Savings;

use App\Enums\SavingsGoalStatus;
use App\Enums\SavingsMode;

final readonly class SavingsGoalPlanData
{
    /**
     * @param  numeric-string  $targetAmount
     * @param  numeric-string  $recognizedSavings
     * @param  numeric-string  $pendingSavings
     * @param  numeric-string  $recommendedMonthlyQuota
     * @param  numeric-string  $originalFixedMonthlyQuota
     */
    public function __construct(
        public int $goalId,
        public string $goalName,
        public SavingsMode $mode,
        public SavingsGoalStatus $status,
        public string $currencyCode,
        public string $targetAmount,
        public string $recognizedSavings,
        public string $pendingSavings,
        public int $remainingMonths,
        public string $recommendedMonthlyQuota,
        public string $originalFixedMonthlyQuota,
        public int $completionPercentage,
        public string $targetDate,
        public bool $isCompleted,
        public bool $requiresAdjustment,
    ) {}
}
