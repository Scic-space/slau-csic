<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\AttendanceCorrectionRequest;
use App\Models\Meeting;
use App\Models\MeetingFeedback;
use App\Models\MeetingInterest;
use App\Models\User;
use App\Notifications\AttendanceCorrectionRequestedNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeachingSessionShow extends Component
{
    use GuardsPendingMembers;

    public Meeting $meeting;

    public bool $hasAttended = false;

    public ?int $attendanceId = null;

    public bool $isInterested = false;

    public int $interestCount = 0;

    public bool $hasPendingCorrection = false;

    public bool $showCorrectionForm = false;

    public string $correctionMessage = '';

    public int $rating = 0;

    public string $comment = '';

    public bool $hasSubmittedFeedback = false;

    public bool $showFeedbackForm = false;

    public function mount(Meeting $meeting): void
    {
        abort_unless($meeting->isTeachingSession(), 404);

        $user = Auth::user();

        $this->attendanceId = $meeting->attendance()
            ->where('user_id', $user->id)
            ->whereIn('status', ['present', 'late'])
            ->value('id');

        $this->hasAttended = $this->attendanceId !== null;

        if ($meeting->hasEnded()) {
            $isAssociated = $meeting->hasUserAttended($user);
            $isStaff = $user->hasAnyRole(['admin', 'super-admin']) || $meeting->created_by === $user->id;

            abort_unless($this->hasAttended || $isAssociated || $isStaff, 403);
        }

        $this->meeting = $meeting->load(['creator', 'training', 'agendaItems', 'attachments', 'feedback.user']);

        $this->isInterested = $meeting->isInterested($user);
        $this->interestCount = $meeting->interests()->count();
        $this->hasPendingCorrection = AttendanceCorrectionRequest::where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        $this->hasSubmittedFeedback = MeetingFeedback::where('meeting_id', $meeting->id)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function toggleFeedbackForm(): void
    {
        $this->showFeedbackForm = ! $this->showFeedbackForm;
    }

    public function setRating(int $value): void
    {
        $this->rating = max(1, min(5, $value));
    }

    public function toggleCorrectionForm(): void
    {
        $this->showCorrectionForm = ! $this->showCorrectionForm;
    }

    public function requestCorrection(): void
    {
        $user = Auth::user();

        abort_unless($this->meeting->hasEnded(), 403);

        if ($this->hasAttended || $this->hasPendingCorrection) {
            return;
        }

        $this->validate([
            'correctionMessage' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = $this->meeting->attendance()
            ->where('user_id', $user->id)
            ->first();

        $request = AttendanceCorrectionRequest::firstOrCreate(
            [
                'meeting_id' => $this->meeting->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ],
            [
                'attendance_id' => $existing?->id,
                'message' => $this->correctionMessage ?: null,
            ]
        );

        $request->load(['meeting', 'user']);

        $admins = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'super-admin']))->get();

        foreach ($admins as $admin) {
            try {
                $admin->notify(new AttendanceCorrectionRequestedNotification($request));
            } catch (\Exception $e) {
                report($e);
            }
        }

        $this->hasPendingCorrection = true;
        $this->showCorrectionForm = false;
        $this->correctionMessage = '';
    }

    public function toggleInterest(): void
    {
        $user = Auth::user();

        abort_if($this->meeting->isCancelled() || $this->meeting->hasEnded(), 403);
        abort_unless($this->meeting->canUserAttend($user), 403);

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

    public function submitFeedback(): void
    {
        abort_unless($this->hasAttended, 403);

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
            'averageRating' => round($this->meeting->feedback->avg('rating') ?? 0, 1),
            'feedbackCount' => $this->meeting->feedback->count(),
        ]);
    }
}
