<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\ElectionVote;
use Livewire\Component;

class ElectionShow extends Component
{
    public ?string $slug = null;

    public array $selectedCandidates = [];

    public ?string $receiptCode = null;

    public bool $showReceipt = true;

    public bool $showConfirmModal = false;

    public ?int $confirmElectionId = null;

    public ?string $confirmCandidateName = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $user = auth()->user();

        $query = Election::where('slug', $slug);

        if (! $user->hasRole(['admin', 'super-admin'])) {
            $query->live();
        }

        $election = $query
            ->with([
                'candidates',
                'votes' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->withCount('votes')
            ->first();

        if (! $election) {
            abort(404);
        }

        if ($election->votes->first()) {
            $this->selectedCandidates[$election->id] = $election->votes->first()->election_candidate_id;
        }

        $this->receiptCode = session('receipt_code');

        if (! $this->receiptCode && $election->votes->first()) {
            $this->receiptCode = $election->votes->first()->receipt_token;
        }
    }

    public function selectCandidate(int $electionId, int $candidateId): void
    {
        if ($this->userHasVoted($electionId)) {
            return;
        }

        $this->selectedCandidates[$electionId] = $candidateId;
    }

    private function userHasVoted(int $electionId): bool
    {
        $user = auth()->user();

        return ElectionVote::where('election_id', $electionId)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function requestConfirm(int $electionId): void
    {
        $user = auth()->user();
        $query = $user->hasRole(['admin', 'super-admin'])
            ? Election::query()
            : Election::live();
        $election = $query->findOrFail($electionId);

        $candidateId = $this->selectedCandidates[$electionId] ?? null;
        if (! $candidateId) {
            $this->addError('candidate', 'Please select a candidate before casting your vote.');

            return;
        }

        $candidate = $election->candidates()->firstWhere('id', $candidateId);
        if (! $candidate) {
            return;
        }

        $this->confirmElectionId = $electionId;
        $this->confirmCandidateName = $candidate->name;
        $this->showConfirmModal = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirmModal = false;
        $this->confirmElectionId = null;
        $this->confirmCandidateName = null;
    }

    public function castVote(): void
    {
        $this->showConfirmModal = false;

        $user = auth()->user();
        $query = $user->hasRole(['admin', 'super-admin'])
            ? Election::query()
            : Election::live();
        $election = $query->findOrFail($this->confirmElectionId);

        if (! $user->canVoteIn($election)) {
            $this->addError('vote', 'You are not eligible to vote in this election.');

            return;
        }

        if ($user->hasVotedIn($election)) {
            $this->addError('vote', 'You have already voted in this election. Vote changes are not allowed.');

            return;
        }

        $candidateId = $this->selectedCandidates[$election->id] ?? null;
        if (! $candidateId) {
            return;
        }

        $candidate = $election->candidates()->findOrFail($candidateId);

        $receiptCode = ElectionVote::generateReceiptCode();
        $receiptHash = ElectionVote::receiptHash($receiptCode);

        ElectionVote::updateOrCreate(
            ['election_id' => $election->id, 'user_id' => $user->id],
            ['election_candidate_id' => $candidate->id, 'receipt_code' => $receiptHash, 'receipt_token' => $receiptCode],
        );

        activity()
            ->performedOn($election)
            ->causedBy($user)
            ->withProperties([
                'candidate_id' => $candidate->id,
                'candidate_name' => $candidate->name,
                'election_title' => $election->title,
            ])
            ->log('vote_cast');

        $user->notify(new \App\Notifications\VoteReceiptNotification($election, $receiptCode));

        $this->receiptCode = $receiptCode;
        $this->showReceipt = true;
        $this->confirmElectionId = null;
        $this->confirmCandidateName = null;

        session()->flash('receipt_code', $receiptCode);
        session()->flash('status', 'Your vote has been recorded.');
    }

    public function dismissReceipt(): void
    {
        $this->showReceipt = false;
    }

    public function render()
    {
        $user = auth()->user();

        $renderQuery = Election::where('slug', $this->slug);

        if (! $user->hasRole(['admin', 'super-admin'])) {
            $renderQuery->live();
        }

        $election = $renderQuery
            ->with(['candidates.votes'])
            ->withCount('votes')
            ->first();

        if (! $election) {
            abort(404);
        }

        $eligibleCount = \App\Models\User::activeMembers()->count();
        $userVote = $election->votes->first();
        $maxVotes = $election->candidates->max(fn ($c) => $c->votes->count());
        $winner = $election->candidates->first(fn ($c) => $c->votes->count() === $maxVotes && $maxVotes > 0);

        $phase = match (true) {
            $election->results_visible => 'results',
            $election->status === 'open' && $election->isOpen() => 'voting',
            $election->status === 'open' && $election->isAcceptingApplications() => 'nominations',
            $election->status === 'open' && $election->starts_at && $election->starts_at->isFuture() => 'upcoming',
            default => 'ended',
        };

        $turnout = $eligibleCount > 0
            ? round(($election->votes_count / $eligibleCount) * 100, 1)
            : 0;

        $data = [
            'id' => $election->id,
            'slug' => $election->slug,
            'title' => $election->title,
            'position' => $election->position,
            'description' => $election->description,
            'status' => $election->status,
            'phase' => $phase,
            'is_open' => $election->isOpen(),
            'results_visible' => $election->results_visible,
            'allow_vote_changes' => $election->allowsVoteChanges(),
            'total_votes' => $election->votes_count,
            'eligible_voters' => $eligibleCount,
            'turnout' => $turnout,
            'starts_at' => $election->starts_at?->toIso8601String(),
            'ends_at' => $election->ends_at?->toIso8601String(),
            'applications_starts_at' => $election->applications_starts_at?->toIso8601String(),
            'applications_ends_at' => $election->applications_ends_at?->toIso8601String(),
            'user_has_voted' => $userVote !== null,
            'user_vote_candidate_id' => $userVote?->election_candidate_id,
            'winner' => $election->results_visible && $winner ? [
                'id' => $winner->id,
                'name' => $winner->name,
                'votes_count' => $winner->votes->count(),
            ] : null,
            'candidates' => $election->candidates->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'photo' => $c->photo ? asset('storage/'.$c->photo) : null,
                'manifesto' => $c->manifesto,
                'agenda' => $c->agenda,
                'votes_count' => $c->votes->count(),
                'percentage' => $election->votes_count > 0
                    ? round(($c->votes->count() / $election->votes_count) * 100, 1)
                    : 0,
                'is_winner' => $winner && $c->id === $winner->id,
            ]),
        ];

        return view('livewire.election-show', [
            'election' => $data,
        ]);
    }
}
