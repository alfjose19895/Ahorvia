<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerNature: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Income = 'income';
    case Expense = 'expense';

    public function isDebitNormal(): bool
    {
        return match ($this) {
            self::Asset, self::Expense => true,
            self::Liability, self::Equity, self::Income => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Asset => 'Activo',
            self::Liability => 'Pasivo',
            self::Equity => 'Patrimonio',
            self::Income => 'Ingreso',
            self::Expense => 'Gasto',
        };
    }
}
