<?php

use App\Filament\Pages\CtfDashboard;
use App\Filament\Widgets\BudgetCategoryStatusCards;
use App\Filament\Widgets\CtfDashboardStatsWidget;
use App\Filament\Widgets\ExamStatusCards;
use App\Filament\Widgets\MeetingStatusCards;
use App\Models\BudgetCategory;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\CtfTeam;
use App\Models\CtfWriteup;
use App\Models\Exam;
use App\Models\Meeting;
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

it('calculates meeting card counts from the same status rules as the tabs', function () {
    Meeting::factory()->upcoming()->create();
    Meeting::factory()->ongoing()->create();
    Meeting::factory()->past()->create();
    Meeting::factory()->cancelled()->create();

    $widget = app(MeetingStatusCards::class);
    $method = new ReflectionMethod($widget, 'getStats');
    $cards = collect($method->invoke($widget))->keyBy(fn ($card) => $card->getLabel());

    expect((int) $cards['All']->getValue())->toBe(4)
        ->and((int) $cards['Upcoming']->getValue())->toBe(1)
        ->and((int) $cards['Ongoing']->getValue())->toBe(1)
        ->and((int) $cards['Past']->getValue())->toBe(2)
        ->and((int) $cards['Cancelled']->getValue())->toBe(1);
});

