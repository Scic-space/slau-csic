<?php

namespace App\Livewire;

use App\Models\Training;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Trainings')]
class TrainingListing extends Component
{
    public string $search = '';

    public string $category = '';

    public string $difficulty = '';

    public function updatedSearch(): void
    {
        //
    }

    public function updatedCategory(): void
    {
        //
    }

    public function updatedDifficulty(): void
    {
        //
    }

    public function render()
    {
        $trainings = Training::published()
            ->withCount('modules')
            ->withCount('enrollments')
            ->with('instructor')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->difficulty, fn ($q) => $q->where('difficulty', $this->difficulty))
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Training::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $difficulties = Training::where('is_published', true)
            ->whereNotNull('difficulty')
            ->distinct()
            ->pluck('difficulty');

        return view('livewire.training-listing', [
            'trainings' => $trainings,
            'categories' => $categories,
            'difficulties' => $difficulties,
        ]);
    }
}
