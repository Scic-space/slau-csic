<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CtfCompetitions\CtfCompetitionResource;
use App\Filament\Resources\CtfSubmissions\CtfSubmissionResource;
use App\Filament\Resources\CtfWriteups\CtfWriteupResource;
use App\Filament\Support\MaterialIconStat as Stat;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\CtfTeam;
use App\Models\CtfWriteup;
use Filament\Widgets\StatsOverviewWidget;

class CtfDashboardStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    /** @return array{all_competitions: int, active_competitions: int, pending_writeups: int, total_solves: int, total_participants: int, total_teams: int} */
    public function statistics(): array
    {
        return [
            'all_competitions' => CtfCompetition::query()->count(),
            'active_competitions' => CtfCompetition::query()->published()->currentlyActive()->count(),
            'pending_writeups' => CtfWriteup::query()->where('status', 'pending')->count(),
            'total_solves' => CtfSubmission::query()->where('is_correct', true)->count(),
            'total_participants' => CtfSubmission::query()->where('is_correct', true)->distinct('user_id')->count('user_id'),
            'total_teams' => CtfTeam::query()->count(),
        ];
    }

    /** @return array<int, Stat> */
    protected function getStats(): array
    {
        $statistics = $this->statistics();

        return [
            $this->stat('Active Competitions', $statistics['active_competitions'], 'emoji_events', CtfCompetitionResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'published']]]), 'success'),
            $this->stat('Total Solves', $statistics['total_solves'], 'flag', CtfSubmissionResource::getUrl('index'), 'primary'),
            $this->stat('Participants', $statistics['total_participants'], 'people', CtfCompetitionResource::getUrl('index'), 'info'),
            $this->stat('Teams', $statistics['total_teams'], 'groups', CtfCompetitionResource::getUrl('index'), 'warning'),
            $this->stat('Pending Writeups', $statistics['pending_writeups'], 'pending_actions', CtfWriteupResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]]), 'danger'),
            $this->stat('All Competitions', $statistics['all_competitions'], 'military_tech', CtfCompetitionResource::getUrl('index'), 'gray'),
        ];
    }

    private function stat(string $label, int $value, string $icon, string $url, string $color): Stat
    {
        return Stat::make($label, number_format($value))
            ->icon($icon)
            ->description('View records')
            ->descriptionIcon('arrow_forward')
            ->color($color)
            ->url($url)
            ->extraAttributes(['class' => 'rounded-sm transition hover:-translate-y-0.5 hover:shadow-md']);
    }

    /** @return array<string, int> */
    protected function getColumns(): array
    {
        return ['default' => 1, 'md' => 2, 'lg' => 3];
    }
}
