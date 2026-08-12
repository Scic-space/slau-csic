<?php

use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createContactMessage(array $overrides = []): ContactMessage
{
    return ContactMessage::create(array_merge([
        'name' => 'Grace Namutebi',
        'email' => 'grace@example.com',
        'topic' => 'Membership',
        'message' => 'How do I renew my membership?',
    ], $overrides));
}

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

it('creates contact messages as unread', function () {
    $message = createContactMessage();

    expect($message->isUnread())->toBeTrue()
        ->and(ContactMessage::unread()->count())->toBe(1);
});

it('marks a contact message as read', function () {
    $message = createContactMessage();

    $message->markAsRead();

    expect($message->fresh()->isUnread())->toBeFalse()
        ->and($message->fresh()->read_at)->not->toBeNull()
        ->and(ContactMessage::unread()->count())->toBe(0);
});

it('does not re-stamp read_at on an already read message', function () {
    $message = createContactMessage();
    $message->markAsRead();

    $firstReadAt = $message->fresh()->read_at;
    $message->fresh()->markAsRead();

    expect($message->fresh()->read_at->equalTo($firstReadAt))->toBeTrue();
});

it('marks all unread messages as read when the admin views the inbox', function () {
    createContactMessage();
    createContactMessage(['topic' => 'Events']);
    $alreadyRead = createContactMessage();
    $alreadyRead->markAsRead();

    expect(ContactMessage::unread()->count())->toBe(2);

    $this->get('/admin/contact-messages')->assertSuccessful();

    expect(ContactMessage::unread()->count())->toBe(0);
});

it('shows the unread count in the admin navigation badge', function () {
    createContactMessage();
    createContactMessage(['topic' => 'Events']);
    createContactMessage(['name' => 'Read Message'])->markAsRead();

    $navigation = Filament::getPanel('admin')->getNavigation();
    $badges = [];

    foreach ($navigation as $group) {
        foreach ($group->getItems() as $item) {
            if ($item->getLabel() === 'Contact Messages') {
                $badges[] = $item->getBadge();
            }
        }
    }

    expect($badges)->not->toBeEmpty()
        ->and($badges[0])->toBe('2');
});
