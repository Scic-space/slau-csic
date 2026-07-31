<?php

namespace App\Notifications;

use App\Models\CtfWriteup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WriteupReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CtfWriteup $writeup,
    ) {}

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        $status = $this->writeup->status;

        return [
            'writeup_id' => $this->writeup->id,
            'challenge_id' => $this->writeup->ctf_challenge_id,
            'challenge_title' => $this->writeup->challenge->title,
            'status' => $status,
            'message' => $status === 'approved'
                ? "Your writeup for '{$this->writeup->challenge->title}' has been approved!"
                : "Your writeup for '{$this->writeup->challenge->title}' has been rejected.",
        ];
    }
}
