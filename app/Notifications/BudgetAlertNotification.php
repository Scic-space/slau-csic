<?php

namespace App\Notifications;

use App\Models\BudgetCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public BudgetCategory $category,
        public string $alertLevel,
        public float $spent,
        public float $allocated,
        public float $percentage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $categoryType = ucfirst($this->category->type);
        $message = match ($this->alertLevel) {
            'over_budget' => "The {$categoryType} category '{$this->category->name}' has exceeded its budget of UGX ".number_format($this->allocated, 0).' by UGX '.number_format($this->spent - $this->allocated, 0).'.',
            'critical' => "The {$categoryType} category '{$this->category->name}' has used {$this->percentage}% of its budget. Only 10% remaining.",
            'warning' => "The {$categoryType} category '{$this->category->name}' has used {$this->percentage}% of its budget.",
            default => "Budget alert for {$categoryType} category '{$this->category->name}': {$this->percentage}% used.",
        };

        return (new MailMessage)
            ->subject(match ($this->alertLevel) {
                'over_budget' => 'Budget Exceeded: '.$this->category->name,
                'critical' => 'Critical Budget Alert: '.$this->category->name,
                default => 'Budget Warning: '.$this->category->name,
            })
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line('Spent: UGX '.number_format($this->spent, 0))
            ->line('Allocated: UGX '.number_format($this->allocated, 0))
            ->action('View Budget', url('/admin/treasurer-dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'budget_alert',
            'alert_level' => $this->alertLevel,
            'category_id' => $this->category->id,
            'category_name' => $this->category->name,
            'spent' => $this->spent,
            'allocated' => $this->allocated,
            'percentage' => $this->percentage,
            'message' => match ($this->alertLevel) {
                'over_budget' => "Budget exceeded: {$this->category->name} is over by UGX ".number_format($this->spent - $this->allocated, 0),
                'critical' => "Critical: {$this->category->name} at {$this->percentage}% of budget",
                default => "Warning: {$this->category->name} at {$this->percentage}% of budget",
            },
        ];
    }
}
