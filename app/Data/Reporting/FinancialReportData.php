<?php

declare(strict_types=1);

namespace App\Data\Reporting;

final readonly class FinancialReportData
{
    /**
     * @param  numeric-string  $totalIncome
     * @param  numeric-string  $totalExpenses
     * @param  numeric-string  $netSavings
     * @param  list<string>  $monthlyLabels
     * @param  list<float>  $monthlyIncomes
     * @param  list<float>  $monthlyExpenses
     * @param  list<float>  $monthlyNetSavings
     * @param  list<float>  $monthlyNetWorth
     * @param  list<string>  $categoryLabels
     * @param  list<float>  $categoryValues
     * @param  list<string>  $categoryColors
     * @param  list<array{name: string, color: string, total: string, percent: int}>  $categoryTable
     */
    public function __construct(
        public int $periodMonths,
        public string $currencyCode,
        public string $totalIncome,
        public string $totalExpenses,
        public string $netSavings,
        public int $savingsRatePercent,
        public array $monthlyLabels,
        public array $monthlyIncomes,
        public array $monthlyExpenses,
        public array $monthlyNetSavings,
        public array $monthlyNetWorth,
        public array $categoryLabels,
        public array $categoryValues,
        public array $categoryColors,
        public array $categoryTable,
        public bool $hasData,
    ) {}
}
