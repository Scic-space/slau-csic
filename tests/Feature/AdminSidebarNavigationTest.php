<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

it('renders the admin navigation groups and standalone pages', function () {
    $response = $this->get('/admin');

    $response
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertSee('My Profile');

    foreach (['Membership', 'Events', 'Meetings', 'Finance', 'Fines', 'Exams', 'CTF', 'Testimonials', 'Elections', 'Assignments', 'Projects', 'System'] as $section) {
        $response->assertSee("data-group-label=\"{$section}\"", false);
    }
});

it('renders admin-only material icons and expandable navigation behavior', function () {
    $response = $this->get('/admin');

    $response
        ->assertSuccessful()
        ->assertSee('material-symbols-outlined', false)
        ->assertSee('sectionIcons', false)
        ->assertSee('itemIcons', false)
        ->assertSee('toggleCollapsedGroup', false)
        ->assertSee('collapsedGroups', false)
        ->assertSee('fi-sidebar-group.fi-active', false);
});

it('keeps the member sidebar implementation separate', function () {
    $memberSidebar = file_get_contents(resource_path('views/layouts/sidebar.blade.php'));
    $adminEnhancements = file_get_contents(resource_path('views/filament/admin-sidebar-enhancements.blade.php'));

    expect($memberSidebar)
        ->not->toContain('admin-sidebar-enhancements')
        ->and($adminEnhancements)
        ->toContain('.fi-sidebar')
        ->toContain("font-family: 'Google Sans Flex'");
});

it('loads the remaining admin sidebar destinations', function (string $path) {
    $this->get($path)->assertSuccessful();
})->with([
    'profile' => '/admin/my-profile',
    'pending approvals' => '/admin/users?tab=pending',
    'alumni' => '/admin/users?tab=alumni',
    'testimonials' => '/admin/testimonials',
    'contact messages' => '/admin/contact-messages',
    'projects' => '/admin/projects',
    'news' => '/admin/manage-news',
]);
