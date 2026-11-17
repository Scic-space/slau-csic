<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class WordDocument
{
    public const MIME_TYPE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const WORD_NAMESPACE = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const RELATIONSHIP_NAMESPACE = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const MAX_DOCUMENT_SIZE = 20 * 1024 * 1024;

    private const MAX_XML_SIZE = 8 * 1024 * 1024;

    public function __construct(private EventDescriptionDocument $descriptions) {}

    public function create(string $html, string $title = ''): string
    {
        if (strlen($html) > self::MAX_XML_SIZE) {
            throw new InvalidArgumentException('The lesson notes are too large to convert.');
        }

        $html = $this->descriptions->html($html) ?? '';
        $document = new DOMDocument;
        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        $body = $title === '' ? '' : '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr>'.$this->run($title).'</w:p>';
        $body .= $this->blocks($document->getElementsByTagName('body')->item(0));
        $parts = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="'.self::RELATIONSHIP_NAMESPACE.'/officeDocument" Target="word/document.xml"/></Relationships>',
            'word/document.xml' => '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="'.self::WORD_NAMESPACE.'"><w:body>'.($body ?: '<w:p/>').'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134"/></w:sectPr></w:body></w:document>',
            'word/styles.xml' => $this->styles(),
            'word/_rels/document.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="styles" Type="'.self::RELATIONSHIP_NAMESPACE.'/styles" Target="styles.xml"/></Relationships>',
        ];

        return $this->archive($parts);
    }

    public function preview(string $contents): string
    {
        if (strlen($contents) > self::MAX_DOCUMENT_SIZE) {
            throw new InvalidArgumentException('The Word document is too large to preview.');
        }

        $path = $this->temporaryPath();
        $archive = new ZipArchive;
        $opened = false;

        try {
            file_put_contents($path, $contents);
            $opened = $archive->open($path, ZipArchive::RDONLY) === true;

            if (! $opened || $archive->numFiles > 2000) {
                throw new InvalidArgumentException('The file is not a supported Word document.');
            }

            $document = $this->xml($this->readPart($archive, 'word/document.xml'));
            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('w', self::WORD_NAMESPACE);
            $body = $xpath->query('/w:document/w:body')->item(0);

            if ($body === null) {
                throw new InvalidArgumentException('The Word document has no readable content.');
            }

            $links = [];
            $images = [];
            $imageBytes = 0;
            $relationships = $this->readPart($archive, 'word/_rels/document.xml.rels', false);

            if ($relationships !== '') {
                foreach ($this->xml($relationships)->getElementsByTagName('Relationship') as $relationship) {
                    $url = $relationship->getAttribute('Target');

                    if ($relationship->getAttribute('Type') === self::RELATIONSHIP_NAMESPACE.'/hyperlink' && $this->safeUrl($url)) {
                        $links[$relationship->getAttribute('Id')] = $url;
                    }

                    if ($relationship->getAttribute('Type') === self::RELATIONSHIP_NAMESPACE.'/image' && $relationship->getAttribute('TargetMode') !== 'External' && preg_match('#^media/[^/\\\\]+$#', $url) && count($images) < 25) {
                        $stat = $archive->statName('word/'.$url);

                        if ($stat !== false && $stat['size'] <= 2 * 1024 * 1024 && $imageBytes + $stat['size'] <= 5 * 1024 * 1024) {
                            $bytes = $archive->getFromName('word/'.$url, 2 * 1024 * 1024 + 1);
                            $size = $bytes === false ? false : @getimagesizefromstring($bytes);

                            if ($size !== false && in_array($size[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true) && $size[0] <= 8000 && $size[1] <= 8000) {
                                $images[$relationship->getAttribute('Id')] = 'data:'.$size['mime'].';base64,'.base64_encode($bytes);
                                $imageBytes += strlen($bytes);
                            }
                        }
                    }
                }
            }

            return $this->previewNode($body, $links, $images);
        } finally {
            if ($opened) {
                $archive->close();
            }

            unlink($path);
        }
    }

    private function blocks(DOMNode $parent): string
    {
        $xml = '';
        $inline = '';

        foreach ($parent->childNodes as $node) {
            if ($node instanceof DOMElement && in_array($node->tagName, ['p', 'div', 'main', 'article', 'section', 'header', 'footer', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'table', 'figure'], true)) {
                if ($inline !== '') {
                    $xml .= '<w:p>'.$inline.'</w:p>';
                    $inline = '';
                }

                if ($node->tagName === 'table') {
                    $xml .= $this->table($node);
                } elseif (in_array($node->tagName, ['ul', 'ol'], true)) {
                    $index = max(1, (int) ($node->getAttribute('start') ?: 1));

                    foreach ($node->childNodes as $item) {
                        if ($item instanceof DOMElement && $item->tagName === 'li') {
                            $prefix = $node->tagName === 'ol' ? $index++.'. ' : '• ';
                            $xml .= '<w:p><w:pPr><w:ind w:left="360" w:hanging="240"/></w:pPr>'.$this->run($prefix).$this->inline($item).'</w:p>';
                        }
                    }
                } elseif (in_array($node->tagName, ['div', 'main', 'article', 'section', 'header', 'footer', 'figure', 'blockquote'], true)) {
                    $xml .= $this->blocks($node);
                } else {
                    $style = preg_match('/^h([1-6])$/', $node->tagName, $match) ? '<w:pPr><w:pStyle w:val="Heading'.$match[1].'"/></w:pPr>' : '';
                    $xml .= '<w:p>'.$style.$this->inline($node).'</w:p>';
                }
            } elseif ($node->nodeType !== XML_TEXT_NODE || trim($node->textContent) !== '') {
                $inline .= $this->inline($node);
            }
        }

        return $xml.($inline === '' ? '' : '<w:p>'.$inline.'</w:p>');
    }

    /** @param array<string, bool> $format */
    private function inline(DOMNode $node, array $format = []): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $this->run($node->textContent, $format);
        }

        if ($node instanceof DOMElement) {
            $property = match ($node->tagName) {
                'strong', 'b' => 'b',
                'em', 'i' => 'i',
                'u' => 'u',
                's', 'del' => 'strike',
                default => null,
            };

            if ($property !== null) {
                $format[$property] = true;
            }

            if ($node->tagName === 'br') {
                return '<w:r><w:br/></w:r>';
            }
        }

        $xml = '';

        foreach ($node->childNodes as $child) {
            $xml .= $this->inline($child, $format);
        }

        if ($node instanceof DOMElement && $node->tagName === 'a' && $this->safeUrl($node->getAttribute('href')) && trim($node->textContent) !== $node->getAttribute('href')) {
            $xml .= $this->run(' ('.$node->getAttribute('href').')');
        }

        return $xml;
    }

    /** @param array<string, bool> $format */
    private function run(string $text, array $format = []): string
    {
        $properties = '';

        foreach (array_keys($format) as $property) {
            $properties .= $property === 'u' ? '<w:u w:val="single"/>' : '<w:'.$property.'/>';
        }

        return '<w:r>'.($properties === '' ? '' : '<w:rPr>'.$properties.'</w:rPr>').'<w:t xml:space="preserve">'.$this->escape($text).'</w:t></w:r>';
    }

    private function table(DOMElement $table): string
    {
        $columns = 1;

        foreach ($table->getElementsByTagName('tr') as $row) {
            $count = 0;

            foreach ($row->childNodes as $cell) {
                if ($cell instanceof DOMElement && in_array($cell->tagName, ['td', 'th'], true)) {
                    $count += max(1, min(100, (int) ($cell->getAttribute('colspan') ?: 1)));
                }
            }

            $columns = max($columns, $count);
        }

        $width = max(1, intdiv(9638, $columns));
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/></w:tblPr><w:tblGrid>'.str_repeat('<w:gridCol w:w="'.$width.'"/>', $columns).'</w:tblGrid>';

        foreach ($table->getElementsByTagName('tr') as $row) {
            $xml .= '<w:tr>';

            foreach ($row->childNodes as $cell) {
                if ($cell instanceof DOMElement && in_array($cell->tagName, ['td', 'th'], true)) {
                    $span = max(1, min(100, (int) ($cell->getAttribute('colspan') ?: 1)));
                    $xml .= '<w:tc><w:tcPr><w:tcW w:w="'.($width * $span).'" w:type="dxa"/>'.($span > 1 ? '<w:gridSpan w:val="'.$span.'"/>' : '').'</w:tcPr>'.$this->blocks($cell).'<w:p/></w:tc>';
                }
            }

            $xml .= '</w:tr>';
        }

        return $xml.'</w:tbl>';
    }

    /**
     * @param  array<string, string>  $links
     * @param  array<string, string>  $images
     */
    private function previewNode(DOMNode $node, array $links, array &$images): string
    {
        if (! $node instanceof DOMElement || $node->namespaceURI !== self::WORD_NAMESPACE) {
            return '';
        }

        if ($node->localName === 't') {
            return $this->escape($node->textContent);
        }

        if (in_array($node->localName, ['br', 'cr'], true)) {
            return '<br>';
        }

        if ($node->localName === 'tab') {
            return '&#9;';
        }

        if (in_array($node->localName, ['drawing', 'pict'], true)) {
            $html = '';

            foreach ($node->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'blip') as $image) {
                $id = $image->getAttributeNS(self::RELATIONSHIP_NAMESPACE, 'embed');
                $source = $images[$id] ?? null;

                if ($source !== null) {
                    $html .= '<img src="'.$source.'" alt="Lesson illustration">';
                    unset($images[$id]);
                }
            }

            foreach ($node->getElementsByTagNameNS(self::WORD_NAMESPACE, 'txbxContent') as $textbox) {
                $html .= $this->previewNode($textbox, $links, $images);
            }

            return $html;
        }

        if (! in_array($node->localName, ['body', 'p', 'r', 'hyperlink', 'tbl', 'tr', 'tc', 'sdt', 'sdtContent', 'ins', 'txbxContent'], true)) {
            return '';
        }

        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $this->previewNode($child, $links, $images);
        }

        if ($node->localName === 'hyperlink') {
            $url = $links[$node->getAttributeNS(self::RELATIONSHIP_NAMESPACE, 'id')] ?? null;

            return $url === null ? $html : '<a href="'.$this->escape($url).'" rel="noopener noreferrer">'.$html.'</a>';
        }

        if ($node->localName === 'r') {
            $properties = $this->directChild($node, 'rPr');

            foreach (['b' => 'strong', 'i' => 'em', 'u' => 'u', 'strike' => 's'] as $property => $tag) {
                $value = $properties === null ? null : $this->directChild($properties, $property);

                if ($value !== null && ! in_array($value->getAttributeNS(self::WORD_NAMESPACE, 'val'), ['0', 'false', 'none'], true)) {
                    $html = '<'.$tag.'>'.$html.'</'.$tag.'>';
                }
            }
        }

        $tag = match ($node->localName) {
            'p' => 'p', 'tbl' => 'table', 'tr' => 'tr', 'tc' => 'td', default => null,
        };

        if ($node->localName === 'p') {
            $properties = $this->directChild($node, 'pPr');
            $style = $properties === null ? null : $this->directChild($properties, 'pStyle');

            if ($style !== null && preg_match('/^Heading([1-6])$/i', $style->getAttributeNS(self::WORD_NAMESPACE, 'val'), $match)) {
                $tag = 'h'.$match[1];
            }

            if ($properties !== null && $this->directChild($properties, 'numPr') !== null) {
                $html = '• '.$html;
            }
        }

        return $tag === null ? $html : '<'.$tag.'>'.$html.'</'.$tag.'>';
    }

    private function directChild(DOMElement $parent, string $name): ?DOMElement
    {
        foreach ($parent->childNodes as $node) {
            if ($node instanceof DOMElement && $node->namespaceURI === self::WORD_NAMESPACE && $node->localName === $name) {
                return $node;
            }
        }

        return null;
    }

    private function xml(string $xml): DOMDocument
    {
        if ($xml === '' || preg_match('/<!\s*(DOCTYPE|ENTITY)\b/i', $xml)) {
            throw new InvalidArgumentException('The Word document contains unsupported XML.');
        }

        $document = new DOMDocument;
        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            if (! $document->loadXML($xml, LIBXML_NONET)) {
                throw new InvalidArgumentException('The Word document contains invalid XML.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        return $document;
    }

    private function readPart(ZipArchive $archive, string $name, bool $required = true): string
    {
        $stat = $archive->statName($name);

        if ($stat === false) {
            if (! $required) {
                return '';
            }

            throw new InvalidArgumentException('The file is not a supported Word document.');
        }

        if ($stat['size'] > self::MAX_XML_SIZE) {
            throw new InvalidArgumentException('The Word document is too large to preview.');
        }

        $contents = $archive->getFromName($name, self::MAX_XML_SIZE + 1);

        if ($contents === false || strlen($contents) > self::MAX_XML_SIZE) {
            throw new InvalidArgumentException('The Word document cannot be read.');
        }

        return $contents;
    }

    /** @param array<string, string> $parts */
    private function archive(array $parts): string
    {
        $path = $this->temporaryPath();
        $archive = new ZipArchive;

        try {
            if ($archive->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('The Word document could not be created.');
            }

            foreach ($parts as $name => $contents) {
                $archive->addFromString($name, $contents);
                $archive->setCompressionName($name, ZipArchive::CM_DEFLATE, 9);
            }

            $archive->close();
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new RuntimeException('The Word document could not be read.');
            }

            return $contents;
        } finally {
            unlink($path);
        }
    }

    private function temporaryPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'lesson-word-');

        if ($path === false) {
            throw new RuntimeException('Temporary document storage is unavailable.');
        }

        return $path;
    }

    private function styles(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><w:styles xmlns:w="'.self::WORD_NAMESPACE.'"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr></w:rPrDefault></w:docDefaults><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>';

        for ($level = 1; $level <= 6; $level++) {
            $xml .= '<w:style w:type="paragraph" w:styleId="Heading'.$level.'"><w:name w:val="heading '.$level.'"/><w:basedOn w:val="Normal"/><w:pPr><w:keepNext/><w:outlineLvl w:val="'.($level - 1).'"/></w:pPr><w:rPr><w:b/><w:sz w:val="'.(36 - $level * 2).'"/></w:rPr></w:style>';
        }

        return $xml.'</w:styles>';
    }

    private function safeUrl(string $url): bool
    {
        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto'], true)
            && ! preg_match('/[\x00-\x20]/', $url);
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
    }
}
