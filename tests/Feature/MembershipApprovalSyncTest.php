<?php

use App\Models\Membership;
use App\Models\User;
use App\Services\MembershipService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('marks the user row as pending when a pending membership is registered', function () {
    $user = User::factory()->create(['membership_status' => 'active']);

    (new MembershipService)->registerPending($user, []);

    expect($user->fresh()->membership_status)->toBe('pending')
        ->and($user->fresh()->approved_at)->toBeNull()
        ->and($user->isPendingApproval())->toBeTrue();
});

it('creates a pending membership row during the register flow', function () {
    Storage::fake('public');
    Event::fake();

    $this->post(route('auth.register'), [
        'name' => 'Grace Namutebi',
        'email' => 'grace@example.com',
        'registration_number' => 'BACS/26D/U/A0000',
        'phone' => '0700000001',
        'program' => 'Bachelor of Information Technology (BIT)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
        'intake' => 'august',
        'intake_year' => 2024,
        'date_of_birth' => '2002-06-04',
        'gender' => 'Female',
        'residence' => 'Nsambya Hostel',
        'headline' => 'Aspiring web application security analyst',
        'bio' => 'I am focused on practical web security and beginner friendly labs.',
        'emergency_contact_name' => 'Ritah Namutebi',
        'emergency_contact_phone' => '0700000002',
        'profile_photo' => UploadedFile::fake()->image('passport.jpg'),
        'password' => 'Password1!pass',
        'password_confirmation' => 'Password1!pass',
        'terms' => '1',
    ])->assertRedirect(route('verification.notice', absolute: false));

    $user = User::query()->where('email', 'grace@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->fresh()->membership_status)->toBe('pending')
        ->and($user->fresh()->membership)->not->toBeNull()
        ->and($user->fresh()->membership->status)->toBe('pending')
        ->and($user->fresh()->membership->type)->toBe('active')
        ->and($user->fresh()->membership->joined_at)->not->toBeNull();
});

it('syncs the membership row when a user is approved', function () {
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
        'year_of_study' => 2,
    ]);
    $membership = Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
        'joined_at' => now(),
    ]);

    $approver = User::factory()->create();

    $user->approve($approver);

    expect($user->fresh()->membership_status)->toBe('active')
        ->and($user->fresh()->approved_at)->not->toBeNull()
        ->and($membership->fresh()->status)->toBe('active')
        ->and($membership->fresh()->approved_at)->not->toBeNull()
        ->and($membership->fresh()->approved_by)->toBe($approver->id)
        ->and($membership->fresh()->isActive())->toBeTrue();
});

it('assigns sequential member numbers as users are approved', function () {
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $approver = User::factory()->create();

    $first = User::factory()->create(['membership_status' => 'pending']);
    $second = User::factory()->create(['membership_status' => 'pending']);
    $third = User::factory()->create(['membership_status' => 'pending']);

    $first->approve($approver);
    $second->approve($approver);
    $third->approve($approver);

    expect($first->fresh()->member_number)->toBe(1)
        ->and($second->fresh()->member_number)->toBe(2)
        ->and($third->fresh()->member_number)->toBe(3);
});

it('keeps the original member number when an approved user is approved again', function () {
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $approver = User::factory()->create();

    $user = User::factory()->create(['membership_status' => 'pending']);
    $user->approve($approver);

    $firstNumber = $user->fresh()->member_number;

    $user->approve($approver);

    expect($user->fresh()->member_number)->toBe($firstNumber);
});

it('assigns a member number when a membership is approved via the service', function () {
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
    ]);
    $membership = Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    $approver = User::factory()->create();

    (new MembershipService)->approve($membership, $approver);

    expect($user->fresh()->member_number)->not->toBeNull()
        ->and($user->fresh()->member_number)->toBe(1);
});

it('shows the sequential member number on the card after approval', function () {
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
        'year_of_study' => 2,
    ]);
    Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    $user->approve(User::factory()->create());

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('#'.str_pad((string) $user->fresh()->member_number, 5, '0', STR_PAD_LEFT));
});

it('syncs the membership row when a user is rejected', function () {
    Notification::fake();

    $user = User::factory()->create(['membership_status' => 'pending']);
    $membership = Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    $rejecter = User::factory()->create();

    $user->reject($rejecter);

    expect($user->fresh()->membership_status)->toBe('inactive')
        ->and($membership->fresh()->status)->toBe('rejected')
        ->and($membership->fresh()->isRejected())->toBeTrue();
});

it('shows a pending approval message instead of the card', function () {
    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
        'name' => 'Grace Namutebi',
    ]);
    Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('Your membership is awaiting approval')
        ->assertSee('Pending Approval')
        ->assertSee('Grace Namutebi')
        ->assertSee('Back to Dashboard')
        ->assertDontSee('Active Member');
});

it('shows an active member card once approved', function () {
    Carbon::setTestNow('2026-06-15 12:00:00');
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
        'year_of_study' => 2,
    ]);
    Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    $approver = User::factory()->create();

    $user->approve($approver);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('Active Member')
        ->assertDontSee('Pending Approval');
});
