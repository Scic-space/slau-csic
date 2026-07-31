<?php

use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingModule;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists published trainings', function () {
    Training::factory()->published()->count(3)->create();
    Training::factory()->draft()->count(2)->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingListing::class)
        ->assertStatus(200);
});

it('shows a training detail page', function () {
    $training = Training::factory()->published()->create();
    TrainingModule::factory()->count(3)->create(['training_id' => $training->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingShow::class, ['training' => $training])
        ->assertStatus(200);
});

it('allows a user to enroll in a training', function () {
    $training = Training::factory()->published()->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingShow::class, ['training' => $training])
        ->assertViewHas('enrolled', false)
        ->call('enroll')
        ->assertViewHas('enrolled', true);

    $this->assertDatabaseHas('training_enrollments', [
        'training_id' => $training->id,
        'user_id' => $this->user->id,
        'status' => 'enrolled',
    ]);
});

it('allows marking a module as complete when enrolled', function () {
    $training = Training::factory()->published()->create();
    $module = TrainingModule::factory()->create(['training_id' => $training->id]);

    TrainingEnrollment::create([
        'training_id' => $training->id,
        'user_id' => $this->user->id,
        'status' => 'enrolled',
        'enrolled_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingShow::class, ['training' => $training])
        ->call('completeModule', $module->id);

    $this->assertDatabaseHas('module_progress', [
        'training_module_id' => $module->id,
        'user_id' => $this->user->id,
        'completed' => true,
    ]);
});

it('auto-completes training when all modules are done', function () {
    $training = Training::factory()->published()->create();
    $module1 = TrainingModule::factory()->create(['training_id' => $training->id]);
    $module2 = TrainingModule::factory()->create(['training_id' => $training->id]);

    $enrollment = TrainingEnrollment::create([
        'training_id' => $training->id,
        'user_id' => $this->user->id,
        'status' => 'enrolled',
        'enrolled_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingShow::class, ['training' => $training])
        ->call('completeModule', $module1->id)
        ->call('completeModule', $module2->id);

    expect($enrollment->fresh()->status)->toBe('completed');
    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('does not allow completing modules without enrollment', function () {
    $training = Training::factory()->published()->create();
    $module = TrainingModule::factory()->create(['training_id' => $training->id]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingShow::class, ['training' => $training])
        ->call('completeModule', $module->id);

    $this->assertDatabaseMissing('module_progress', [
        'training_module_id' => $module->id,
        'user_id' => $this->user->id,
    ]);
});

it('filters trainings by category', function () {
    Training::factory()->published()->create(['category' => 'ethical_hacking']);
    Training::factory()->published()->create(['category' => 'network_security']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingListing::class)
        ->set('category', 'ethical_hacking')
        ->assertViewHas('trainings', fn ($trainings) => $trainings->every('category', 'ethical_hacking'));
});

it('filters trainings by difficulty', function () {
    Training::factory()->published()->create(['difficulty' => 'beginner']);
    Training::factory()->published()->create(['difficulty' => 'advanced']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingListing::class)
        ->set('difficulty', 'advanced')
        ->assertViewHas('trainings', fn ($trainings) => $trainings->every('difficulty', 'advanced'));
});

it('searches trainings by title', function () {
    Training::factory()->published()->create(['title' => 'Network Security Basics']);
    Training::factory()->published()->create(['title' => 'Web Application Security']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\TrainingListing::class)
        ->set('search', 'Network')
        ->assertViewHas('trainings', fn ($trainings) => $trainings->contains('title', 'Network Security Basics'));
});
