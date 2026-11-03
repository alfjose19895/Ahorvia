<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait estándar de aislamiento multi-usuario por propietario (`user_id`).
 */
trait BelongsToUser
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where($this->qualifyColumn('user_id'), $userId);
    }

    public function isOwnedBy(User|int $user): bool
    {
        $userId = $user instanceof User ? (int) $user->getKey() : $user;

        return (int) $this->getAttribute('user_id') === $userId;
    }
}
