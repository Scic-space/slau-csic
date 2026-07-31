<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberRejected extends Notification
{
    use Queueable;

    public function __construct(
        public User $rejecter,
        public ?string $notes = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on Your Club Membership Application')
            ->greeting("Hello {$notifiable->name},")
            ->line('After reviewing your application, we regret to inform you that your membership request has not been approved at this time.')
            ->when($this->notes, fn ($mail) => $mail->line("Reason: {$this->notes}"))
            ->line('You are welcome to reapply in the future.')
            ->action('Visit Our Website', url('/'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_rejected',
            'message' => 'Your membership application has been reviewed and was not approved.',
        ];
    }
}
