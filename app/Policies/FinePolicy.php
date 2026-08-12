<?php

namespace App\Policies;

use App\Models\Fine;
use App\Models\User;

class FinePolicy
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
        return $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function view(User $user, Fine $fine): bool
    {
        return $user->id === $fine->user_id || $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President', 'Technical Lead']);
    }

    public function update(User $user, Fine $fine): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President', 'Technical Lead']);
    }

    public function delete(User $user, Fine $fine): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function waive(User $user, Fine $fine): bool
    {
        return $user->hasAnyRole(['admin', 'President', 'Technical Lead']);
    }

    public function recordPayment(User $user, Fine $fine): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer']);
    }
}
