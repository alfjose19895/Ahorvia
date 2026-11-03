<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecurringRule;
use App\Models\User;

class RecurringRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, RecurringRule $rule): bool
    {
        return $rule->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, RecurringRule $rule): bool
    {
        return $rule->isOwnedBy($user);
    }

    public function delete(User $user, RecurringRule $rule): bool
    {
        return $rule->isOwnedBy($user);
    }
}
