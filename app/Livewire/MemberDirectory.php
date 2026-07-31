<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Members')]
class MemberDirectory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filter = '';

    public string $year = '';

    public string $program = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::publiclyVisible()
            ->with('roles')
            ->withCount('eventRegistrations');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhereHas('memberProfile', fn ($p) => $p->where('program', 'like', "%{$this->search}%"));
            });
        }

        match ($this->filter) {
            'active' => $query->where('membership_status', 'active'),
            'alumni' => $query->where('membership_type', 'alumni'),
            'executive' => $query->executiveBoard(),
            default => null,
        };

        if ($this->year) {
            $query->whereHas('memberProfile', fn ($p) => $p->where('year_of_study', $this->year));
        }

        if ($this->program) {
            $query->whereHas('memberProfile', fn ($p) => $p->where('program', 'like', "%{$this->program}%"));
        }

        $members = $query->orderBy('name')
            ->paginate(12)
            ->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role_names' => $u->roles->pluck('name'),
                'membership_status' => $u->membership_status,
                'membership_type' => $u->membership_type,
                'profile_photo_url' => $u->profile_photo_url,
                'program' => $u->memberProfile?->program,
                'year_of_study' => $u->memberProfile?->year_of_study,
                'bio' => $u->memberProfile?->bio,
                'headline' => $u->memberProfile?->headline,
                'event_count' => $u->event_registrations_count,
            ]);

        $years = User::publiclyVisible()
            ->whereHas('memberProfile', fn ($q) => $q->whereNotNull('year_of_study'))
            ->with('memberProfile')
            ->get()
            ->pluck('memberProfile.year_of_study')
            ->unique()
            ->sort()
            ->values();

        $programs = User::publiclyVisible()
            ->whereHas('memberProfile', fn ($q) => $q->whereNotNull('program'))
            ->with('memberProfile')
            ->get()
            ->pluck('memberProfile.program')
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'total' => User::publiclyVisible()->count(),
            'active' => User::publiclyVisible()->where('membership_status', 'active')->count(),
            'alumni' => User::publiclyVisible()->where('membership_type', 'alumni')->count(),
        ];

        return view('livewire.member-directory', [
            'members' => $members,
            'years' => $years,
            'programs' => $programs,
            'stats' => $stats,
        ]);
    }
}
