<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, Budget $budget): bool
    {
        return $budget->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, Budget $budget): bool
    {
        return $budget->isOwnedBy($user);
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $budget->isOwnedBy($user);
    }
}
