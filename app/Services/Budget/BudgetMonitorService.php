<?php

declare(strict_types=1);

namespace App\Services\Budget;

use App\Data\Budget\BudgetExecutionData;
use App\Enums\EntryStatus;
use App\Enums\LedgerNature;
use App\Models\Budget;
use App\Models\JournalLine;
use App\Models\User;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class BudgetMonitorService
{
    public function evaluateCurrentMonth(User $user, ?CarbonImmutable $referenceDate = null): ?BudgetExecutionData
    {
        $date = $referenceDate ?? CarbonImmutable::now($user->timezone ?: 'UTC');
        $currency = strtoupper($user->currency_code ?: 'USD');

        $budget = Budget::query()
            ->ownedBy($user)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->where('currency_code', $currency)
            ->where('is_active', true)
            ->with(['lines.category'])
            ->first();

        if ($budget === null) {
            return null;
        }

        return $this->evaluate($budget);
    }

    public function evaluate(Budget $budget): BudgetExecutionData
    {
        $budget->loadMissing(['lines.category']);

        $periodStart = CarbonImmutable::create($budget->year, $budget->month, 1)->startOfMonth()->toDateString();
        $periodEnd = CarbonImmutable::create($budget->year, $budget->month, 1)->endOfMonth()->toDateString();

        $spentByCategory = $this->spentByCategoryForPeriod(
            userId: (int) $budget->user_id,
            currencyCode: strtoupper($budget->currency_code),
            fromDate: $periodStart,
            toDate: $periodEnd,
        );

        $totalLimit = DecimalMath::ZERO;
        $totalSpent = DecimalMath::ZERO;
        $highestThreshold = 0;
        $evaluatedLines = [];

        foreach ($budget->lines as $line) {
            $categoryId = (int) $line->category_id;
            $limit = DecimalMath::normalize((string) $line->limit_amount);
            $spent = $spentByCategory[$categoryId] ?? DecimalMath::ZERO;
            $remaining = DecimalMath::sub($limit, $spent);

            $consumptionPercent = $this->calculatePercentage($spent, $limit);
            $threshold = $this->resolveThreshold($spent, $limit);

            if ($threshold > $highestThreshold) {
                $highestThreshold = $threshold;
            }

            $totalLimit = DecimalMath::add($totalLimit, $limit);
            $totalSpent = DecimalMath::add($totalSpent, $spent);

            $evaluatedLines[] = [
                'category_id' => $categoryId,
                'category_name' => $line->category !== null ? $line->category->name : "Categoría #{$categoryId}",
                'limit_amount' => $limit,
                'spent_amount' => $spent,
                'remaining_amount' => $remaining,
                'consumption_percent' => $consumptionPercent,
                'alert_threshold' => $threshold,
            ];
        }

        if (DecimalMath::compare((string) $budget->total_limit, $totalLimit) > 0) {
            $totalLimit = DecimalMath::normalize((string) $budget->total_limit);
        }

        $overallPercent = $this->calculatePercentage($totalSpent, $totalLimit);
        $overallThreshold = $this->resolveThreshold($totalSpent, $totalLimit);

        if ($overallThreshold > $highestThreshold) {
            $highestThreshold = $overallThreshold;
        }

        return new BudgetExecutionData(
            budgetId: (int) $budget->getKey(),
            year: (int) $budget->year,
            month: (int) $budget->month,
            currencyCode: strtoupper($budget->currency_code),
            totalLimit: $totalLimit,
            totalSpent: $totalSpent,
            remainingAmount: DecimalMath::sub($totalLimit, $totalSpent),
            consumptionPercent: $overallPercent,
            highestThresholdReached: $highestThreshold,
            lines: $evaluatedLines,
        );
    }

    /**
     * Calcula el gasto neto confirmado por categoría (débitos - créditos en cuentas de naturaleza gasto).
     * Los asientos reversados se anulan automáticamente sin alterar el historial.
     *
     * @return array<int, numeric-string>
     */
    public function spentByCategoryForPeriod(
        int $userId,
        string $currencyCode,
        string $fromDate,
        string $toDate,
    ): array {
        $rows = JournalLine::query()
            ->select([
                'journal_lines.category_id',
                'journal_lines.debit',
                'journal_lines.credit',
            ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journal_lines.user_id', $userId)
            ->whereNotNull('journal_lines.category_id')
            ->where('accounts.ledger_nature', LedgerNature::Expense->value)
            ->where('accounts.currency_code', $currencyCode)
            ->whereIn('journal_entries.status', [EntryStatus::Posted->value, EntryStatus::Reversed->value])
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->get();

        /** @var array<int, numeric-string> $totals */
        $totals = [];

        foreach ($rows as $row) {
            $categoryId = (int) $row->category_id;
            $current = $totals[$categoryId] ?? DecimalMath::ZERO;
            $netLine = DecimalMath::sub((string) $row->debit, (string) $row->credit);
            $totals[$categoryId] = DecimalMath::add($current, $netLine);
        }

        foreach ($totals as $categoryId => $amount) {
            if (DecimalMath::isNegative($amount)) {
                $totals[$categoryId] = DecimalMath::ZERO;
            }
        }

        return $totals;
    }

    private function calculatePercentage(string $spent, string $limit): int
    {
        if (DecimalMath::compare($limit, DecimalMath::ZERO) <= 0) {
            return DecimalMath::compare($spent, DecimalMath::ZERO) > 0 ? 100 : 0;
        }

        $scaled = DecimalMath::mul($spent, 100);

        return max(0, (int) bcdiv($scaled, DecimalMath::normalize($limit), 0));
    }

    private function resolveThreshold(string $spent, string $limit): int
    {
        if (DecimalMath::compare($limit, DecimalMath::ZERO) <= 0) {
            return DecimalMath::compare($spent, DecimalMath::ZERO) > 0 ? 100 : 0;
        }

        if (DecimalMath::compare($spent, $limit) >= 0) {
            return 100;
        }

        if (DecimalMath::compare($spent, DecimalMath::mul($limit, '0.9000')) >= 0) {
            return 90;
        }

        if (DecimalMath::compare($spent, DecimalMath::mul($limit, '0.8000')) >= 0) {
            return 80;
        }

        return 0;
    }
}
