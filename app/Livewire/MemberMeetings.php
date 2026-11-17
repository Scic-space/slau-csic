<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Meeting;
use App\Models\MeetingInterest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Meetings')]
class MemberMeetings extends Component
{
    use GuardsPendingMembers;
    use WithPagination;

    private const MEETING_TYPES = [
        'general' => 'General',
        'executive' => 'Executive',
        'special' => 'Special',
        'training' => 'Training',
        'workshop' => 'Workshop',
    ];

    public string $filter = 'upcoming';

    public string $type = '';

    public string $search = '';

    protected $queryString = [
        'filter' => ['except' => 'upcoming'],
        'type' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatedFilter(): void
    {
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

    public function resetFilters(): void
    {
        $this->filter = 'upcoming';
        $this->type = '';
        $this->search = '';
        $this->resetPage();
    }

    public function toggleInterest(int $meetingId): void
    {
        $user = Auth::user();
        $meeting = Meeting::findOrFail($meetingId);

        abort_if($meeting->isTeachingSession(), 404);
        abort_if($meeting->isCancelled() || $meeting->hasEnded() || ($meeting->isPast() && ! $meeting->attendance_open), 403);
        abort_unless($meeting->canUserAttend($user), 403);

        if (in_array($meeting->audience, ['year_1', 'year_2_plus'], true)) {
            $memberYear = $user->memberProfile?->year_of_study;
            $isTargetAudience = $meeting->audience === 'year_1'
                ? $memberYear === 1
                : is_int($memberYear) && $memberYear > 1;

            abort_unless($isTargetAudience, 403);
        }

        $existing = MeetingInterest::where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return;
        }

        MeetingInterest::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
        ]);
    }

    public function render(): View
    {
        $user = Auth::user();
        $memberYear = $user->memberProfile?->year_of_study;

        $query = Meeting::query()
            ->where('type', '!=', 'teaching_session')
            ->notCancelled()
            ->where(function ($query) use ($memberYear) {
                $query->whereNull('audience')->orWhere('audience', 'all');

                if ($memberYear === 1) {
                    $query->orWhere('audience', 'year_1');
                } elseif (is_int($memberYear) && $memberYear > 1) {
                    $query->orWhere('audience', 'year_2_plus');
                }
            })
            ->with(['creator:id,name'])
            ->withCount('interests')
            ->with(['interests' => fn ($query) => $query->where('user_id', $user->id)])
            ->with(['attendance' => fn ($query) => $query->where('user_id', $user->id)]);

        if ($this->search !== '') {
            $searchTerm = str_replace(['%', '_'], ['\\%', '\\_'], $this->search);

            $query->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        if (array_key_exists($this->type, self::MEETING_TYPES)) {
            $query->where('type', $this->type);
        }

        if ($this->filter === 'past') {
            $query->where(function ($query) {
                $query->whereNotNull('ended_at')
                    ->orWhere(function ($query) {
                        $query->where('scheduled_at', '<', now())
                            ->where('attendance_open', false);
                    });
            })->orderByDesc('scheduled_at');
        } elseif ($this->filter === 'all') {
            $query->orderByDesc('scheduled_at');
        } else {
            $query->whereNull('ended_at')
                ->where(function ($query) {
                    $query->where('scheduled_at', '>=', now())
                        ->orWhere('attendance_open', true);
                })
                ->orderBy('scheduled_at');
        }

        return view('livewire.member-meetings', [
            'meetings' => $query->paginate(12),
            'meetingTypes' => collect(self::MEETING_TYPES)
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values(),
        ]);
    }
}
