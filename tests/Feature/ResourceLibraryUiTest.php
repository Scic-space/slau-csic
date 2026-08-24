<?php

use App\Livewire\ExamCertificates;
use App\Livewire\ExamListing;
use App\Livewire\MyGrades;
use App\Livewire\ResourceLibrary;
use App\Models\ClubResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters resource results live while typing', function () {
    $user = User::factory()->create(['approved_at' => now()]);
    ClubResource::factory()->create(['title' => 'Unique Cryptography Guide']);
    ClubResource::factory()->create(['title' => 'Network Defense Handbook']);

    Livewire::actingAs($user)
        ->test(ResourceLibrary::class)
        ->assertSee('Unique Cryptography Guide')
        ->assertSee('Network Defense Handbook')
        ->set('search', 'Cryptography')
        ->assertSee('Unique Cryptography Guide')
        ->assertDontSee('Network Defense Handbook');
});

it('renders resource controls in the requested order with live bindings', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    Livewire::actingAs($user)
        ->test(ResourceLibrary::class)
        ->assertSeeInOrder(['Search resources', 'All Categories', 'All Difficulties', 'All Statuses'])
        ->assertSeeHtml('wire:model.live.debounce.250ms="search"')
        ->assertSeeHtml('wire:model.live="category"')
        ->assertSeeHtml('wire:model.live="difficulty"')
        ->assertSeeHtml('wire:model.live="status"')
        ->assertSeeInOrder(['search', 'category', 'speed', 'check_circle'])
        ->assertSeeHtml('xl:grid-cols-[18rem_minmax(12rem,1fr)_minmax(12rem,1fr)_minmax(12rem,1fr)]');
});

it('filters resources by category difficulty and status', function () {
    $user = User::factory()->create(['approved_at' => now()]);
    ClubResource::factory()->create([
        'title' => 'Beginner Active Class',
        'category' => 'class',
        'difficulty' => 'Beginner',
        'status' => 'active',
    ]);
    ClubResource::factory()->create([
        'title' => 'Advanced Scheduled Lab',
        'category' => 'ctf',
        'difficulty' => 'Advanced',
        'status' => 'scheduled',
    ]);

    Livewire::actingAs($user)
        ->test(ResourceLibrary::class)
        ->set('category', 'class')
        ->assertSee('Beginner Active Class')
        ->assertDontSee('Advanced Scheduled Lab')
        ->set('category', '')
        ->set('difficulty', 'Advanced')
        ->assertSee('Advanced Scheduled Lab')
        ->assertDontSee('Beginner Active Class')
        ->set('difficulty', '')
        ->set('status', 'active')
        ->assertSee('Beginner Active Class')
        ->assertDontSee('Advanced Scheduled Lab');
});

it('renders the learning pages with material icons', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    Livewire::actingAs($user)->test(ExamListing::class)->assertSeeHtml('material-symbols-outlined');
    Livewire::actingAs($user)->test(MyGrades::class)->assertSeeHtml('material-symbols-outlined');
    Livewire::actingAs($user)->test(ExamCertificates::class)->assertSeeHtml('material-symbols-outlined');

    $this->actingAs($user)
        ->get(route('portal.classes'))
        ->assertSuccessful()
        ->assertSee('material-symbols-outlined', false);
});
