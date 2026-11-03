<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ScheduledObligation;
use App\Models\User;

class ScheduledObligationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, ScheduledObligation $obligation): bool
    {
        return $obligation->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, ScheduledObligation $obligation): bool
    {
        return $obligation->isOwnedBy($user);
    }

    public function delete(User $user, ScheduledObligation $obligation): bool
    {
        return $obligation->isOwnedBy($user);
    }
}
