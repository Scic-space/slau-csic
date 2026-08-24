<?php

use App\Filament\Pages\EventAnalytics;
use App\Filament\Pages\EventCalendar;
use App\Filament\Pages\MeetingAnalytics;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Widgets\TransactionStatusCards;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders a full-width navigable calendar without the category id mismatch', function () {
    $response = $this->get(EventCalendar::getUrl());

    $response
        ->assertSuccessful()
        ->assertSee('adminCalendar()', false)
        ->assertSee('Previous month')
        ->assertSee('Next month')
        ->assertSee('min-w-[48rem]', false)
        ->assertSee('selectedCategories.includes(String(id))', false)
        ->assertSee('material-symbols-outlined', false);
});

it('uses purpose-matched Material Icons on event and meeting analytics cards', function () {
    $this->get(EventAnalytics::getUrl())
        ->assertSuccessful()
        ->assertSee('event_upcoming')
        ->assertSee('play_circle')
        ->assertSee('how_to_reg')
        ->assertSee('calendar_month');

    $this->get(MeetingAnalytics::getUrl())
        ->assertSuccessful()
        ->assertSee('groups')
        ->assertSee('analytics')
        ->assertSee('person_check')
        ->assertSee('history');
});

it('calculates live transaction card counts and amounts', function () {
    Transaction::factory()->create([
        'created_by' => $this->admin->id,
        'type' => 'income',
        'status' => 'pending',
        'amount' => 1000,
    ]);
    Transaction::factory()->create([
        'created_by' => $this->admin->id,
        'type' => 'expense',
        'status' => 'approved',
        'amount' => 250,
    ]);
    Transaction::factory()->create([
        'created_by' => $this->admin->id,
        'type' => 'expense',
        'status' => 'rejected',
        'amount' => 500,
    ]);

    $widget = app(TransactionStatusCards::class);
    $method = new ReflectionMethod($widget, 'getStats');
    $cards = collect($method->invoke($widget))->keyBy(fn ($card) => $card->getLabel());

    expect($cards['All']->getValue())->toBe('3')
        ->and($cards['Pending']->getValue())->toBe('1')
        ->and($cards['Approved']->getValue())->toBe('1')
        ->and($cards['Rejected']->getValue())->toBe('1')
        ->and($cards['Income']->getValue())->toBe('UGX 1,000')
        ->and($cards['Expenses']->getValue())->toBe('UGX 750');
});

it('renders responsive transaction cards linked to each existing filter tab', function () {
    $response = $this->get(TransactionResource::getUrl('index'));

    $response
        ->assertSuccessful()
        ->assertSee('fi-wi-stats-overview-stat', false)
        ->assertSee('fi-wi-stats-overview-stat-content', false)
        ->assertSee('rounded-sm', false)
        ->assertSee('>arrow_forward<', false)
        ->assertDontSee('class="fi-tabs', false);

    foreach (['all', 'pending', 'approved', 'rejected', 'income', 'expenses'] as $tab) {
        $response->assertSee("tab={$tab}", false);
    }

    foreach (['receipt_long', 'pending', 'check_circle', 'cancel', 'arrow_downward', 'arrow_upward'] as $icon) {
        $response->assertSee(">{$icon}<", false);
    }

    $widget = app(TransactionStatusCards::class);
    $columns = new ReflectionMethod($widget, 'getColumns');

    expect($columns->invoke($widget))->toBe(['default' => 1, 'md' => 2, 'lg' => 3]);
});

it('uses the meeting analytics card structure on transactions', function () {
    $transactions = $this->get(TransactionResource::getUrl('index'));
    $analytics = $this->get(MeetingAnalytics::getUrl());

    foreach (['fi-wi-stats-overview', 'fi-wi-stats-overview-stat', 'fi-wi-stats-overview-stat-label-ctn', 'fi-wi-stats-overview-stat-value', 'material-symbols-outlined'] as $class) {
        $transactions->assertSee($class, false);
        $analytics->assertSee($class, false);
    }
});

it('preserves transaction tab filtering through the analytics cards', function () {
    Transaction::factory()->create([
        'created_by' => $this->admin->id,
        'description' => 'Pending income card transaction',
        'type' => 'income',
        'status' => 'pending',
    ]);
    Transaction::factory()->create([
        'created_by' => $this->admin->id,
        'description' => 'Approved expense card transaction',
        'type' => 'expense',
        'status' => 'approved',
    ]);

    $this->get(TransactionResource::getUrl('index', ['tab' => 'pending']))
        ->assertSuccessful()
        ->assertSee('Pending income card transaction')
        ->assertDontSee('Approved expense card transaction');

    $this->get(TransactionResource::getUrl('index', ['tab' => 'expenses']))
        ->assertSuccessful()
        ->assertSee('Approved expense card transaction')
        ->assertDontSee('Pending income card transaction');
});
