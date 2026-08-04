<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Election;
use App\Models\User;
use Livewire\Component;

class ElectionVoting extends Component
{
    use GuardsPendingMembers;

    public string $filter = 'all';

    public function mount(): void
    {
        $this->filter = session('voting_filter') ?? 'all';
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        session(['voting_filter' => $filter]);
    }

    public function render()
    {
        $user = auth()->user();

        $allElections = Election::live()
            ->with(['candidates', 'votes' => fn ($q) => $q->where('user_id', $user->id)])
            ->withCount('votes')
            ->latest('starts_at')
            ->get();

        $eligibleCount = User::activeMembers()->count();

        $elections = $allElections->map(function ($election) use ($eligibleCount) {
            $userVote = $election->votes->first();
            $maxVotes = $election->candidates->max(fn ($c) => $c->votes_count ?? $c->votes->count());
            $winner = $election->candidates->first(fn ($c) => ($c->votes_count ?? $c->votes->count()) === $maxVotes && $maxVotes > 0);

            $statusLabel = match (true) {
                $election->results_visible => 'Results Published',
                $election->status === 'open' && $election->isOpen() => 'Voting Live',
                $election->status === 'open' && $election->starts_at && $election->starts_at->isFuture() => 'Upcoming',
                $election->status === 'open' && $election->isAcceptingApplications() => 'Accepting Applications',
                $election->status === 'suspended' => 'Suspended',
                $election->status === 'closed' => 'Closed',
                $election->status === 'draft' => 'Draft',
                default => ucfirst($election->status),
            };

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

            return [
                'id' => $election->id,
                'slug' => $election->slug,
                'title' => $election->title,
                'position' => $election->position,
                'description' => $election->description,
                'status' => $election->status,
                'status_label' => $statusLabel,
                'phase' => $phase,
                'is_open' => $election->isOpen(),
                'results_visible' => $election->results_visible,
                'allow_vote_changes' => $election->allowsVoteChanges(),
                'total_votes' => $election->votes_count,
                'eligible_voters' => $eligibleCount,
                'turnout' => $turnout,
                'candidates_count' => $election->candidates->count(),
                'user_has_voted' => $userVote !== null,
                'user_vote_candidate' => $userVote ? $election->candidates->firstWhere('id', $userVote->election_candidate_id)?->name : null,
                'starts_at' => $election->starts_at?->toIso8601String(),
                'ends_at' => $election->ends_at?->toIso8601String(),
                'applications_starts_at' => $election->applications_starts_at?->toIso8601String(),
                'applications_ends_at' => $election->applications_ends_at?->toIso8601String(),
                'winner' => $election->results_visible && $winner ? [
                    'name' => $winner->name,
                    'votes_count' => $winner->votes_count ?? $winner->votes->count(),
                ] : null,
            ];
        });

        $filtered = match ($this->filter) {
            'active' => $elections->filter(fn ($e) => $e['is_open'] || $e['phase'] === 'voting'),
            'upcoming' => $elections->filter(fn ($e) => in_array($e['phase'], ['upcoming', 'nominations'])),
            'past' => $elections->filter(fn ($e) => in_array($e['phase'], ['ended', 'results'])),
            default => $elections,
        };

        $stats = [
            'total' => $allElections->count(),
            'active' => $allElections->filter(fn ($e) => $e->isOpen())->count(),
            'upcoming' => $allElections->filter(fn ($e) => $e->status === 'open' && $e->starts_at && $e->starts_at->isFuture())->count(),
            'user_votes_cast' => $allElections->filter(fn ($e) => $e->votes->isNotEmpty())->count(),
        ];

        return view('livewire.election-voting', [
            'elections' => $filtered,
            'stats' => $stats,
        ]);
    }
}
