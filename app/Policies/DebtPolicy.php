<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Debt;
use App\Models\User;

class DebtPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, Debt $debt): bool
    {
        return $debt->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, Debt $debt): bool
    {
        return $debt->isOwnedBy($user);
    }

    public function recordPayment(User $user, Debt $debt): bool
    {
        return $debt->isOwnedBy($user);
    }
}
