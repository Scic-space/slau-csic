<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Notifications\MeetingCancelledNotification;
use App\Notifications\MeetingRescheduledNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMeeting extends EditRecord
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('open_attendance')
                    ->label('Open Attendance')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (): bool => ! $this->record->isAttendanceOpen() && ! $this->record->hasEnded() && ! $this->record->isCancelled())
                    ->action(function () {
                        $this->record->openAttendance();
                        Notification::make()->title('Attendance opened')->success()->send();
                    }),

                Action::make('close_attendance')
                    ->label('Close Attendance')
                    ->color('warning')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (): bool => $this->record->isAttendanceOpen())
                    ->action(function () {
                        $this->record->closeAttendance();
                        Notification::make()->title('Attendance closed')->success()->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel Meeting')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->visible(fn (): bool => ! $this->record->isCancelled())
                    ->schema([
                        Textarea::make('cancellation_reason')
                            ->label('Reason for cancellation')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (array $data) {
                        $this->record->cancel($data['cancellation_reason']);

                        $this->record->attendance()
                            ->with('user')
                            ->get()
                            ->each(fn ($attendance) => $attendance->user->notify(
                                new MeetingCancelledNotification($this->record, $data['cancellation_reason'])
                            ));

                        Notification::make()->title('Meeting cancelled')->success()->send();
                    }),

                Action::make('reschedule')
                    ->label('Reschedule')
                    ->color('info')
                    ->icon('heroicon-o-calendar-days')
                    ->visible(fn (): bool => ! $this->record->isCancelled())
                    ->schema([
                        TextInput::make('new_date')
                            ->label('New Date & Time')
                            ->type('datetime-local')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $oldDate = $this->record->scheduled_at->format('M d, Y g:i A');
                        $newDateTime = \Carbon\Carbon::parse($data['new_date']);

                        $this->record->reschedule($newDateTime);

                        $this->record->attendance()
                            ->with('user')
                            ->get()
                            ->each(fn ($attendance) => $attendance->user->notify(
                                new MeetingRescheduledNotification(
                                    $this->record,
                                    $oldDate,
                                    $newDateTime->format('M d, Y g:i A')
                                )
                            ));

                        Notification::make()->title('Meeting rescheduled')->success()->send();
                    }),

                Action::make('finalize_minutes')
                    ->label('Finalize Minutes')
                    ->color('primary')
                    ->icon('heroicon-o-document-check')
                    ->visible(fn (): bool => $this->record->minutes_status === 'draft' && $this->record->hasEnded())
                    ->action(function () {
                        $this->record->finalizeMinutes();
                        Notification::make()->title('Minutes finalized')->success()->send();
                    }),

                Action::make('publish_minutes')
                    ->label('Publish Minutes')
                    ->color('success')
                    ->icon('heroicon-o-globe-alt')
                    ->visible(fn (): bool => $this->record->minutes_status === 'finalized')
                    ->action(function () {
                        $this->record->publishMinutes();
                        Notification::make()->title('Minutes published')->success()->send();
                    }),

                DeleteAction::make(),
            ]),
        ];
    }
}
