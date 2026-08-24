<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\MaterialIconStat as Stat;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;

class MemberStatusCards extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('view_users') ?? false;
    }

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            $this->status('All Members', User::query()->count(), 'groups', 'all', 'info'),
            $this->status('Pending Approval', User::query()->where('membership_status', 'pending')->count(), 'pending_actions', 'pending', 'warning'),
            $this->status('Active Members', User::query()->where('membership_status', 'active')->count(), 'person_check', 'active', 'success'),
            $this->status('Alumni', User::query()->where('membership_type', 'alumni')->count(), 'school', 'alumni', 'primary'),
            $this->status('Suspended', User::query()->where('membership_status', 'suspended')->count(), 'person_off', 'suspended', 'danger'),
            $this->status('Expiring Soon', User::query()->expiringSoon()->count(), 'event_busy', 'expiring', 'warning'),
        ];
    }

    private function status(string $label, int $count, string $icon, string $tab, string $color): Stat
    {
        $isActive = request()->string('tab', 'all')->toString() === $tab;

        return Stat::make($label, number_format($count))
            ->icon($icon)
            ->description('View members')
            ->descriptionIcon('arrow_forward')
            ->color($color)
            ->url(UserResource::getUrl('index', ['tab' => $tab]))
            ->extraAttributes([
                'class' => 'rounded-sm transition hover:-translate-y-0.5 hover:shadow-md'.($isActive ? ' ring-2 ring-primary-500/30' : ''),
                'aria-current' => $isActive ? 'page' : null,
            ]);
    }

    /** @return array<string, int> */
    protected function getColumns(): array
    {
        return ['default' => 1, 'md' => 2, 'lg' => 3];
    }
}
