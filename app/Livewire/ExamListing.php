<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Exams')]
class ExamListing extends Component
{
    use GuardsPendingMembers;

    public function render()
    {
        $exams = Exam::published()
            ->withCount('questions')
            ->with(['attempts' => fn ($q) => $q->where('user_id', Auth::id())])
            ->get();

        return view('livewire.exam-listing', [
            'exams' => $exams,
        ]);
    }
}
