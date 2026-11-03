<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $authenticatedUser, User $targetUser): bool
    {
        return (int) $authenticatedUser->getKey() === (int) $targetUser->getKey();
    }

    public function update(User $authenticatedUser, User $targetUser): bool
    {
        return (int) $authenticatedUser->getKey() === (int) $targetUser->getKey();
    }
}
