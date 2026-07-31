<?php

namespace App\Filament\Resources\Attendance;

use App\Filament\Resources\Attendance\Pages\EditMeetingAttendance;
use App\Filament\Resources\Attendance\Pages\ListMeetingAttendance;
use App\Models\Attendance;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeetingAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $slug = 'meeting-attendance';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('meeting_id')
                    ->relationship('meeting', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                    ])
                    ->required(),
                Select::make('check_in_method')
                    ->options([
                        'qr_code' => 'QR Code Scan',
                        'manual' => 'Manual Entry',
                        'nfc' => 'NFC Tap',
                        'admin_override' => 'Admin Override',
                    ]),
                DateTimePicker::make('checked_in_at')
                    ->label('Checked In At'),
                TextInput::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meeting.title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('check_in_method')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('checked_in_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                    ]),
                SelectFilter::make('check_in_method')
                    ->options([
                        'qr_code' => 'QR Code Scan',
                        'manual' => 'Manual Entry',
                        'nfc' => 'NFC Tap',
                        'admin_override' => 'Admin Override',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeetingAttendance::route('/'),
            'edit' => EditMeetingAttendance::route('/{record}/edit'),
        ];
    }
}
