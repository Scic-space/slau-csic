<?php

namespace App\Services;

use App\Models\CertificateEligibility;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class CertificateService
{
    public function createEligibility(ExamAttempt $attempt): CertificateEligibility
    {
        if (! $attempt->passed) {
            throw new \InvalidArgumentException('Cannot create eligibility for failed attempt');
        }

        return CertificateEligibility::updateOrCreate(
            ['exam_attempt_id' => $attempt->id],
            [
                'user_id' => $attempt->user_id,
                'exam_id' => $attempt->exam_id,
                'eligible' => true,
            ]
        );
    }

    public function revokeEligibility(CertificateEligibility $eligibility): bool
    {
        $eligibility->eligible = false;
        $timestamp = now()->toDateTimeString();
        if ($eligibility->notes) {
            $eligibility->notes .= "\n[{$timestamp}] Revoked";
        } else {
            $eligibility->notes = "[{$timestamp}] Revoked";
        }

        return $eligibility->save();
    }

    public function getEligibleMembers(Exam $exam): Collection
    {
        return CertificateEligibility::with('user')
            ->where('exam_id', $exam->id)
            ->where('eligible', true)
            ->get();
    }

    public function getUserEligibilities(User $user): Collection
    {
        return CertificateEligibility::with(['exam', 'examAttempt'])
            ->where('user_id', $user->id)
            ->where('eligible', true)
            ->get();
    }

    public function verify(string $code): ?CertificateEligibility
    {
        return CertificateEligibility::with(['exam', 'user', 'examAttempt'])
            ->where('verification_code', $code)
            ->first();
    }

    public function downloadPdf(CertificateEligibility $eligibility): Response
    {
        $eligibility->loadMissing(['exam', 'examAttempt', 'user']);

        $verificationUrl = route('certificates.verify', $eligibility->verification_code);

        $clubLogo = public_path('images/club_logo.png');

        $pdf = Pdf::loadView('pdf.certificate', [
            'user' => $eligibility->user,
            'exam' => $eligibility->exam,
            'score' => $eligibility->examAttempt->total_score,
            'passedAt' => $eligibility->created_at->format('F j, Y'),
            'certificateId' => $eligibility->certificate_id,
            'verificationUrl' => $verificationUrl,
            'verificationCode' => $eligibility->verification_code,
            'clubLogo' => $clubLogo,
        ])->setPaper([0, 0, 1056, 750]);

        $filename = 'certificate-'.str($eligibility->exam->title)->slug('-').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
