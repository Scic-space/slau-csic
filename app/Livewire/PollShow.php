<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\PollVote;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Poll')]
class PollShow extends Component
{
    public ?array $poll = null;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    /** @var array<int> */
    public $selectedOptions = [];

    public function mount(string $slug): void
    {
        $poll = Poll::published()
            ->where('slug', $slug)
            ->with(['options', 'author'])
            ->first();

        if (! $poll) {
            abort(404);
        }

        $user = auth()->user();
        $hasVoted = $user ? $poll->hasVoted($user) : false;

        $this->poll = [
            'id' => $poll->id,
            'question' => $poll->question,
            'description' => $poll->description,
            'slug' => $poll->slug,
            'is_active' => $poll->isActive(),
            'is_expired' => $poll->isExpired(),
            'allow_multiple' => $poll->allow_multiple,
            'votes_count' => $poll->votes_count,
            'expires_at' => $poll->expires_at?->toIso8601String(),
            'created_at' => $poll->created_at?->toIso8601String(),
            'author' => $poll->author?->name ?? 'Administration',
            'has_voted' => $hasVoted,
            'options' => $poll->options
                ->sortBy('sort_order')
                ->map(fn ($option) => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'votes_count' => $option->votes_count,
                    'percentage' => $option->percentage(),
                ])
                ->values()
                ->toArray(),
        ];
    }

    public function vote(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $user = auth()->user();
        if (! $user) {
            $this->errorMessage = 'You must be signed in to vote.';

            return;
        }

        $selectedIds = is_array($this->selectedOptions) ? $this->selectedOptions : [$this->selectedOptions];
        $selectedIds = array_filter($selectedIds, fn ($id) => is_numeric($id));

        if (empty($selectedIds)) {
            $this->errorMessage = 'Please select at least one option.';

            return;
        }

        $poll = Poll::published()->find($this->poll['id']);
        if (! $poll || ! $poll->isActive()) {
            $this->errorMessage = 'This poll is no longer active.';

            return;
        }

        if ($poll->hasVoted($user)) {
            $this->errorMessage = 'You have already voted on this poll.';

            return;
        }

        $validOptionIds = $poll->options()->pluck('id')->toArray();
        $selectedIds = array_intersect($selectedIds, $validOptionIds);

        if (empty($selectedIds)) {
            $this->errorMessage = 'The selected options are not valid for this poll.';

            return;
        }

        if (! $poll->allow_multiple) {
            $selectedIds = [array_values($selectedIds)[0]];
        }

        foreach ($selectedIds as $selectedId) {
            PollVote::create([
                'poll_id' => $poll->id,
                'option_id' => (int) $selectedId,
                'user_id' => $user->id,
            ]);

            $poll->options()->where('id', $selectedId)->increment('votes_count');
        }
        $poll->increment('votes_count', count($selectedIds));

        $poll->refresh();

        $this->poll['has_voted'] = true;
        $this->poll['votes_count'] = $poll->votes_count;
        $this->poll['options'] = $poll->options
            ->sortBy('sort_order')
            ->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'votes_count' => $option->votes_count,
                'percentage' => $option->percentage(),
            ])
            ->values()
            ->toArray();
        $this->selectedOptions = [];

        $this->successMessage = 'Your vote has been recorded.';
    }

    public function render()
    {
        return view('livewire.poll-show');
    }
}
