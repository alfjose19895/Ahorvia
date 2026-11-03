<?php

declare(strict_types=1);

namespace App\Enums;

enum AccessStatus: string
{
    case Active = 'active';
    case PendingActivation = 'pending_activation';
    case Suspended = 'suspended';
    case Revoked = 'revoked';

    public function canAccessApplication(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::PendingActivation => 'Pendiente de activación',
            self::Suspended => 'Suspendido',
            self::Revoked => 'Acceso revocado',
        };
    }
}
