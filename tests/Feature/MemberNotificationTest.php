<?php

use App\Models\User;
use App\Notifications\MemberApprovalNotification;
use App\Notifications\MemberRequiresApproval;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('renders the approval-required email with an inline logo and no duplicated club name', function () {
    $admin = User::factory()->create();
    $pending = User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);

    $mail = (new MemberRequiresApproval($pending))->toMail($admin);

    expect($mail->actionText)->toBe('Review Pending Members');
    expect($mail->actionUrl)->toContain('/admin/users');
    expect($mail->actionUrl)->toContain('tableFilters');
    expect($mail->actionUrl)->toContain('pending');

    $html = (string) $mail->render();

    expect($html)->toContain('data:image/png;base64,');
    expect($html)->not->toContain('images/club_logo.png');

    $body = preg_replace('/<head>.*?<\/head>/s', '', $html);
    $visibleText = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $body)));
    expect(substr_count($visibleText, 'SLAU Cybersecurity & Innovations Club'))->toBe(1);
});

it('renders the approval email with the embedded logo', function () {
    $member = User::factory()->create();
    $admin = User::factory()->create();

    $mail = (new MemberApprovalNotification($admin))->toMail($member);

    $html = (string) $mail->render();

    expect($html)->toContain('data:image/png;base64,');
    expect($html)->not->toContain('images/club_logo.png');
    expect(html_entity_decode(strip_tags($html)))->toContain('Best regards, The Cybersecurity & Innovations Club Team');
});

it('notifies the member when their application is approved', function () {
    Notification::fake();

    $member = User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);
    $admin = User::factory()->create();

    expect($member->approve($admin))->toBeTrue();

    Notification::assertSentTo($member, MemberApprovalNotification::class);
});

it('notifies the member when their application is rejected', function () {
    Notification::fake();

    $member = User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);
    $admin = User::factory()->create();

    expect($member->reject($admin, 'Duplicate application'))->toBeTrue();

    Notification::assertSentTo($member, App\Notifications\MemberRejectionNotification::class);
});
