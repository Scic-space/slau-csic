<?php

namespace App\Notifications;

use App\Models\ExamAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamGradedNotification extends Notification
{
    use Queueable;

    public function __construct(public ExamAttempt $attempt) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->attempt->passed ? 'passed' : 'failed';
        $examTitle = $this->attempt->exam->title;

        return (new MailMessage)
            ->subject("Exam Graded: {$examTitle} - {$status}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your exam \"{$examTitle}\" has been graded.")
            ->line("Score: {$this->attempt->total_score}%")
            ->line('Status: '.($this->attempt->passed ? 'Passed' : 'Failed'))
            ->when($this->attempt->passed, fn ($m) => $m
                ->line('Congratulations! You passed the exam.')
                ->action('View Certificate', url('/exams/certificates'))
            )
            ->action('View Results', url("/exams/attempts/{$this->attempt->id}/result"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'exam_graded',
            'exam_attempt_id' => $this->attempt->id,
            'exam_id' => $this->attempt->exam_id,
            'exam_title' => $this->attempt->exam->title,
            'total_score' => $this->attempt->total_score,
            'passed' => $this->attempt->passed,
            'message' => "Your exam \"{$this->attempt->exam->title}\" has been graded. Score: {$this->attempt->total_score}%",
        ];
    }
}
