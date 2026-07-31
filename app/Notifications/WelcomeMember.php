<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeMember extends Notification
{
    use Queueable;

    public function __construct(
        public User $user,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to SLAU-CSIC!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Thank you for registering with the St. Lawrence University Cybersecurity & Innovations Club.')
            ->line('Your application is now pending review by an administrator.')
            ->line('You will receive a notification once your membership has been approved.')
            ->action('Visit the Club Portal', url('/'))
            ->line('Welcome aboard!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_registered',
            'message' => 'Your membership application has been received and is pending review.',
        ];
    }
}
