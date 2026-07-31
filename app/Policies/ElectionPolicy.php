<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;

class ElectionPolicy
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
        return true;
    }

    public function view(User $user, Election $election): bool
    {
        if ($election->status === 'open' || $election->status === 'closed') {
            return true;
        }

        return $user->hasAnyRole(['admin', 'president']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'president']);
    }

    public function update(User $user, Election $election): bool
    {
        return $user->hasAnyRole(['admin', 'president']);
    }

    public function delete(User $user, Election $election): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function open(User $user, Election $election): bool
    {
        return $user->hasAnyRole(['admin', 'president']);
    }

    public function close(User $user, Election $election): bool
    {
        return $user->hasAnyRole(['admin', 'president']);
    }

    public function manageResults(User $user, Election $election): bool
    {
        return $user->hasAnyRole(['admin', 'president']);
    }

    public function vote(User $user, Election $election): bool
    {
        return $user->isActiveMember()
            && $user->hasPermissionTo('vote_in_elections')
            && $election->isOpen();
    }

    public function nominate(User $user, ?Election $election = null): bool
    {
        return $user->isActiveMember();
    }
}
