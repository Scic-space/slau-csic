<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
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
        return $user->can('view_events');
    }

    public function view(User $user, Event $event): bool
    {
        return $user->can('view_events');
    }

    public function create(User $user): bool
    {
        return $user->can('create_events');
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can('edit_events');
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->can('delete_events');
    }

    public function publish(User $user, Event $event): bool
    {
        return $user->can('publish_events');
    }

    public function cancel(User $user, Event $event): bool
    {
        return $user->can('cancel_events');
    }
}
