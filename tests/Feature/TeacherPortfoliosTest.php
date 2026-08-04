<?php

use App\Livewire\TeacherPortfolios;
use App\Models\PortfolioExperience;
use App\Models\PortfolioSkill;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('pre-selects the member in the experience form', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    Livewire::actingAs($member)
        ->test(TeacherPortfolios::class)
        ->call('openExpForm')
        ->assertSet('expStudentId', (string) $member->id);
});

it('pre-selects the member in the skill form', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    Livewire::actingAs($member)
        ->test(TeacherPortfolios::class)
        ->call('openSkillForm')
        ->assertSet('skillStudentId', (string) $member->id);
});

it('pre-selects the member in the certification form', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    Livewire::actingAs($member)
        ->test(TeacherPortfolios::class)
        ->call('openCertForm')
        ->assertSet('certStudentId', (string) $member->id);
});

it('pre-selects the member in the portfolio form', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    Livewire::actingAs($member)
        ->test(TeacherPortfolios::class)
        ->call('openCreateForm')
        ->assertSet('formStudentId', (string) $member->id);
});

it('saves an experience for the member without picking a student', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    Livewire::actingAs($member)
        ->test(TeacherPortfolios::class)
        ->call('openExpForm')
        ->set('expTitle', 'Internship')
        ->set('expOrganization', 'TechCorp')
        ->set('expStartDate', '2024-01-01')
        ->set('expType', 'experience')
        ->call('saveExp');

    expect(PortfolioExperience::where('user_id', $member->id)->count())->toBe(1);
    $exp = PortfolioExperience::where('user_id', $member->id)->first();
    expect($exp->title)->toBe('Internship');
    expect($exp->organization)->toBe('TechCorp');
});

it('saves a skill for the member without picking a student', function () {
    $member = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $member->assignRole('member');

    Livewire::actingAs($member)
        ->test(TeacherPortfolios::class)
        ->call('openSkillForm')
        ->set('skillName', 'Laravel')
        ->set('skillProficiency', 4)
        ->call('saveSkill');

    expect(PortfolioSkill::where('user_id', $member->id)->count())->toBe(1);
});

it('leaves the student empty for managers', function () {
    $manager = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $manager->assignRole('president');

    Livewire::actingAs($manager)
        ->test(TeacherPortfolios::class)
        ->call('openExpForm')
        ->assertSet('expStudentId', '');
});

it('lets a manager save an experience for a chosen student', function () {
    $manager = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $manager->assignRole('president');
    $student = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);

    Livewire::actingAs($manager)
        ->test(TeacherPortfolios::class)
        ->call('openExpForm')
        ->set('expStudentId', (string) $student->id)
        ->set('expTitle', 'Research Assistant')
        ->set('expOrganization', 'University')
        ->set('expStartDate', '2024-05-01')
        ->set('expType', 'experience')
        ->call('saveExp');

    expect(PortfolioExperience::where('user_id', $student->id)->count())->toBe(1);
});
