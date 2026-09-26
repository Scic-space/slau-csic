<?php

namespace App\Services;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HttpFoundation\Response;

class EventDescriptionDocument
{
    public function html(?string $description): ?string
    {
        if ($description === null || trim($description) === '') {
            return null;
        }

        if (! preg_match('/<\/?[a-z][^>]*>/i', $description)) {
            $description = nl2br(e($description, false), false);
        }

        $config = (new HtmlSanitizerConfig)
            ->withMaxInputLength(-1)
            ->allowElement('a', ['href', 'title'])
            ->allowElement('img', ['src', 'alt', 'title'])
            ->allowElement('ol', ['start'])
            ->allowElement('td', ['colspan', 'rowspan'])
            ->allowElement('th', ['colspan', 'rowspan'])
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias();

        foreach (['p', 'br', 'b', 'i', 'u', 'strong', 'em', 's', 'del', 'sub', 'sup', 'ul', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'span', 'div', 'hr', 'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'figure', 'figcaption'] as $tag) {
            $config = $config->allowElement($tag);
        }

        $html = (new HtmlSanitizer($config))->sanitize($description);
        $html = $this->replaceImagesWithLinks($html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match('/[^\s\p{Z}\x{200B}\x{FEFF}]/u', $text) ? $html : null;
    }

    public function download(Event $event): Response
    {
        $descriptionHtml = $this->html($event->description);

        abort_if($descriptionHtml === null, 404);

        $pdf = Pdf::loadView('pdf.event-description', [
            'event' => $event,
            'descriptionHtml' => $descriptionHtml,
        ])
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', false)
            ->setOption('isJavascriptEnabled', false);

        $filename = (Str::slug($event->title) ?: 'event-'.$event->getKey()).'-description.pdf';

        return $pdf->download($filename);
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
