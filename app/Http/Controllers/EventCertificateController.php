<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventCertificateController extends Controller
{
    public function __invoke(Request $request, Event $event, EventRegistration $registration): Response
    {
        if ($registration->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($registration->event_id !== $event->id) {
            abort(404);
        }

        if (! $registration->hasAttended()) {
            abort(410, 'Attendance not confirmed for this event.');
        }

        if (! $event->end_date || ! $event->end_date->isPast()) {
            abort(410, 'Event has not yet ended.');
        }

        $pdf = Pdf::loadView('pdf.event-certificate', [
            'user' => $request->user(),
            'event' => $event,
            'registration' => $registration,
            'certificateId' => 'EVT-CERT-'.str_pad((string) $registration->id, 6, '0', STR_PAD_LEFT),
            'clubLogo' => public_path('images/club_logo.png'),
            'attendedAt' => ($registration->attended_at ?? $event->end_date)?->format('M j, Y') ?? 'N/A',
            'eventType' => ucfirst((string) ($event->type ?: 'Event')),
        ])->setPaper([0, 0, 1056, 750]);

        $filename = 'event-certificate-'.str($event->title)->slug('-').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
