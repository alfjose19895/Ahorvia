<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Enums\AccountType;
use App\Enums\EntryStatus;
use App\Enums\LedgerNature;
use App\Enums\SavingsContributionType;
use App\Models\Account;
use App\Models\JournalLine;
use App\Models\SavingsContribution;
use App\Models\User;
use App\Support\DecimalMath;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    /**
     * Calcula el saldo contable exacto de una cuenta a partir de sus líneas confirmadas.
     *
     * @return numeric-string
     */
    public function balanceForAccount(Account $account, ?CarbonImmutable $asOfDate = null): string
    {
        [$totalDebit, $totalCredit] = $this->sumDebitsAndCreditsForAccount($account, $asOfDate);

        return $account->ledger_nature->isDebitNormal()
            ? DecimalMath::sub($totalDebit, $totalCredit)
            : DecimalMath::sub($totalCredit, $totalDebit);
    }

    /**
     * Importe reservado activamente para metas de ahorro sobre una cuenta específica (sin transferencia externa).
     *
     * @return numeric-string
     */
    public function reservedAmountForAccount(Account $account): string
    {
        $contributions = SavingsContribution::query()
            ->ownedBy($account->user_id)
            ->where('account_id', $account->getKey())
            ->where('contribution_type', SavingsContributionType::Reserve)
            ->where('status', EntryStatus::Posted)
            ->pluck('amount');

        $reserved = DecimalMath::ZERO;

        foreach ($contributions as $amount) {
            $reserved = DecimalMath::add($reserved, (string) $amount);
        }

        return $reserved;
    }

    /**
     * Calcula la liquidez libre disponible en cuentas de activo o el cupo disponible en tarjetas de crédito.
     *
     * @return numeric-string
     */
    public function availableForAccount(Account $account): string
    {
        $ledgerBalance = $this->balanceForAccount($account);

        if ($account->type === AccountType::CreditCard && $account->credit_limit !== null) {
            return DecimalMath::sub((string) $account->credit_limit, $ledgerBalance);
        }

        if ($account->is_system || ! $account->type->isAsset()) {
            return DecimalMath::ZERO;
        }

        $reserved = $this->reservedAmountForAccount($account);

        return DecimalMath::sub($ledgerBalance, $reserved);
    }

    /**
     * Consolida indicadores patrimoniales y de flujo mensual del usuario desde el libro contable.
     *
     * @return array{
     *     available_cash: numeric-string,
     *     total_assets: numeric-string,
     *     total_liabilities: numeric-string,
     *     net_worth: numeric-string,
     *     monthly_income: numeric-string,
     *     monthly_expenses: numeric-string,
     *     monthly_cash_flow: numeric-string,
     *     accumulated_savings: numeric-string
     * }
     */
    public function consolidatedMetricsForUser(User $user, CarbonImmutable $referenceDate): array
    {
        $currency = strtoupper($user->currency_code ?: 'USD');

        $accounts = Account::query()
            ->ownedBy($user)
            ->where('currency_code', $currency)
            ->where('is_active', true)
            ->get();

        $availableCash = DecimalMath::ZERO;
        $totalAssets = DecimalMath::ZERO;
        $totalLiabilities = DecimalMath::ZERO;

        foreach ($accounts as $account) {
            if ($account->is_system) {
                continue;
            }

            $balance = $this->balanceForAccount($account);

            if ($account->ledger_nature === LedgerNature::Asset && $account->include_in_net_worth) {
                $totalAssets = DecimalMath::add($totalAssets, $balance);
            }

            if ($account->ledger_nature === LedgerNature::Liability && $account->include_in_net_worth) {
                $totalLiabilities = DecimalMath::add($totalLiabilities, $balance);
            }

            if ($account->type->isAvailableLiquidity()) {
                $availableCash = DecimalMath::add($availableCash, $this->availableForAccount($account));
            }
        }

        $startOfMonth = $referenceDate->startOfMonth()->toDateString();
        $endOfMonth = $referenceDate->endOfMonth()->toDateString();

        $monthlyIncome = $this->sumNominalNatureForPeriod($user, LedgerNature::Income, $currency, $startOfMonth, $endOfMonth);
        $monthlyExpenses = $this->sumNominalNatureForPeriod($user, LedgerNature::Expense, $currency, $startOfMonth, $endOfMonth);

        $accumulatedSavings = $this->totalRecognizedSavingsForUser($user, $currency);

        return [
            'available_cash' => $availableCash,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_worth' => DecimalMath::sub($totalAssets, $totalLiabilities),
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'monthly_cash_flow' => DecimalMath::sub($monthlyIncome, $monthlyExpenses),
            'accumulated_savings' => $accumulatedSavings,
        ];
    }

    /**
     * @return numeric-string
     */
    public function sumNominalNatureForPeriod(
        User $user,
        LedgerNature $nature,
        string $currency,
        string $fromDate,
        string $toDate,
    ): string {
        $lines = JournalLine::query()
            ->select(['journal_lines.debit', 'journal_lines.credit'])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journal_lines.user_id', $user->getKey())
            ->where('accounts.ledger_nature', $nature->value)
            ->where('accounts.currency_code', $currency)
            ->whereIn('journal_entries.status', [EntryStatus::Posted->value, EntryStatus::Reversed->value])
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->get();

        $debits = DecimalMath::ZERO;
        $credits = DecimalMath::ZERO;

        foreach ($lines as $line) {
            $debits = DecimalMath::add($debits, (string) $line->debit);
            $credits = DecimalMath::add($credits, (string) $line->credit);
        }

        return $nature->isDebitNormal()
            ? DecimalMath::sub($debits, $credits)
            : DecimalMath::sub($credits, $debits);
    }

    /**
     * @return numeric-string
     */
    public function totalRecognizedSavingsForUser(User $user, string $currency): string
    {
        $amounts = SavingsContribution::query()
            ->select('savings_contributions.amount')
            ->join('savings_goals', 'savings_goals.id', '=', 'savings_contributions.savings_goal_id')
            ->where('savings_contributions.user_id', $user->getKey())
            ->where('savings_goals.currency_code', $currency)
            ->where('savings_contributions.status', EntryStatus::Posted->value)
            ->pluck('amount');

        $total = DecimalMath::ZERO;

        foreach ($amounts as $amount) {
            $total = DecimalMath::add($total, (string) $amount);
        }

        return $total;
    }

    /**
     * @return array{0: numeric-string, 1: numeric-string}
     */
    private function sumDebitsAndCreditsForAccount(Account $account, ?CarbonImmutable $asOfDate = null): array
    {
        $query = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.user_id', $account->user_id)
            ->where('journal_lines.account_id', $account->getKey())
            ->whereIn('journal_entries.status', [EntryStatus::Posted->value, EntryStatus::Reversed->value]);

        if ($asOfDate !== null) {
            $query->where('journal_entries.entry_date', '<=', $asOfDate->toDateString());
        }

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            /** @var object{total_debit: string|null, total_credit: string|null}|null $row */
            $row = $query
                ->selectRaw('COALESCE(SUM(journal_lines.debit), 0) as total_debit, COALESCE(SUM(journal_lines.credit), 0) as total_credit')
                ->first();

            return [
                DecimalMath::normalize($row->total_debit ?? '0'),
                DecimalMath::normalize($row->total_credit ?? '0'),
            ];
        }

        $lines = $query->select(['journal_lines.debit', 'journal_lines.credit'])->get();
        $debits = DecimalMath::ZERO;
        $credits = DecimalMath::ZERO;

        foreach ($lines as $line) {
            $debits = DecimalMath::add($debits, (string) $line->debit);
            $credits = DecimalMath::add($credits, (string) $line->credit);
        }

        return [$debits, $credits];
    }
}
