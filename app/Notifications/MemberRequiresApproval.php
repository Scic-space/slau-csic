<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberRequiresApproval extends Notification
{
    use Queueable;

    public function __construct(
        public User $pendingUser,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Member Requires Approval')
            ->greeting("Hello {$notifiable->name},")
            ->line("A new member has registered: {$this->pendingUser->name} ({$this->pendingUser->email})")
            ->action('Review Pending Members', url('/admin/pending-members'))
            ->line('Please review and approve or reject this application.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_requires_approval',
            'pending_user_id' => $this->pendingUser->id,
            'pending_user_name' => $this->pendingUser->name,
            'message' => "New member {$this->pendingUser->name} requires approval.",
        ];
    }
}
