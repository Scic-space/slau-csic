<?php

use App\Models\Event;
use App\Models\EventResource;
use App\Models\User;
use App\Services\WordDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

it('offers resource downloads before during and after a lesson without registration', function (string $status, string $startDate, string $endDate, bool $isMember) {
    $event = Event::factory()->create([
        'status' => $status,
        'is_public' => ! $isMember,
        'start_date' => now()->modify($startDate),
        'end_date' => now()->modify($endDate),
    ]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'title' => 'Lecture Slides',
        'type' => 'slide',
        'file_path' => 'event-resources/lecture.docx',
        'url' => null,
    ]);
    $document = app(WordDocument::class)->create('<h1>Lesson slides</h1><p>Linux OS commands.</p>');
    Storage::disk('public')->put($resource->file_path, $document);
    $downloadUrl = route('events.resources.download', [$event, $resource]);

    if ($isMember) {
        $this->actingAs(User::factory()->create())
            ->get(route('events.member-show', $event))
            ->assertOk()
            ->assertSee('Lecture Slides')
            ->assertSee('Download')
            ->assertSee($downloadUrl, false);
    } else {
        $this->get(route('events.show', $event))
            ->assertInertia(fn (Assert $page) => $page
                ->component('events/Show')
                ->where('event.resources.0.download_url', $downloadUrl));
    }

    $response = $this->get($downloadUrl)
        ->assertOk()
        ->assertHeader('Content-Type', WordDocument::MIME_TYPE)
        ->assertDownload('lecture-slides.docx');

    expect($response->streamedContent())->toBe($document)->toStartWith('PK');
    expect(eventResourceDocumentXml($response->streamedContent()))->toContain('Linux OS commands.');
    expect($event->registrations()->exists())->toBeFalse();
})->with([
    'before published lesson' => ['published', '+1 day', '+1 day +2 hours'],
    'before scheduled lesson' => ['scheduled', '+1 day', '+1 day +2 hours'],
    'during lesson' => ['ongoing', '-1 hour', '+1 hour'],
    'after lesson' => ['completed', '-2 days', '-1 day'],
])->with([
    'public guest' => false,
    'member' => true,
]);

it('does not download resources for hidden or deleted events', function (string $status, bool $isDeleted, bool $isMember) {
    $event = Event::factory()->create(['status' => $status, 'is_public' => true]);
    $resource = EventResource::factory()->create(['event_id' => $event->id]);
    $resource->update(['file_path' => 'event-resources/lesson.pdf', 'url' => null]);
    Storage::disk('public')->put($resource->file_path, 'Hidden lesson');
    $downloadUrl = route('events.resources.download', [$event, $resource]);

    if ($isMember) {
        $this->actingAs(User::factory()->create());
    }

    if ($isDeleted) {
        $event->delete();
    }

    $this->get($downloadUrl)->assertNotFound();
})->with([
    'draft' => ['draft', false],
    'cancelled' => ['cancelled', false],
    'deleted' => ['published', true],
])->with([
    'public guest' => false,
    'member' => true,
]);

it('keeps private resources unavailable to guests and members awaiting approval', function (bool $isPendingMember) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => false]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/private.pdf',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, 'Private lesson');

    if ($isPendingMember) {
        $this->actingAs(User::factory()->pending()->create());
    }

    $this->get(route('events.resources.download', [$event, $resource]))->assertNotFound();
})->with([
    'guest' => false,
    'pending member' => true,
]);

it('does not download resources belonging to another event or no event', function (bool $isUnattached) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $isUnattached ? null : Event::factory()->create()->id,
        'file_path' => 'event-resources/another-lesson.pdf',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, 'Another lesson');

    $this->get(route('events.resources.download', [$event, $resource]))->assertNotFound();
})->with([
    'another event' => false,
    'unattached resource' => true,
]);

it('returns not found when a resource has no downloadable file', function (?string $filePath, ?string $url) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => $filePath,
        'url' => $url,
    ]);

    $this->get(route('events.resources.download', [$event, $resource]))->assertNotFound();
})->with([
    'missing stored file' => ['event-resources/missing.pdf', null],
    'external link' => [null, 'https://example.com/slides'],
    'empty file path' => ['', null],
    'no file or link' => [null, null],
]);

it('renders external links and empty resources without download links', function (?string $filePath, ?string $url) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => $filePath,
        'url' => $url,
    ]);

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Show')
            ->where('event.resources.0.url', $url)
            ->where('event.resources.0.download_url', null));

    $response = $this->actingAs(User::factory()->create())
        ->get(route('events.member-show', $event))
        ->assertOk()
        ->assertSee($resource->title)
        ->assertDontSee(route('events.resources.download', [$event, $resource]), false);

    if ($url !== null) {
        $response->assertSee($url, false)->assertSee('View');
    }
})->with([
    'external link' => [null, 'https://example.com/slides'],
    'empty file path' => ['', null],
    'no file or link' => [null, null],
]);

