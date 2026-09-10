<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Fine;
use App\Models\Meeting;
use App\Models\Poll;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SidebarBadges extends Component
{
    public int $unreadAnnouncements = 0;

    public int $unansweredPolls = 0;

    public int $unpaidFines = 0;

    public int $upcomingMeetings = 0;

    public int $eventsToRegister = 0;

    public int $upcomingMyEvents = 0;

    public function mount(): void
    {
        $this->refreshCounts();
    }

    #[On(['sidebar-badges-refresh'])]
    public function refreshCounts(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->reset(['unreadAnnouncements', 'unansweredPolls', 'unpaidFines', 'upcomingMeetings', 'eventsToRegister', 'upcomingMyEvents']);

            return;
        }

        $this->unreadAnnouncements = Announcement::published()
            ->active()
            ->whereDoesntHave('views', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        $this->unansweredPolls = Poll::published()
            ->active()
            ->whereDoesntHave('votes', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        $this->unpaidFines = Fine::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'partially_paid'])
            ->count();

        $canManageMeetings = $user->hasAnyRole(['admin', 'super-admin', 'President', 'Treasurer', 'General Secretary']);
        $this->upcomingMeetings = $canManageMeetings ? Meeting::upcoming()->count() : 0;

        $this->eventsToRegister = Event::publiclyVisible()
            ->where('start_date', '>=', now())
            ->where('registration_required', true)
            ->where(fn ($q) => $q->whereNull('registration_deadline')->orWhere('registration_deadline', '>', now()))
            ->whereDoesntHave('registrations', fn ($q) => $q->where('user_id', $user->id)->whereIn('status', ['registered', 'waitlist']))
            ->count();

        $this->upcomingMyEvents = Event::whereHas('registrations', fn ($q) => $q->where('user_id', $user->id)->where('status', 'registered'))
            ->where('start_date', '>', now())
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    public function render()
    {
        return view('livewire.sidebar-badges');
    }
}
