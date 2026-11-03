<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsMode: string
{
    case Fixed = 'fixed';
    case Adjustable = 'adjustable';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Ahorro fijo',
            self::Adjustable => 'Ahorro ajustable',
        };
    }
}
