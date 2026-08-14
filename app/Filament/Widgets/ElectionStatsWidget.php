<?php

namespace App\Filament\Widgets;

use App\Models\Election;
use App\Models\ElectionVote;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ElectionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'President', 'General Secretary']) ?? false;
    }

    protected function getStats(): array
    {
        $activeElections = Election::where('status', 'open')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->count();

        $suspendedElections = Election::where('status', 'suspended')->count();

        $upcomingElections = Election::where('starts_at', '>', now())->count();

        $totalVotesCast = ElectionVote::whereHas('election', fn ($q) => $q->where('status', 'open'))->count();

        $totalEligible = User::activeMembers()->count();

        $closingSoon = Election::where('status', 'open')
            ->where('ends_at', '<=', now()->addDay())
            ->where('ends_at', '>', now())
            ->count();

        $pendingPublish = Election::whereNotNull('results_publish_at')
            ->where('results_publish_at', '<=', now())
            ->where('results_visible', false)
            ->count();

        return [
            Stat::make('Active Elections', $activeElections)
                ->description($upcomingElections > 0 ? $upcomingElections.' upcoming' : 'No upcoming elections')
                ->descriptionIcon('heroicon-m-scale')
                ->color($activeElections > 0 ? 'warning' : 'primary'),

            Stat::make('Votes Cast', $totalVotesCast)
                ->description('Out of '.$totalEligible.' eligible voters')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Turnout', $totalEligible > 0 ? round(($totalVotesCast / $totalEligible) * 100, 1).'%' : '0%')
                ->description('Voter participation rate')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($totalEligible > 0 && ($totalVotesCast / $totalEligible) >= 0.5 ? 'success' : 'warning'),

            Stat::make('Attention', $this->attentionText($suspendedElections, $closingSoon, $pendingPublish))
                ->description($this->attentionDescription($suspendedElections, $closingSoon, $pendingPublish))
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($suspendedElections > 0 || $closingSoon > 0 ? 'danger' : 'info'),
        ];
    }

    private function attentionText(int $suspended, int $closingSoon, int $pendingPublish): string
    {
        $parts = [];
        if ($suspended > 0) {
            $parts[] = $suspended.' suspended';
        }
        if ($closingSoon > 0) {
            $parts[] = $closingSoon.' closing soon';
        }
        if ($pendingPublish > 0) {
            $parts[] = $pendingPublish.' to publish';
        }

        return empty($parts) ? 'All clear' : implode(', ', $parts);
    }

    private function attentionDescription(int $suspended, int $closingSoon, int $pendingPublish): string
    {
        if ($suspended > 0) {
            return 'Suspended elections need attention';
        }
        if ($closingSoon > 0) {
            return 'Elections closing within 24 hours';
        }
        if ($pendingPublish > 0) {
            return 'Results scheduled for publication';
        }

        return 'No issues requiring attention';
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
