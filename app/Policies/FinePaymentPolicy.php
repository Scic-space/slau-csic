<?php

namespace App\Policies;

use App\Models\FinePayment;
use App\Models\User;

class FinePaymentPolicy
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

    public function view(User $user, FinePayment $finePayment): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer']);
    }

    public function update(User $user, FinePayment $finePayment): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer']);
    }

    public function delete(User $user, FinePayment $finePayment): bool
    {
        return $user->hasRole('super-admin');
    }
}
