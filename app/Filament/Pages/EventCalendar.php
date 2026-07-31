<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\EventCategory;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class EventCalendar extends Page
{
    protected string $view = 'filament.pages.event-calendar';

    protected static ?string $title = 'Event Calendar';

    public static function getNavigationLabel(): string
    {
        return 'Calendar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Events';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-calendar-days';
    }

    public function getEventsJsonProperty(): array
    {
        return Event::query()
            ->with('categories')
            ->orderBy('start_date')
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date?->format('Y-m-d\TH:i:s'),
                'end' => $event->end_date?->format('Y-m-d\TH:i:s'),
                'color' => $event->categories->first()?->color ?? '#465fff',
                'location' => $event->location,
                'description' => strip_tags($event->description ?? ''),
                'slug' => $event->slug,
                'status' => $event->status,
                'categoryIds' => $event->categories->pluck('id')->toArray(),
                'editUrl' => EventResource::getUrl('edit', ['record' => $event]),
            ])
            ->toArray();
    }

    public function getCategoriesProperty(): Collection
    {
        return EventCategory::query()
            ->active()
            ->ordered()
            ->get();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_events') ?? false;
    }
}
