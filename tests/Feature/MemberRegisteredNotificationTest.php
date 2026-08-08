<?php

use App\Events\MemberRegistered;
use App\Models\Membership;
use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use App\Notifications\MemberRequiresApproval;
use App\Notifications\WelcomeMember;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends exactly one welcome notification when a member registers', function () {
    Notification::fake();

    $user = User::factory()->create();
    $membership = Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    MemberRegistered::dispatch($user, $membership);

    Notification::assertSentToTimes($user, WelcomeMember::class, 1);
});

it('sends exactly one approval request to each admin when a member registers', function () {
    Notification::fake();

    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $admin->membership()->create([
        'type' => 'active',
        'status' => 'active',
    ]);

    $user = User::factory()->create();
    $membership = Membership::create([
        'user_id' => $user->id,
        'type' => 'active',
        'status' => 'pending',
    ]);

    MemberRegistered::dispatch($user, $membership);

    Notification::assertSentToTimes($admin, MemberRequiresApproval::class, 1);
    Notification::assertSentToTimes($user, WelcomeMember::class, 1);
});

it('sends exactly one email verification code notification on registration', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    event(new Registered($user));

    Notification::assertSentToTimes($user, EmailVerificationCodeNotification::class, 1);
    Notification::assertNotSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
});
