<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MemberGrowthChart;
use App\Filament\Widgets\MemberStatusCards;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class MembershipStatistics extends Page
{
    protected string $view = 'filament.pages.membership-statistics';

    protected static ?string $title = 'Membership Statistics';

    protected static ?string $slug = 'membership-statistics';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Membership Statistics';

    protected static string|UnitEnum|null $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_users') ?? false;
    }

    public function getHeaderWidgets(): array
    {
        return [
            MemberStatusCards::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            MemberGrowthChart::class,
        ];
    }

    /** @return array<string, mixed> */
    public function getStatistics(): array
    {
        $total = User::query()->count();
        $active = User::query()->where('membership_status', 'active')->count();

        return [
            'total' => $total,
            'active' => $active,
            'pending' => User::query()->where('membership_status', 'pending')->count(),
            'suspended' => User::query()->where('membership_status', 'suspended')->count(),
            'inactive' => User::query()->where('membership_status', 'inactive')->count(),
            'expiring_soon' => User::query()->expiringSoon()->count(),
            'new_this_month' => User::query()->where('created_at', '>=', now()->startOfMonth())->count(),
            'new_this_year' => User::query()->where('created_at', '>=', now()->startOfYear())->count(),
            'active_rate' => $total > 0 ? round(($active / $total) * 100, 1) : 0.0,
            'by_status' => $this->breakdown('membership_status'),
            'by_type' => $this->breakdown('membership_type'),
            'by_faculty' => $this->breakdown('faculty', 'Unspecified'),
            'by_program' => $this->breakdown('program', 'Unspecified'),
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function breakdown(string $column, ?string $blankLabel = null): array
    {
        $counts = User::query()
            ->selectRaw("COALESCE(NULLIF({$column}, ''), NULL) as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->pluck('total', 'label')
            ->all();

        if ($blankLabel === null) {
            return $counts;
        }

        $result = [];

        foreach ($counts as $label => $count) {
            $result[$label === null ? $blankLabel : $label] = (int) $count;
        }

        return $result;
    }
}
