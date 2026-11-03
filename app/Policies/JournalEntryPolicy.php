<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EntryStatus;
use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function view(User $user, JournalEntry $entry): bool
    {
        return $entry->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function reverse(User $user, JournalEntry $entry): bool
    {
        return $entry->isOwnedBy($user)
            && $entry->status === EntryStatus::Posted
            && $entry->reversed_by_entry_id === null;
    }

    public function delete(User $user, JournalEntry $entry): bool
    {
        return $entry->isOwnedBy($user) && $entry->status === EntryStatus::Draft;
    }
}
