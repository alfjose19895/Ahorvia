<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Data\Reporting\FinancialReportData;
use App\Enums\EntryStatus;
use App\Enums\LedgerNature;
use App\Models\Category;
use App\Models\JournalLine;
use App\Models\User;
use App\Services\Finance\AccountBalanceService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class FinancialReportingService
{
    public function __construct(
        private readonly AccountBalanceService $accountBalanceService,
    ) {}

    public function generateReport(User $user, int $months = 6, ?CarbonImmutable $referenceDate = null): FinancialReportData
    {
        $months = max(1, min(24, $months));
        $now = ($referenceDate ?? CarbonImmutable::now($user->timezone ?: 'UTC'))->locale('es');
        $currency = strtoupper($user->currency_code ?: 'USD');

        $monthlyLabels = [];
        $monthlyIncomes = [];
        $monthlyExpenses = [];
        $monthlyNetSavings = [];
        $monthlyNetWorth = [];

        $totalIncome = DecimalMath::ZERO;
        $totalExpenses = DecimalMath::ZERO;

        $hasData = false;

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = $now->subMonths($i);
            $monthLabel = ucfirst($monthDate->translatedFormat('M Y'));
            $metrics = $this->accountBalanceService->consolidatedMetricsForUser($user, $monthDate);

            $inc = (float) $metrics['monthly_income'];
            $exp = (float) $metrics['monthly_expenses'];
            $savings = (float) $metrics['monthly_cash_flow'];
            $netWorth = (float) $metrics['net_worth'];

            if ($inc > 0 || $exp > 0 || abs($netWorth) > 0) {
                $hasData = true;
            }

            $monthlyLabels[] = $monthLabel;
            $monthlyIncomes[] = $inc;
            $monthlyExpenses[] = $exp;
            $monthlyNetSavings[] = $savings;
            $monthlyNetWorth[] = $netWorth;

            $totalIncome = DecimalMath::add($totalIncome, $metrics['monthly_income']);
            $totalExpenses = DecimalMath::add($totalExpenses, $metrics['monthly_expenses']);
        }

        $netSavings = DecimalMath::sub($totalIncome, $totalExpenses);

        $savingsRate = 0;
        if (DecimalMath::compare($totalIncome, DecimalMath::ZERO) > 0) {
            $ratio = DecimalMath::div($netSavings, $totalIncome);
            $savingsRate = (int) round((float) DecimalMath::mul($ratio, '100'));
        }

        // Distribución de gastos por categoría en el período completo
        $rangeStart = $now->subMonths($months - 1)->startOfMonth()->toDateString();
        $rangeEnd = $now->endOfMonth()->toDateString();

        [$categoryLabels, $categoryValues, $categoryColors, $categoryTable] = $this->resolveCategoryDistribution(
            user: $user,
            startDate: $rangeStart,
            endDate: $rangeEnd,
            totalPeriodExpenses: $totalExpenses,
        );

        return new FinancialReportData(
            periodMonths: $months,
            currencyCode: $currency,
            totalIncome: $totalIncome,
            totalExpenses: $totalExpenses,
            netSavings: $netSavings,
            savingsRatePercent: $savingsRate,
            monthlyLabels: $monthlyLabels,
            monthlyIncomes: $monthlyIncomes,
            monthlyExpenses: $monthlyExpenses,
            monthlyNetSavings: $monthlyNetSavings,
            monthlyNetWorth: $monthlyNetWorth,
            categoryLabels: $categoryLabels,
            categoryValues: $categoryValues,
            categoryColors: $categoryColors,
            categoryTable: $categoryTable,
            hasData: $hasData,
        );
    }

    /**
     * @return array{
     *     0: list<string>,
     *     1: list<float>,
     *     2: list<string>,
     *     3: list<array{name: string, color: string, total: string, percent: int}>
     * }
     */
    private function resolveCategoryDistribution(
        User $user,
        string $startDate,
        string $endDate,
        string $totalPeriodExpenses,
    ): array {
        $lines = JournalLine::query()
            ->whereHas('journalEntry', function ($query) use ($user, $startDate, $endDate): void {
                $query->where('user_id', $user->getKey())
                    ->where('status', EntryStatus::Posted)
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->whereHas('account', function ($query): void {
                $query->where('ledger_nature', LedgerNature::Expense);
            })
            ->whereNotNull('category_id')
            ->with('category')
            ->get(['category_id', 'debit', 'credit']);

        $totalsByCategory = [];
        $palette = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#14B8A6'];
        $categoriesMap = [];

        foreach ($lines as $line) {
            $catId = (int) $line->category_id;
            $net = DecimalMath::sub((string) $line->debit, (string) $line->credit);

            if (! isset($totalsByCategory[$catId])) {
                $totalsByCategory[$catId] = DecimalMath::ZERO;
                $categoriesMap[$catId] = $line->category;
            }

            $totalsByCategory[$catId] = DecimalMath::add($totalsByCategory[$catId], $net);
        }

        // Filtrar positivos y ordenar de mayor a menor
        $sorted = [];
        foreach ($totalsByCategory as $catId => $amount) {
            if (DecimalMath::compare($amount, DecimalMath::ZERO) > 0) {
                $sorted[$catId] = $amount;
            }
        }

        uasort($sorted, static fn (string $a, string $b): int => DecimalMath::compare($b, $a));

        $labels = [];
        $values = [];
        $colors = [];
        $table = [];
        $colorIdx = 0;

        foreach ($sorted as $catId => $amount) {
            /** @var Category|null $cat */
            $cat = $categoriesMap[$catId] ?? null;
            $name = $cat !== null ? $cat->name : "Categoría #{$catId}";
            $color = ($cat !== null && ! empty($cat->color))
                ? $cat->color
                : $palette[$colorIdx % count($palette)];

            $pct = DecimalMath::compare($totalPeriodExpenses, DecimalMath::ZERO) > 0
                ? (int) round((float) DecimalMath::mul(DecimalMath::div($amount, $totalPeriodExpenses), '100'))
                : 0;

            $labels[] = $name;
            $values[] = (float) $amount;
            $colors[] = $color;
            $table[] = [
                'name' => $name,
                'color' => $color,
                'total' => $amount,
                'percent' => $pct,
            ];

            $colorIdx++;
        }

        return [$labels, $values, $colors, $table];
    }
}
