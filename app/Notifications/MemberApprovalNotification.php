<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberApprovalNotification extends Notification
{
    public function __construct(
        public User $approvedBy,
        public ?string $notes = null
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
            ->subject('Welcome to Cybersecurity & Innovations Club!')
            ->greeting('Congratulations, '.$notifiable->name.'!')
            ->line('Your membership application has been approved by '.$this->approvedBy->name.'.')
            ->line('You now have full access to all club features and benefits.')
            ->when($this->notes, function ($message) {
                $message->line('**Additional Notes:** '.$this->notes);
            })
            ->action('View Your Profile', route('user-profile'))
            ->line('Welcome to our community! We look forward to your active participation.')
            ->salutation('Best regards, The Cybersecurity & Innovations Club Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_approved',
            'approved_by' => $this->approvedBy->name,
            'approved_by_id' => $this->approvedBy->id,
            'notes' => $this->notes,
            'message' => 'Your membership has been approved!',
        ];
    }
}
