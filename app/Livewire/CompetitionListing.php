<?php

namespace App\Livewire;

use App\Models\Competition;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Competitions')]
class CompetitionListing extends Component
{
    use WithPagination;

    public string $type = '';

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected $queryString = [
        'type',
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function resetFilters(): void
    {
        $this->type = '';
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Competition::query()->orderBy('start_date', 'desc');

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->dateFrom) {
            $query->whereDate('start_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('end_date', '<=', $this->dateTo);
        }

        $competitions = $query->withCount('participants')
            ->paginate(12)
            ->through(fn (Competition $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => str($c->description)->limit(250),
                'type' => $c->type,
                'start_date' => $c->start_date?->toIso8601String(),
                'end_date' => $c->end_date?->toIso8601String(),
                'location' => $c->location,
                'is_team_based' => $c->is_team_based,
                'participants_count' => $c->participants_count,
                'club_ranking' => $c->club_ranking,
                'achievements' => $c->achievements,
                'status' => $c->statusLabel(),
            ]);

        $competitionTypes = [
            ['value' => 'ctf', 'label' => 'CTF'],
            ['value' => 'hackathon', 'label' => 'Hackathon'],
            ['value' => 'coding', 'label' => 'Coding'],
            ['value' => 'cybersecurity', 'label' => 'Cybersecurity'],
        ];

        return view('livewire.competition-listing', [
            'competitions' => $competitions,
            'competitionTypes' => $competitionTypes,
        ]);
    }
}
