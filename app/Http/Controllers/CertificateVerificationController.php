<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateVerificationController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService,
    ) {}

    public function show(Request $request, string $code): Response
    {
        $eligibility = $this->certificateService->verify($code);

        if (! $eligibility) {
            return Inertia::render('certificates/Verify', [
                'status' => 'not_found',
                'certificate' => null,
            ]);
        }

        return Inertia::render('certificates/Verify', [
            'status' => 'found',
            'certificate' => [
                'holder_name' => $eligibility->user->name,
                'exam_title' => $eligibility->exam->title,
                'score' => $eligibility->examAttempt->total_score,
                'passing_score' => $eligibility->exam->passing_score,
                'issued_at' => $eligibility->created_at->format('F j, Y'),
                'certificate_id' => $eligibility->certificate_id,
                'verification_code' => $eligibility->verification_code,
                'is_valid' => $eligibility->eligible,
            ],
        ]);
    }
}
