<?php

use App\Filament\Resources\News\Pages\ManageNews;
use App\Models\News;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $this->admin->assignRole('super-admin');

    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function newsFormData(array $overrides = []): array
{
    return array_merge([
        'title' => 'New Cyber Drill Announced',
        'slug' => 'new-cyber-drill-announced',
        'excerpt' => 'A campus-wide cyber drill runs next week.',
        'content' => '<p>Details of the upcoming drill.</p>',
        'category' => 'industry',
        'content_type' => 'article',
        'is_published' => true,
    ], $overrides);
}

it('loads the news management page', function () {
    $this->get('/admin/manage-news')->assertSuccessful();
});

it('suggests the slug from the title when creating news', function () {
    Livewire::test(ManageNews::class)
        ->callAction('create', data: array_merge(newsFormData(), ['slug' => null]))
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('news', [
        'title' => 'New Cyber Drill Announced',
        'slug' => 'new-cyber-drill-announced',
    ]);
});

it('rejects a duplicate slug', function () {
    News::create(array_merge(newsFormData(), [
        'created_by' => $this->admin->id,
        'published_at' => now(),
    ]));

    Livewire::test(ManageNews::class)
        ->callAction('create', data: newsFormData())
        ->assertHasActionErrors(['slug']);
});

it('stamps the publish date and author when publishing without them', function () {
    Livewire::test(ManageNews::class)
        ->callAction('create', data: newsFormData())
        ->assertHasNoActionErrors();

    $news = News::where('slug', 'new-cyber-drill-announced')->first();

    expect($news)->not->toBeNull()
        ->and($news->published_at)->not->toBeNull()
        ->and($news->created_by)->toBe($this->admin->id);
});
