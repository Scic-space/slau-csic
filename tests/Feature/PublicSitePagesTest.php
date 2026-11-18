<?php

use App\Models\Announcement;
use App\Models\Competition;
use App\Models\News;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public home page', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
});

it('shows announcement reader counts and latest read time on the public home page', function () {
    $firstReader = User::factory()->create(['membership_status' => 'active']);
    $secondReader = User::factory()->create(['membership_status' => 'active']);

    $announcement = Announcement::factory()->create([
        'title' => 'Public Home News',
        'content' => '<p>Important club update.</p>',
        'audience' => 'all',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $announcement->markAsViewedBy($firstReader);
    $announcement->markAsViewedBy($secondReader);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->where('announcements.0.view_count', 2)
            ->where('announcements.0.last_viewed_at', fn ($value) => is_string($value))
            ->where('announcements.0.content', 'Important club update.'));
});

it('renders the projects page', function () {
    Project::factory()->create(['name' => 'Test Project']);

    $response = $this->get(route('projects'));

    $response->assertSuccessful();
});

it('renders the competitions page', function () {
    Competition::factory()->create(['name' => 'Test Competition']);

    $response = $this->get(route('competitions.index'));

    $response->assertSuccessful();
});

it('renders the announcements page', function () {
    $user = User::factory()->create();

    Announcement::create([
        'title' => 'Welcome Message',
        'content' => 'Club is starting new projects.',
        'type' => 'general',
        'audience' => 'all',
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->get(route('announcements.index'));

    $response->assertSuccessful();
});

it('tracks unique news readers and shows the count on public news pages', function () {
    $news = News::factory()->create([
        'title' => 'Public News Views',
        'excerpt' => 'A public news update.',
        'content' => '<p>A public news update.</p>',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->withSession(['news_viewer_key' => 'reader-one'])
        ->get(route('news.show', $news))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('public/NewsShow')
            ->where('article.view_count', 1)
            ->where('article.last_viewed_at', fn ($value) => is_string($value)));

    $this->withSession(['news_viewer_key' => 'reader-two'])
        ->get(route('news.show', $news))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('public/NewsShow')->where('article.view_count', 2));

    $this->withSession(['news_viewer_key' => 'reader-two'])
        ->get(route('news.show', $news))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('public/NewsShow')->where('article.view_count', 2));

    $this->get(route('news.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('public/News')
            ->where('articles.0.view_count', 2)
            ->where('articles.0.last_viewed_at', fn ($value) => is_string($value)));
});

it('shows zero readers for a public news article that has not been viewed', function () {
    News::factory()->create([
        'title' => 'Unread Public News',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('news.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('public/News')
            ->where('articles.0.view_count', 0)
            ->where('articles.0.last_viewed_at', null));
});

it('renders the leaderboard page', function () {
    $response = $this->get(route('leaderboard.index'));

    $response->assertSuccessful();
});

it('shows approved members in the public directory', function () {
    User::factory()->create([
        'name' => 'Doreen Nanyonga',
        'email' => 'doreen@example.com',
        'membership_status' => 'active',
        'approved_at' => now(),
        'joined_at' => now()->subMonths(4),
        'headline' => 'Digital forensics learner',
        'bio' => 'Focused on evidence handling, structured investigation, and club challenge work.',
        'privacy_settings' => [
            'show_profile' => true,
            'show_program' => true,
            'show_year' => true,
        ],
    ]);

    $response = $this->get(route('members.public'));

    $response->assertSuccessful();
    $response->assertSee('Doreen Nanyonga');
});

it('shows the public profile of an approved visible member', function () {
    $member = User::factory()->create([
        'name' => 'Paul Kato',
        'email' => 'paul@example.com',
        'membership_status' => 'active',
        'approved_at' => now(),
        'joined_at' => now()->subMonths(2),
        'headline' => 'CTF participant',
        'bio' => 'Building practical experience through club labs, competition practice, and secure development exercises.',
        'privacy_settings' => [
            'show_profile' => true,
            'show_program' => true,
            'show_year' => true,
        ],
    ]);

    $response = $this->get(route('members.public.show', $member));

    $response->assertSuccessful();
    $response->assertSee('Paul Kato');
    $response->assertSee('CTF participant');
});
