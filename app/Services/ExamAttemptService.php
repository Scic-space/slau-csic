<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Notifications\ExamGradedNotification;
use Illuminate\Support\Facades\Log;

class ExamAttemptService
{
    public function startAttempt(Exam $exam, User $user): ExamAttempt
    {
        return ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'user_id' => $user->id],
            [
                'started_at' => now(),
                'time_remaining_seconds' => $exam->duration_minutes * 60,
            ],
        );
    }

    public function submitAttempt(ExamAttempt $attempt, bool $checkTimer = true): array
    {
        if ($checkTimer && $this->isExpired($attempt)) {
            $attempt->update([
                'submitted_at' => now(),
                'time_remaining_seconds' => 0,
            ]);

            $gradingResult = app(ExamGradingService::class)->gradeAttempt($attempt);

            if ($gradingResult['passed']) {
                $this->handleCertificateEligibility($attempt);
                $this->awardExamPoints($attempt, $gradingResult);
            }

            $this->sendGradedNotification($attempt);

            return $gradingResult;
        }

        $attempt->update([
            'submitted_at' => now(),
            'time_remaining_seconds' => 0,
        ]);

        $gradingResult = app(ExamGradingService::class)->gradeAttempt($attempt);

        if ($gradingResult['passed']) {
            $this->handleCertificateEligibility($attempt);
            $this->awardExamPoints($attempt, $gradingResult);
        }

        $this->sendGradedNotification($attempt);

        return $gradingResult;
    }

    public function saveAnswer(ExamAttempt $attempt, ExamQuestion $examQuestion, array $data): ExamAnswer
    {
        if ($this->isExpired($attempt)) {
            throw new \RuntimeException('Time expired for this attempt');
        }

        $answer = ExamAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $examQuestion->id,
            ],
            [
                'answer_text' => $data['answer_text'] ?? null,
                'selected_option_id' => $data['selected_option_id'] ?? null,
            ]
        );

        return $answer;
    }

    public function getUserAttempt(Exam $exam, User $user): ?ExamAttempt
    {
        return ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function isExpired(ExamAttempt $attempt): bool
    {
        if (! $attempt->started_at) {
            return false;
        }

        $duration = $attempt->exam->duration_minutes * 60;
        $elapsed = now()->diffInSeconds($attempt->started_at, true);

        return $elapsed >= $duration;
    }

    public function remainingSeconds(ExamAttempt $attempt): int
    {
        if (! $attempt->started_at) {
            return $attempt->exam->duration_minutes * 60;
        }

        $duration = $attempt->exam->duration_minutes * 60;
        $elapsed = now()->diffInSeconds($attempt->started_at, true);

        return max(0, $duration - $elapsed);
    }

    protected function awardExamPoints(ExamAttempt $attempt, array $gradingResult): void
    {
        if (! $gradingResult['passed']) {
            return;
        }

        $totalMarks = $attempt->exam->questions->sum(fn ($question) => $question->effective_marks);
        $percentage = $totalMarks > 0 ? (($gradingResult['total_score'] / $totalMarks) * 100) : 0;
        $points = $percentage >= 80
            ? GamificationService::POINTS_EXAM_DISTINCTION
            : GamificationService::POINTS_EXAM_PASSED;

        app(GamificationService::class)->awardOnce(
            $attempt->user,
            $points,
            "Passed exam: {$attempt->exam->title}",
            ExamAttempt::class,
            $attempt->id,
        );
    }

    protected function handleCertificateEligibility(ExamAttempt $attempt): void
    {
        try {
            app(CertificateService::class)->createEligibility($attempt);
        } catch (\Exception $e) {
            Log::warning('Certificate eligibility already exists for attempt '.$attempt->id);
        }
    }

    protected function sendGradedNotification(ExamAttempt $attempt): void
    {
        try {
            $attempt->user->notify(new ExamGradedNotification($attempt));
        } catch (\Exception $e) {
            Log::error('Failed to send graded notification: '.$e->getMessage());
        }
    }
}
