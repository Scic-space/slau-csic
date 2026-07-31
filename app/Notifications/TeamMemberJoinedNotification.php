<?php

namespace App\Notifications;

use App\Models\CtfTeam;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamMemberJoinedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CtfTeam $team,
        protected User $joinedUser,
    ) {}

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'competition_id' => $this->team->ctf_competition_id,
            'joined_user_id' => $this->joinedUser->id,
            'joined_user_name' => $this->joinedUser->name,
            'message' => "{$this->joinedUser->name} joined your team '{$this->team->name}'",
        ];
    }
}
