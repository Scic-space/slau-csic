<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventDescriptionDocument;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventDescriptionDownloadController extends Controller
{
    public function __invoke(Request $request, Event $event, EventDescriptionDocument $document): Response
    {
        abort_unless(in_array($event->status, ['published', 'scheduled', 'ongoing', 'completed']), 404);
        abort_unless($event->is_public || ($request->user() && ! $request->user()->isPendingApproval()), 404);

        return $document->download($event);
    }
}
