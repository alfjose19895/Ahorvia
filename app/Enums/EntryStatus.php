<?php

declare(strict_types=1);

namespace App\Enums;

enum EntryStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Reversed = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Posted => 'Confirmado',
            self::Reversed => 'Reversado',
        };
    }
}
