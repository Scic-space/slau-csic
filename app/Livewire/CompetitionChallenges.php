<?php

namespace App\Livewire;

use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\Competition;
use Livewire\Component;

class CompetitionChallenges extends Component
{
    public Competition $competition;

    public string $answer = '';

    public ?int $answeringChallengeId = null;

    public bool $showSuccess = false;

    public string $successChallenge = '';

    public function mount(): void
    {
        $this->competition->load(['challenges' => fn ($q) => $q->where('is_active', true)]);
    }

    public function startAnswering(int $challengeId): void
    {
        $this->answeringChallengeId = $challengeId;
        $this->answer = '';
        $this->showSuccess = false;
    }

    public function cancelAnswering(): void
    {
        $this->answeringChallengeId = null;
        $this->answer = '';
    }

    public function submit(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validate(['answer' => 'required|string|max:5000']);

        $challenge = Challenge::findOrFail($this->answeringChallengeId);

        $existing = ChallengeSubmission::where('challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $this->dispatch('toast-show', message: 'You already submitted an answer for this challenge.', type: 'warning');

            return;
        }

        $isCorrect = $challenge->verifyAnswer($this->answer);

        ChallengeSubmission::create([
            'challenge_id' => $challenge->id,
            'user_id' => auth()->id(),
            'answer' => $this->answer,
            'is_correct' => $isCorrect,
            'points_awarded' => $isCorrect ? $challenge->points : 0,
            'submitted_at' => now(),
        ]);

        if ($isCorrect) {
            $this->showSuccess = true;
            $this->successChallenge = $challenge->title;

            $this->dispatch('toast-show', message: "Correct! You earned {$challenge->points} points.", type: 'success');
        } else {
            $this->answeringChallengeId = null;
            $this->answer = '';

            $this->dispatch('toast-show', message: 'Incorrect answer. Try again.', type: 'error');
        }
    }

    public function render()
    {
        $user = auth()->user();

        $challenges = $this->competition->challenges()
            ->where('is_active', true)
            ->with(['submissions' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderBy('sort_order')
            ->get();

        $totalPoints = $challenges->sum('points');
        $earnedPoints = $challenges->sum(fn ($c) => $c->submissions->first()?->points_awarded ?? 0);
        $solvedCount = $challenges->filter(fn ($c) => $c->submissions->first()?->is_correct)->count();

        return view('livewire.competition-challenges', [
            'challenges' => $challenges,
            'totalPoints' => $totalPoints,
            'earnedPoints' => $earnedPoints,
            'solvedCount' => $solvedCount,
        ]);
    }
}
