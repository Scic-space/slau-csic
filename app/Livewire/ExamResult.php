<?php

namespace App\Livewire;

use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExamResult extends Component
{
    public ExamAttempt $attempt;

    public bool $showAnswers = false;

    public function mount(ExamAttempt $attempt): void
    {
        abort_unless($attempt->user_id === Auth::id(), 403);
        $this->attempt = $attempt->load(['exam', 'answers.examQuestion.question.options']);
    }

    public function toggleAnswers(): void
    {
        $this->showAnswers = ! $this->showAnswers;
    }

    public function render()
    {
        $answers = $this->attempt->answers->map(fn ($a) => [
            'id' => $a->id,
            'question_id' => $a->exam_question_id,
            'answer_text' => $a->answer_text,
            'selected_option_id' => $a->selected_option_id,
            'is_correct' => $a->is_correct,
            'marks_awarded' => $a->marks_awarded,
            'question' => [
                'question_text' => $a->examQuestion->question->question_text,
                'image' => $a->examQuestion->question->image ? \Illuminate\Support\Facades\Storage::url($a->examQuestion->question->image) : null,
                'type' => $a->examQuestion->question->type,
                'marks' => $a->examQuestion->question->marks,
                'code_block' => $a->examQuestion->question->code_block,
                'code_language' => $a->examQuestion->question->code_language,
                'explanation' => $a->examQuestion->question->explanation,
                'options' => $a->examQuestion->question->options->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    'is_correct' => $o->is_correct,
                    'order' => $o->order,
                ]),
            ],
        ]);

        $attempt = [
            'id' => $this->attempt->id,
            'total_score' => $this->attempt->total_score,
            'passed' => $this->attempt->passed,
            'started_at' => $this->attempt->started_at?->toIso8601String(),
            'submitted_at' => $this->attempt->submitted_at?->toIso8601String(),
        ];

        $exam = [
            'id' => $this->attempt->exam->id,
            'title' => $this->attempt->exam->title,
            'description' => $this->attempt->exam->description,
            'passing_score' => $this->attempt->exam->passing_score,
            'duration_minutes' => $this->attempt->exam->duration_minutes,
        ];

        $totalPossible = $answers->sum(fn ($a) => $a['question']['marks']);

        $certificateVerificationUrl = null;
        if ($this->attempt->passed && $this->attempt->certificateEligibility) {
            $certificateVerificationUrl = $this->attempt->certificateEligibility->verification_url;
        }

        return view('livewire.exam-result', [
            'attempt' => $attempt,
            'exam' => $exam,
            'answers' => $answers,
            'totalPossible' => $totalPossible,
            'certificateVerificationUrl' => $certificateVerificationUrl,
        ]);
    }
}
