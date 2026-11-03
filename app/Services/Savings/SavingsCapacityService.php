<?php

declare(strict_types=1);

namespace App\Services\Savings;

use App\Data\Savings\SavingsCapacityData;
use App\Enums\DebtStatus;
use App\Enums\EntryStatus;
use App\Enums\LedgerNature;
use App\Enums\MovementType;
use App\Enums\ObligationStatus;
use App\Enums\SavingsGoalStatus;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\JournalLine;
use App\Models\RecurringOccurrence;
use App\Models\RecurringRule;
use App\Models\SavingsGoal;
use App\Models\ScheduledObligation;
use App\Models\User;
use App\Services\Budget\BudgetMonitorService;
use App\Services\Finance\AccountBalanceService;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;

class SavingsCapacityService
{
    public function __construct(
        private readonly AccountBalanceService $accountBalanceService,
        private readonly BudgetMonitorService $budgetMonitorService,
        private readonly SavingsPlannerService $savingsPlannerService,
    ) {}

    /**
     * Evalúa la capacidad mensual de ahorro utilizando ingresos, presupuestos y obligaciones previstas,
     * evitando descontar dos veces obligaciones ya contempladas en presupuestos activos.
     */
    public function evaluateForMonth(
        User $user,
        ?CarbonImmutable $referenceDate = null,
        ?string $projectedIncomeOverride = null,
    ): SavingsCapacityData {
        $date = ($referenceDate ?? CarbonImmutable::now($user->timezone ?: 'UTC'))->startOfDay();
        $currency = strtoupper($user->currency_code ?: 'USD');
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $expectedIncome = $projectedIncomeOverride !== null
            ? DecimalMath::normalize($projectedIncomeOverride)
            : $this->estimateMonthlyIncome($user, $currency, $startOfMonth, $endOfMonth);

        [$budgetedExpenses, $budgetedCategoryIds] = $this->resolveBudgetedAndActualExpenses(
            user: $user,
            currency: $currency,
            year: $date->year,
            month: $date->month,
            fromDate: $startOfMonth,
            toDate: $endOfMonth,
        );

        $unbudgetedObligations = $this->resolveUnbudgetedObligations(
            user: $user,
            currency: $currency,
            fromDate: $startOfMonth,
            toDate: $endOfMonth,
            budgetedCategoryIds: $budgetedCategoryIds,
        );

        $totalCommitments = DecimalMath::add($budgetedExpenses, $unbudgetedObligations);
        $grossCapacity = DecimalMath::sub($expectedIncome, $totalCommitments);

        $activeGoals = SavingsGoal::query()
            ->ownedBy($user)
            ->where('currency_code', $currency)
            ->where('status', SavingsGoalStatus::Active)
            ->get();

        $activeGoalsDemand = DecimalMath::ZERO;

        foreach ($activeGoals as $goal) {
            $plan = $this->savingsPlannerService->analyzeGoal($goal, $date);
            $activeGoalsDemand = DecimalMath::add($activeGoalsDemand, $plan->recommendedMonthlyQuota);
        }

        $netUnallocated = DecimalMath::sub($grossCapacity, $activeGoalsDemand);
        $isSustainable = ! DecimalMath::isNegative($grossCapacity)
            && DecimalMath::compare($grossCapacity, $activeGoalsDemand) >= 0;

        return new SavingsCapacityData(
            currencyCode: $currency,
            expectedMonthlyIncome: $expectedIncome,
            budgetedExpenses: $budgetedExpenses,
            unbudgetedObligations: $unbudgetedObligations,
            totalCommitments: $totalCommitments,
            grossSavingsCapacity: $grossCapacity,
            activeGoalsMonthlyDemand: $activeGoalsDemand,
            netUnallocatedCapacity: $netUnallocated,
            isSustainable: $isSustainable,
        );
    }

    /**
     * @return numeric-string
     */
    private function estimateMonthlyIncome(
        User $user,
        string $currency,
        string $fromDate,
        string $toDate,
    ): string {
        $postedIncome = $this->accountBalanceService->sumNominalNatureForPeriod(
            user: $user,
            nature: LedgerNature::Income,
            currency: $currency,
            fromDate: $fromDate,
            toDate: $toDate,
        );

        $pendingIncomeRules = RecurringRule::query()
            ->ownedBy($user)
            ->where('movement_type', MovementType::Income)
            ->where('currency_code', $currency)
            ->where('is_active', true)
            ->whereBetween('next_run_date', [$fromDate, $toDate])
            ->get();

        $projectedPendingIncome = DecimalMath::ZERO;

        foreach ($pendingIncomeRules as $rule) {
            $alreadyExecuted = RecurringOccurrence::query()
                ->ownedBy($user)
                ->where('recurring_rule_id', $rule->getKey())
                ->whereBetween('scheduled_date', [$fromDate, $toDate])
                ->whereNotNull('journal_entry_id')
                ->exists();

            if (! $alreadyExecuted) {
                $projectedPendingIncome = DecimalMath::add($projectedPendingIncome, (string) $rule->amount);
            }
        }

        return DecimalMath::add($postedIncome, $projectedPendingIncome);
    }

