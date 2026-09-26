<?php

use App\Services\WordDocument;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

uses(Tests\TestCase::class);

function wordDocumentFixture(array $parts): string
{
    $path = tempnam(sys_get_temp_dir(), 'word-test-');
    $archive = new ZipArchive;
    $archive->open($path, ZipArchive::OVERWRITE);

    foreach ($parts as $name => $contents) {
        $archive->addFromString($name, $contents);
    }

    $archive->close();
    $contents = file_get_contents($path);
    unlink($path);

    return $contents;
}

function wordDocumentXml(string $body): string
{
    return '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><w:body>'.$body.'</w:body></w:document>';
}

it('creates compressed Word documents with readable Unicode lessons and formatting', function () {
    $service = app(WordDocument::class);
    $contents = $service->create('<h2>Linux OS</h2><p>第一课 — café &amp; terminal</p><p><strong>Install</strong> <em>Ubuntu</em></p><ol start="3"><li>Open terminal</li><li>Run commands</li></ol><ul><li>Safe updates</li></ul><table><tr><th>Command</th><th>Purpose</th></tr><tr><td>pwd</td><td>Current directory</td></tr></table><p><a href="https://ubuntu.com/tutorials">Read more</a></p>', 'Lesson notes');
    $path = tempnam(sys_get_temp_dir(), 'word-test-');
    file_put_contents($path, $contents);
    $archive = new ZipArchive;

    try {
        expect($contents)->toStartWith('PK')
            ->and($archive->open($path))->toBeTrue()
            ->and($archive->getFromName('[Content_Types].xml'))->toContain('wordprocessingml.document.main+xml')
            ->and($archive->getFromName('_rels/.rels'))->toContain('word/document.xml')
            ->and($archive->statName('word/document.xml')['comp_method'])->toBe(ZipArchive::CM_DEFLATE);
        $document = new DOMDocument;
        expect($document->loadXML($archive->getFromName('word/document.xml')))->toBeTrue();
    } finally {
        $archive->close();
        unlink($path);
    }

    expect($service->preview($contents))
        ->toContain('<h1>Lesson notes</h1>', '<h2>Linux OS</h2>', '第一课 — café &amp; terminal', '<strong>Install</strong>', '<em>Ubuntu</em>', '3. Open terminal', '4. Run commands', '• Safe updates', '<table>', '<td><p>pwd</p>', 'https://ubuntu.com/tutorials');
});

it('sanitizes unsafe HTML before generating a Word lesson', function () {
    $service = app(WordDocument::class);
    $contents = $service->create('<p onclick="unsafe()">Linux notes</p><script>alert("unsafe")</script><p><a href="javascript:unsafe()">Open guide</a></p>');

    expect($service->preview($contents))->toContain('Linux notes', 'Open guide')
        ->not->toContain('onclick', 'javascript:', 'script', 'unsafe');
});

it('creates documents that LibreOffice opens with the lesson content intact', function () {
    $binary = (new ExecutableFinder)->find('libreoffice');

    if ($binary === null) {
        $this->markTestSkipped('LibreOffice is unavailable.');
    }

    $directory = sys_get_temp_dir().'/word-reader-'.bin2hex(random_bytes(8));
    mkdir($directory);
    $contents = app(WordDocument::class)->create('<h2>Linux OS</h2><p>Terminal café lessons</p><table><tr><td>pwd</td><td>Current directory</td></tr></table>');
    file_put_contents($directory.'/lesson.docx', $contents);

    try {
        $process = new Process([$binary, '-env:UserInstallation=file://'.$directory.'/profile', '--headless', '--convert-to', 'pdf', '--outdir', $directory, $directory.'/lesson.docx']);
        $process->setTimeout(30)->mustRun();

        $text = new Process(['pdftotext', $directory.'/lesson.pdf', '-']);
        $text->setTimeout(10)->mustRun();

        expect($text->getOutput())->toContain('Linux OS', 'Terminal café lessons', 'pwd', 'Current directory');
    } finally {
        app(\Illuminate\Filesystem\Filesystem::class)->deleteDirectory($directory);
    }
});

it('previews only safe external hyperlinks and escapes lesson text', function () {
    $document = wordDocumentXml('<w:p><w:hyperlink r:id="safe"><w:r><w:t>Ubuntu guide</w:t></w:r></w:hyperlink><w:hyperlink r:id="unsafe"><w:r><w:t>&lt;script&gt;Unsafe&lt;/script&gt;</w:t></w:r></w:hyperlink></w:p>');
    $relationships = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="safe" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="https://ubuntu.com/tutorials" TargetMode="External"/><Relationship Id="unsafe" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="javascript:alert(1)" TargetMode="External"/></Relationships>';
    $contents = wordDocumentFixture(['word/document.xml' => $document, 'word/_rels/document.xml.rels' => $relationships]);

    expect(app(WordDocument::class)->preview($contents))->toContain('href="https://ubuntu.com/tutorials"', 'Ubuntu guide', '&lt;script&gt;Unsafe&lt;/script&gt;')
        ->not->toContain('javascript:', '<script>');
});

it('previews embedded images and text boxes without fetching external files', function () {
    $document = wordDocumentXml('<w:p><w:r><w:drawing xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:blip r:embed="image"/><a:blip r:embed="remote"/><w:txbxContent><w:p><w:r><w:t>Linux screenshot</w:t></w:r></w:p></w:txbxContent></w:drawing></w:r></w:p>');
    $relationships = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="image" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image.png"/><Relationship Id="remote" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="https://example.com/private.png" TargetMode="External"/></Relationships>';
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jR5kAAAAASUVORK5CYII=');
    $contents = wordDocumentFixture(['word/document.xml' => $document, 'word/_rels/document.xml.rels' => $relationships, 'word/media/image.png' => $png]);

    expect(app(WordDocument::class)->preview($contents))->toContain('Linux screenshot', '<img src="data:image/png;base64,')
        ->not->toContain('https://example.com', 'private.png');
});

it('rejects invalid archives and unsupported XML', function (string $contents) {
    expect(fn () => app(WordDocument::class)->preview($contents))->toThrow(InvalidArgumentException::class);
})->with([
    'HTML renamed docx' => '<html>Not Word</html>',
    'missing document' => fn () => wordDocumentFixture(['other.xml' => '<test/>']),
    'invalid document' => fn () => wordDocumentFixture(['word/document.xml' => '<invalid>']),
    'empty document' => fn () => wordDocumentFixture(['word/document.xml' => '']),
    'wrong root' => fn () => wordDocumentFixture(['word/document.xml' => '<test/>']),
    'external entities' => fn () => wordDocumentFixture(['word/document.xml' => '<!DOCTYPE document [<!ENTITY external SYSTEM "file:///etc/passwd">]>'.wordDocumentXml('<w:p><w:r><w:t>&external;</w:t></w:r></w:p>')]),
    'inflated oversized XML' => fn () => wordDocumentFixture(['word/document.xml' => wordDocumentXml('<w:p><w:r><w:t>'.str_repeat('a', 8 * 1024 * 1024).'</w:t></w:r></w:p>')]),
]);
