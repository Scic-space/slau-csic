<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
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
        return $user->can('send_announcements');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->can('send_announcements');
    }

    public function create(User $user): bool
    {
        return $user->can('send_announcements');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('send_announcements');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('send_announcements');
    }
}
