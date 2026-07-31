<?php

namespace App\Filament\Resources\QuestionBankQuestions\Pages;

use App\Filament\Resources\QuestionBankQuestions\QuestionBankQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionBankQuestion extends CreateRecord
{
    protected static string $resource = QuestionBankQuestionResource::class;

    protected function afterCreate(): void
    {
        if ($this->data['type'] === 'true_false') {
            $correct = $this->data['correct_answer'] ?? 'true';
            $this->record->options()->createMany([
                ['option_text' => 'True', 'is_correct' => $correct === 'true', 'order' => 0],
                ['option_text' => 'False', 'is_correct' => $correct === 'false', 'order' => 1],
            ]);
        }
    }
}
