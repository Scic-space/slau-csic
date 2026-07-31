<?php

namespace App\Livewire;

use App\Models\ClubResource;
use App\Models\ClubResourceProgress as Progress;
use Livewire\Component;

class ClubResourceProgress extends Component
{
    public ClubResource $resource;

    public string $status = 'not_started';

    public int $progressPercentage = 0;

    public int $completedUnits = 0;

    public int $score = 0;

    public ?string $ranking = null;

    public ?string $notes = null;

    public bool $showSaved = false;

    protected function rules(): array
    {
        return [
            'status' => 'required|in:not_started,in_progress,completed',
            'progressPercentage' => 'required|integer|min:0|max:100',
            'completedUnits' => 'required|integer|min:0',
            'score' => 'required|integer|min:0',
            'ranking' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    public function mount(): void
    {
        $progress = Progress::query()
            ->where('club_resource_id', $this->resource->id)
            ->where('user_id', auth()->id())
            ->first();

        $this->status = $progress?->status ?? 'not_started';
        $this->progressPercentage = $progress?->progress_percentage ?? 0;
        $this->completedUnits = $progress?->completed_units ?? 0;
        $this->score = $progress?->score ?? 0;
        $this->ranking = $progress?->ranking;
        $this->notes = $progress?->notes;
    }

    public function canAwardPoints(): bool
    {
        $user = auth()->user();

        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'status' => $this->status,
            'progress_percentage' => $this->progressPercentage,
            'completed_units' => $this->completedUnits,
            'notes' => $this->notes,
            'last_activity_at' => now(),
        ];

        if ($this->canAwardPoints()) {
            $data['score'] = $this->score;
            $data['ranking'] = $this->ranking;
        }

        Progress::query()->updateOrCreate(
            [
                'club_resource_id' => $this->resource->id,
                'user_id' => auth()->id(),
            ],
            $data,
        );

        $this->showSaved = true;

        $this->dispatch('progress-saved');
    }

    public function dismissSaved(): void
    {
        $this->showSaved = false;
    }

    public function render()
    {
        return view('livewire.club-resource-progress');
    }
}
