<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(public Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Exam Available: {$this->exam->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new exam has been published: {$this->exam->title}")
            ->line("Duration: {$this->exam->duration_minutes} minutes")
            ->line("Passing Score: {$this->exam->passing_score}%")
            ->when($this->exam->description, fn ($m) => $m->line($this->exam->description))
            ->action('Take the Exam', url('/exams'))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'exam_published',
            'exam_id' => $this->exam->id,
            'title' => $this->exam->title,
            'message' => "New exam published: {$this->exam->title}",
        ];
    }
}
