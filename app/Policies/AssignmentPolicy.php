<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
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

    public function view(User $user, Assignment $assignment): bool
    {
        return $this->hasPermission($user, 'view_assignments');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'manage_assignments');
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->hasPermission($user, 'manage_assignments');
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->hasPermission($user, 'manage_assignments');
    }

    public function generate(User $user, Assignment $assignment): bool
    {
        return $this->hasPermission($user, 'manage_assignments') && $assignment->status === 'draft';
    }

    public function approve(User $user, Assignment $assignment): bool
    {
        return $this->hasPermission($user, 'manage_assignments') && $assignment->status === 'pending_review';
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
