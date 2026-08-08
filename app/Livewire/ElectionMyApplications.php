<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Election;
use App\Models\ElectionNomination;
use Livewire\Component;
use Livewire\WithFileUploads;

class ElectionMyApplications extends Component
{
    use GuardsPendingMembers;
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $selectedElectionId = null;

    public ?string $statement = null;

    public ?string $manifesto = null;

    public ?string $agenda = null;

    public $photo = null;

    public array $documentFiles = [];

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

    public function openApplicationForm(): void
    {
        $this->showForm = true;
    }

    public function closeApplicationForm(): void
    {
        $this->reset('showForm', 'selectedElectionId', 'statement', 'manifesto', 'agenda', 'photo', 'documentFiles');
        $this->resetValidation();
    }

    public function submitApplication(): void
    {
        $user = auth()->user();

        abort_unless($user->isActiveMember(), 403);

        $election = Election::findOrFail($this->selectedElectionId);
        abort_unless($election->isAcceptingApplications(), 403, 'Applications are no longer being accepted for this position.');

        $this->validate([
            'selectedElectionId' => ['required', 'integer', 'exists:elections,id'],
            'statement' => ['nullable', 'string', 'max:5000'],
            'manifesto' => ['nullable', 'string', 'max:10000'],
            'agenda' => ['nullable', 'string', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'documentFiles' => ['nullable', 'array', 'max:5'],
            'documentFiles.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $existingNomination = ElectionNomination::where([
            'election_id' => $this->selectedElectionId,
            'user_id' => $user->id,
        ])->first();

        if ($existingNomination && ! $existingNomination->canReapply()) {
            $this->addError('selectedElectionId', 'You already have an active application for this position.');

            return;
        }

        $data = [
            'statement' => $this->statement,
            'manifesto' => $this->manifesto,
            'agenda' => $this->agenda,
        ];

        if ($existingNomination) {
            $existingNomination->reapply($data);
            $nomination = $existingNomination;
        } else {
            $nomination = ElectionNomination::create(array_merge($data, [
                'election_id' => $this->selectedElectionId,
                'user_id' => $user->id,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]));
        }

        if ($this->photo) {
            $nomination->update(['photo' => $this->photo->store('nomination-photos', 'public')]);
        }

        if (! empty($this->documentFiles)) {
            $paths = [];
            foreach ($this->documentFiles as $doc) {
                $paths[] = $doc->store('nomination-documents', 'public');
            }
            $nomination->update(['documents' => $paths]);
        }

        activity()
            ->performedOn($election)
            ->causedBy($user)
            ->log('nomination_submitted');

        $this->closeApplicationForm();

        session()->flash('status', 'Your application has been submitted for review.');
    }

    public function render()
    {
        $user = auth()->user();

        $openElections = Election::live()
            ->acceptingApplications()
            ->whereIn('status', ['draft', 'open'])
            ->latest('starts_at')
            ->get();

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
            'openElections' => $openElections,
            'applications' => $applications,
        ]);
    }
}
