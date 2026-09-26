<?php

use App\Models\Event;
use App\Models\EventResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
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
        'file_path' => 'event-resources/lecture.pdf',
        'url' => null,
    ]);
    Storage::disk('public')->put($resource->file_path, 'Lesson slides');
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
        ->assertDownload('lecture-slides.pdf');

    expect($response->streamedContent())->toBe('Lesson slides');
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
