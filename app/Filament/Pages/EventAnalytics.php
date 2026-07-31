<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AttendanceTrendChart;
use App\Filament\Widgets\EventStatsWidget;
use BackedEnum;
use Filament\Pages\Page;

class EventAnalytics extends Page
{
    protected string $view = 'filament.pages.event-analytics';

    protected static ?string $title = 'Event Analytics';

    public static function getNavigationLabel(): string
    {
        return 'Analytics';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Events';
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-chart-bar';
    }

    public function getHeaderWidgets(): array
    {
        return [
            EventStatsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            AttendanceTrendChart::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_events') ?? false;
    }
}
