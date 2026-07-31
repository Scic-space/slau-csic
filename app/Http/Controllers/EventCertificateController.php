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
        ]);

        $filename = 'event-certificate-'.str($event->title)->slug('-').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
