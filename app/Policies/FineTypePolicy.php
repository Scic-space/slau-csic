<?php

namespace App\Policies;

use App\Models\FineType;
use App\Models\User;

class FineTypePolicy
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

    public function view(User $user, FineType $fineType): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'President']);
    }

    public function update(User $user, FineType $fineType): bool
    {
        return $user->hasAnyRole(['admin', 'President']);
    }

    public function delete(User $user, FineType $fineType): bool
    {
        return $user->hasRole('super-admin');
    }
}
