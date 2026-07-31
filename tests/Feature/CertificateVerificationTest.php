<?php

use App\Models\CertificateEligibility;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('returns certificate details for a valid verification code', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $exam = Exam::factory()->published()->create(['title' => 'Network Security']);
    $attempt = ExamAttempt::factory()->passed()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'total_score' => 85,
    ]);

    $eligibility = CertificateEligibility::create([
        'exam_attempt_id' => $attempt->id,
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'eligible' => true,
    ]);

    $response = $this->get(route('certificates.verify', $eligibility->verification_code));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('certificates/Verify')
        ->has('certificate')
        ->where('status', 'found')
        ->where('certificate.holder_name', 'Jane Doe')
        ->where('certificate.exam_title', 'Network Security')
        ->where('certificate.score', 85)
        ->where('certificate.is_valid', true)
    );
});

it('returns not_found for an invalid verification code', function () {
    $response = $this->get(route('certificates.verify', 'non-existent-code'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('certificates/Verify')
        ->where('status', 'not_found')
        ->where('certificate', null)
    );
});

it('returns revoked status for a revoked certificate', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->published()->create();
    $attempt = ExamAttempt::factory()->passed()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
    ]);

    $eligibility = CertificateEligibility::create([
        'exam_attempt_id' => $attempt->id,
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'eligible' => false,
        'notes' => '['.now()->toDateTimeString().'] Revoked',
    ]);

    $response = $this->get(route('certificates.verify', $eligibility->verification_code));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('certificates/Verify')
        ->where('status', 'found')
        ->where('certificate.is_valid', false)
    );
});

it('generates a unique verification_code on creation', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->published()->create();
    $attempt = ExamAttempt::factory()->passed()->create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
    ]);

    $eligibility = CertificateEligibility::create([
        'exam_attempt_id' => $attempt->id,
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'eligible' => true,
    ]);

    expect($eligibility->verification_code)->not->toBeNull();
    expect($eligibility->verification_code)->toHaveLength(36);
});

it('generates a certificate_id in the correct format', function () {
    $eligibility = CertificateEligibility::factory()->create(['id' => 42]);

    expect($eligibility->certificate_id)->toBe('CERT-000042');
});

it('provides a verification_url accessor', function () {
    $eligibility = CertificateEligibility::factory()->create();

    expect($eligibility->verification_url)->toContain($eligibility->verification_code);
});

it('can revoke a certificate via the service', function () {
    $eligibility = CertificateEligibility::factory()->create(['eligible' => true]);

    $result = app(\App\Services\CertificateService::class)->revokeEligibility($eligibility);

    expect($result)->toBeTrue();
    expect($eligibility->fresh()->eligible)->toBeFalse();
    expect($eligibility->fresh()->notes)->toContain('Revoked');
});

it('can verify a certificate via the service', function () {
    $eligibility = CertificateEligibility::factory()->create();

    $result = app(\App\Services\CertificateService::class)->verify($eligibility->verification_code);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($eligibility->id);
});

it('returns null when verifying with invalid code', function () {
    $result = app(\App\Services\CertificateService::class)->verify('invalid-code');

    expect($result)->toBeNull();
});
