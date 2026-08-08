<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Election;
use App\Models\ElectionNomination;
use Livewire\Component;
use Livewire\WithFileUploads;

class ElectionNominations extends Component
{
    use GuardsPendingMembers;
    use WithFileUploads;

    public string $activeTab = 'statement';

    public array $statements = [];

    public array $manifestos = [];

    public array $agendas = [];

    public $photos = [];

    public array $photoPreviews = [];

    public array $documentFiles = [];

    public function submitNomination(int $electionId): void
    {
        $user = auth()->user();

        abort_unless($user->isActiveMember(), 403);

        $election = Election::findOrFail($electionId);
        abort_unless(in_array($election->status, ['draft', 'open']), 403);

        $rules = [
            "statements.{$electionId}" => ['nullable', 'string', 'max:5000'],
            "manifestos.{$electionId}" => ['nullable', 'string', 'max:10000'],
            "agendas.{$electionId}" => ['nullable', 'string', 'max:10000'],
        ];

        if (isset($this->photos[$electionId])) {
            $rules["photos.{$electionId}"] = ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'];
        }

        if (isset($this->documentFiles[$electionId])) {
            foreach ($this->documentFiles[$electionId] as $i => $file) {
                $rules["documentFiles.{$electionId}.{$i}"] = ['file', 'mimes:pdf,doc,docx', 'max:10240'];
            }
        }

        $this->validate($rules);

        $existingNomination = ElectionNomination::where([
            'election_id' => $electionId,
            'user_id' => $user->id,
        ])->first();

        if ($existingNomination && $existingNomination->canReapply()) {
            $existingNomination->reapply([
                'statement' => $this->statements[$electionId] ?? null,
                'manifesto' => $this->manifestos[$electionId] ?? null,
                'agenda' => $this->agendas[$electionId] ?? null,
            ]);

            $nomination = $existingNomination;
        } else {
            $nomination = ElectionNomination::updateOrCreate(
                ['election_id' => $electionId, 'user_id' => $user->id],
                [
                    'statement' => $this->statements[$electionId] ?? null,
                    'manifesto' => $this->manifestos[$electionId] ?? null,
                    'agenda' => $this->agendas[$electionId] ?? null,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ],
            );
        }

        if (isset($this->photos[$electionId])) {
            $nomination->update([
                'photo' => $this->photos[$electionId]->store('nomination-photos', 'public'),
            ]);
            unset($this->photos[$electionId]);
        }

        if (isset($this->documentFiles[$electionId]) && count($this->documentFiles[$electionId]) > 0) {
            $paths = [];
            foreach ($this->documentFiles[$electionId] as $doc) {
                $paths[] = $doc->store('nomination-documents', 'public');
            }
            $nomination->update(['documents' => $paths]);
            unset($this->documentFiles[$electionId]);
        }

        activity()
            ->performedOn($election)
            ->causedBy($user)
            ->log('nomination_submitted');

        session()->flash('status', 'Your application has been submitted for review.');
    }

    public function render()
    {
        $user = auth()->user();

        $openElections = Election::live()
            ->whereIn('status', ['draft', 'open'])
            ->withCount('candidates')
            ->latest('starts_at')
            ->get();

        $userNominations = ElectionNomination::where('user_id', $user->id)
            ->with('election')
            ->get()
            ->keyBy('election_id');

        $elections = $openElections->map(fn ($election) => [
            'id' => $election->id,
            'title' => $election->title,
            'position' => $election->position,
            'description' => $election->description,
            'status' => $election->status,
            'candidates_count' => $election->candidates_count,
            'applications_starts_at' => $election->applications_starts_at?->toIso8601String(),
            'applications_ends_at' => $election->applications_ends_at?->toIso8601String(),
            'is_accepting_applications' => $election->isAcceptingApplications(),
            'user_nomination' => $userNominations->get($election->id)?->only([
                'id', 'statement', 'manifesto', 'agenda', 'photo', 'status',
                'submitted_at', 'reviewed_at', 'admin_notes',
            ]),
        ]);

        return view('livewire.election-nominations', [
            'elections' => $elections,
        ]);
    }
}
