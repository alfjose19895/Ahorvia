<?php

declare(strict_types=1);

namespace App\Enums;

enum DebtType: string
{
    case PersonalLoan = 'personal_loan';
    case BankLoan = 'bank_loan';
    case CreditCard = 'credit_card';
    case Mortgage = 'mortgage';
    case InformalDebt = 'informal_debt';

    public function label(): string
    {
        return match ($this) {
            self::PersonalLoan => 'Préstamo personal',
            self::BankLoan => 'Préstamo bancario',
            self::CreditCard => 'Tarjeta de crédito',
            self::Mortgage => 'Crédito hipotecario',
            self::InformalDebt => 'Deuda personal',
        };
    }
}
