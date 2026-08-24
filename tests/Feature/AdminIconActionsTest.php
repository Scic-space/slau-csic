<?php

use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\System\Pages\ListContentPages;
use App\Filament\Resources\System\Pages\ListSettings;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\ContentPage;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders consistent accessible icon-only actions on the four admin pages', function (string $path, callable $createRecord, array $icons) {
    $createRecord();

    $response = $this->get($path);

    $response->assertSuccessful()
        ->assertSee('fi-icon-btn', false)
        ->assertSee('rounded-sm', false)
        ->assertSee('transition-colors', false);

    foreach ($icons as $label => $icon) {
        $response->assertSee(">{$icon}<", false)
            ->assertSee("aria-label=\"{$label}\"", false)
            ->assertSee("title=\"{$label}\"", false);
    }
})->with([
    'roles' => ['/admin/roles', fn () => Role::create(['name' => 'Icon Test Role', 'guard_name' => 'web']), ['View' => 'visibility', 'Edit' => 'edit', 'Delete' => 'delete']],
    'settings' => ['/admin/system/settings', fn () => Setting::query()->create(['key' => 'icon_test', 'value' => 'yes', 'type' => 'string', 'group' => 'general']), ['View' => 'visibility', 'Edit' => 'edit', 'Delete' => 'delete']],
    'content pages' => ['/admin/system/content-pages', fn () => ContentPage::query()->create(['title' => 'Icon Test Page', 'slug' => 'icon-test-page', 'content' => 'Content']), ['View' => 'visibility', 'Edit' => 'edit', 'Delete' => 'delete']],
    'users' => ['/admin/users', fn () => User::factory()->create(['name' => 'Deletable Icon User']), ['View' => 'visibility', 'Edit' => 'edit', 'Delete' => 'delete']],
]);

it('uses one shared action style for icons tooltips accessibility colors and dimensions', function () {
    $style = file_get_contents(app_path('Filament/Support/AdminActionStyle.php'));

    expect($style)
        ->toContain('->iconButton()')
        ->toContain('->tooltip($label)')
        ->toContain("'aria-label' => \$label")
        ->toContain("'title' => \$label")
        ->toContain('rounded-sm transition-colors')
        ->toContain('text-[20px]');

    foreach ([
        app_path('Filament/Resources/Roles/Tables/RolesTable.php'),
        app_path('Filament/Resources/System/SettingsResource.php'),
        app_path('Filament/Resources/System/ContentPageResource.php'),
        app_path('Filament/Resources/Users/Tables/UsersTable.php'),
    ] as $resource) {
        expect(file_get_contents($resource))->toContain('AdminActionStyle::apply');
    }
});

it('keeps role view edit and confirmed delete actions operational', function () {
    $role = Role::create(['name' => 'Action Test Role', 'guard_name' => 'web']);

    Livewire::test(ListRoles::class)
        ->mountTableAction('view', $role)
        ->assertSuccessful()
        ->unmountTableAction()
        ->assertTableActionExists('edit', record: $role)
        ->mountTableAction('delete', $role);

    $this->assertDatabaseHas('roles', ['id' => $role->id]);

    Livewire::test(ListRoles::class)
        ->callTableAction('delete', $role)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    $this->get("/admin/roles/{$this->admin->roles()->first()->id}/edit")->assertSuccessful();
});

it('keeps settings view edit and confirmed delete actions operational', function () {
    $setting = Setting::query()->create(['key' => 'action_test', 'value' => 'yes', 'type' => 'string', 'group' => 'general']);

    Livewire::test(ListSettings::class)
        ->mountTableAction('view', $setting)
        ->assertSuccessful()
        ->unmountTableAction()
        ->mountTableAction('delete', $setting);

    $this->assertDatabaseHas('settings', ['id' => $setting->id]);
    $this->get("/admin/system/settings/{$setting->id}/edit")->assertSuccessful();

    Livewire::test(ListSettings::class)->callTableAction('delete', $setting);
    $this->assertDatabaseMissing('settings', ['id' => $setting->id]);
});

it('keeps content page view edit and confirmed delete actions operational', function () {
    $page = ContentPage::query()->create(['title' => 'Action Test Page', 'slug' => 'action-test-page', 'content' => 'Content']);

    Livewire::test(ListContentPages::class)
        ->mountTableAction('view', $page)
        ->assertSuccessful()
        ->unmountTableAction()
        ->mountTableAction('delete', $page);

    $this->assertDatabaseHas('content_pages', ['id' => $page->id]);
    $this->get("/admin/system/content-pages/{$page->id}/edit")->assertSuccessful();

    Livewire::test(ListContentPages::class)->callTableAction('delete', $page);
    $this->assertDatabaseMissing('content_pages', ['id' => $page->id]);
});

it('adds an operational confirmed delete action without changing other user actions', function () {
    $user = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->assertTableActionExists('view', record: $user)
        ->assertTableActionExists('edit', record: $user)
        ->assertTableActionExists('delete', record: $user)
        ->mountTableAction('delete', $user);

    $this->assertDatabaseHas('users', ['id' => $user->id]);

    Livewire::test(ListUsers::class)
        ->callTableAction('delete', $user)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
