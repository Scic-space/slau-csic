<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
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
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']) || $user->id === $transaction->created_by;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function approve(User $user, Transaction $transaction): bool
    {
        return $user->can('approve_transactions') || $user->hasAnyRole(['treasurer', 'president', 'admin']);
    }

    public function reject(User $user, Transaction $transaction): bool
    {
        return $user->can('reject_transactions') || $user->hasAnyRole(['treasurer', 'president', 'admin']);
    }
}
