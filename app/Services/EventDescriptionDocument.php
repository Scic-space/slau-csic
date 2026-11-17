<?php

namespace App\Services;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HttpFoundation\Response;

class EventDescriptionDocument
{
    public function hasDocument(Event $event): bool
    {
        return filled($event->description_file_path) || $this->html($event->description) !== null;
    }

    public function html(?string $description): ?string
    {
        if ($description === null || trim($description) === '') {
            return null;
        }

        if (! preg_match('/<\/?[a-z][^>]*>/i', $description)) {
            $description = nl2br(e($description, false), false);
        }

        $description = $this->documentBody($description);

        $config = (new HtmlSanitizerConfig)
            ->withMaxInputLength(-1)
            ->allowElement('a', ['href', 'title'])
            ->allowElement('img', ['src', 'alt', 'title'])
            ->allowElement('ol', ['start'])
            ->allowElement('td', ['colspan', 'rowspan'])
            ->allowElement('th', ['colspan', 'rowspan'])
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias();

        foreach (['p', 'br', 'b', 'i', 'u', 'strong', 'em', 's', 'del', 'sub', 'sup', 'ul', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'span', 'div', 'main', 'article', 'section', 'header', 'footer', 'hr', 'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'figure', 'figcaption'] as $tag) {
            $config = $config->allowElement($tag);
        }

        $html = (new HtmlSanitizer($config))->sanitize($description);
        $html = $this->replaceImagesWithLinks($html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match('/[^\s\p{Z}\x{200B}\x{FEFF}]/u', $text) ? $html : null;
    }

    public function download(Event $event): Response
    {
        if (filled($event->description_file_path)) {
            return $this->uploadedPdf($event, 'attachment');
        }

        $descriptionHtml = $this->html($event->description);

        abort_if($descriptionHtml === null, 404);

        $filename = (Str::slug($event->title) ?: 'event-'.$event->getKey()).'-description.pdf';

        return $this->downloadHtml($event, $descriptionHtml, $filename);
    }

    public function preview(Event $event): Response
    {
        if (filled($event->description_file_path)) {
            return $this->uploadedPdf($event, 'inline');
        }

        $response = $this->download($event);
        $response->headers->set('Content-Disposition', str_replace('attachment;', 'inline;', $response->headers->get('Content-Disposition')));

        return $response;
    }

    private function uploadedPdf(Event $event, string $disposition): Response
    {
        $disk = Storage::disk('local');

        abort_unless($disk->exists($event->description_file_path), 404);

        $stream = $disk->readStream($event->description_file_path);

        abort_unless(is_resource($stream), 404);

        try {
            abort_unless(fread($stream, 5) === '%PDF-', 415);
        } finally {
            fclose($stream);
        }

        $filename = (Str::slug($event->title) ?: 'event-'.$event->getKey()).'-description.pdf';

        return $disk->response($event->description_file_path, $filename, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ], $disposition);
    }

    public function downloadHtml(Event $event, string $descriptionHtml, string $filename, ?string $title = null): Response
    {
        $pdf = Pdf::loadView('pdf.event-description', [
            'event' => $event,
            'descriptionHtml' => $descriptionHtml,
            'documentTitle' => $title ?? $event->title,
            'documentLabel' => $title === null ? 'Lesson description' : $event->title,
        ])
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', false)
            ->setOption('isJavascriptEnabled', false);

        return $pdf->download($filename);
    }

    private function documentBody(string $html): string
    {
        if (! preg_match('/<(?:!doctype\b|\/?(?:html|head|body)\b)/i', $html)) {
            return $html;
        }

        $previousErrorHandling = libxml_use_internal_errors(true);
        $document = new DOMDocument;

        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        $body = $document->getElementsByTagName('body')->item(0);

        if ($body === null) {
            return '';
        }

        $html = '';

        foreach ($body->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }

    private function replaceImagesWithLinks(string $html): string
    {
        if (! str_contains($html, '<img')) {
            return $html;
        }

        $previousErrorHandling = libxml_use_internal_errors(true);
        $document = new DOMDocument;

        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        foreach (iterator_to_array($document->getElementsByTagName('img')) as $image) {
            $label = trim($image->getAttribute('alt') ?: $image->getAttribute('title'));
            $imageUrl = $this->imageUrl($image);
            $replacement = $document->createElement('span');

            if ($imageUrl !== null) {
                $link = $document->createElement('a');
                $link->setAttribute('href', $imageUrl);
                $link->appendChild($document->createTextNode('Image: '.($label ?: 'View image')));
                $replacement->appendChild($link);
            } elseif ($label !== '') {
                $replacement->appendChild($document->createTextNode('Image: '.$label));
            }

            $image->parentNode->replaceChild($replacement, $image);
        }

        $body = $document->getElementsByTagName('body')->item(0);
        $html = '';

        foreach ($body->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }

    private function imageUrl(DOMElement $image): ?string
    {
        $source = trim($image->getAttribute('src'));
        $scheme = strtolower((string) parse_url($source, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true) && filter_var($source, FILTER_VALIDATE_URL)) {
            return $source;
        }

        if (str_starts_with($source, '/storage/') || str_starts_with($source, 'storage/')) {
            return url('/'.ltrim($source, '/'));
        }

        return null;
    }
}
