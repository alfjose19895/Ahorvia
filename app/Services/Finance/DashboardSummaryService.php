<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Data\Finance\DashboardMetricsData;
use App\Enums\EntryStatus;
use App\Enums\LedgerNature;
use App\Enums\ObligationStatus;
use App\Enums\SavingsGoalStatus;
use App\Models\Account;
use App\Models\Category;
use App\Models\JournalEntry;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\ScheduledObligation;
use App\Models\User;
use App\Services\Budget\BudgetMonitorService;
use App\Services\Savings\SavingsPlannerService;
use App\Support\DecimalMath;
use App\Support\MoneyFormatter;
use Carbon\CarbonImmutable;

class DashboardSummaryService
{
    public function __construct(
        private readonly AccountBalanceService $accountBalanceService,
        private readonly BudgetMonitorService $budgetMonitorService,
        private readonly SavingsPlannerService $savingsPlannerService,
    ) {}

    /**
     * Construye el resumen financiero consolidado del usuario autenticado
     * derivando todos los saldos e indicadores desde asientos contables reales.
     */
    public function summarizeForUser(User $user, ?CarbonImmutable $referenceDate = null): DashboardMetricsData
    {
        $date = ($referenceDate ?? CarbonImmutable::now($user->timezone ?: 'UTC'))->locale('es');
        $periodLabel = ucfirst($date->translatedFormat('F Y'));
        $currency = strtoupper($user->currency_code ?: (string) config('ahorvia.finance.default_currency', 'USD'));

        $hasAccounts = Account::query()
            ->ownedBy($user)
            ->userFacing()
            ->where('is_active', true)
            ->exists();

        $hasTransactions = JournalEntry::query()
            ->ownedBy($user)
            ->where('status', EntryStatus::Posted)
            ->exists();

        $consolidated = $this->accountBalanceService->consolidatedMetricsForUser($user, $date);

        [$activeGoalsCount, $overallCompletion, $activeGoalsList, $goalAlerts] = $this->buildGoalsSummary($user, $currency, $date);
        $upcomingMaturities = $this->buildUpcomingMaturities($user, $currency, $date);
        $budgetAlerts = $this->buildBudgetAlerts($user, $date);
        $expenseDistribution = $this->buildExpenseDistribution($user, $currency, $date);
        $monthlyEvolution = $this->buildSixMonthTimeline($user, $currency, $date, $hasTransactions);

        return new DashboardMetricsData(
            currencyCode: $currency,
            periodLabel: $periodLabel,
            availableCash: $consolidated['available_cash'],
            totalAssets: $consolidated['total_assets'],
            totalLiabilities: $consolidated['total_liabilities'],
            netWorth: $consolidated['net_worth'],
            monthlyIncome: $consolidated['monthly_income'],
            monthlyExpenses: $consolidated['monthly_expenses'],
            monthlyCashFlow: $consolidated['monthly_cash_flow'],
            accumulatedSavings: $consolidated['accumulated_savings'],
            activeGoalsCount: $activeGoalsCount,
            overallGoalsCompletionPercent: $overallCompletion,
            hasAccounts: $hasAccounts,
            hasTransactions: $hasTransactions,
            activeGoals: $activeGoalsList,
            upcomingMaturities: $upcomingMaturities,
            financialAlerts: array_merge($budgetAlerts, $goalAlerts),
            expenseDistribution: $expenseDistribution,
            monthlyEvolution: $monthlyEvolution,
        );
    }

    /**
     * @return array{
     *     0: int,
     *     1: int,
     *     2: list<array{name: string, target: string, current: string, progress: int, target_date: string}>,
     *     3: list<array{level: string, title: string, message: string}>
     * }
     */
    private function buildGoalsSummary(User $user, string $currency, CarbonImmutable $date): array
    {
        $goals = SavingsGoal::query()
            ->ownedBy($user)
            ->where('currency_code', $currency)
            ->where('status', SavingsGoalStatus::Active)
            ->orderBy('target_date')
            ->get();

        if ($goals->isEmpty()) {
            return [0, 0, [], []];
        }

        $totalTarget = DecimalMath::ZERO;
        $totalRecognized = DecimalMath::ZERO;
        $goalItems = [];
        $alerts = [];

        foreach ($goals as $goal) {
            $plan = $this->savingsPlannerService->analyzeGoal($goal, $date);
            $totalTarget = DecimalMath::add($totalTarget, $plan->targetAmount);
            $totalRecognized = DecimalMath::add($totalRecognized, $plan->recognizedSavings);

            $goalItems[] = [
                'name' => $plan->goalName,
                'target' => MoneyFormatter::format($plan->targetAmount, $currency),
                'current' => MoneyFormatter::format($plan->recognizedSavings, $currency),
                'progress' => $plan->completionPercentage,
                'target_date' => $plan->targetDate,
            ];

            if ($plan->requiresAdjustment) {
                $formattedQuota = MoneyFormatter::format($plan->recommendedMonthlyQuota, $currency);
                $alerts[] = [
                    'level' => 'warning',
                    'title' => "Ajuste sugerido: {$plan->goalName}",
                    'message' => "Cuota recalculada a {$formattedQuota}/mes para cumplir la meta a tiempo.",
                ];
            }
        }

        $overallCompletion = DecimalMath::compare($totalTarget, DecimalMath::ZERO) > 0
            ? min(100, max(0, (int) bcdiv(DecimalMath::mul($totalRecognized, 100), $totalTarget, 0)))
            : 0;

        return [$goals->count(), $overallCompletion, $goalItems, $alerts];
    }

