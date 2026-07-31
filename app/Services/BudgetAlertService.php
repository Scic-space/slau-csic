<?php

namespace App\Services;

use App\Models\BudgetCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BudgetAlertNotification;
use Illuminate\Support\Facades\Log;

class BudgetAlertService
{
    public static function checkBudgetAlerts(): void
    {
        $activeCategories = BudgetCategory::where('is_active', true)->get();

        foreach ($activeCategories as $category) {
            $spent = Transaction::where('category', $category->name)
                ->where('status', 'approved')
                ->where('type', $category->type)
                ->whereYear('date', now()->year)
                ->sum('amount');

            $allocated = $category->allocated_amount;

            if ($allocated > 0) {
                $percentage = ($spent / $allocated) * 100;

                if ($percentage >= 100) {
                    self::sendAlert($category, 'over_budget', $spent, $allocated, $percentage);
                } elseif ($percentage >= 90) {
                    self::sendAlert($category, 'critical', $spent, $allocated, $percentage);
                } elseif ($percentage >= 80) {
                    self::sendAlert($category, 'warning', $spent, $allocated, $percentage);
                }
            }
        }
    }

    private static function sendAlert(BudgetCategory $category, string $alertLevel, float $spent, float $allocated, float $percentage): void
    {
        $message = self::formatAlertMessage($category, $alertLevel, $spent, $allocated, $percentage);

        Log::warning('Budget Alert', [
            'category' => $category->name,
            'alert_level' => $alertLevel,
            'spent' => $spent,
            'allocated' => $allocated,
            'percentage' => $percentage,
            'message' => $message,
        ]);

        $notifiable = User::role(['treasurer', 'president', 'super-admin'])->get();

        foreach ($notifiable as $user) {
            $user->notify(new BudgetAlertNotification($category, $alertLevel, $spent, $allocated, $percentage));
        }
    }

    public static function formatAlertMessage(BudgetCategory $category, string $alertLevel, float $spent, float $allocated, float $percentage): string
    {
        $categoryType = ucfirst($category->type);

        return match ($alertLevel) {
            'over_budget' => "Budget Alert: {$categoryType} category '{$category->name}' has exceeded budget by UGX ".number_format($spent - $allocated, 0)." ({$percentage}% used).",
            'critical' => "Critical Budget Alert: {$categoryType} category '{$category->name}' has used {$percentage}% of budget (UGX ".number_format($spent, 0).' / UGX '.number_format($allocated, 0).'). Only 10% remaining.',
            'warning' => "Budget Warning: {$categoryType} category '{$category->name}' has used {$percentage}% of budget (UGX ".number_format($spent, 0).' / UGX '.number_format($allocated, 0).').',
            default => "Budget Alert for {$categoryType} category '{$category->name}': {$percentage}% used.",
        };
    }

    public static function getBudgetStatus(): array
    {
        $activeCategories = BudgetCategory::where('is_active', true)->get();
        $alerts = [];

        foreach ($activeCategories as $category) {
            $spent = Transaction::where('category', $category->name)
                ->where('status', 'approved')
                ->where('type', $category->type)
                ->whereYear('date', now()->year)
                ->sum('amount');

            $allocated = $category->allocated_amount;
            $percentage = $allocated > 0 ? ($spent / $allocated) * 100 : 0;

            if ($percentage >= 80) {
                $alerts[] = [
                    'category' => $category->name,
                    'type' => $category->type,
                    'spent' => $spent,
                    'allocated' => $allocated,
                    'percentage' => $percentage,
                    'status' => $percentage >= 100 ? 'over_budget' : ($percentage >= 90 ? 'critical' : 'warning'),
                    'remaining' => max(0, $allocated - $spent),
                ];
            }
        }

        return $alerts;
    }
}