it('renders meeting status cards with the analytics component structure and active state', function () {
    $response = $this->get('/admin/meetings?tab=upcoming');

    $response->assertSuccessful()
        ->assertSee('href="http://localhost/admin/meetings?tab=all"', false)
        ->assertSee('href="http://localhost/admin/meetings?tab=upcoming"', false)
        ->assertSee('href="http://localhost/admin/meetings?tab=ongoing"', false)
        ->assertSee('href="http://localhost/admin/meetings?tab=past"', false)
        ->assertSee('href="http://localhost/admin/meetings?tab=cancelled"', false)
        ->assertSee('fi-wi-stats-overview-stat', false)
        ->assertSee('fi-wi-stats-overview-stat-label', false)
        ->assertSee('fi-wi-stats-overview-stat-value', false)
        ->assertSee('rounded-sm', false)
        ->assertSee('hover:-translate-y-0.5', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('>arrow_forward<', false);

    foreach (['groups', 'event_upcoming', 'sensors', 'history', 'event_busy'] as $icon) {
        $response->assertSee(">{$icon}<", false);
    }

    $widget = app(MeetingStatusCards::class);
    $columns = new ReflectionMethod($widget, 'getColumns');

    expect($columns->invoke($widget))->toBe([
        'default' => 1,
        'md' => 2,
        'lg' => 3,
    ]);
});

it('uses the same filament stats card structure and icon treatment as meeting analytics', function () {
    $meetings = $this->get('/admin/meetings');
    $analytics = $this->get('/admin/meeting-analytics');

    $meetings->assertSuccessful();
    $analytics->assertSuccessful();

    foreach (['fi-wi-stats-overview', 'fi-wi-stats-overview-stat', 'fi-wi-stats-overview-stat-content', 'fi-wi-stats-overview-stat-label-ctn', 'fi-wi-stats-overview-stat-value', 'material-symbols-outlined'] as $class) {
        $meetings->assertSee($class, false);
        $analytics->assertSee($class, false);
    }
});

it('calculates budget category and exam card counts from live data', function () {
    BudgetCategory::factory()->income()->create(['is_active' => true]);
    BudgetCategory::factory()->expense()->create(['is_active' => false]);
    Exam::factory()->published()->create();
    Exam::factory()->create();
    Exam::factory()->archived()->create();

    $budgetWidget = app(BudgetCategoryStatusCards::class);
    $budgetStats = new ReflectionMethod($budgetWidget, 'getStats');
    $budgetCards = collect($budgetStats->invoke($budgetWidget))->keyBy(fn ($card) => $card->getLabel());
    $examWidget = app(ExamStatusCards::class);
    $examStats = new ReflectionMethod($examWidget, 'getStats');
    $examCards = collect($examStats->invoke($examWidget))->keyBy(fn ($card) => $card->getLabel());

    expect((int) $budgetCards['All']->getValue())->toBe(2)
        ->and((int) $budgetCards['Income']->getValue())->toBe(1)
        ->and((int) $budgetCards['Expense']->getValue())->toBe(1)
        ->and((int) $budgetCards['Active']->getValue())->toBe(1)
        ->and((int) $examCards['All']->getValue())->toBe(3)
        ->and((int) $examCards['Published']->getValue())->toBe(1)
        ->and((int) $examCards['Draft']->getValue())->toBe(1)
        ->and((int) $examCards['Archived']->getValue())->toBe(1);
});

it('renders exam cards with analytics styling, navigation, icons, and responsive columns', function () {
    $response = $this->get('/admin/exams?tab=published');

    $response->assertSuccessful()
        ->assertSee('fi-wi-stats-overview-stat', false)
        ->assertSee('fi-wi-stats-overview-stat-content', false)
        ->assertSee('rounded-sm', false)
        ->assertSee('hover:-translate-y-0.5', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('>arrow_forward<', false);

    foreach (['all', 'published', 'draft', 'archived'] as $tab) {
        $response->assertSee("exams?tab={$tab}", false);
    }

    foreach (['quiz', 'publish', 'edit_note', 'inventory_2'] as $icon) {
        $response->assertSee(">{$icon}<", false);
    }

    $widget = app(ExamStatusCards::class);
    $columns = new ReflectionMethod($widget, 'getColumns');

    expect($columns->invoke($widget))->toBe(['default' => 1, 'md' => 2, 'lg' => 3]);
});

it('uses the meeting analytics card structure on exams', function () {
    $exams = $this->get('/admin/exams');
    $analytics = $this->get('/admin/meeting-analytics');

    foreach (['fi-wi-stats-overview', 'fi-wi-stats-overview-stat', 'fi-wi-stats-overview-stat-label-ctn', 'fi-wi-stats-overview-stat-value', 'material-symbols-outlined'] as $class) {
        $exams->assertSee($class, false);
        $analytics->assertSee($class, false);
    }
});

it('renders budget category cards with analytics styling, navigation, icons, and responsive columns', function () {
    $response = $this->get('/admin/budget-categories?tab=income');

    $response->assertSuccessful()
        ->assertSee('fi-wi-stats-overview-stat', false)
        ->assertSee('fi-wi-stats-overview-stat-content', false)
        ->assertSee('rounded-sm', false)
        ->assertSee('hover:-translate-y-0.5', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('>arrow_forward<', false);

    foreach (['all', 'income', 'expense', 'active'] as $tab) {
        $response->assertSee("budget-categories?tab={$tab}", false);
    }

    foreach (['category', 'trending_up', 'trending_down', 'check_circle'] as $icon) {
        $response->assertSee(">{$icon}<", false);
    }

    $widget = app(BudgetCategoryStatusCards::class);
    $columns = new ReflectionMethod($widget, 'getColumns');

    expect($columns->invoke($widget))->toBe(['default' => 1, 'md' => 2, 'lg' => 3]);
});

it('uses the meeting analytics card structure on budget categories', function () {
    $budgetCategories = $this->get('/admin/budget-categories');
    $analytics = $this->get('/admin/meeting-analytics');

    foreach (['fi-wi-stats-overview', 'fi-wi-stats-overview-stat', 'fi-wi-stats-overview-stat-label-ctn', 'fi-wi-stats-overview-stat-value', 'material-symbols-outlined'] as $class) {
        $budgetCategories->assertSee($class, false);
        $analytics->assertSee($class, false);
    }
});

it('uses card tab links to filter the existing records tables', function () {
    Meeting::factory()->upcoming()->create(['title' => 'Future planning session']);
    Meeting::factory()->cancelled()->create(['title' => 'Cancelled planning session']);
    BudgetCategory::factory()->income()->create(['name' => 'Card income category']);
    BudgetCategory::factory()->expense()->create(['name' => 'Card expense category']);
    Exam::factory()->published()->create(['title' => 'Published card exam']);
    Exam::factory()->create(['title' => 'Draft card exam']);

    $this->get('/admin/meetings?tab=upcoming')->assertSuccessful()
        ->assertSee('Future planning session')->assertDontSee('Cancelled planning session');
    $this->get('/admin/budget-categories?tab=income')->assertSuccessful()
        ->assertSee('Card income category')->assertDontSee('Card expense category');
    $this->get('/admin/exams?tab=published')->assertSuccessful()
        ->assertSee('Published card exam')->assertDontSee('Draft card exam');
});

it('keeps the new exam action above the cards with its route and material icon', function () {
    $response = $this->get('/admin/exams');
    $content = $response->getContent();

    $response->assertSuccessful()
        ->assertSee('New Exam')
        ->assertSee('/admin/exams/create', false)
        ->assertSee('>add_circle<', false);

    expect(strpos($content, 'New Exam'))
        ->toBeLessThan(strpos($content, 'href="http://localhost/admin/exams?tab=all"'));
});

it('calculates and renders all ctf dashboard statistics with relevant links', function () {
    CtfCompetition::factory()->create();
    CtfCompetition::factory()->upcoming()->create();
    CtfSubmission::factory()->count(2)->create();
    CtfSubmission::factory()->incorrect()->create();
    CtfTeam::factory()->create();
    CtfWriteup::factory()->create();
    CtfWriteup::factory()->approved()->create();

    $stats = app(CtfDashboard::class)->getStats();

    expect($stats['all_competitions'])->toBeGreaterThanOrEqual(2)
        ->and($stats['active_competitions'])->toBeGreaterThanOrEqual(1)
        ->and($stats['total_solves'])->toBe(2)
        ->and($stats['total_participants'])->toBe(2)
        ->and($stats['total_teams'])->toBe(1)
        ->and($stats['pending_writeups'])->toBe(1);

    $response = $this->get('/admin/ctf-dashboard');

    $response->assertSuccessful()
        ->assertSee('fi-wi-stats-overview-stat', false)
        ->assertSee('fi-wi-stats-overview-stat-content', false)
        ->assertSee('All Competitions')->assertSee('Active Competitions')
        ->assertSee('Total Solves')->assertSee('Participants')->assertSee('Teams')->assertSee('Pending Writeups')
        ->assertSee('rounded-sm', false)
        ->assertSee('hover:-translate-y-0.5', false)
        ->assertSee('>arrow_forward<', false);

    foreach (['emoji_events', 'flag', 'people', 'groups', 'pending_actions', 'military_tech'] as $icon) {
        $response->assertSee(">{$icon}<", false);
    }

    $widget = app(CtfDashboardStatsWidget::class);
    $columns = new ReflectionMethod($widget, 'getColumns');

    expect($columns->invoke($widget))->toBe(['default' => 1, 'md' => 2, 'lg' => 3]);
});

it('uses the meeting analytics card structure on the ctf dashboard', function () {
    $ctfDashboard = $this->get('/admin/ctf-dashboard');
    $analytics = $this->get('/admin/meeting-analytics');

    foreach (['fi-wi-stats-overview', 'fi-wi-stats-overview-stat', 'fi-wi-stats-overview-stat-label-ctn', 'fi-wi-stats-overview-stat-value', 'material-symbols-outlined'] as $class) {
        $ctfDashboard->assertSee($class, false);
        $analytics->assertSee($class, false);
    }
});
