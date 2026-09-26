<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventDescriptionDocument;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventDescriptionViewController extends Controller
{
    public function __invoke(Request $request, Event $event, EventDescriptionDocument $document): Response
    {
        $user = $request->user();
        $canPreviewDraft = $event->status === 'draft' && $user && ! $user->isPendingApproval()
            && ($user->id === $event->organizer_id || $user->hasAnyRole(['admin', 'super-admin']));

        abort_unless($canPreviewDraft || in_array($event->status, ['published', 'scheduled', 'ongoing', 'completed']), 404);
        abort_unless($event->is_public || ($user && ! $user->isPendingApproval()), 404);

        return $document->preview($event);
    }
}
