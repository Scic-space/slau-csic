<?php

namespace App\Filament\Resources\Meetings\RelationManagers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendance';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('checked_in_at')
                    ->label('Checked In')
                    ->dateTime('g:i A')
                    ->sortable(),
                TextColumn::make('check_in_method')
                    ->label('Method')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                    ])
                    ->multiple()
                    ->default(['present', 'late']),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->recordActions([
                Action::make('change_status')
                    ->label('Change status')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'late' => 'Late',
                                'absent' => 'Absent',
                            ])
                            ->required()
                            ->default(fn (Attendance $record): ?string => $record->status),
                    ])
                    ->action(function (array $data, Attendance $record) {
                        app(AttendanceService::class)->setAttendanceStatus(
                            $record->meeting,
                            $record->user,
                            $data['status'],
                            auth()->user(),
                        );
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
