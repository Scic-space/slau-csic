<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->can('view_users');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('view_users');
    }

    public function create(User $user): bool
    {
        return $user->can('create_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('edit_users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('delete_users');
    }

    public function approve(User $user, User $model): bool
    {
        return $user->can('approve_members');
    }

    public function suspend(User $user, User $model): bool
    {
        return $user->can('suspend_members');
    }
}
