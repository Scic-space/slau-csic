<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventResourceDownloadController extends Controller
{
    public function __invoke(Request $request, Event $event, EventResource $resource): StreamedResponse
    {
        abort_unless(in_array($event->status, ['published', 'scheduled', 'ongoing', 'completed']), 404);
        abort_unless($event->is_public || ($request->user() && ! $request->user()->isPendingApproval()), 404);

        $disk = Storage::disk('public');

        abort_unless($resource->file_path && $disk->exists($resource->file_path), 404);

        $extension = pathinfo($resource->file_path, PATHINFO_EXTENSION);
        $filename = Str::slug($resource->title) ?: 'resource-'.$resource->id;

        if ($extension !== '') {
            $filename .= '.'.$extension;
        }

        return $disk->download($resource->file_path, $filename);
    }
}
