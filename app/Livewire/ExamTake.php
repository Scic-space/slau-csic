<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExamTake extends Component
{
    use GuardsPendingMembers;

    public Exam $exam;

    public ExamAttempt $attempt;

    public array $questions = [];

    public array $answers = [];

    protected ExamAttemptService $examAttemptService;

    public function boot(ExamAttemptService $examAttemptService): void
    {
        $this->examAttemptService = $examAttemptService;
    }

    public function mount(Exam $exam): void
    {
        $user = Auth::user();
        $service = app(ExamAttemptService::class);
        $existingAttempt = $service->getUserAttempt($exam, $user);

        if ($existingAttempt && $existingAttempt->is_completed) {
            $this->redirectRoute('exams.result', $existingAttempt);

            return;
        }

        if (! $existingAttempt) {
            $attempt = $service->startAttempt($exam, $user);
        } else {
            $attempt = $existingAttempt;
        }

        $this->attempt = $attempt;
        $this->exam = $exam;

        $examQuestions = $exam->questions()->with(['question.options'])->get();
        $this->questions = $examQuestions->map(fn ($q) => [
            'id' => $q->id,
            'order' => $q->order,
            'custom_marks' => $q->custom_marks,
            'question' => [
                'id' => $q->question->id,
                'type' => $q->question->type,
                'question_text' => $q->question->question_text,
                'image' => $q->question->image ? \Illuminate\Support\Facades\Storage::url($q->question->image) : null,
                'code_block' => $q->question->code_block,
                'code_language' => $q->question->code_language,
                'marks' => $q->question->marks,
                'explanation' => '', // intentionally excluded until after submission
                'options' => $q->question->options->sortBy('order')->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    'order' => $o->order,
                ])->values()->toArray(),
            ],
        ])->toArray();

        $existingAnswers = [];
        foreach ($attempt->answers as $answer) {
            if ($answer->answer_text) {
                $existingAnswers[(string) $answer->exam_question_id]['text'] = $answer->answer_text;
            }
            if ($answer->selected_option_id) {
                $existingAnswers[(string) $answer->exam_question_id]['option_id'] = $answer->selected_option_id;
            }
        }
        $this->answers = $existingAnswers;
    }

    public function saveAnswer(int $questionId, ?string $text = null, ?int $optionId = null): void
    {
        $attempt = $this->attempt->fresh();
        if (! $attempt || $attempt->is_completed) {
            return;
        }

        $examQuestion = $this->exam->questions()->findOrFail($questionId);
        $data = array_filter([
            'answer_text' => $text,
            'selected_option_id' => $optionId,
        ]);

        if (! empty($data)) {
            try {
                app(ExamAttemptService::class)->saveAnswer($attempt, $examQuestion, $data);
            } catch (\RuntimeException) {
                // time expired
            }
        }

        $this->answers[(string) $questionId] = array_merge($this->answers[(string) $questionId] ?? [], $data);
    }

    public function submitExam(): void
    {
        $attempt = $this->attempt->fresh();
        if (! $attempt || $attempt->is_completed) {
            return;
        }

        $service = app(ExamAttemptService::class);

        if ($service->isExpired($attempt)) {
            $service->submitAttempt($attempt);
            $this->redirectRoute('exams.result', $attempt);

            return;
        }

        $service->submitAttempt($attempt);
        $this->redirectRoute('exams.result', $attempt);
    }

    public function render()
    {
        return view('livewire.exam-take', [
            'examTitle' => $this->exam->title,
            'attemptData' => [
                'id' => $this->attempt->id,
                'time_remaining_seconds' => $this->attempt->time_remaining_seconds ?? ($this->exam->duration_minutes * 60),
            ],
        ]);
    }
}
