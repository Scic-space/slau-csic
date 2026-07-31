<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_meetings');
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return $user->can('view_meetings');
    }

    public function create(User $user): bool
    {
        return $user->can('create_meetings');
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->can('edit_meetings');
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->can('delete_meetings');
    }
}
