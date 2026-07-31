<?php

namespace App\Filament\Resources\QuestionBankQuestions\Pages;

use App\Filament\Resources\QuestionBankQuestions\QuestionBankQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionBankQuestions extends ListRecords
{
    protected static string $resource = QuestionBankQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
