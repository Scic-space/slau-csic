<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberApproved extends Notification
{
    use Queueable;

    public function __construct(
        public User $approver,
        public ?string $notes = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to the Club!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your membership application has been approved!')
            ->when($this->notes, fn ($mail) => $mail->line("Notes: {$this->notes}"))
            ->line('You now have full access to the club portal, events, CTF challenges, and more.')
            ->action('Go to Dashboard', url('/dashboard'))
            ->line('Welcome to SLAU-CSIC!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_approved',
            'approved_by' => $this->approver->name,
            'message' => 'Your membership has been approved! Welcome to SLAU-CSIC.',
        ];
    }
}
