<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AttendanceTrendChart;
use App\Filament\Widgets\CtfParticipationChart;
use App\Filament\Widgets\CtfStatsWidget;
use App\Filament\Widgets\ElectionStatsWidget;
use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\JoinableMeetingsWidget;
use App\Filament\Widgets\MemberGrowthChart;
use App\Filament\Widgets\PendingApprovalsWidget;
use App\Filament\Widgets\PersonalStatsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RevenueOverviewChart;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TrainingStatsWidget;
use App\Filament\Widgets\UpcomingScheduleWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Admin Dashboard';

    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            PersonalStatsWidget::class,
            StatsOverviewWidget::class,
            JoinableMeetingsWidget::class,
            FinancialStatsWidget::class,
            TrainingStatsWidget::class,
            CtfStatsWidget::class,
            ElectionStatsWidget::class,
            PendingApprovalsWidget::class,
            MemberGrowthChart::class,
            AttendanceTrendChart::class,
            RevenueOverviewChart::class,
            CtfParticipationChart::class,
            UpcomingScheduleWidget::class,
            RecentActivityWidget::class,
        ];
    }
}
