<?php

namespace App\Policies;

use App\Models\BudgetAllocation;
use App\Models\User;

class BudgetAllocationPolicy
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

    public function view(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function update(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $user->hasAnyRole(['admin', 'treasurer', 'president']);
    }

    public function delete(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $user->hasRole('super-admin');
    }
}
