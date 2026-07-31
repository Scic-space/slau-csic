<?php

namespace App\Notifications;

use App\Models\Election;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NominationStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Election $election,
        public string $status,
        public ?string $notes = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approved = $this->status === 'approved';
        $underReview = $this->status === 'under_review';
        $shortlisted = $this->status === 'shortlisted';

        $subject = match ($this->status) {
            'approved' => "Application Approved: {$this->election->title}",
            'rejected' => "Application Update: {$this->election->title}",
            'shortlisted' => "Application Shortlisted: {$this->election->title}",
            default => "Application Under Review: {$this->election->title}",
        };

        $message = match ($this->status) {
            'approved' => "Your application for {$this->election->title} ({$this->election->position}) has been approved! You are now listed as a candidate.",
            'shortlisted' => "Your application for {$this->election->title} ({$this->election->position}) has been shortlisted. You are among the top candidates.",
            'rejected' => "Your application for {$this->election->title} ({$this->election->position}) has been reviewed. You may re-apply for future elections.",
            default => "Your application for {$this->election->title} ({$this->election->position}) is now under review.",
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->when($this->notes, fn ($m) => $m->line("Admin notes: {$this->notes}"))
            ->when($approved || $shortlisted, fn ($m) => $m->action('View Election', url('/voting')))
            ->action('View My Applications', url('/voting/my-applications'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_'.$this->status,
            'election_id' => $this->election->id,
            'title' => $this->election->title,
            'message' => "Your application for {$this->election->title} has been {$this->status}.",
        ];
    }
}
