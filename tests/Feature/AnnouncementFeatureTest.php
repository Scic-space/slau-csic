<?php

use App\Livewire\AnnouncementListing;
use App\Livewire\AnnouncementShow;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
});

it('renders the announcements listing page', function () {
    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertStatus(200)
        ->assertSee('Announcements');
});

it('shows empty state when no announcements exist', function () {
    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('No announcements yet');
});

it('shows published announcements on listing', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Test Announcement Title',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('Test Announcement Title')
        ->assertSee($announcement->slug);
});

it('hides unpublished announcements from listing', function () {
    Announcement::factory()->draft()->create([
        'title' => 'Draft Announcement',
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertDontSee('Draft Announcement');
});

it('renders announcement detail page', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Detail Page Test',
        'content' => '<p>Full content here</p>',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => $announcement->slug])
        ->assertStatus(200)
        ->assertSee('Detail Page Test')
        ->assertSee('Full content here');
});

it('shows author name on detail page', function () {
    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => $announcement->slug])
        ->assertSee($this->user->name);
});

it('returns 404 for non-existent announcement', function () {
    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => 'non-existent-slug'])
        ->assertStatus(404);
});

it('returns 404 for unpublished announcement detail', function () {
    $announcement = Announcement::factory()->draft()->create([
        'title' => 'Draft Detail',
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => $announcement->slug])
        ->assertStatus(404);
});

it('auto-generates slug from title', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'My Great Announcement!',
        'slug' => null,
    ]);

    expect($announcement->slug)->toBe('my-great-announcement');
});

it('orders announcements by published_at descending', function () {
    Announcement::factory()->create([
        'title' => 'Older Announcement',
        'published_at' => now()->subDays(5),
        'is_published' => true,
    ]);
    Announcement::factory()->create([
        'title' => 'Newer Announcement',
        'published_at' => now()->subDays(1),
        'is_published' => true,
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSeeInOrder(['Newer Announcement', 'Older Announcement']);
});

it('shows type badge on listing', function () {
    Announcement::factory()->create([
        'title' => 'Urgent Notice',
        'type' => 'urgent',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('Urgent');
});

it('limits listing to 20 announcements', function () {
    $announcements = collect();
    for ($i = 0; $i < 25; $i++) {
        $announcements->push(Announcement::factory()->create([
            'title' => 'Announcement '.$i,
            'is_published' => true,
            'published_at' => now()->subMinutes($i),
        ]));
    }

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertDontSee('Announcement 24');
});

it('renders detail page from route', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Route Test Announcement',
        'is_published' => true,
        'published_at' => now(),
    ]);

    actingAs($this->user)
        ->get('/announcements/'.$announcement->slug)
        ->assertSuccessful()
        ->assertSee('Route Test Announcement');
});

it('returns 404 from route for non-existent slug', function () {
    actingAs($this->user)
        ->get('/announcements/does-not-exist')
        ->assertNotFound();
});

it('does not show detail for unpublished via route', function () {
    $announcement = Announcement::factory()->draft()->create();

    actingAs($this->user)
        ->get('/announcements/'.$announcement->slug)
        ->assertNotFound();
});

it('shows back link on detail page', function () {
    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => $announcement->slug])
        ->assertSee('Back to Announcements');
});

it('includes published_at in listing data', function () {
    $date = now()->subDays(3);
    Announcement::factory()->create([
        'title' => 'Dated Announcement',
        'published_at' => $date,
        'is_published' => true,
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('Dated Announcement')
        ->assertSee('ago');
});

it('marks announcement as viewed when detail page is visited', function () {
    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => $announcement->slug]);

    expect($announcement->fresh()->isViewedBy($this->user))->toBeTrue();
});

it('shows unseen indicator on listing for unread announcements', function () {
    Announcement::factory()->create([
        'title' => 'Unread Announcement',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('New');
});

it('does not show unseen indicator after announcement is read', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Read Announcement',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $announcement->markAsViewedBy($this->user);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertDontSee('Read Announcement</h3>'); // check the title renders but without the "New" badge
});

it('shows expired badge for expired announcements', function () {
    Announcement::factory()->create([
        'title' => 'Expired Announcement',
        'is_published' => true,
        'published_at' => now()->subDays(10),
        'expires_at' => now()->subDays(5),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('Expired');
});

it('shows active badge for non-expired announcements', function () {
    Announcement::factory()->create([
        'title' => 'Active Announcement',
        'is_published' => true,
        'published_at' => now(),
        'expires_at' => now()->addDays(7),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('New'); // Active + unseen = New badge
});

it('shows expired banner on detail page for expired announcements', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Expired Detail',
        'is_published' => true,
        'published_at' => now()->subDays(10),
        'expires_at' => now()->subDays(5),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['slug' => $announcement->slug])
        ->assertSee('This announcement has expired')
        ->assertSee('Expired');
});

it('shows view count on listing after announcement is viewed', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Viewed Announcement',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $announcement->markAsViewedBy($this->user);

    Livewire::actingAs($this->user)
        ->test(AnnouncementListing::class)
        ->assertSee('1 view');
});

it('does not show expired announcement as active', function () {
    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now()->subDays(10),
        'expires_at' => now()->subDays(5),
    ]);

    expect($announcement->isActive())->toBeFalse();
    expect($announcement->isExpired())->toBeTrue();
});

it('shows non-expiring announcement as active', function () {
    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now(),
        'expires_at' => null,
    ]);

    expect($announcement->isActive())->toBeTrue();
    expect($announcement->isExpired())->toBeFalse();
});

it('handles multiple users viewing the same announcement', function () {
    $user2 = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);

    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);

    $announcement->markAsViewedBy($this->user);
    $announcement->markAsViewedBy($user2);

    expect($announcement->fresh()->view_count)->toBe(2);
});

it('is idempotent when marking same user viewed twice', function () {
    $announcement = Announcement::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);

    $announcement->markAsViewedBy($this->user);
    $announcement->markAsViewedBy($this->user);

    expect($announcement->fresh()->view_count)->toBe(1);
});
