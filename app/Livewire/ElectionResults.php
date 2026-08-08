<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Election;
use App\Models\User;
use Livewire\Component;

class ElectionResults extends Component
{
    use GuardsPendingMembers;

    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function render()
    {
        $eligibleCount = User::activeMembers()->count();

        $elections = Election::live()->with(['candidates.votes'])
            ->withCount('votes')
            ->where(fn ($q) => $q->where('results_visible', true)->orWhere('status', 'closed'))
            ->latest('ends_at')
            ->get()
            ->map(function ($election) use ($eligibleCount) {
                $maxVotes = $election->candidates->max(fn ($c) => $c->votes->count());
                $winner = $election->candidates->first(fn ($c) => $c->votes->count() === $maxVotes && $maxVotes > 0);

                $turnout = $eligibleCount > 0
                    ? round(($election->votes_count / $eligibleCount) * 100, 1)
                    : 0;

                return [
                    'id' => $election->id,
                    'slug' => $election->slug,
                    'title' => $election->title,
                    'position' => $election->position,
                    'status' => $election->status,
                    'results_visible' => $election->results_visible,
                    'total_votes' => $election->votes_count,
                    'eligible_voters' => $eligibleCount,
                    'turnout' => $turnout,
                    'starts_at' => $election->starts_at,
                    'ends_at' => $election->ends_at,
                    'winner' => $winner ? [
                        'id' => $winner->id,
                        'name' => $winner->name,
                        'votes_count' => $winner->votes->count(),
                        'percentage' => $election->votes_count > 0
                            ? round(($winner->votes->count() / $election->votes_count) * 100, 1)
                            : 0,
                    ] : null,
                    'candidates' => $election->candidates
                        ->map(fn ($c) => [
                            'id' => $c->id,
                            'name' => $c->name,
                            'photo' => $c->photo ? asset('storage/'.$c->photo) : null,
                            'votes_count' => $c->votes->count(),
                            'percentage' => $election->votes_count > 0
                                ? round(($c->votes->count() / $election->votes_count) * 100, 1)
                                : 0,
                            'is_winner' => $winner && $c->id === $winner->id,
                        ])
                        ->sortByDesc('votes_count')
                        ->values(),
                ];
            });

        $filtered = match ($this->filter) {
            'published' => $elections->filter(fn ($e) => $e['results_visible']),
            'closed' => $elections->filter(fn ($e) => ! $e['results_visible'] && $e['status'] === 'closed'),
            default => $elections,
        };

        return view('livewire.election-results', [
            'elections' => $filtered,
        ]);
    }
}
