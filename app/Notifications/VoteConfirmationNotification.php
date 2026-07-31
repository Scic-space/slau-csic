<?php

namespace App\Notifications;

use App\Models\Election;
use App\Models\ElectionCandidate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VoteConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Election $election,
        public ElectionCandidate $candidate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vote_confirmation',
            'election_id' => $this->election->id,
            'candidate_id' => $this->candidate->id,
            'title' => $this->election->title,
            'candidate_name' => $this->candidate->name,
            'message' => "Your vote for {$this->candidate->name} in {$this->election->title} has been recorded.",
        ];
    }
}
