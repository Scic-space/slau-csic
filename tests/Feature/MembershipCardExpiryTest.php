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
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Grace Namutebi',
        'email' => 'grace@example.com',
        'registration_number' => 'BACS/26D/U/A0000',
        'phone' => '0700000001',
        'program' => 'Bachelor of Information Technology (BIT)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
        'intake' => 'january',
        'intake_year' => 2024,
        'date_of_birth' => '2002-06-04',
        'gender' => 'Female',
        'residence' => 'Nsambya Hostel',
        'headline' => 'Aspiring web application security analyst',
        'bio' => 'I am focused on practical web security, beginner friendly labs, and helping the club organise stronger learning routines across campus.',
        'specialization_track' => 'Web security',
        'emergency_contact_name' => 'Ritah Namutebi',
        'emergency_contact_phone' => '0700000002',
        'github_username' => 'gracecodes',
        'linkedin_url' => 'https://linkedin.com/in/grace-namutebi',
        'discord_username' => 'grace#0001',
        'profile_photo' => UploadedFile::fake()->image('passport.jpg'),
        'password' => 'Password1!pass',
        'password_confirmation' => 'Password1!pass',
        'terms' => '1',
    ], $overrides);
}

it('shows the study-based expiry on the card when no expiry is stored', function () {
    Carbon::setTestNow('2026-06-15 12:00:00');

    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'year_of_study' => 3,
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('August 31, 2026');
});

it('prefers the stored expiry date over the study-based date', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'year_of_study' => 3,
        'membership_expires_at' => Carbon::create(2026, 11, 30),
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('November 30, 2026');
});

it('falls back to the member profile year of study', function () {
    Carbon::setTestNow('2026-06-15 12:00:00');

    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'year_of_study' => null,
        'program' => 'Diploma in Information Technology (DIT)',
    ]);
    $user->memberProfile()->create(['year_of_study' => 1]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('August 31, 2027');
});

it('shows N/A on the card for alumni members', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'alumni',
        'year_of_study' => 3,
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('N/A');
});

it('shows the sequential member id distinct from the registration number', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'member_number' => 42,
        'registration_number' => 'BACS/26D/U/A0000',
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('#00042')
        ->assertSee('BACS/26D/U/A0000')
        ->assertDontSee('#'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT));
});

it('shows the program abbreviation on the card', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $user->memberProfile()->create(['program' => 'Bachelor of Information Technology (BIT)']);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('BIT');
});

it('falls back to the full program when it has no abbreviation', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'program' => 'Bachelor of Science in Cyber Security',
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('Bachelor of Science in Cyber Security');
});

it('uses the intake year to compute the card expiry', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
        'year_of_study' => 3,
        'intake' => 'january',
        'intake_year' => 2024,
    ]);

    actingAs($user)
        ->get(route('membership.card'))
        ->assertOk()
        ->assertSee('January 31, 2027');
});

it('graduates year 4 january intake in the same year as year 3 august intake', function () {
    $january = User::factory()->create([
        'year_of_study' => 4,
        'intake' => 'january',
        'intake_year' => 2023,
    ]);

    $august = User::factory()->create([
        'year_of_study' => 3,
        'intake' => 'august',
        'intake_year' => 2023,
    ]);

    expect($january->graduationYear())->toBe($august->graduationYear())->toBe(2026);
});

it('computes the graduation year from the intake year', function (int $intakeYear, int $graduationYear) {
    $user = User::factory()->create([
        'intake_year' => $intakeYear,
    ]);

    expect($user->graduationYear())->toBe($graduationYear);
})->with([
    '2021 intake' => [2021, 2024],
    '2022 intake' => [2022, 2025],
    '2023 intake' => [2023, 2026],
    '2024 intake' => [2024, 2027],
]);

it('uses the intake month for the card expiry day', function (string $intake, string $expected) {
    $user = User::factory()->create([
        'intake' => $intake,
        'intake_year' => 2023,
    ]);

    expect($user->studyExpiryDate()->toDateString())->toBe($expected);
})->with([
    'august' => ['august', '2026-08-31'],
    'january' => ['january', '2026-01-31'],
    'may' => ['may', '2026-05-31'],
]);

it('uses the end of the year when no intake month is set', function () {
    $user = User::factory()->create([
        'intake' => null,
        'intake_year' => 2023,
    ]);

    expect($user->studyExpiryDate()->toDateString())->toBe('2026-12-31');
});