it('returns not found for a missing resource', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);

    $this->get(route('events.resources.download', [$event, 999]))->assertNotFound();
});

it('converts uploaded lesson notes into real Word downloads', function (string $extension, string $notes) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'title' => 'Network Security Notes',
        'type' => 'document',
        'file_path' => 'event-resources/network-notes.'.$extension,
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, $notes);
    $downloadUrl = route('events.resources.download', [$event, $resource]);

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.resources.0.download_url', $downloadUrl));

    $this->actingAs(User::factory()->create())
        ->get(route('events.member-show', $event))
        ->assertOk()
        ->assertSee($downloadUrl, false);

    $response = $this->get($downloadUrl)
        ->assertOk()
        ->assertHeader('Content-Type', WordDocument::MIME_TYPE)
        ->assertDownload('network-security-notes.docx');

    expect($response->getContent())->toStartWith('PK');
    expect(eventResourceDocumentXml($response->getContent()))
        ->toContain('Network security')
        ->toContain('Protect ')
        ->toContain('each')
        ->toContain('connection.');
})->with([
    'HTML document' => ['html', '<!DOCTYPE html><html><head><title>Lesson</title><style>body { color: red; }</style></head><body><main><section><h1>Network security</h1><p>Protect each connection.</p></section></main></body></html>'],
    'HTM document' => ['htm', '<h1>Network security</h1><p>Protect each connection.</p>'],
    'plain text document' => ['txt', "Network security\nProtect each connection.\n1 < 2 & 3 > 2"],
    'Markdown short extension' => ['md', "# Network security\n\nProtect **each** connection."],
    'Markdown long extension' => ['markdown', "# Network security\n\nProtect **each** connection."],
]);

it('rejects HTML files disguised as PDFs without serving raw HTML', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/disguised.pdf',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, '<!DOCTYPE html><html><body>raw-html-marker</body></html>');

    $this->get(route('events.resources.download', [$event, $resource]))
        ->assertUnsupportedMediaType()
        ->assertDontSee('raw-html-marker');
});

it('does not offer a Word download for unsupported resource files', function (string $extension) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/archive.'.$extension,
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, 'Unsupported resource bytes');
    $downloadUrl = route('events.resources.download', [$event, $resource]);

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.resources.0.download_url', null));

    $this->actingAs(User::factory()->create())
        ->get(route('events.member-show', $event))
        ->assertOk()
        ->assertDontSee($downloadUrl, false);

    $this->get($downloadUrl)->assertUnsupportedMediaType();
})->with(['zip', 'mp4']);

it('converts an uploaded PDF to a Word document containing its lesson content', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'title' => 'Linux OS',
        'file_path' => 'event-resources/linux-os.pdf',
        'url' => null,
    ]);
    $pdf = Pdf::loadHTML('<h1>Linux OS</h1><p>Use pwd to display the current directory.</p><p>Use ls to list files.</p>')->output();
    Storage::disk('public')->put($resource->file_path, $pdf);

    $response = $this->get(route('events.resources.download', [$event, $resource]))
        ->assertOk()
        ->assertHeader('Content-Type', WordDocument::MIME_TYPE)
        ->assertDownload('linux-os.docx');

    expect($response->getContent())->toStartWith('PK')->not->toStartWith('%PDF-');
    $xml = eventResourceDocumentXml($response->getContent());
    $document = new DOMDocument;
    expect($document->loadXML($xml, LIBXML_NONET))->toBeTrue();
    expect($document->textContent)
        ->toContain('Linux OS')
        ->toContain('Use pwd to display the current directory.')
        ->toContain('Use ls to list files.');
});

it('rejects malformed Word files without serving their raw contents', function (string $contents) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/disguised.docx',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, $contents);

    $this->get(route('events.resources.download', [$event, $resource]))
        ->assertUnsupportedMediaType()
        ->assertDontSee('invalid-word-document-marker');
})->with([
    'renamed HTML' => '<h1>invalid-word-document-marker</h1>',
    'invalid ZIP' => 'PK invalid-word-document-marker',
]);

function eventResourceDocumentXml(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'event-resource-docx-');
    file_put_contents($path, $contents);
    $archive = new ZipArchive;

    try {
        expect($archive->open($path))->toBeTrue();
        expect($archive->getFromName('[Content_Types].xml'))
            ->toContain('application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml');
        expect($archive->getFromName('_rels/.rels'))->toBeString();
        $xml = $archive->getFromName('word/document.xml');
        expect($xml)->toBeString()->toContain('http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        return $xml;
    } finally {
        $archive->close();
        unlink($path);
    }
}
