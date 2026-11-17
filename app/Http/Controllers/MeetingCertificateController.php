<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Meeting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingCertificateController extends Controller
{
    public function __invoke(Request $request, Meeting $meeting, Attendance $attendance): Response
    {
        if ($attendance->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($attendance->meeting_id !== $meeting->id) {
            abort(404);
        }

        if (! in_array($attendance->status, ['present', 'late'], true)) {
            abort(410, 'Attendance not confirmed for this class.');
        }

        if (! $meeting->hasEnded()) {
            abort(410, 'Class has not yet ended.');
        }

        $pdf = Pdf::loadView('pdf.meeting-certificate', [
            'user' => $request->user(),
            'meeting' => $meeting,
            'attendance' => $attendance,
            'certificateId' => 'CLS-CERT-'.str_pad((string) $attendance->id, 6, '0', STR_PAD_LEFT),
            'clubLogo' => public_path('images/club_logo.png'),
            'attendedAt' => ($attendance->checked_in_at ?? $meeting->scheduled_at)?->format('M j, Y') ?? 'N/A',
            'sessionType' => 'Teaching Session',
        ])->setPaper([0, 0, 1056, 750]);

        $filename = 'class-certificate-'.str($meeting->title)->slug('-').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
