<?php

use App\Models\Meeting;
use App\Models\TeachingMaterial;
use App\Models\Training;
use App\Models\User;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('renders the instructor dashboard for authenticated user', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)
        ->get(route('instructor.dashboard'))
        ->assertOk();
});

it('redirects guests from instructor dashboard', function () {
    $this->get(route('instructor.dashboard'))
        ->assertRedirect('/auth/login');
});

it('shows stats on instructor dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $training = Training::factory()->create(['instructor_id' => $user->id]);

    actingAs($user)
        ->get(route('instructor.dashboard'))
        ->assertOk()
        ->assertSee('Teaching Dashboard')
        ->assertSee('Trainings');
});

it('renders the instructor trainings page', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)
        ->get(route('instructor.trainings'))
        ->assertOk()
        ->assertSee('My Trainings');
});

it('only shows trainings for the current instructor', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $myTraining = Training::factory()->create(['instructor_id' => $user->id, 'title' => 'My Training']);
    $otherTraining = Training::factory()->create(['title' => 'Other Training']);

    actingAs($user)
        ->get(route('instructor.trainings'))
        ->assertSee('My Training')
        ->assertDontSee('Other Training');
});

it('renders the instructor sessions page', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)
        ->get(route('instructor.sessions'))
        ->assertOk()
        ->assertSee('Teaching Sessions');
});

it('only shows sessions created by the current instructor', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $mySession = Meeting::factory()->create([
        'type' => 'teaching_session',
        'created_by' => $user->id,
        'title' => 'My Session',
    ]);
    $otherUser = User::factory()->create();

    $otherSession = Meeting::factory()->create([
        'type' => 'teaching_session',
        'created_by' => $otherUser->id,
        'title' => 'Other Session',
    ]);

    actingAs($user);

    $sessions = \App\Models\Meeting::teachingSessions()
        ->where('created_by', $user->id)
        ->pluck('title');

    expect($sessions)->toHaveCount(1);
    expect($sessions)->toContain('My Session');
    expect($sessions)->not->toContain('Other Session');
});

it('renders the instructor materials page', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user)
        ->get(route('instructor.materials'))
        ->assertOk()
        ->assertSee('Course Materials');
});

it('creates a teaching material via livewire', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->call('openCreateForm')
        ->set('formTitle', 'Test Material')
        ->set('formType', 'document')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teaching_materials', [
        'title' => 'Test Material',
        'uploaded_by' => $user->id,
    ]);
});

it('validates required fields when creating material', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->call('openCreateForm')
        ->set('formTitle', '')
        ->call('save')
        ->assertHasErrors(['formTitle']);
});

it('deletes a teaching material via livewire', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $material = TeachingMaterial::create([
        'title' => 'To Delete',
        'type' => 'document',
        'uploaded_by' => $user->id,
    ]);

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->call('delete', $material->id);

    $this->assertDatabaseMissing('teaching_materials', ['id' => $material->id]);
});

it('searches materials by title', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    TeachingMaterial::create(['title' => 'Python Basics', 'type' => 'document', 'uploaded_by' => $user->id]);
    TeachingMaterial::create(['title' => 'Java Guide', 'type' => 'document', 'uploaded_by' => $user->id]);

    actingAs($user);

    Livewire::test(\App\Livewire\InstructorMaterials::class)
        ->set('search', 'Python')
        ->assertSee('Python Basics')
        ->assertDontSee('Java Guide');
});
