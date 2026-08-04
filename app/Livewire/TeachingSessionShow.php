<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Meeting;
use App\Models\MeetingFeedback;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeachingSessionShow extends Component
{
    use GuardsPendingMembers;

    public Meeting $meeting;

    public bool $hasAttended = false;

    public int $rating = 0;

    public string $comment = '';

    public bool $hasSubmittedFeedback = false;

    public bool $showFeedbackForm = false;

    public function mount(Meeting $meeting): void
    {
        abort_unless($meeting->isTeachingSession(), 404);

        $this->meeting = $meeting->load(['creator', 'training', 'agendaItems', 'attachments', 'feedback.user']);

        $this->hasAttended = $meeting->hasUserAttended(Auth::user());

        $this->hasSubmittedFeedback = MeetingFeedback::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function toggleFeedbackForm(): void
    {
        $this->showFeedbackForm = ! $this->showFeedbackForm;
    }

    public function submitFeedback(): void
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($this->hasSubmittedFeedback) {
            return;
        }

        MeetingFeedback::create([
            'meeting_id' => $this->meeting->id,
            'user_id' => Auth::id(),
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        $this->hasSubmittedFeedback = true;
        $this->showFeedbackForm = false;
        $this->rating = 0;
        $this->comment = '';

        $this->meeting->load('feedback.user');
    }

    public function render()
    {
        return view('livewire.teaching-session-show', [
            'meeting' => $this->meeting,
            'hasAttended' => $this->hasAttended,
        ]);
    }
}
