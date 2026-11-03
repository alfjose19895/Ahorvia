<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        return $attachment->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasActiveAccess();
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $attachment->isOwnedBy($user);
    }
}
