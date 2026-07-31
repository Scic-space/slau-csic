<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve Membership')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->membership_status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->approve(auth()->user());
                }),

            Action::make('reject')
                ->label('Reject Membership')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->membership_status === 'pending')
                ->schema([
                    Textarea::make('rejection_notes')
                        ->label('Rejection Notes')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->reject(auth()->user(), $data['rejection_notes']);
                }),

            Action::make('suspend')
                ->label('Suspend Member')
                ->icon('heroicon-o-pause-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->membership_status === 'active')
                ->schema([
                    Textarea::make('reason')
                        ->label('Suspension Reason')
                        ->required(),
                    DateTimePicker::make('suspended_until')
                        ->label('Suspend Until (optional)'),
                ])
                ->action(function (array $data) {
                    $this->record->suspend(
                        $data['reason'],
                        auth()->user(),
                        $data['suspended_until'] ? Carbon::parse($data['suspended_until']) : null,
                    );
                }),

            Action::make('reactivate')
                ->label('Reactivate Member')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn (): bool => $this->record->membership_status === 'suspended')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'membership_status' => 'active',
                        'suspension_reason' => null,
                        'suspended_until' => null,
                        'suspended_by' => null,
                    ]);
                }),

            Action::make('convert_to_alumni')
                ->label('Convert to Alumni')
                ->icon('heroicon-o-academic-cap')
                ->color('gray')
                ->visible(fn (): bool => $this->record->membership_status === 'active' && $this->record->membership_type !== 'alumni')
                ->requiresConfirmation()
                ->modalHeading('Convert to Alumni')
                ->modalDescription('This will mark the member as alumni.')
                ->action(function () {
                    $this->record->convertToAlumni();
                }),

            DeleteAction::make(),
        ];
    }
}
