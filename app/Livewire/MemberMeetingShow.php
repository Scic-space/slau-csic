<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Meeting;
use App\Models\MeetingInterest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Meeting Details')]
class MemberMeetingShow extends Component
{
    use GuardsPendingMembers;

    public Meeting $meeting;

    public bool $isInterested = false;

    public int $interestCount = 0;

    public ?string $attendanceStatus = null;

    public function mount(Meeting $meeting): void
    {
        $user = Auth::user();

        if ($meeting->isTeachingSession()) {
            $this->redirectRoute('classes.show', ['meeting' => $meeting], navigate: true);

            return;
        }

        $this->meeting = $meeting->load([
            'creator:id,name',
            'instructor:id,name',
            'agendaItems',
            'attachments',
        ]);

        $this->isInterested = $meeting->isInterested($user);
        $this->interestCount = $meeting->interests()->count();
        $this->attendanceStatus = $meeting->attendance()
            ->where('user_id', $user->id)
            ->value('status');
    }

    public function toggleInterest(): void
    {
        $user = Auth::user();

        abort_if($this->meeting->isCancelled() || $this->meeting->hasEnded() || ($this->meeting->isPast() && ! $this->meeting->attendance_open), 403);
        abort_unless($this->meeting->isVisibleTo($user), 403);

        $existing = MeetingInterest::where('meeting_id', $this->meeting->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->isInterested = false;
        } else {
            MeetingInterest::create([
                'meeting_id' => $this->meeting->id,
                'user_id' => $user->id,
            ]);
            $this->isInterested = true;
        }

        $this->interestCount = $this->meeting->interests()->count();
    }

    public function render(): View
    {
        return view('livewire.member-meeting-show');
    }
}
