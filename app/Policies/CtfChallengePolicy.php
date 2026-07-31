<?php

namespace App\Policies;

use App\Models\CtfChallenge;
use App\Models\User;

class CtfChallengePolicy
{
    public function submitFlag(User $user, CtfChallenge $challenge): bool
    {
        if (! $challenge->is_active) {
            return false;
        }

        $competition = $challenge->competition;

        return $competition->status === 'published'
            && $competition->is_public
            && $competition->start_date <= now()
            && ($competition->end_date === null || $competition->end_date >= now());
    }

    public function createWriteup(User $user, CtfChallenge $challenge): bool
    {
        return $challenge->is_active && $challenge->isSolvedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, CtfChallenge $challenge): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, CtfChallenge $challenge): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }
}
