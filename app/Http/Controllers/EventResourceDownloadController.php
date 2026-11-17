<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResource;
use App\Services\EventResourceDocument;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventResourceDownloadController extends Controller
{
    public function __invoke(Request $request, Event $event, EventResource $resource, EventResourceDocument $document): Response
    {
        abort_unless(in_array($event->status, ['published', 'scheduled', 'ongoing', 'completed']), 404);
        abort_unless($event->is_public || ($request->user() && ! $request->user()->isPendingApproval()), 404);

        return $document->download($event, $resource);
    }
}
