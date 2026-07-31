<?php

namespace App\Http\Controllers;

use App\Models\CertificateEligibility;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamCertificateDownloadController extends Controller
{
    public function __invoke(Request $request, CertificateEligibility $eligibility): Response
    {
        if ($eligibility->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $eligibility->eligible) {
            abort(410, 'Certificate eligibility has been revoked.');
        }

        return app(CertificateService::class)->downloadPdf($eligibility);
    }
}
