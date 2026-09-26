<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventResource;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class EventResourceDocument
{
    public function __construct(
        private readonly EventDescriptionDocument $document,
        private readonly WordDocument $wordDocument,
        private readonly PdfWordConverter $pdfConverter,
    ) {}

    public function notesHtml(EventResource $resource): ?string
    {
        [$disk, $path] = $this->storedFile($resource);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contents = $disk->get($path);

        abort_unless(is_string($contents), 404);

        if ($extension === 'docx') {
            try {
                return $this->wordDocument->preview($contents);
            } catch (InvalidArgumentException) {
                abort(415, 'This file is not a readable Word document.');
            }
        }

        if (! in_array($extension, ['html', 'htm', 'txt', 'md', 'markdown'], true)) {
            return null;
        }

        abort_unless(mb_check_encoding($contents, 'UTF-8') && ! str_contains($contents, "\0"), 415);

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
        [$disk, $path] = $this->storedFile($resource);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($this->isPdf($disk, $path)) {
            return $disk->response($path, $this->filename($resource, 'pdf'), [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ], 'inline');
        }

        abort_if($extension === 'pdf', 415);

        if (! $resource->supportsDocxDownload()) {
            return $disk->download($path, $this->filename($resource, $extension), [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $notesHtml = $this->notesHtml($resource);

        abort_if($notesHtml === null, 404);

        return response()->view('events.resource-notes', compact('event', 'resource', 'notesHtml'))
            ->header('Content-Disposition', 'inline')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function download(Event $event, EventResource $resource): Response
    {
        [$disk, $path] = $this->storedFile($resource);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        abort_unless($resource->supportsDocxDownload(), 415);

        if ($extension === 'docx') {
            $this->notesHtml($resource);

            return $disk->download($path, $this->filename($resource), [
                'Content-Type' => WordDocument::MIME_TYPE,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        if ($this->isPdf($disk, $path)) {
            try {
                $contents = $this->pdfConverter->convert($disk->get($path));
            } catch (RuntimeException $exception) {
                report($exception);
                abort(503, 'The Word download is temporarily unavailable. Please try again shortly.');
            }
        } else {
            abort_if($extension === 'pdf', 415);
            $notesHtml = $this->notesHtml($resource);
            abort_if($notesHtml === null, 404);

            try {
                $contents = $this->wordDocument->create($notesHtml, $resource->title);
            } catch (InvalidArgumentException) {
                abort(415, 'These lesson notes cannot be converted to a Word document.');
            }
        }

        $response = response($contents, 200, [
            'Content-Type' => WordDocument::MIME_TYPE,
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition('attachment', $this->filename($resource)));

        return $response;
    }

    /**
     * @return array{FilesystemAdapter, string}
     */
    private function storedFile(EventResource $resource): array
    {
        $path = trim(str_replace('\\', '/', (string) $resource->file_path));

        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
            abort_unless(str_starts_with($path, '/storage/'), 404);
        }

        $path = str_replace('\\', '/', rawurldecode($path));
        abort_if($path === '' || preg_match('/[\x00-\x1F\x7F]/', $path), 404);
        abort_if(in_array('..', explode('/', $path), true) || str_contains($path, ':'), 404);

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        } elseif (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        abort_if(str_starts_with($path, '/'), 404);
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return [$disk, $path];
        }

        if (str_starts_with($path, 'event-resources/')) {
            $disk = Storage::disk('local');

            if ($disk->exists($path)) {
                return [$disk, $path];
            }
        }

        abort(404, 'The resource file is missing. Please ask the event administrator to upload it again.');
    }

    private function isPdf(FilesystemAdapter $disk, string $path): bool
    {
        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        try {
            return fread($stream, 5) === '%PDF-';
        } finally {
            fclose($stream);
        }
    }

    private function filename(EventResource $resource, string $extension = 'docx'): string
    {
        return (Str::slug($resource->title) ?: 'resource-'.$resource->getKey()).($extension === '' ? '' : '.'.$extension);
    }
}
