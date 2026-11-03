<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

class SavingsGoalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, SavingsGoal $goal): bool
    {
        return $goal->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, SavingsGoal $goal): bool
    {
        return $goal->isOwnedBy($user);
    }

    public function contribute(User $user, SavingsGoal $goal): bool
    {
        return $goal->isOwnedBy($user);
    }
}
