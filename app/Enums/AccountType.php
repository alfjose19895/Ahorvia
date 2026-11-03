<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountType: string
{
    case Cash = 'cash';
    case Checking = 'checking';
    case Savings = 'savings';
    case Virtual = 'virtual';
    case CreditCard = 'credit_card';
    case Loan = 'loan';
    case SystemEquity = 'system_equity';
    case SystemIncome = 'system_income';
    case SystemExpense = 'system_expense';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Checking => 'Cuenta corriente',
            self::Savings => 'Cuenta de ahorros',
            self::Virtual => 'Cuenta virtual',
            self::CreditCard => 'Tarjeta de crédito',
            self::Loan => 'Préstamo',
            self::SystemEquity => 'Capital y ajustes contables',
            self::SystemIncome => 'Cuenta contable de ingresos',
            self::SystemExpense => 'Cuenta contable de gastos',
        };
    }

    public function ledgerNature(): LedgerNature
    {
        return match ($this) {
            self::Cash, self::Checking, self::Savings, self::Virtual => LedgerNature::Asset,
            self::CreditCard, self::Loan => LedgerNature::Liability,
            self::SystemEquity => LedgerNature::Equity,
            self::SystemIncome => LedgerNature::Income,
            self::SystemExpense => LedgerNature::Expense,
        };
    }

    public function isDebitNormal(): bool
    {
        return $this->ledgerNature()->isDebitNormal();
    }

    public function isAsset(): bool
    {
        return $this->ledgerNature() === LedgerNature::Asset;
    }

    public function isLiability(): bool
    {
        return $this->ledgerNature() === LedgerNature::Liability;
    }

    public function isSystemLedger(): bool
    {
        return match ($this) {
            self::SystemEquity, self::SystemIncome, self::SystemExpense => true,
            default => false,
        };
    }

    public function isUserSelectable(): bool
    {
        return ! $this->isSystemLedger();
    }

    public function isAvailableLiquidity(): bool
    {
        return match ($this) {
            self::Cash, self::Checking, self::Virtual => true,
            default => false,
        };
    }

    /**
     * @return list<self>
     */
    public static function userCases(): array
    {
        return [
            self::Cash,
            self::Checking,
            self::Savings,
            self::Virtual,
            self::CreditCard,
            self::Loan,
        ];
    }
}
