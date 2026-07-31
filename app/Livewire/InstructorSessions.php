<?php

namespace App\Livewire;

use App\Models\Meeting;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Teaching Sessions')]
class InstructorSessions extends Component
{
    use WithPagination;

    public string $filter = 'upcoming';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Meeting::teachingSessions()
            ->where('created_by', auth()->id())
            ->with(['training', 'creator'])
            ->withCount('attendance');

        if ($this->filter === 'upcoming') {
            $query->where('scheduled_at', '>=', now());
        } elseif ($this->filter === 'past') {
            $query->where('scheduled_at', '<', now());
        }

        $sessions = $query->latest('scheduled_at')->paginate(12);

        return view('livewire.instructor-sessions', [
            'sessions' => $sessions,
        ]);
    }
}
