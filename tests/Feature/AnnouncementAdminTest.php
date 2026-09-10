<?php

use App\Filament\Resources\Announcements\Pages\ManageAnnouncements;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('loads the announcement management page', function () {
    Announcement::factory()->create(['title' => 'Loaded announcement']);

    $this->get('/admin/manage-announcements')
        ->assertSuccessful()
        ->assertSee('Loaded announcement');
});

it('renders the redesigned create and edit announcement forms', function () {
    $announcement = Announcement::factory()->create();

    Livewire::test(ManageAnnouncements::class)
        ->assertActionExists(
            CreateAction::class,
            fn (\Filament\Actions\Action $action): bool => $action->getModalHeading() === 'Create announcement',
        )
        ->assertTableActionExists(
            'edit',
            fn (\Filament\Actions\Action $action): bool => $action->getModalHeading() === 'Edit announcement',
            $announcement,
        );
});

it('creates an announcement with a manual slug and valid audience', function () {
    Livewire::test(ManageAnnouncements::class)
        ->callAction(CreateAction::class, data: [
            'title' => 'Manual Slug Announcement',
            'slug' => 'a-carefully-chosen-slug',
            'content' => '<p>Announcement content</p>',
            'type' => 'general',
            'audience' => 'active_members',
            'target_roles' => [],
            'is_published' => false,
            'send_email' => false,
            'send_push' => false,
            'published_at' => null,
            'expires_at' => null,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('announcements', [
        'title' => 'Manual Slug Announcement',
        'slug' => 'a-carefully-chosen-slug',
        'audience' => 'active_members',
        'created_by' => $this->admin->id,
    ]);
});

it('validates invalid and duplicate manual slugs', function () {
    Announcement::factory()->create(['slug' => 'existing-slug']);

    Livewire::test(ManageAnnouncements::class)
        ->callAction(CreateAction::class, data: [
            'title' => 'Duplicate Slug',
            'slug' => 'existing-slug',
            'content' => '<p>Announcement content</p>',
            'type' => 'general',
            'audience' => 'all',
        ])
        ->assertHasActionErrors(['slug' => 'unique']);

    Livewire::test(ManageAnnouncements::class)
        ->callAction(CreateAction::class, data: [
            'title' => 'Invalid Slug',
            'slug' => 'Invalid slug!',
            'content' => '<p>Announcement content</p>',
            'type' => 'general',
            'audience' => 'all',
        ])
        ->assertHasActionErrors(['slug' => 'regex']);
});

it('generates distinct slugs when a slug is not supplied outside the form', function () {
    $first = Announcement::factory()->create([
        'title' => 'Repeated Title',
        'slug' => null,
    ]);
    $second = Announcement::factory()->create([
        'title' => 'Repeated Title',
        'slug' => null,
    ]);

    expect($first->slug)->toBe('repeated-title')
        ->and($second->slug)->toBe('repeated-title-2');
});

it('edits manual slugs without changing existing slugs unnecessarily', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-slug',
    ]);

    Livewire::test(ManageAnnouncements::class)
        ->callTableAction('edit', $announcement, data: [
            'title' => 'Updated Title',
            'slug' => 'updated-manual-slug',
            'content' => $announcement->content,
            'type' => $announcement->type,
            'audience' => 'board',
            'target_roles' => [],
            'is_published' => false,
            'send_email' => false,
            'send_push' => false,
            'published_at' => null,
            'expires_at' => null,
        ])
        ->assertHasNoTableActionErrors();

    expect($announcement->refresh()->slug)->toBe('updated-manual-slug')
        ->and($announcement->audience)->toBe('board');

    $announcement->update(['title' => 'Another Updated Title']);

    expect($announcement->refresh()->slug)->toBe('updated-manual-slug');
});

it('hydrates a generated slug when editing a legacy announcement without one', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Legacy Announcement',
        'slug' => 'temporary-slug',
    ]);
    Announcement::withoutEvents(fn () => $announcement->update(['slug' => null]));

    Livewire::test(ManageAnnouncements::class)
        ->mountTableAction('edit', $announcement)
        ->assertTableActionDataSet([
            'slug' => 'legacy-announcement',
        ]);
});

it('deletes announcements from the management page', function () {
    $announcement = Announcement::factory()->create();

    Livewire::test(ManageAnnouncements::class)
        ->callTableAction('delete', $announcement)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
});
