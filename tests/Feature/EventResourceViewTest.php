<?php

use App\Models\Event;
use App\Models\EventResource;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('opens administrator resource notes before during and after lessons', function (string $status, bool $isMember) {
    $event = Event::factory()->create(['status' => $status, 'is_public' => ! $isMember]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'title' => 'Administrator lesson notes',
        'type' => 'document',
        'file_path' => 'event-resources/lesson.html',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, '<h2>Network security</h2><p>Protect every connection.</p>');
    $viewUrl = route('events.resources.show', [$event, $resource]);

    if ($isMember) {
        $this->actingAs(User::factory()->create())
            ->get(route('events.member-show', $event))
            ->assertOk()
            ->assertSee($viewUrl, false)
            ->assertSee('View');
    } else {
        $this->get(route('events.show', $event))
            ->assertInertia(fn (Assert $page) => $page
                ->component('events/Show')
                ->where('event.resources.0.url', $viewUrl));
    }

    $this->get($viewUrl)
        ->assertOk()
        ->assertViewIs('events.resource-notes')
        ->assertSee('Administrator lesson notes')
        ->assertSee('<h2>Network security</h2>', false)
        ->assertSee('Protect every connection.')
        ->assertSee(route('events.resources.download', [$event, $resource]), false);

    expect($event->registrations()->exists())->toBeFalse();
})->with(['published', 'scheduled', 'ongoing', 'completed'])->with([
    'public guest' => false,
    'approved member' => true,
]);

it('renders complete uploaded HTML documents with safe readable formatting', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/complete-notes.html',
        'url' => null,
    ]);
    $notes = '<!DOCTYPE html><html><head><title>Uploaded document</title>'
        .'<style>style-secret-marker</style></head><body><main><section>'
        .'<h2 onclick="steal()">Résumé – lesson notes</h2>'
        .'<p>Keep <strong>important content</strong> readable.</p>'
        .'<ul><li>First exercise</li></ul><table><tr><td>Network diagram</td></tr></table>'
        .'<script>script-secret-marker</script>'
        .'<iframe src="https://remote.example.test/frame">iframe-secret-marker</iframe>'
        .'<a href="javascript:steal()">Unsafe link text</a>'
        .'<a href="https://example.com/reading">Further reading</a>'
        .'</section></main></body></html>';
    Storage::disk('public')->put($resource->file_path, $notes);

    $this->get(route('events.resources.show', [$event, $resource]))
        ->assertOk()
        ->assertSee('Résumé – lesson notes')
        ->assertSee('<strong>important content</strong>', false)
        ->assertSee('<li>First exercise</li>', false)
        ->assertSee('<td>Network diagram</td>', false)
        ->assertSee('Unsafe link text')
        ->assertSee('href="https://example.com/reading"', false)
        ->assertDontSee('script-secret-marker')
        ->assertDontSee('style-secret-marker')
        ->assertDontSee('iframe-secret-marker')
        ->assertDontSee('onclick=', false)
        ->assertDontSee('javascript:steal()', false)
        ->assertDontSee('remote.example.test');
});

it('preserves literal text and newlines in uploaded text notes', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/plain-notes.txt',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, "First line\nSecond line\n1 < 2 & 3 > 2\n<script>example()</script>");

    $response = $this->get(route('events.resources.show', [$event, $resource]))
        ->assertOk()
        ->assertSee('First line')
        ->assertSee('Second line')
        ->assertSee('1 < 2 & 3 > 2')
        ->assertSee('<script>example()</script>')
        ->assertDontSee('<script>example()</script>', false);

    expect(preg_match_all('/<br\s*\/?\s*>/i', $response->getContent()))->toBeGreaterThanOrEqual(3);
});

it('renders Markdown notes with formatting', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/lesson.md',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, "# Lesson notes\n\nRead **carefully**.\n\n- First exercise");

    $this->get(route('events.resources.show', [$event, $resource]))
        ->assertOk()
        ->assertSee('<h1>Lesson notes</h1>', false)
        ->assertSee('<strong>carefully</strong>', false)
        ->assertSee('<li>First exercise</li>', false);
});

it('streams uploaded PDFs inline through the resource view route', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'title' => 'Network Lesson',
        'file_path' => 'event-resources/network-lesson.pdf',
        'url' => null,
    ]);
    $pdf = Pdf::loadHTML('<h1>Network lesson notes</h1>')->output();
    Storage::disk('public')->put($resource->file_path, $pdf);

    $response = $this->get(route('events.resources.show', [$event, $resource]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->headers->get('Content-Disposition'))->toStartWith('inline;')->toContain('network-lesson.pdf');
    expect($response->streamedContent())->toBe($pdf);
});

it('does not view resources for hidden or deleted events', function (string $status, bool $isDeleted, bool $isMember) {
    $event = Event::factory()->create(['status' => $status, 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/hidden.html',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, '<p>Private lesson content</p>');
    $viewUrl = route('events.resources.show', [$event, $resource]);

    if ($isMember) {
        $this->actingAs(User::factory()->create());
    }

    if ($isDeleted) {
        $event->delete();
    }

    $this->get($viewUrl)->assertNotFound();
})->with([
    'draft' => ['draft', false],
    'cancelled' => ['cancelled', false],
    'deleted' => ['published', true],
])->with([
    'public guest' => false,
    'member' => true,
]);

it('keeps private resource views unavailable to guests and members awaiting approval', function (bool $isPendingMember) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => false]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => 'event-resources/private.html',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, '<p>Private lesson content</p>');

    if ($isPendingMember) {
        $this->actingAs(User::factory()->pending()->create());
    }

    $this->get(route('events.resources.show', [$event, $resource]))->assertNotFound();
})->with([
    'guest' => false,
    'pending member' => true,
]);

it('does not view resources belonging to another event or no event', function (bool $isUnattached) {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);
    $resource = EventResource::factory()->create([
        'event_id' => $isUnattached ? null : Event::factory()->create()->id,
        'file_path' => 'event-resources/another-lesson.html',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, '<p>Another lesson content</p>');

    $this->get(route('events.resources.show', [$event, $resource]))->assertNotFound();
})->with([
    'another event' => false,
    'unattached resource' => true,
]);

it('does not substitute the event description for missing resource files', function (?string $filePath, ?string $url) {
    $event = Event::factory()->create([
        'status' => 'published',
        'is_public' => true,
        'description' => '<p>Different event description</p>',
    ]);
    $resource = EventResource::factory()->create([
        'event_id' => $event->id,
        'file_path' => $filePath,
        'url' => $url,
    ]);

    $this->get(route('events.resources.show', [$event, $resource]))->assertNotFound();
})->with([
    'missing stored file' => ['event-resources/missing.html', null],
    'external link' => [null, 'https://example.com/slides'],
    'empty file path' => ['', null],
    'no file or link' => [null, null],
]);

it('returns not found for a missing resource view', function () {
    $event = Event::factory()->create(['status' => 'published', 'is_public' => true]);

    $this->get(route('events.resources.show', [$event, 999]))->assertNotFound();
});
