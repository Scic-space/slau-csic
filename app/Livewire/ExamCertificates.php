<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Certificates')]
class ExamCertificates extends Component
{
    use GuardsPendingMembers;

    public function render()
    {
        $eligibilities = app(CertificateService::class)
            ->getUserEligibilities(Auth::user());

        return view('livewire.exam-certificates', [
            'eligibilities' => $eligibilities,
        ]);
    }
}