    /**
     * @return list<array{title: string, due_date: string, amount: string, type: string}>
     */
    private function buildUpcomingMaturities(User $user, string $currency, CarbonImmutable $date): array
    {
        $obligations = ScheduledObligation::query()
            ->ownedBy($user)
            ->where('currency_code', $currency)
            ->whereIn('status', [ObligationStatus::Pending, ObligationStatus::Overdue])
            ->where('due_date', '>=', $date->subDays(7)->toDateString())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $items = [];

        foreach ($obligations as $obligation) {
            $items[] = [
                'title' => $obligation->title,
                'due_date' => $obligation->due_date->toDateString(),
                'amount' => MoneyFormatter::format((string) $obligation->amount, $currency),
                'type' => $obligation->status->label(),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{level: string, title: string, message: string}>
     */
    private function buildBudgetAlerts(User $user, CarbonImmutable $date): array
    {
        $execution = $this->budgetMonitorService->evaluateCurrentMonth($user, $date);

        if ($execution === null) {
            return [];
        }

        $alerts = [];

        foreach ($execution->lines as $line) {
            if ($line['alert_threshold'] >= 80) {
                $level = $line['alert_threshold'] >= 100 ? 'danger' : 'warning';
                $alerts[] = [
                    'level' => $level,
                    'title' => "Presupuesto al {$line['consumption_percent']}%: {$line['category_name']}",
                    'message' => "Umbral del {$line['alert_threshold']}% alcanzado en el periodo actual.",
                ];
            }
        }

        return $alerts;
    }

    /**
     * @return list<array{category: string, amount: float, color: string}>
     */
    private function buildExpenseDistribution(User $user, string $currency, CarbonImmutable $date): array
    {
        $spentByCategory = $this->budgetMonitorService->spentByCategoryForPeriod(
            userId: (int) $user->getKey(),
            currencyCode: $currency,
            fromDate: $date->startOfMonth()->toDateString(),
            toDate: $date->endOfMonth()->toDateString(),
        );

        if ($spentByCategory === []) {
            return [];
        }

        $categories = Category::query()
            ->ownedBy($user)
            ->whereIn('id', array_keys($spentByCategory))
            ->get()
            ->keyBy('id');

        $distribution = [];

        foreach ($spentByCategory as $categoryId => $amount) {
            if (DecimalMath::compare($amount, DecimalMath::ZERO) <= 0) {
                continue;
            }

            $category = $categories->get($categoryId);
            $distribution[] = [
                'category' => $category !== null ? $category->name : "Categoría #{$categoryId}",
                'amount' => (float) bcadd($amount, '0', 2),
                'color' => $category?->color ?: '#10B981',
            ];
        }

        return $distribution;
    }

    /**
     * @return list<array{month: string, income: float, expense: float, savings: float}>
     */
    private function buildSixMonthTimeline(
        User $user,
        string $currency,
        CarbonImmutable $referenceDate,
        bool $hasTransactions,
    ): array {
        $timeline = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $point = $referenceDate->subMonths($offset)->locale('es');
            $monthLabel = ucfirst($point->translatedFormat('M Y'));

            if (! $hasTransactions) {
                $timeline[] = [
                    'month' => $monthLabel,
                    'income' => 0.0,
                    'expense' => 0.0,
                    'savings' => 0.0,
                ];

                continue;
            }

            $from = $point->startOfMonth()->toDateString();
            $to = $point->endOfMonth()->toDateString();

            $income = $this->accountBalanceService->sumNominalNatureForPeriod(
                $user,
                LedgerNature::Income,
                $currency,
                $from,
                $to,
            );
            $expense = $this->accountBalanceService->sumNominalNatureForPeriod(
                $user,
                LedgerNature::Expense,
                $currency,
                $from,
                $to,
            );
            $monthlySavings = $this->sumSavingsContributionsForPeriod($user, $currency, $from, $to);

            $timeline[] = [
                'month' => $monthLabel,
                'income' => (float) bcadd($income, '0', 2),
                'expense' => (float) bcadd($expense, '0', 2),
                'savings' => (float) bcadd($monthlySavings, '0', 2),
            ];
        }

        return $timeline;
    }

    /**
     * @return numeric-string
     */
    private function sumSavingsContributionsForPeriod(
        User $user,
        string $currency,
        string $fromDate,
        string $toDate,
    ): string {
        $amounts = SavingsContribution::query()
            ->select('savings_contributions.amount')
            ->join('savings_goals', 'savings_goals.id', '=', 'savings_contributions.savings_goal_id')
            ->where('savings_contributions.user_id', $user->getKey())
            ->where('savings_goals.currency_code', $currency)
            ->where('savings_contributions.status', EntryStatus::Posted->value)
            ->whereBetween('savings_contributions.contribution_date', [$fromDate, $toDate])
            ->pluck('amount');

        $total = DecimalMath::ZERO;

        foreach ($amounts as $amount) {
            $total = DecimalMath::add($total, (string) $amount);
        }

        return $total;
    }
}
