<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventResourceRequest;
use App\Models\Event;
use App\Models\EventResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventResourceController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $resources = $event->resources()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'type' => $r->type,
                'url' => $r->url,
                'file_url' => $r->file_path ? asset('storage/'.$r->file_path) : null,
                'created_at' => $r->created_at,
            ]);

        return response()->json(['data' => $resources]);
    }

    public function store(StoreEventResourceRequest $request, Event $event)
    {
        if ($event->organizer_id !== $request->user()->id && ! $request->user()->can('edit_events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->only(['title', 'type', 'url']);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('event-materials/'.$event->id, 'public');
        }

        $resource = $event->resources()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Material added',
            'data' => [
                'id' => $resource->id,
                'title' => $resource->title,
                'type' => $resource->type,
                'url' => $resource->url,
                'file_url' => $resource->file_path ? asset('storage/'.$resource->file_path) : null,
                'created_at' => $resource->created_at,
            ],
        ], 201);
    }

    public function destroy(Request $request, Event $event, EventResource $resource)
    {
        if ($resource->event_id !== $event->id) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        if ($event->organizer_id !== $request->user()->id && ! $request->user()->can('edit_events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material deleted',
        ]);
    }
}
