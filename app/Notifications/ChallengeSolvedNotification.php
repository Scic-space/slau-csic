<?php

namespace App\Notifications;

use App\Models\CtfChallenge;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChallengeSolvedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CtfChallenge $challenge,
        protected int $points,
    ) {}

    public function getPreferenceType(): string
    {
        return 'challenge_solved';
    }

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        return [
            'challenge_id' => $this->challenge->id,
            'challenge_title' => $this->challenge->title,
            'competition_id' => $this->challenge->ctf_competition_id,
            'points' => $this->points,
            'message' => "You solved '{$this->challenge->title}' for {$this->points} points!",
        ];
    }
}
