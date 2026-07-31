<?php

use App\Models\Election;
use App\Models\Event;
use App\Models\Exam;
use App\Models\Meeting;
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

// Dashboard
it('loads the dashboard page', function () {
    $this->get('/admin')->assertSuccessful();
});

// Events
it('loads the events list page', function () {
    $this->get('/admin/manage-events')->assertSuccessful();
});

it('loads the event calendar page', function () {
    $this->get('/admin/event-calendar')->assertSuccessful();
});

it('loads the event analytics page', function () {
    $this->get('/admin/event-analytics')->assertSuccessful();
});

it('loads the event categories page', function () {
    $this->get('/admin/manage-event-categories')->assertSuccessful();
});

it('loads the event registrations page', function () {
    $this->get('/admin/registrations')->assertSuccessful();
});

it('loads the event attendance page', function () {
    $this->get('/admin/attendance')->assertSuccessful();
});

it('loads the event create page', function () {
    $this->get('/admin/manage-events/create')->assertSuccessful();
});

it('loads the event edit page', function () {
    $event = Event::factory()->create();
    $this->get("/admin/manage-events/{$event->id}/edit")->assertSuccessful();
});

// Users
it('loads the users list page', function () {
    $this->get('/admin/users')->assertSuccessful();
});

it('loads the user create page', function () {
    $this->get('/admin/users/create')->assertSuccessful();
});

// Meetings
it('loads the meetings list page', function () {
    $this->get('/admin/meetings')->assertSuccessful();
});

it('loads the meeting attendance page', function () {
    $this->get('/admin/meeting-attendance')->assertSuccessful();
});

it('loads the meeting analytics page', function () {
    $this->get('/admin/meeting-analytics')->assertSuccessful();
});

it('loads the meeting create page', function () {
    $this->get('/admin/meetings/create')->assertSuccessful();
});

it('loads the meeting edit page', function () {
    $meeting = Meeting::factory()->create(['created_by' => $this->admin->id]);
    $this->get("/admin/meetings/{$meeting->id}/edit")->assertSuccessful();
});

// Finance
it('loads the transactions list page', function () {
    $this->get('/admin/transactions')->assertSuccessful();
});

it('loads the transaction create page', function () {
    $this->get('/admin/transactions/create')->assertSuccessful();
});

it('loads the budget categories page', function () {
    $this->get('/admin/budget-categories')->assertSuccessful();
});

it('loads the budget category create page', function () {
    $this->get('/admin/budget-categories/create')->assertSuccessful();
});

it('loads the financial report page', function () {
    $this->get('/admin/financial-report')->assertSuccessful();
});

// Fines
it('loads the fines page', function () {
    $this->get('/admin/manage-fines')->assertSuccessful();
});

it('loads the fine types page', function () {
    $this->get('/admin/manage-fine-types')->assertSuccessful();
});

it('loads the fine appeals page', function () {
    $this->get('/admin/fine-appeals')->assertSuccessful();
});

// Elections
it('loads the elections page', function () {
    $this->get('/admin/manage-elections')->assertSuccessful();
});

it('loads the election create page', function () {
    $this->get('/admin/manage-elections/create')->assertSuccessful();
});

it('loads the election edit page', function () {
    $election = Election::factory()->create();
    $this->get("/admin/manage-elections/{$election->id}/edit")->assertSuccessful();
});

// Exams
it('loads the exams page', function () {
    $this->get('/admin/exams')->assertSuccessful();
});

it('loads the exam create page', function () {
    $this->get('/admin/exams/create')->assertSuccessful();
});

it('loads the exam edit page', function () {
    $exam = Exam::factory()->create();
    $this->get("/admin/exams/{$exam->id}/edit")->assertSuccessful();
});

// Trainings
it('loads the trainings page', function () {
    $this->get('/admin/trainings')->assertSuccessful();
});

// Announcements
it('loads the announcements page', function () {
    $this->get('/admin/manage-announcements')->assertSuccessful();
});

// Badges
it('loads the badges page', function () {
    $this->get('/admin/manage-badges')->assertSuccessful();
});

// CTF
it('loads the ctf categories page', function () {
    $this->get('/admin/manage-ctf-categories')->assertSuccessful();
});

it('loads the ctf competitions page', function () {
    $this->get('/admin/manage-ctf-competitions')->assertSuccessful();
});

it('loads the ctf competition create page', function () {
    $this->get('/admin/manage-ctf-competitions/create')->assertSuccessful();
});

it('loads the ctf submissions page', function () {
    $this->get('/admin/manage-ctf-submissions')->assertSuccessful();
});

it('loads the ctf writeups page', function () {
    $this->get('/admin/manage-ctf-writeups')->assertSuccessful();
});

it('loads the ctf dashboard page', function () {
    $this->get('/admin/ctf-dashboard')->assertSuccessful();
});

// System
it('loads the roles page', function () {
    $this->get('/admin/roles')->assertSuccessful();
});

it('loads the role create page', function () {
    $this->get('/admin/roles/create')->assertSuccessful();
});

it('loads the role templates page', function () {
    $this->get('/admin/role-templates')->assertSuccessful();
});

it('loads the assignment wizard page', function () {
    $this->get('/admin/assignment-wizard')->assertSuccessful();
});

it('loads the system settings page', function () {
    $this->get('/admin/system/settings')->assertSuccessful();
});

it('loads the content pages page', function () {
    $this->get('/admin/system/content-pages')->assertSuccessful();
});

it('loads the content page create page', function () {
    $this->get('/admin/system/content-pages/create')->assertSuccessful();
});

it('loads the audit logs page', function () {
    $this->get('/admin/system/audit-logs')->assertSuccessful();
});

it('loads the system overview page', function () {
    $this->get('/admin/system-overview')->assertSuccessful();
});
