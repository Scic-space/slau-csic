<?php

use App\Filament\Widgets\MemberStatusCards;
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

it('calculates all six member status card counts from live member data', function () {
    User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);
    User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    User::factory()->create(['membership_status' => 'inactive', 'membership_type' => 'alumni']);
    User::factory()->create(['membership_status' => 'suspended', 'membership_type' => 'active']);
    User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'membership_expires_at' => now()->addDays(10),
    ]);

    $widget = app(MemberStatusCards::class);
    $method = new ReflectionMethod($widget, 'getStats');
    $cards = collect($method->invoke($widget))->keyBy(fn ($card) => $card->getLabel());

    expect((int) $cards['All Members']->getValue())->toBe(6)
        ->and((int) $cards['Pending Approval']->getValue())->toBe(1)
        ->and((int) $cards['Active Members']->getValue())->toBe(3)
        ->and((int) $cards['Alumni']->getValue())->toBe(1)
        ->and((int) $cards['Suspended']->getValue())->toBe(1)
        ->and((int) $cards['Expiring Soon']->getValue())->toBe(1);
});

it('renders clickable responsive status cards linked to each users tab', function () {
    $response = $this->get('/admin/users');

    $response
        ->assertSuccessful()
        ->assertSee('fi-wi-stats-overview-stat', false)
        ->assertSee('fi-wi-stats-overview-stat-content', false)
        ->assertDontSee('class="fi-tabs', false)
        ->assertSee('rounded-sm', false)
        ->assertSee('hover:-translate-y-0.5', false)
        ->assertSee('>arrow_forward<', false);

    $page = file_get_contents(app_path('Filament/Resources/Users/Pages/ListUsers.php'));

    expect($page)
        ->toContain('MemberStatusCards::class')
        ->toContain('parent::getTabsContentComponent()->hidden()')
        ->toContain('public function getTabs(): array');

    foreach (['all', 'pending', 'active', 'alumni', 'suspended', 'expiring'] as $tab) {
        $response->assertSee("tab={$tab}", false);
    }

    foreach (['groups', 'pending_actions', 'person_check', 'school', 'person_off', 'event_busy'] as $icon) {
        $response->assertSee(">{$icon}<", false);
    }

    $widget = app(MemberStatusCards::class);
    $columns = new ReflectionMethod($widget, 'getColumns');

    expect($columns->invoke($widget))->toBe(['default' => 1, 'md' => 2, 'lg' => 3]);
});

it('uses the meeting analytics card structure and icon treatment on users', function () {
    $users = $this->get('/admin/users');
    $analytics = $this->get('/admin/meeting-analytics');

    foreach (['fi-wi-stats-overview', 'fi-wi-stats-overview-stat', 'fi-wi-stats-overview-stat-label-ctn', 'fi-wi-stats-overview-stat-value', 'material-symbols-outlined'] as $class) {
        $users->assertSee($class, false);
        $analytics->assertSee($class, false);
    }
});

it('preserves member filtering through the analytics cards', function () {
    User::factory()->create(['name' => 'Pending Filter Member', 'membership_status' => 'pending']);
    User::factory()->create(['name' => 'Suspended Filter Member', 'membership_status' => 'suspended']);

    $this->get('/admin/users?tab=pending')
        ->assertSuccessful()
        ->assertSee('Pending Filter Member')
        ->assertDontSee('Suspended Filter Member');

    $this->get('/admin/users?tab=suspended')
        ->assertSuccessful()
        ->assertSee('Suspended Filter Member')
        ->assertDontSee('Pending Filter Member');
});

it('limits the users table to the requested member fields', function () {
    $table = file_get_contents(app_path('Filament/Resources/Users/Tables/UsersTable.php'));

    expect($table)
        ->toContain("TextColumn::make('name')")
        ->toContain("TextColumn::make('memberProfile.year_of_study')")
        ->toContain("TextColumn::make('memberProfile.faculty')")
        ->toContain("TextColumn::make('memberProfile.phone')")
        ->toContain("TextColumn::make('roles.name')")
        ->toContain("->label('Role')")
        ->not->toContain("TextColumn::make('email')")
        ->not->toContain("TextColumn::make('registration_number')")
        ->not->toContain("TextColumn::make('membership_status')")
        ->not->toContain("TextColumn::make('membership_type')")
        ->not->toContain("TextColumn::make('joined_at')")
        ->not->toContain("TextColumn::make('approved_at')");
});

it('provides material-icon member actions and keeps additional data in the view action', function () {
    $table = file_get_contents(app_path('Filament/Resources/Users/Tables/UsersTable.php'));
    $resource = file_get_contents(app_path('Filament/Resources/Users/UserResource.php'));
    $actionStyle = file_get_contents(app_path('Filament/Support/AdminActionStyle.php'));

    expect($table)
        ->toContain('ViewAction::make()')
        ->toContain('EditAction::make()')
        ->toContain("Action::make('approve')")
        ->toContain("Action::make('suspend')")
        ->toContain("Action::make('convert_to_alumni')")
        ->toContain("AdminActionStyle::apply(ViewAction::make(), 'View', 'visibility', 'info')")
        ->toContain("AdminActionStyle::apply(EditAction::make(), 'Edit', 'edit', 'teal')")
        ->toContain("AdminActionStyle::apply(DeleteAction::make(), 'Delete', 'delete', 'danger')")
        ->toContain("materialIcon('check_circle')")
        ->toContain("materialIcon('pause_circle')")
        ->toContain("materialIcon('school')")
        ->toContain("->color('success')")
        ->toContain("->color('danger')")
        ->toContain("->color('purple')")
        ->toContain("->tooltip('Approve')")
        ->toContain("->tooltip('Suspend')")
        ->toContain("->tooltip('Convert to Alumni')")
        ->toContain("'aria-label' => \$label")
        ->toContain("'title' => \$label")
        ->and($actionStyle)
        ->toContain('->iconButton()')
        ->toContain('->tooltip($label)')
        ->toContain("'aria-label' => \$label")
        ->toContain("'title' => \$label")
        ->and($resource)
        ->toContain('getViewForm')
        ->toContain("TextInput::make('email')")
        ->toContain("TextInput::make('registration_number')")
        ->toContain("TextInput::make('program')")
        ->toContain("Textarea::make('admin_notes')");
});

it('uses teal material-icon actions on the users page', function () {
    $page = file_get_contents(app_path('Filament/Resources/Users/Pages/ListUsers.php'));

    expect($page)
        ->toContain("->color('teal')")
        ->toContain("materialIcon('person_add')")
        ->toContain("materialIcon('upload_file')")
        ->toContain("materialIcon('mail')");
});
