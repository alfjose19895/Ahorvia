<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\FinancialInstitution;
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

    public function ensureDefaultCategories(User $user): void
    {
        $defaults = [
            // Ingresos
            ['name' => 'Sueldo / Nómina', 'slug' => 'sueldo', 'type' => CategoryType::Income, 'color' => '#10B981'],
            ['name' => 'Negocio / Freelance', 'slug' => 'negocio-freelance', 'type' => CategoryType::Income, 'color' => '#3B82F6'],
            ['name' => 'Rentas e Inversiones', 'slug' => 'inversiones', 'type' => CategoryType::Income, 'color' => '#8B5CF6'],
            ['name' => 'Otros Ingresos', 'slug' => 'otros-ingresos', 'type' => CategoryType::Income, 'color' => '#64748B'],

            // Gastos esenciales y estilo de vida
            ['name' => 'Alimentación y Supermercado', 'slug' => 'alimentacion', 'type' => CategoryType::Expense, 'color' => '#10B981'],
            ['name' => 'Vivienda y Servicios', 'slug' => 'vivienda', 'type' => CategoryType::Expense, 'color' => '#F59E0B'],
            ['name' => 'Transporte y Movilidad', 'slug' => 'transporte', 'type' => CategoryType::Expense, 'color' => '#64748B'],
            ['name' => 'Salud y Farmacia', 'slug' => 'salud', 'type' => CategoryType::Expense, 'color' => '#06B6D4'],
            ['name' => 'Educación y Libros', 'slug' => 'educacion', 'type' => CategoryType::Expense, 'color' => '#8B5CF6'],
            ['name' => 'Ocio y Restaurantes', 'slug' => 'ocio', 'type' => CategoryType::Expense, 'color' => '#EC4899'],
            ['name' => 'Financiero e Intereses', 'slug' => 'intereses-comisiones', 'type' => CategoryType::Expense, 'color' => '#EF4444'],
            ['name' => 'Otros Gastos', 'slug' => 'otros-gastos', 'type' => CategoryType::Expense, 'color' => '#94A3B8'],
        ];

        foreach ($defaults as $cat) {
            Category::query()->firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'slug' => $cat['slug'],
                ],
                [
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'color' => $cat['color'],
                    'is_system' => false,
                    'is_active' => true,
                ]
            );
        }
    }

    public function ensureDefaultInstitutions(User $user): void
    {
        $defaults = [
            'Banco Pichincha',
            'Banco Guayaquil',
            'Produbanco',
            'Banco del Pacífico',
            'Banco Bolivariano',
            'Banco Internacional',
            'Cooperativa JEP',
            'Cooperativa Policía Nacional',
            'Efectivo / Caja Personal',
        ];

        foreach ($defaults as $name) {
            FinancialInstitution::query()->firstOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'name' => $name,
                ],
                [
                    'is_active' => true,
                ]
            );
        }
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
