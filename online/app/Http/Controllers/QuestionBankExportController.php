<?php

namespace App\Http\Controllers;

use App\Models\QuestionBankQuestion;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionBankExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $questions = QuestionBankQuestion::with('options')->get();

        $data = [
            'exam_title' => 'Question Bank Export',
            'version' => 1,
            'questions' => $questions->map(fn ($q) => $q->toExamShieldFormat())->toArray(),
        ];

        $filename = 'question_bank_'.now()->format('Ymd_His').'.json';

        return response()->streamDownload(
            fn () => fwrite(fopen('php://output', 'w'), json_encode($data, JSON_PRETTY_PRINT)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }
}
