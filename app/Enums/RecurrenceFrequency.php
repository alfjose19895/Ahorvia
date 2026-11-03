<?php

declare(strict_types=1);

namespace App\Enums;

enum RecurrenceFrequency: string
{
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Biweekly => 'Quincenal',
            self::Monthly => 'Mensual',
            self::Quarterly => 'Trimestral',
            self::Yearly => 'Anual',
        };
    }
}
