<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsContributionType: string
{
    case Opening = 'opening';
    case Transfer = 'transfer';
    case Reserve = 'reserve';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Ahorro inicial reconocido',
            self::Transfer => 'Transferencia contable a ahorro',
            self::Reserve => 'Reserva identificable en cuenta',
        };
    }
}
