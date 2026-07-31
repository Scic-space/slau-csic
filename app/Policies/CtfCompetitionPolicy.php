<?php

namespace App\Policies;

use App\Models\CtfCompetition;
use App\Models\User;

class CtfCompetitionPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, CtfCompetition $competition): bool
    {
        if ($competition->status === 'published' && $competition->is_public) {
            return true;
        }

        return $user?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, CtfCompetition $competition): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, CtfCompetition $competition): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function viewScoreboard(?User $user, CtfCompetition $competition): bool
    {
        return $competition->status === 'published' && $competition->is_public;
    }
}
