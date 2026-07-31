<?php

namespace App\Livewire;

use App\Models\ElectionNomination;
use Livewire\Component;

class ElectionMyApplications extends Component
{
    public function withdrawNomination(int $electionId): void
    {
        $nomination = ElectionNomination::where([
            'election_id' => $electionId,
            'user_id' => auth()->id(),
        ])->firstOrFail();

        abort_unless($nomination->canWithdraw(), 403, 'You cannot withdraw this application.');

        $nomination->withdraw();

        session()->flash('status', 'Your application has been withdrawn.');
    }

    public function render()
    {
        $user = auth()->user();

        $applications = ElectionNomination::where('user_id', $user->id)
            ->with(['election', 'reviewer', 'reviews.user'])
            ->latest('submitted_at')
            ->get()
            ->map(fn ($nom) => [
                'id' => $nom->id,
                'election' => [
                    'id' => $nom->election->id,
                    'title' => $nom->election->title,
                    'position' => $nom->election->position,
                ],
                'statement' => $nom->statement,
                'manifesto' => $nom->manifesto,
                'agenda' => $nom->agenda,
                'photo' => $nom->photo ? asset('storage/'.$nom->photo) : null,
                'documents' => $nom->documents ? collect($nom->documents)->map(fn ($d) => asset('storage/'.$d))->toArray() : [],
                'status' => $nom->status,
                'score_average' => $nom->score_average,
                'admin_notes' => $nom->admin_notes,
                'reviewer_name' => $nom->reviewer?->name,
                'submitted_at' => $nom->submitted_at?->toIso8601String(),
                'reviewed_at' => $nom->reviewed_at?->toIso8601String(),
                'interview_scheduled_at' => $nom->interview_scheduled_at?->toIso8601String(),
                'interview_location' => $nom->interview_location,
                'interview_notes' => $nom->interview_notes,
                'can_withdraw' => $nom->canWithdraw(),
                'can_reapply' => $nom->canReapply(),
                'reviews' => $nom->reviews->map(fn ($r) => [
                    'id' => $r->id,
                    'from_status' => $r->from_status,
                    'to_status' => $r->to_status,
                    'notes' => $r->notes,
                    'reviewer_name' => $r->user?->name,
                    'created_at' => $r->created_at->toIso8601String(),
                ]),
            ]);

        return view('livewire.election-my-applications', [
            'applications' => $applications,
        ]);
    }
}
