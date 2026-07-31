<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MeetingAttendanceTrendChart;
use App\Filament\Widgets\MeetingStatsWidget;
use BackedEnum;
use Filament\Pages\Page;

class MeetingAnalytics extends Page
{
    protected string $view = 'filament.pages.meeting-analytics';

    protected static ?string $title = 'Meeting Analytics';

    public static function getNavigationLabel(): string
    {
        return 'Analytics';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Meetings';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-chart-bar';
    }

    public function getHeaderWidgets(): array
    {
        return [
            MeetingStatsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            MeetingAttendanceTrendChart::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_attendance') ?? false;
    }
}
