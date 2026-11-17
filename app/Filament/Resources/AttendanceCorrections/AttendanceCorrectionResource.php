<?php

namespace App\Filament\Resources\AttendanceCorrections;

use App\Filament\Resources\AttendanceCorrections\Pages\ManageAttendanceCorrections;
use App\Models\AttendanceCorrectionRequest;
use App\Notifications\AttendanceCorrectionReviewedNotification;
use App\Services\AttendanceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceCorrectionResource extends Resource
{
    protected static ?string $model = AttendanceCorrectionRequest::class;

    protected static ?string $slug = 'attendance-corrections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Correction Requests';

    protected static string|UnitEnum|null $navigationGroup = 'Meetings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('meeting.title')
                    ->label('Class')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('message')
                    ->label('Note')
                    ->limit(60)
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AttendanceCorrectionRequest $record): bool => $record->isPending())
                    ->action(function (AttendanceCorrectionRequest $record) {
                        app(AttendanceService::class)->setAttendanceStatus(
                            $record->meeting,
                            $record->user,
                            'present',
                            auth()->user(),
                        );

                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        try {
                            $record->user->notify(new AttendanceCorrectionReviewedNotification($record->fresh(), true));
                        } catch (\Exception $e) {
                            report($e);
                        }

                        Notification::make()
                            ->title('Correction approved')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Decline')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (AttendanceCorrectionRequest $record): bool => $record->isPending())
                    ->action(function (AttendanceCorrectionRequest $record) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        try {
                            $record->user->notify(new AttendanceCorrectionReviewedNotification($record->fresh(), false));
                        } catch (\Exception $e) {
                            report($e);
                        }

                        Notification::make()
                            ->title('Correction declined')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttendanceCorrections::route('/'),
        ];
    }
}
