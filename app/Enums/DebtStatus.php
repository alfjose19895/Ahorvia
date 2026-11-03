<?php

declare(strict_types=1);

namespace App\Enums;

enum DebtStatus: string
{
    case Active = 'active';
    case PaidOff = 'paid_off';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::PaidOff => 'Liquidada',
            self::Cancelled => 'Cancelada',
        };
    }
}
