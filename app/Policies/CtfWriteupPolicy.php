<?php

namespace App\Policies;

use App\Models\CtfWriteup;
use App\Models\User;

class CtfWriteupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function review(User $user, CtfWriteup $writeup): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
