<?php

namespace App\Filament\Widgets;

use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CtfStatsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false;
    }

    protected function getStats(): array
    {
        $activeCompetitions = CtfCompetition::published()
            ->currentlyActive()
            ->count();

        $upcomingCompetitions = CtfCompetition::published()
            ->where('start_date', '>', now())
            ->count();

        $totalSubmissions = CtfSubmission::where('is_correct', true)->count();

        $uniqueParticipants = CtfSubmission::where('is_correct', true)
            ->distinct('user_id')
            ->count('user_id');

        return [
            Stat::make('Active CTFs', $activeCompetitions)
                ->description($upcomingCompetitions > 0 ? $upcomingCompetitions.' upcoming' : 'No upcoming competitions')
                ->descriptionIcon('heroicon-m-flag')
                ->color('danger'),

            Stat::make('Correct Submissions', $totalSubmissions)
                ->description('Accepted flag submissions')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Participants', $uniqueParticipants)
                ->description('Members who solved challenges')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Total Competitions', CtfCompetition::published()->count())
                ->description('All published CTF events')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
