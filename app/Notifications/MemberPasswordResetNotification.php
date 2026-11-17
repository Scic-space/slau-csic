<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberPasswordResetNotification extends Notification
{
    public function __construct(
        public string $temporaryPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your SLAU CSIC password has been reset')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A club administrator has reset your password.')
            ->line("Your temporary password is: **{$this->temporaryPassword}**")
            ->line('For your security, you will be asked to choose a new password the next time you sign in.')
            ->action('Sign In', route('auth.login'))
            ->line('If you did not expect this, please contact the club administration immediately.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'An administrator reset your password. Please sign in with the temporary password and set a new one.',
        ];
    }
}
