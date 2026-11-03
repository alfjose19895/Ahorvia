<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLoggerService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        User $user,
        string $event,
        Model $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        $request = app()->bound('request') ? request() : null;

        return AuditLog::query()->create([
            'user_id' => $user->getKey(),
            'event' => $event,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => (int) $auditable->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request !== null ? mb_substr((string) $request->userAgent(), 0, 255) : null,
            'created_at' => now(),
        ]);
    }
}
