<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsGoalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Completed => 'Completada',
            self::Paused => 'Pausada',
            self::Cancelled => 'Cancelada',
        };
    }
}
