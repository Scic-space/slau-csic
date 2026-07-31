<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Widgets\TableWidget;

class JoinableMeetingsWidget extends TableWidget
{
    protected static ?string $heading = 'Live & Upcoming Meetings';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_meetings') ?? false;
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Meeting::with('creator')
            ->whereNotNull('meeting_link')
            ->whereNull('ended_at')
            ->whereNull('cancelled_at')
            ->where(function ($q) {
                $q->where('attendance_open', true)
                    ->orWhere(function ($q) {
                        $q->where('scheduled_at', '>=', now()->subHours(2))
                            ->where('scheduled_at', '<=', now()->addMinutes(30));
                    });
            })
            ->orderBy('scheduled_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label('Meeting')
                ->searchable()
                ->weight('bold'),
            Tables\Columns\TextColumn::make('creator.name')
                ->label('Host')
                ->badge()
                ->color('gray'),
            Tables\Columns\TextColumn::make('scheduled_at')
                ->label('When')
                ->dateTime('D, M j · g:i A')
                ->sortable(),
            Tables\Columns\TextColumn::make('location')
                ->label('Location')
                ->placeholder('Online'),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn ($record) => match ($record->status) {
                    'ongoing' => 'success',
                    'scheduled' => 'info',
                    'completed' => 'gray',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('join')
                ->label('Join')
                ->icon('heroicon-m-video-camera')
                ->color('primary')
                ->visible(fn (Meeting $record): bool => $record->hasMeetingLink() && $record->isJoinable())
                ->url(fn (Meeting $record): string => $record->meeting_link)
                ->openUrlInNewTab(),
        ];
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-video-camera';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No live meetings';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'There are no currently active or upcoming meetings with join links.';
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
