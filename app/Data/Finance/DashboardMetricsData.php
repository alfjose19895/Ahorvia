<?php

declare(strict_types=1);

namespace App\Data\Finance;

use App\Support\MoneyFormatter;

final readonly class DashboardMetricsData
{
    /**
     * @param  numeric-string  $availableCash
     * @param  numeric-string  $totalAssets
     * @param  numeric-string  $totalLiabilities
     * @param  numeric-string  $netWorth
     * @param  numeric-string  $monthlyIncome
     * @param  numeric-string  $monthlyExpenses
     * @param  numeric-string  $monthlyCashFlow
     * @param  numeric-string  $accumulatedSavings
     * @param  list<array{name: string, target: string, current: string, progress: int, target_date: string}>  $activeGoals
     * @param  list<array{title: string, due_date: string, amount: string, type: string}>  $upcomingMaturities
     * @param  list<array{level: string, title: string, message: string}>  $financialAlerts
     * @param  list<array{category: string, amount: float, color: string}>  $expenseDistribution
     * @param  list<array{month: string, income: float, expense: float, savings: float}>  $monthlyEvolution
     */
    public function __construct(
        public string $currencyCode,
        public string $periodLabel,
        public string $availableCash,
        public string $totalAssets,
        public string $totalLiabilities,
        public string $netWorth,
        public string $monthlyIncome,
        public string $monthlyExpenses,
        public string $monthlyCashFlow,
        public string $accumulatedSavings,
        public int $activeGoalsCount,
        public int $overallGoalsCompletionPercent,
        public bool $hasAccounts,
        public bool $hasTransactions,
        public array $activeGoals = [],
        public array $upcomingMaturities = [],
        public array $financialAlerts = [],
        public array $expenseDistribution = [],
        public array $monthlyEvolution = [],
    ) {}

    public function format(string $amount): string
    {
        return MoneyFormatter::format($amount, $this->currencyCode);
    }
}
