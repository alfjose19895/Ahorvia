<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;

class LedgerBootstrapService
{
    public function systemEquityAccount(User $user, ?string $currencyCode = null): Account
    {
        return $this->ensureSystemAccount($user, AccountType::SystemEquity, $currencyCode);
    }

    public function systemIncomeAccount(User $user, ?string $currencyCode = null): Account
    {
        return $this->ensureSystemAccount($user, AccountType::SystemIncome, $currencyCode);
    }

    public function systemExpenseAccount(User $user, ?string $currencyCode = null): Account
    {
        return $this->ensureSystemAccount($user, AccountType::SystemExpense, $currencyCode);
    }

    public function systemInterestCategory(User $user): Category
    {
        return Category::query()->firstOrCreate(
            [
                'user_id' => $user->getKey(),
                'type' => CategoryType::Expense,
                'slug' => 'intereses-financieros',
            ],
            [
                'name' => 'Intereses financieros',
                'color' => '#EF4444',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    public function systemFeeCategory(User $user): Category
    {
        return Category::query()->firstOrCreate(
            [
                'user_id' => $user->getKey(),
                'type' => CategoryType::Expense,
                'slug' => 'comisiones-bancarias',
            ],
            [
                'name' => 'Comisiones bancarias',
                'color' => '#F59E0B',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    private function ensureSystemAccount(User $user, AccountType $type, ?string $currencyCode = null): Account
    {
        $currency = strtoupper($currencyCode ?: ($user->currency_code ?: 'USD'));

        return Account::query()->firstOrCreate(
            [
                'user_id' => $user->getKey(),
                'type' => $type,
                'currency_code' => $currency,
                'is_system' => true,
            ],
            [
                'name' => $type->label()." ({$currency})",
                'ledger_nature' => $type->ledgerNature(),
                'is_active' => true,
                'include_in_net_worth' => false,
            ]
        );
    }
}
