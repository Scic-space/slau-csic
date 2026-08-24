<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the responsive sidebar shell and content offsets', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('sidebar-shell', false)
        ->assertSee("'w-[280px]'", false)
        ->assertSee("'w-[80px]'", false)
        ->assertSee("'xl:ml-[280px]'", false)
        ->assertSee("'xl:ml-[80px]'", false)
        ->assertSee('Toggle Mobile Menu')
        ->assertSee('Close navigation menu');
});

it('keeps member navigation routes and maps them to material icons', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(route('dashboard'), false)
        ->assertSee(route('profile.edit'), false)
        ->assertSee(route('members.index'), false)
        ->assertSee(route('events.index'), false)
        ->assertSee(route('attendance.calendar'), false)
        ->assertSee(route('resources.index'), false)
        ->assertSee("'Dashboard': 'dashboard'", false)
        ->assertSee("'Attendance': 'fact_check'", false)
        ->assertSee("'Resource Library': 'library_books'", false)
        ->assertSee('sidebar-material-icon', false);
});

it('uses a clear active and hover style for navigation items', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('@utility menu-item-active')
        ->toContain('bg-brand-50 text-brand-600 shadow-sm ring-1')
        ->toContain('hover:bg-gray-100 hover:text-gray-900')
        ->toContain('.sidebar-shell .sidebar-material-icon');
});

it('renders accessible collapsible navigation sections and opens the current section', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();

    foreach (['member', 'events', 'finance', 'activities', 'learning', 'community'] as $section) {
        $response
            ->assertSee("toggleSubmenu('{$section}')", false)
            ->assertSee("isSubmenuOpen('{$section}').toString()", false)
            ->assertSee("isSubmenuOpen('{$section}') &&", false);
    }

    $response
        ->assertSee("this.openSubmenus['member'] = true", false)
        ->assertSee('expand_more', false)
        ->assertSee('x-collapse', false);
});

it('styles section controls and indented child navigation consistently', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('.sidebar-section-button')
        ->toContain('.sidebar-section-button-active')
        ->toContain('.sidebar-section-chevron')
        ->toContain('.sidebar-section-children');
});

it('adds material symbols to the CTF arena cards and sections', function () {
    $view = file_get_contents(resource_path('views/pages/club/ctf.blade.php'));

    expect($view)
        ->toContain('sports_esports')
        ->toContain('terminal')
        ->toContain('task_alt')
        ->toContain('stars')
        ->toContain('leaderboard')
        ->toContain('account_tree');
});
