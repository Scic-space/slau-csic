<?php

namespace App\Livewire\Concerns;

trait GuardsPendingMembers
{
    public function bootGuardsPendingMembers(): void
    {
        if (auth()->user()?->isPendingApproval()) {
            $this->redirectRoute('dashboard');
        }
    }
}
