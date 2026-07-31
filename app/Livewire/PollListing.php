<?php

namespace App\Livewire;

use App\Models\Poll;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Polls')]
class PollListing extends Component
{
    public function render()
    {
        $user = auth()->user();

        $polls = Poll::published()
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function (Poll $poll) use ($user) {
                return [
                    'id' => $poll->id,
                    'question' => $poll->question,
                    'description' => $poll->description,
                    'slug' => $poll->slug,
                    'options_count' => $poll->options()->count(),
                    'votes_count' => $poll->votes_count,
                    'has_voted' => $user ? $poll->hasVoted($user) : false,
                    'is_active' => $poll->isActive(),
                    'is_expired' => $poll->isExpired(),
                    'allow_multiple' => $poll->allow_multiple,
                    'expires_at' => $poll->expires_at?->toIso8601String(),
                    'created_at' => $poll->created_at?->toIso8601String(),
                ];
            });

        return view('livewire.poll-listing', [
            'polls' => $polls,
        ]);
    }
}
