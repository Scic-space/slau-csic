<?php

namespace App\Filament\Resources\QuestionBankQuestions\Pages;

use App\Filament\Resources\QuestionBankQuestions\QuestionBankQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionBankQuestion extends EditRecord
{
    protected static string $resource = QuestionBankQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->data['type'] === 'true_false') {
            $correct = $this->data['correct_answer'] ?? 'true';
            $this->record->options()->delete();
            $this->record->options()->createMany([
                ['option_text' => 'True', 'is_correct' => $correct === 'true', 'order' => 0],
                ['option_text' => 'False', 'is_correct' => $correct === 'false', 'order' => 1],
            ]);
        }
    }
}
