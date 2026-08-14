<?php

namespace App\Policies;

use App\Models\FineAppeal;
use App\Models\User;

class FineAppealPolicy
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
        return $user->hasAnyRole(['admin', 'President', 'Technical Lead']);
    }

    public function view(User $user, FineAppeal $fineAppeal): bool
    {
        return $user->id === $fineAppeal->fine->user_id || $user->hasAnyRole(['admin', 'President', 'Technical Lead']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FineAppeal $fineAppeal): bool
    {
        return $user->hasAnyRole(['admin', 'President', 'Technical Lead']);
    }

    public function delete(User $user, FineAppeal $fineAppeal): bool
    {
        return $user->hasRole('super-admin');
    }

    public function review(User $user, FineAppeal $fineAppeal): bool
    {
        return $fineAppeal->canBeReviewed() && $user->hasAnyRole(['admin', 'President', 'Technical Lead']);
    }
}
