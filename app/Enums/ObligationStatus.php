<?php

declare(strict_types=1);

namespace App\Enums;

enum ObligationStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Paid => 'Pagada',
            self::Overdue => 'Vencida',
            self::Cancelled => 'Cancelada',
        };
    }
}
