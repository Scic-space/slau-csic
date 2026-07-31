<?php

namespace App\Filament\Resources\Exams\Pages;

use App\Filament\Resources\Exams\ExamResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExam extends EditRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('view_submissions')
                    ->label('Submissions')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (): string => url('/admin/exam-attempts'))
                    ->openUrlInNewTab(),

                DeleteAction::make(),
            ]),
        ];
    }
}
