<?php

namespace App\Policies;

use App\Models\Competition;
use App\Models\User;

class CompetitionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Competition $competition): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, Competition $competition): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, Competition $competition): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function restore(User $user, Competition $competition): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function forceDelete(User $user, Competition $competition): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function join(User $user, Competition $competition): bool
    {
        return $competition->participants()->where('user_id', $user->id)->doesntExist();
    }

    public function leave(User $user, Competition $competition): bool
    {
        return $competition->participants()->where('user_id', $user->id)->exists();
    }
}
