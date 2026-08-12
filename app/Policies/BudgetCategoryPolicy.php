<?php

namespace App\Policies;

use App\Models\BudgetCategory;
use App\Models\User;

class BudgetCategoryPolicy
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

    public function view(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function update(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->hasAnyRole(['admin', 'Treasurer', 'President']);
    }

    public function delete(User $user, BudgetCategory $budgetCategory): bool
    {
        return $user->hasRole('super-admin');
    }
}
