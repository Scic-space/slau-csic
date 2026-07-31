<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\QuestionBankQuestion;
use App\Models\User;
use App\Notifications\ExamPublishedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ExamService
{
    public function createExam(User $user, array $data): Exam
    {
        $data['user_id'] = $user->id;

        return Exam::create($data);
    }

    public function updateExam(Exam $exam, array $data): bool
    {
        return $exam->update($data);
    }

    public function toggleStatus(Exam $exam): string
    {
        $wasPublished = $exam->status === 'published';
        $newStatus = match ($exam->status) {
            'draft' => 'published',
            'published' => 'archived',
            'archived' => 'draft',
            default => 'draft',
        };

        $exam->update(['status' => $newStatus]);

        if ($newStatus === 'published' && ! $wasPublished) {
            $this->notifyPublished($exam);
        }

        return $newStatus;
    }

    public function deleteExam(Exam $exam): bool
    {
        return $exam->delete();
    }

    public function getExamsWithStats(): Collection
    {
        return Exam::with(['creator', 'questions'])
            ->withCount('questions')
            ->get()
            ->map(function ($exam) {
                $exam->question_count = $exam->questions_count;
                $exam->total_marks = $exam->questions->sum(fn ($eq) => $eq->effective_marks);

                return $exam;
            });
    }

    public function addQuestion(Exam $exam, int $questionBankQuestionId, ?int $customMarks = null): ExamQuestion
    {
        $maxOrder = $exam->questions()->max('order') ?? -1;

        return ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_bank_question_id' => $questionBankQuestionId,
            'custom_marks' => $customMarks,
            'order' => $maxOrder + 1,
        ]);
    }

    public function removeQuestion(Exam $exam, int $examQuestionId): bool
    {
        $question = ExamQuestion::where('exam_id', $exam->id)
            ->where('id', $examQuestionId)
            ->first();

        if (! $question) {
            return false;
        }

        $question->delete();

        $this->reorderQuestions($exam, $exam->questions()->pluck('id')->toArray());

        return true;
    }

    public function reorderQuestions(Exam $exam, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ExamQuestion::where('exam_id', $exam->id)
                ->where('id', $id)
                ->update(['order' => $index]);
        }
    }

    public function getAvailableQuestions(Exam $exam, ?string $search = null, ?string $type = null): Collection
    {
        $query = QuestionBankQuestion::whereNotIn('id', function ($q) use ($exam) {
            $q->select('question_bank_question_id')
                ->from('exam_questions')
                ->where('exam_id', $exam->id);
        });

        if ($search) {
            $query->where('question_text', 'like', "%{$search}%");
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->with('options')->get();
    }

    public function getExamTotalMarks(Exam $exam): int
    {
        return $exam->questions->sum(fn ($eq) => $eq->effective_marks);
    }

    protected function notifyPublished(Exam $exam): void
    {
        try {
            $users = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['member', 'associate']))->get();
            foreach ($users as $user) {
                $user->notify(new ExamPublishedNotification($exam));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send exam published notification: '.$e->getMessage());
        }
    }
}
