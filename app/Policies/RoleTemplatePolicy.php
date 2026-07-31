<?php

namespace App\Policies;

use App\Models\RoleTemplate;
use App\Models\User;

class RoleTemplatePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view_assignments');
    }

    public function view(User $user, RoleTemplate $roleTemplate): bool
    {
        return $this->hasPermission($user, 'view_assignments');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'manage_assignments');
    }

    public function update(User $user, RoleTemplate $roleTemplate): bool
    {
        return $this->hasPermission($user, 'manage_assignments');
    }

    public function delete(User $user, RoleTemplate $roleTemplate): bool
    {
        return $this->hasPermission($user, 'manage_assignments');
    }

    private function hasPermission(User $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
            return false;
        }
    }
}
