<?php

namespace App\Filament\Resources\Attendance;

use App\Filament\Resources\Attendance\Pages\EditEventAttendance;
use App\Filament\Resources\Attendance\Pages\ListEventAttendance;
use App\Models\EventAttendance;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventAttendanceResource extends Resource
{
    protected static ?string $model = EventAttendance::class;

    protected static ?string $slug = 'attendance';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'excused' => 'Excused',
                    ])
                    ->required(),
                DateTimePicker::make('checked_in_at')
                    ->label('Checked In At'),
                DateTimePicker::make('recorded_at')
                    ->label('Recorded At'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'excused' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('checked_in_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('recorded_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'excused' => 'Excused',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventAttendance::route('/'),
            'edit' => EditEventAttendance::route('/{record}/edit'),
        ];
    }
}
