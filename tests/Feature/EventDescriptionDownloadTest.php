<?php

use App\Models\Event;
use App\Models\User;
use App\Services\EventDescriptionDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('offers a compressed description PDF before during and after lessons without registration', function (string $status, string $startDate, string $endDate, bool $isMember) {
    $event = Event::factory()->create([
        'title' => 'Introduction to Network Security',
        'description' => '<h2>Lesson notes</h2><p>Protect every connection.</p>',
        'status' => $status,
        'is_public' => ! $isMember,
        'start_date' => now()->modify($startDate),
        'end_date' => now()->modify($endDate),
    ]);
    $downloadUrl = route('events.description.download', $event);

    if ($isMember) {
        $this->actingAs(User::factory()->create())
            ->get(route('events.member-show', $event))
            ->assertOk()
            ->assertSee('Download description (PDF)')
            ->assertSee($downloadUrl, false)
            ->assertDontSee('Protect every connection.');
    } else {
        $this->get(route('events.show', $event))
            ->assertInertia(fn (Assert $page) => $page
                ->component('events/Show')
                ->where('event.description_download_url', $downloadUrl)
                ->missing('event.description'));
    }

    $response = $this->get($downloadUrl)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('introduction-to-network-security-description.pdf');

    expect($response->getContent())
        ->toStartWith('%PDF-')
        ->toContain('/FlateDecode');
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

it('does not download descriptions for hidden or deleted events', function (string $status, bool $isDeleted, bool $isMember) {
    $event = Event::factory()->create(['status' => $status, 'is_public' => true]);
    $downloadUrl = route('events.description.download', $event);

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

it('keeps private description PDFs unavailable to guests and members awaiting approval', function (bool $isPendingMember) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => false]);

    if ($isPendingMember) {
        $this->actingAs(User::factory()->pending()->create());
    }

    $this->get(route('events.description.download', $event))->assertNotFound();
})->with([
    'guest' => false,
    'pending member' => true,
]);

it('does not offer or download descriptions without readable content', function (string $description) {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description' => $description,
    ]);

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.description_download_url', null)
            ->missing('event.description'));

    $this->actingAs(User::factory()->create())
        ->get(route('events.member-show', $event))
        ->assertOk()
        ->assertDontSee('Download description (PDF)')
        ->assertDontSee(route('events.description.download', $event), false);

    $this->get(route('events.description.download', $event))->assertNotFound();
})->with([
    'empty description' => '',
    'whitespace' => " \n\t ",
    'empty rich text' => '<p><br></p><div>&nbsp;</div>',
    'non-document content' => '<script>alert("empty")</script><style>body { color: red; }</style>',
]);

it('returns not found for a missing event description download', function () {
    $this->get(route('events.description.download', ['event' => 'missing-lesson']))->assertNotFound();
});

it('downloads the latest saved description and stops offering it after removal', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description' => '<p>Original lesson notes.</p>',
    ]);
    $downloadUrl = route('events.description.download', $event);
    $originalPdf = $this->get($downloadUrl)->assertOk()->getContent();

    $event->update(['description' => '<h2>Revised lesson notes</h2><p>Additional practice for the next lesson.</p>']);

    $revisedPdf = $this->get($downloadUrl)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->getContent();

    expect($revisedPdf)->toStartWith('%PDF-')->not->toBe($originalPdf);
    expect(app(EventDescriptionDocument::class)->html($event->fresh()->description))
        ->toContain('Revised lesson notes', 'Additional practice for the next lesson.')
        ->not->toContain('Original lesson notes.');

    $event->update(['description' => '']);

    $this->get($downloadUrl)->assertNotFound();
});

it('preserves document formatting and Unicode when preparing description PDFs', function () {
    $description = '<h2>Résumé – lesson notes</h2>'
        .'<p>Read <strong>carefully</strong> and <em>practise</em>.</p>'
        .'<ul><li>First topic</li></ul><ol><li>First exercise</li></ol>'
        .'<blockquote>Think before acting.</blockquote>'
        .'<table><thead><tr><th>Topic</th></tr></thead><tbody><tr><td>Networks</td></tr></tbody></table>'
        .'<pre><code>if (1 &lt; 2) { return &quot;ready&quot;; }</code></pre>'
        .'<p><a href="https://example.com/lesson">Further reading</a></p>';

    $html = app(EventDescriptionDocument::class)->html($description);

    expect($html)->toContain(
        '<h2>', 'Résumé – lesson notes', '<strong>carefully</strong>', '<em>practise</em>',
        '<ul>', '<ol>', '<li>First exercise</li>', '<blockquote>', '<table>', '<th>Topic</th>',
        '<td>Networks</td>', '<pre>', '<code>', 'href="https://example.com/lesson"',
    );
    expect(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        ->toContain('if (1 < 2) { return "ready"; }');
});

it('preserves line breaks and comparison characters in plain descriptions', function () {
    $html = app(EventDescriptionDocument::class)->html("First line\nSecond line\n1 < 2 & 3 > 2");

    expect(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        ->toContain('First line', 'Second line', '1 < 2 & 3 > 2');
    expect(preg_match_all('/<br\s*\/?\s*>/i', $html))->toBe(2);
});

it('removes executable markup remote embeds and unsafe document links', function () {
    $description = '<h2 onclick="steal()">Safe heading</h2>'
        .'<script>script-secret-marker</script><style>style-secret-marker</style>'
        .'<p style="background: url(https://remote.example.test/image.png)">Safe paragraph</p>'
        .'<img src="file:///etc/passwd" onerror="steal()">'
        .'<iframe src="https://remote.example.test/frame">iframe-secret-marker</iframe>'
        .'<a href="java&#x73;cript:alert(1)">Unsafe script link</a>'
        .'<a href="data:text/html,private">Unsafe data link</a>'
        .'<a href="file:///etc/passwd">Unsafe local link</a>';

    $html = app(EventDescriptionDocument::class)->html($description);

    expect($html)
        ->toContain('Safe heading', 'Safe paragraph', 'Unsafe script link', 'Unsafe data link', 'Unsafe local link')
        ->not->toContain(
            '<script', '<style', '<img', '<iframe', 'onclick=', 'onerror=', 'style=',
            'script-secret-marker', 'style-secret-marker', 'iframe-secret-marker',
            'javascript:', 'data:', 'file:', 'remote.example.test',
        );
});

it('handles malformed rich text without losing its lesson content', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description' => '<h2>Malformed notes<p>Still readable <strong>important details',
    ]);

    expect(app(EventDescriptionDocument::class)->html($event->description))
        ->toContain('Malformed notes', 'Still readable', 'important details');

    $this->get(route('events.description.download', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload();
});

it('keeps image references readable without embedding image files in the PDF', function () {
    $html = app(EventDescriptionDocument::class)->html(
        '<p>Network diagram</p><img src="https://example.com/network.png" alt="Network topology">'
        .'<img src="/storage/events/practice.png" title="Practice diagram">'
        .'<img src="file:///etc/passwd" alt="Unsupported diagram">',
    );

    expect($html)
        ->toContain(
            'href="https://example.com/network.png"',
            'Network topology',
            'href="'.url('/storage/events/practice.png').'"',
            'Practice diagram',
            'Unsupported diagram',
        )
        ->not->toContain('<img', 'file:///etc/passwd');
});

it('treats a missing description as an unavailable document', function () {
    expect(app(EventDescriptionDocument::class)->html(null))->toBeNull();
});
