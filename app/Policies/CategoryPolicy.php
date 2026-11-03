<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, Category $category): bool
    {
        return $category->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function update(User $user, Category $category): bool
    {
        return $category->isOwnedBy($user) && ! $category->is_system;
    }

    public function delete(User $user, Category $category): bool
    {
        return $category->isOwnedBy($user) && ! $category->is_system && ! $category->journalLines()->exists();
    }
}
