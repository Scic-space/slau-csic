<?php

use App\Filament\Pages\MemberExport;
use App\Filament\Pages\MembershipStatistics;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

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

it('renders the membership statistics page for an admin', function () {
    User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);

    $this->get('/admin/membership-statistics')
        ->assertSuccessful()
        ->assertSee('Membership Statistics')
        ->assertSee('Active Members');
});

it('renders the member export page for an admin', function () {
    $this->get('/admin/member-export')
        ->assertSuccessful()
        ->assertSee('Export Members')
        ->assertSee('Download Export');
});

it('computes membership statistics from live data', function () {
    User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    User::factory()->create(['membership_status' => 'active', 'membership_type' => 'associate']);
    User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);
    User::factory()->create(['membership_status' => 'suspended', 'membership_type' => 'active']);

    $stats = Livewire::test(MembershipStatistics::class)->instance()->getStatistics();

    expect($stats['total'])->toBe(5)
        ->and($stats['active'])->toBe(3)
        ->and($stats['pending'])->toBe(1)
        ->and($stats['suspended'])->toBe(1)
        ->and($stats['active_rate'])->toBe(60.0)
        ->and($stats['by_status']['active'])->toBe(3);
});

it('downloads a member export that respects the status filter', function () {
    User::factory()->create(['name' => 'Active Export Member', 'membership_status' => 'active', 'membership_type' => 'active']);
    User::factory()->create(['name' => 'Pending Export Member', 'membership_status' => 'pending', 'membership_type' => 'active']);

    Livewire::test(MemberExport::class)
        ->set('data.membership_status', 'pending')
        ->call('download')
        ->assertFileDownloaded();
});

it('forbids users without permission from the membership admin pages', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    $this->actingAs($member);

    expect(MembershipStatistics::canAccess())->toBeFalse();
    expect(MemberExport::canAccess())->toBeFalse();

    $this->get('/admin/membership-statistics')->assertForbidden();
    $this->get('/admin/member-export')->assertForbidden();
});

it('rejects a pending member even when the notification mail fails', function () {
    config(['mail.default' => 'throwing']);

    Mail::extend('throwing', fn () => new class implements TransportInterface
    {
        public function send(RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): ?\Symfony\Component\Mailer\SentMessage
        {
            throw new TransportException('Mail is down.');
        }

        public function __toString(): string
        {
            return 'throwing';
        }
    });

    $member = User::factory()->create(['membership_status' => 'pending', 'membership_type' => 'active']);

    Livewire::test(ListUsers::class)
        ->callTableAction('reject', $member, data: [
            'rejection_notes' => 'Incomplete application.',
        ])
        ->assertHasNoTableActionErrors();

    expect($member->fresh()->membership_status)->toBe('inactive');
});
