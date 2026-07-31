<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
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
        return true;
    }

    public function view(User $user, Exam $exam): bool
    {
        if ($exam->status === 'published') {
            return true;
        }

        return $user->hasAnyRole(['admin', 'president', 'head_ctf']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'president', 'head_ctf']);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['admin', 'president', 'head_ctf']);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['admin', 'president', 'head_ctf']);
    }

    public function grade(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['admin', 'president', 'head_ctf']);
    }

    public function manageCertificates(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['admin', 'president', 'head_ctf']);
    }

    public function take(User $user, Exam $exam): bool
    {
        return $exam->status === 'published';
    }
}
