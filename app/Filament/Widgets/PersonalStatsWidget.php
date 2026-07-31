<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PersonalStatsWidget extends BaseWidget
{
    protected ?string $heading = 'My Stats';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return [
            Stat::make('Events Attended', $user->attendance_count ?? 0)
                ->description('Lifetime events')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Total Sessions', $user->total_sessions_attended ?? 0)
                ->description('All sessions')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Current Streak', ($user->current_streak ?? 0).' days')
                ->description($user->current_streak > 0 ? 'Keep it up!' : 'Start attending')
                ->descriptionIcon('heroicon-m-fire')
                ->color($user->current_streak > 0 ? 'success' : 'gray'),

            Stat::make('Points', $user->score ?? 0)
                ->description($user->rank ? 'Rank: '.$user->rank : 'Unranked')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
