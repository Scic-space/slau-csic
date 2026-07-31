<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Meeting;
use App\Models\User;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity';

    protected static ?string $heading = 'Recent Activity';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_users') ?? false;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['activities' => collect()];
        }

        $activities = collect();

        if ($user->can('approve_members')) {
            User::where('membership_status', 'pending')
                ->latest()
                ->limit(3)
                ->get()
                ->each(fn ($u) => $activities->push([
                    'description' => "{$u->name} registered and is pending approval",
                    'type' => 'New Registration',
                    'time' => $u->created_at,
                    'color' => 'warning',
                ]));

            User::whereNotNull('approved_at')
                ->latest('approved_at')
                ->limit(3)
                ->get()
                ->each(fn ($u) => $activities->push([
                    'description' => "{$u->name} was approved as member",
                    'type' => 'Member Approved',
                    'time' => $u->approved_at,
                    'color' => 'success',
                ]));
        }

        if ($user->can('approve_expenditures')) {
            \App\Models\Transaction::pending()
                ->latest()
                ->limit(3)
                ->get()
                ->each(fn ($t) => $activities->push([
                    'description' => 'Transaction of UGX '.number_format($t->amount, 0)." ({$t->description}) awaits approval",
                    'type' => 'Transaction',
                    'time' => $t->created_at,
                    'color' => 'warning',
                ]));
        }

        if ($user->can('view_events')) {
            Event::where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->limit(3)
                ->get()
                ->each(fn ($e) => $activities->push([
                    'description' => "Event \"{$e->title}\" was created",
                    'type' => 'Event Created',
                    'time' => $e->created_at,
                    'color' => 'primary',
                ]));
        }

        if ($user->can('view_meetings')) {
            Meeting::where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->limit(3)
                ->get()
                ->each(fn ($m) => $activities->push([
                    'description' => "Meeting \"{$m->title}\" was created",
                    'type' => 'Meeting',
                    'time' => $m->created_at,
                    'color' => 'info',
                ]));
        }

        return [
            'activities' => $activities->sortByDesc('time')->take(10),
        ];
    }
}
