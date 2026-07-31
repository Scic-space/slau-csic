<?php

namespace App\Filament\Resources\Meetings\Tables;

use App\Models\Meeting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Meeting::with('creator'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', ucfirst($state)))
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),

                TextColumn::make('location')
                    ->searchable()
                    ->limit(20),

                TextColumn::make('meeting_code')
                    ->label('Code')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : '-')
                    ->toggleable(),

                TextColumn::make('getAttendanceCount')
                    ->label('Attended')
                    ->getStateUsing(fn (Meeting $record): int => $record->getAttendanceCount())
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (Meeting $record): string => $record->status_color)
                    ->getStateUsing(fn (Meeting $record): string => $record->status)
                    ->sortable(),

                TextColumn::make('cancelled_at')
                    ->label('Cancelled')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'general' => 'General',
                        'executive' => 'Executive',
                        'special' => 'Special',
                        'training' => 'Training',
                        'workshop' => 'Workshop',
                        'teaching_session' => 'Teaching Session',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->query(function ($query, $state) {
                        if (! $state) {
                            return;
                        }

                        return match ($state) {
                            'scheduled' => $query->notCancelled()->where('attendance_open', false)->where('scheduled_at', '>', now()),
                            'ongoing' => $query->notCancelled()->where('attendance_open', true),
                            'completed' => $query->notCancelled()->whereNotNull('ended_at'),
                            'cancelled' => $query->cancelled(),
                            default => $query,
                        };
                    }),
                TernaryFilter::make('is_recurring')
                    ->label('Recurring'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
