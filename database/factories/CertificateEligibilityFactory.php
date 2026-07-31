<?php

namespace Database\Factories;

use App\Models\CertificateEligibility;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateEligibilityFactory extends Factory
{
    protected $model = CertificateEligibility::class;

    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'user_id' => User::factory(),
            'exam_id' => Exam::factory(),
            'eligible' => true,
            'notes' => null,
            'verification_code' => \Illuminate\Support\Str::uuid()->toString(),
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'eligible' => false,
            'notes' => '['.now()->toDateTimeString().'] Revoked',
        ]);
    }
}