    /**
     * @return array{0: numeric-string, 1: list<int>}
     */
    private function resolveBudgetedAndActualExpenses(
        User $user,
        string $currency,
        int $year,
        int $month,
        string $fromDate,
        string $toDate,
    ): array {
        $budget = Budget::query()
            ->ownedBy($user)
            ->where('year', $year)
            ->where('month', $month)
            ->where('currency_code', $currency)
            ->where('is_active', true)
            ->with('lines.category')
            ->first();

        if ($budget === null) {
            $actualExpenses = $this->accountBalanceService->sumNominalNatureForPeriod(
                user: $user,
                nature: LedgerNature::Expense,
                currency: $currency,
                fromDate: $fromDate,
                toDate: $toDate,
            );

            return [$actualExpenses, []];
        }

        $execution = $this->budgetMonitorService->evaluate($budget);
        $budgetedCategoryIds = [];
        $committedBudgetExpenses = DecimalMath::ZERO;

        foreach ($execution->lines as $line) {
            $budgetedCategoryIds[] = $line['category_id'];
            // Se toma el mayor entre el presupuesto asignado y el gasto real ejecutado en la categoría
            $effectiveCategoryCommitment = DecimalMath::compare($line['spent_amount'], $line['limit_amount']) > 0
                ? $line['spent_amount']
                : $line['limit_amount'];
            $committedBudgetExpenses = DecimalMath::add($committedBudgetExpenses, $effectiveCategoryCommitment);
        }

        if ($budgetedCategoryIds === [] && DecimalMath::compare($execution->totalLimit, DecimalMath::ZERO) > 0) {
            $committedBudgetExpenses = $execution->totalLimit;
        }

        // Sumar gastos reales ejecutados en categorías fuera del presupuesto (o sin categoría)
        $unbudgetedActualLines = JournalLine::query()
            ->select(['journal_lines.debit', 'journal_lines.credit'])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journal_lines.user_id', $user->getKey())
            ->where('accounts.ledger_nature', LedgerNature::Expense->value)
            ->where('accounts.currency_code', $currency)
            ->whereIn('journal_entries.status', [EntryStatus::Posted->value, EntryStatus::Reversed->value])
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->when($budgetedCategoryIds !== [], static function ($query) use ($budgetedCategoryIds): void {
                $query->where(static function ($nested) use ($budgetedCategoryIds): void {
                    $nested->whereNull('journal_lines.category_id')
                        ->orWhereNotIn('journal_lines.category_id', $budgetedCategoryIds);
                });
            })
            ->get();

        $unbudgetedSpent = DecimalMath::ZERO;

        foreach ($unbudgetedActualLines as $row) {
            $net = DecimalMath::sub((string) $row->debit, (string) $row->credit);
            $unbudgetedSpent = DecimalMath::add($unbudgetedSpent, $net);
        }

        if (! DecimalMath::isNegative($unbudgetedSpent)) {
            $committedBudgetExpenses = DecimalMath::add($committedBudgetExpenses, $unbudgetedSpent);
        }

        return [$committedBudgetExpenses, $budgetedCategoryIds];
    }

    /**
     * @param  list<int>  $budgetedCategoryIds
     * @return numeric-string
     */
    private function resolveUnbudgetedObligations(
        User $user,
        string $currency,
        string $fromDate,
        string $toDate,
        array $budgetedCategoryIds,
    ): string {
        $obligations = ScheduledObligation::query()
            ->ownedBy($user)
            ->where('currency_code', $currency)
            ->whereIn('status', [ObligationStatus::Pending, ObligationStatus::Overdue])
            ->whereBetween('due_date', [$fromDate, $toDate])
            ->with('debt')
            ->get();

        $totalUnbudgeted = DecimalMath::ZERO;
        $coveredDebtIds = [];

        foreach ($obligations as $obligation) {
            if ($obligation->debt_id !== null) {
                $coveredDebtIds[] = (int) $obligation->debt_id;
            }

            if ($obligation->is_included_in_budget) {
                continue;
            }

            if ($obligation->category_id !== null && in_array((int) $obligation->category_id, $budgetedCategoryIds, true)) {
                continue;
            }

            $debtBudgetCategoryId = $obligation->debt?->budget_category_id;
            if ($debtBudgetCategoryId !== null && in_array((int) $debtBudgetCategoryId, $budgetedCategoryIds, true)) {
                continue;
            }

            $totalUnbudgeted = DecimalMath::add($totalUnbudgeted, (string) $obligation->amount);
        }

        // Evaluar pagos mínimos de deudas activas no cubiertos por presupuestos ni por obligaciones ya listadas
        $activeDebts = Debt::query()
            ->ownedBy($user)
            ->where('currency_code', $currency)
            ->where('status', DebtStatus::Active)
            ->get();

        foreach ($activeDebts as $debt) {
            $minimumPayment = DecimalMath::normalize((string) $debt->minimum_payment);

            if (DecimalMath::compare($minimumPayment, DecimalMath::ZERO) <= 0) {
                continue;
            }

            if (in_array((int) $debt->getKey(), $coveredDebtIds, true)) {
                continue;
            }

            if ($debt->budget_category_id !== null && in_array((int) $debt->budget_category_id, $budgetedCategoryIds, true)) {
                continue;
            }

            $paymentsInPeriod = DebtPayment::query()
                ->ownedBy($user)
                ->where('debt_id', $debt->getKey())
                ->where('status', EntryStatus::Posted)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->pluck('total_amount');

            $paidThisMonth = DecimalMath::ZERO;
            foreach ($paymentsInPeriod as $paidAmount) {
                $paidThisMonth = DecimalMath::add($paidThisMonth, (string) $paidAmount);
            }

            $remainingMinimum = DecimalMath::sub($minimumPayment, $paidThisMonth);

            if (DecimalMath::compare($remainingMinimum, DecimalMath::ZERO) > 0) {
                $totalUnbudgeted = DecimalMath::add($totalUnbudgeted, $remainingMinimum);
            }
        }

        return $totalUnbudgeted;
    }
}
