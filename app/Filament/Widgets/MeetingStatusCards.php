<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Support\MaterialIconStat as Stat;
use App\Models\Meeting;
use Filament\Widgets\StatsOverviewWidget;

class MeetingStatusCards extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            $this->status('All', Meeting::query()->count(), 'groups', 'all', 'info'),
            $this->status('Upcoming', Meeting::query()->upcoming()->count(), 'event_upcoming', 'upcoming', 'primary'),
            $this->status('Ongoing', Meeting::query()->notCancelled()->where('attendance_open', true)->count(), 'sensors', 'ongoing', 'success'),
            $this->status('Past', Meeting::query()->past()->count(), 'history', 'past', 'gray'),
            $this->status('Cancelled', Meeting::query()->cancelled()->count(), 'event_busy', 'cancelled', 'danger'),
        ];
    }

    private function status(string $label, int $count, string $icon, string $tab, string $color): Stat
    {
        $isActive = request()->string('tab', 'all')->toString() === $tab;

        return Stat::make($label, number_format($count))
            ->icon($icon)
            ->description('View meetings')
            ->descriptionIcon('arrow_forward')
            ->color($color)
            ->url(MeetingResource::getUrl('index', ['tab' => $tab]))
            ->extraAttributes([
                'class' => 'rounded-sm transition hover:-translate-y-0.5 hover:shadow-md'.($isActive ? ' ring-2 ring-primary-500/30' : ''),
                'aria-current' => $isActive ? 'page' : null,
            ]);
    }

    /**
     * @return array<string, int>
     */
    protected function getColumns(): array
    {
        return [
            'default' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }
}
