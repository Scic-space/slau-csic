<?php

namespace App\Filament\Widgets;

use App\Models\CtfCompetition;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Training;
use Filament\Widgets\Widget;

class UpcomingScheduleWidget extends Widget
{
    protected string $view = 'filament.widgets.upcoming-schedule';

    protected static ?string $heading = 'Upcoming Schedule';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (auth()->user()?->can('view_events') ?? false)
            || (auth()->user()?->can('view_meetings') ?? false);
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['items' => collect()];
        }

        $items = collect();

        if ($user->can('view_events')) {
            Event::published()
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(5)
                ->get()
                ->each(fn ($e) => $items->push([
                    'type' => 'Event',
                    'title' => $e->title,
                    'date' => $e->start_date,
                    'location' => $e->location ?? 'N/A',
                    'status' => $e->status,
                    'color' => 'primary',
                ]));
        }

        if ($user->can('view_meetings')) {
            Meeting::upcoming()
                ->limit(5)
                ->get()
                ->each(fn ($m) => $items->push([
                    'type' => 'Meeting',
                    'title' => $m->title,
                    'date' => $m->scheduled_at,
                    'location' => $m->location ?? 'N/A',
                    'status' => $m->getStatusAttribute(),
                    'color' => 'warning',
                ]));
        }

        if ($user->can('view_trainings')) {
            Training::published()
                ->where(function ($q) {
                    $q->where('available_from', '>=', now())
                        ->orWhere(function ($q2) {
                            $q2->where('available_from', '<=', now())
                                ->where('available_until', '>=', now());
                        });
                })
                ->orderBy('available_from')
                ->limit(5)
                ->get()
                ->each(fn ($t) => $items->push([
                    'type' => 'Training',
                    'title' => $t->title,
                    'date' => $t->available_from,
                    'location' => 'Online',
                    'status' => $t->is_published ? 'Open' : 'Draft',
                    'color' => 'success',
                ]));
        }

        if ($user->hasAnyRole(['admin', 'super-admin', 'CTF Lead'])) {
            CtfCompetition::published()
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(5)
                ->get()
                ->each(fn ($c) => $items->push([
                    'type' => 'CTF',
                    'title' => $c->title,
                    'date' => $c->start_date,
                    'location' => 'Online',
                    'status' => $c->status,
                    'color' => 'danger',
                ]));
        }

        return [
            'items' => $items->sortBy('date')->take(10),
        ];
    }
}
