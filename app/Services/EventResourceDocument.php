<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventResource;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EventResourceDocument
{
    public function __construct(private readonly EventDescriptionDocument $document) {}

    public function notesHtml(EventResource $resource): ?string
    {
        $disk = $this->diskFor($resource);
        $extension = strtolower(pathinfo($resource->file_path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['html', 'htm', 'txt', 'md', 'markdown'], true)) {
            return null;
        }

        $contents = $disk->get($resource->file_path);

        abort_unless(is_string($contents) && mb_check_encoding($contents, 'UTF-8') && ! str_contains($contents, "\0"), 415);

        if ($extension === 'txt') {
            $contents = '<div>'.nl2br(e($contents), false).'</div>';
        } elseif (in_array($extension, ['md', 'markdown'], true)) {
            $contents = Str::markdown($contents, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }

        return $this->document->html($contents);
    }

    public function preview(Event $event, EventResource $resource): View|Response
    {
        $disk = $this->diskFor($resource);

        if ($this->isPdf($resource, $disk)) {
            return $disk->response($resource->file_path, $this->filename($resource), [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort_if(strtolower(pathinfo($resource->file_path, PATHINFO_EXTENSION)) === 'pdf', 415);

        if (! $resource->supportsPdfDownload()) {
            $extension = pathinfo($resource->file_path, PATHINFO_EXTENSION);
            $filename = (Str::slug($resource->title) ?: 'resource-'.$resource->getKey()).($extension === '' ? '' : '.'.$extension);

            return $disk->download($resource->file_path, $filename, [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $notesHtml = $this->notesHtml($resource);

        abort_if($notesHtml === null, 404);

        return view('events.resource-notes', compact('event', 'resource', 'notesHtml'));
    }

    public function download(Event $event, EventResource $resource): Response
    {
        $disk = $this->diskFor($resource);

        abort_unless($resource->supportsPdfDownload(), 415);

        if ($this->isPdf($resource, $disk)) {
            return $disk->download($resource->file_path, $this->filename($resource), [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort_if(strtolower(pathinfo($resource->file_path, PATHINFO_EXTENSION)) === 'pdf', 415);

        $notesHtml = $this->notesHtml($resource);

        abort_if($notesHtml === null, 404);

        return $this->document->downloadHtml($event, $notesHtml, $this->filename($resource), $resource->title);
    }

    private function diskFor(EventResource $resource): FilesystemAdapter
    {
        $disk = Storage::disk('public');

        abort_unless($resource->file_path && $disk->exists($resource->file_path), 404);

        return $disk;
    }

    private function isPdf(EventResource $resource, FilesystemAdapter $disk): bool
    {
        $stream = $disk->readStream($resource->file_path);

        abort_unless(is_resource($stream), 404);

        try {
            return fread($stream, 5) === '%PDF-';
        } finally {
            fclose($stream);
        }
    }

    private function filename(EventResource $resource): string
    {
        return (Str::slug($resource->title) ?: 'resource-'.$resource->getKey()).'.pdf';
    }
}