it('derives the course duration from the programme level', function (string $program, int $durationYears) {
    $user = User::factory()->create([
        'intake' => 'august',
        'intake_year' => 2024,
        'program' => $program,
    ]);

    expect($user->courseDurationYears())->toBe($durationYears)
        ->and($user->graduationYear())->toBe(2024 + $durationYears)
        ->and($user->studyExpiryDate()->toDateString())->toBe((2024 + $durationYears).'-08-31');
})->with([
    'bachelor' => ['Bachelor of Science in Computer Science (BSCS)', 3],
    'diploma' => ['Diploma in Information Technology (DIT)', 2],
    'certificate' => ['National Certificate in Information Technology (NCIT)', 1],
    'postgraduate diploma' => ['Postgraduate Diploma in Education (PGDE)', 1],
    'master' => ['Master of Business Administration and Management (MBA)', 2],
]);

it('expires a may intake card on may 31', function () {
    $user = User::factory()->create([
        'intake' => 'may',
        'intake_year' => 2024,
        'program' => 'Bachelor of Information Technology (BIT)',
    ]);

    expect($user->studyExpiryDate()->toDateString())->toBe('2027-05-31');
});

it('stores the intake details during registration', function () {
    Storage::fake('public');
    Event::fake();

    $response = $this->post(route('auth.register'), registrationPayload());

    $response->assertRedirect(route('verification.notice', absolute: false));

    $user = User::query()->where('email', 'grace@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->intake)->toBe('january')
        ->and($user->intake_year)->toBe(2024);
});

it('rejects an invalid intake during registration', function () {
    Storage::fake('public');
    Event::fake();

    $response = $this->from(route('auth.register'))->post(route('auth.register'), registrationPayload([
        'intake' => 'june',
    ]));

    $response->assertSessionHasErrors('intake');
});

it('rejects an invalid intake year during registration', function () {
    Storage::fake('public');
    Event::fake();

    $response = $this->from(route('auth.register'))->post(route('auth.register'), registrationPayload([
        'intake_year' => 1800,
    ]));

    $response->assertSessionHasErrors('intake_year');
});

it('updates the intake details from the member profile', function () {
    $user = User::factory()->create(['intake' => 'august']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\MemberProfile::class)
        ->set('name', $user->name)
        ->set('intake', 'january')
        ->set('intake_year', 2024)
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($user->fresh()->intake)->toBe('january')
        ->and($user->fresh()->intake_year)->toBe(2024);
});

it('stamps the expiry when a user is approved', function () {
    Carbon::setTestNow('2026-06-15 12:00:00');
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
        'year_of_study' => 1,
    ]);

    $approver = User::factory()->create();

    $user->approve($approver);

    expect($user->fresh()->membership_expires_at->toDateString())->toBe('2028-08-31');
});

it('stamps the expiry when a membership is approved via the service', function () {
    Carbon::setTestNow('2026-06-15 12:00:00');
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'membership_status' => 'pending',
        'membership_type' => 'active',
    ]);
    $user->memberProfile()->create(['year_of_study' => 2]);

    $membership = Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    $approver = User::factory()->create();

    (new MembershipService)->approve($membership, $approver);

    expect($user->fresh()->membership_expires_at->toDateString())->toBe('2027-08-31');
});

it('accepts a may intake during registration', function () {
    Storage::fake('public');
    Event::fake();

    $this->post(route('auth.register'), registrationPayload([
        'intake' => 'may',
    ]))
        ->assertRedirect(route('verification.notice', absolute: false));

    $user = User::query()->where('email', 'grace@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->intake)->toBe('may');
});

it('accepts a registration number with a trailing letter', function () {
    Storage::fake('public');
    Event::fake();

    $this->post(route('auth.register'), registrationPayload([
        'registration_number' => 'BACS/24D/U/A016O',
    ]))
        ->assertRedirect(route('verification.notice', absolute: false));

    $user = User::query()->where('email', 'grace@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->registration_number)->toBe('BACS/24D/U/A016O');
});

it('rejects a malformed registration number', function () {
    Storage::fake('public');
    Event::fake();

    $this->from(route('auth.register'))
        ->post(route('auth.register'), registrationPayload([
            'registration_number' => 'BACS-24D-U-A016O',
        ]))
        ->assertSessionHasErrors('registration_number');
});
