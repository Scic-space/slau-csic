<?php

use App\Filament\Resources\CtfCategories\Pages\ManageCtfCategories;
use App\Models\CtfCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
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

it('renders accessible material icon-only category actions with tooltips and colors', function () {
    CtfCategory::factory()->create();

    $response = $this->get('/admin/manage-ctf-categories');

    $response->assertSuccessful()
        ->assertSee('>visibility<', false)
        ->assertSee('>edit<', false)
        ->assertSee('>delete<', false)
        ->assertSee('aria-label="View"', false)
        ->assertSee('aria-label="Edit"', false)
        ->assertSee('aria-label="Delete"', false)
        ->assertSee('title="View"', false)
        ->assertSee('title="Edit"', false)
        ->assertSee('title="Delete"', false)
        ->assertSee('fi-icon-btn', false);

    $resource = file_get_contents(app_path('Filament/Resources/CtfCategories/CtfCategoryResource.php'));

    expect($resource)
        ->toContain('ViewAction::make()')
        ->toContain('EditAction::make()')
        ->toContain('DeleteAction::make()')
        ->toContain("->tooltip('View')")
        ->toContain("->tooltip('Edit')")
        ->toContain("->tooltip('Delete')")
        ->toContain("->color('info')")
        ->toContain("->color('teal')")
        ->toContain("->color('danger')");
});

it('keeps the category view and edit actions operational', function () {
    $category = CtfCategory::factory()->create(['name' => 'Original Category']);

    Livewire::test(ManageCtfCategories::class)
        ->assertTableActionExists('view', record: $category)
        ->assertTableActionExists('edit', record: $category)
        ->mountTableAction('view', $category)
        ->assertSuccessful()
        ->unmountTableAction()
        ->callTableAction('edit', $category, data: [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'color' => '#123456',
            'icon' => 'flag',
            'sort_order' => 2,
        ])
        ->assertHasNoTableActionErrors();

    expect($category->refresh()->name)->toBe('Updated Category');
});

it('keeps the category delete action operational', function () {
    $category = CtfCategory::factory()->create();

    Livewire::test(ManageCtfCategories::class)
        ->assertTableActionExists('delete', record: $category)
        ->callTableAction('delete', $category)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('ctf_categories', ['id' => $category->id]);
});
