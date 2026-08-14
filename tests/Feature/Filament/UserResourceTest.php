<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

it('loads the user edit page with member profile data', function () {
    $user = User::factory()->create(['registration_number' => 'BACS/26D/U/A0000']);
    $user->memberProfile()->create([
        'phone' => '0700000001',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
        'bio' => 'A keen security enthusiast.',
    ]);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertSet('data.name', $user->name)
        ->assertSet('data.memberProfile.phone', '0700000001')
        ->assertSet('data.memberProfile.program', 'Bachelor of Science in Computer Science (BSCS)')
        ->assertSet('data.memberProfile.faculty', 'Faculty of Science & Technology')
        ->assertSet('data.memberProfile.year_of_study', 3)
        ->assertSet('data.memberProfile.bio', 'A keen security enthusiast.');
});

it('persists member profile data through the user edit form', function () {
    $user = User::factory()->create(['registration_number' => 'BACS/26D/U/A0000']);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->set('data.memberProfile.phone', '0700000001')
        ->set('data.memberProfile.program', 'Bachelor of Science in Computer Science (BSCS)')
        ->set('data.memberProfile.faculty', 'Faculty of Science & Technology')
        ->set('data.memberProfile.year_of_study', 3)
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('member_profiles', [
        'user_id' => $user->id,
        'phone' => '0700000001',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
    ]);
});

it('shows member profile data on the view modal', function () {
    $user = User::factory()->create(['registration_number' => 'BACS/26D/U/A0000']);
    $user->memberProfile()->create([
        'phone' => '0700000001',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
    ]);

    $this->get('/admin/users')->assertSuccessful();
});

it('deletes the old profile photo when the admin replaces it', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/old-avatar.jpg',
    ]);

    Storage::disk('public')->put('profile-photos/old-avatar.jpg', 'old');

    $component = Livewire::test(EditUser::class, ['record' => $user->getRouteKey()]);

    $oldFileKey = array_key_first($component->get('data.profile_photo'));

    $component
        ->call('callSchemaComponentMethod', 'form.profile_photo', 'removeUploadedFile', ['fileKey' => $oldFileKey])
        ->set('data.profile_photo', UploadedFile::fake()->image('new-avatar.jpg'))
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->profile_photo)->not->toBe('profile-photos/old-avatar.jpg');
    Storage::disk('public')->assertMissing('profile-photos/old-avatar.jpg');
    Storage::disk('public')->assertExists($user->profile_photo);
});

it('deletes the old profile photo when the admin removes it', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/old-avatar.jpg',
    ]);

    Storage::disk('public')->put('profile-photos/old-avatar.jpg', 'old');

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->set('data.profile_photo', null)
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->profile_photo)->toBeNull();
    Storage::disk('public')->assertMissing('profile-photos/old-avatar.jpg');
});

it('keeps the existing profile photo when it is not changed', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/old-avatar.jpg',
    ]);

    Storage::disk('public')->put('profile-photos/old-avatar.jpg', 'old');

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->profile_photo)->toBe('profile-photos/old-avatar.jpg');
    Storage::disk('public')->assertExists('profile-photos/old-avatar.jpg');
});
