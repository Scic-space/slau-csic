<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpiring extends Notification
{
    use Queueable;

    public function __construct(
        public string $expiresAt,
    ) {}

    public function getPreferenceType(): string
    {
        return 'membership_updates';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Membership Is Expiring Soon')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your membership is set to expire on '.$this->expiresAt.'.')
            ->line('Please log in to renew your membership to continue enjoying club benefits.')
            ->action('Renew Membership', url('/membership'))
            ->line('Thank you for being part of our community!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject' => 'Membership Expiring Soon',
            'expires_at' => $this->expiresAt,
        ];
    }
}
