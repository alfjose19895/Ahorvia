<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, Account $account): bool
    {
        return $account->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, Account $account): bool
    {
        return $account->isOwnedBy($user) && ! $account->is_system;
    }

    public function deactivate(User $user, Account $account): bool
    {
        return $account->isOwnedBy($user) && ! $account->is_system;
    }

    public function delete(User $user, Account $account): bool
    {
        return false;
    }
}
