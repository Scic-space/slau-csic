<?php

namespace App\Filament\Resources\ExamAttempts\Pages;

use App\Filament\Resources\ExamAttempts\ExamAttemptResource;
use App\Services\ExamGradingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExamAttempt extends EditRecord
{
    protected static string $resource = ExamAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculate')
                ->label('Recalculate Total')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->action(function (): void {
                    app(ExamGradingService::class)->calculateTotalScore($this->record);

                    Notification::make()
                        ->title('Total score recalculated')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
