<?php

namespace App\Livewire;

use App\Models\ClubResource;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Resource Library')]
class ResourceLibrary extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category = '';

    public string $difficulty = '';

    public string $status = '';

    protected ?string $paginationTheme = 'tailwind';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedDifficulty(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ClubResource::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('summary', 'like', "%{$this->search}%");
            }))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->difficulty, fn ($q) => $q->where('difficulty', $this->difficulty))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('sort_order')
            ->orderBy('title');

        $resources = $query->paginate(12);

        $userProgress = auth()->check()
            ? auth()->user()->clubResourceProgress()
                ->whereIn('club_resource_id', $resources->pluck('id'))
                ->get()
                ->keyBy('club_resource_id')
            : collect();

        return view('livewire.resource-library', [
            'resources' => $resources,
            'userProgress' => $userProgress,
            'categories' => ClubResource::distinct()->pluck('category')->filter()->sort()->values(),
            'difficulties' => ['Beginner', 'Intermediate', 'Advanced'],
            'statuses' => ['open', 'scheduled', 'active'],
            'stats' => [
                'total' => ClubResource::count(),
                'in_progress' => auth()->check() ? auth()->user()->clubResourceProgress()->where('status', 'in_progress')->count() : 0,
                'completed' => auth()->check() ? auth()->user()->clubResourceProgress()->where('status', 'completed')->count() : 0,
            ],
        ]);
    }
}
