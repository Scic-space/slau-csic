<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreMemberRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'registration_number' => ['required', 'string', 'max:50', 'unique:users,registration_number', 'regex:/^[A-Za-z]+\/\d{2}[DW]\/[A-Za-z]\/[A-Za-z]\d+[A-Za-z]?$/'],
            'phone' => ['required', 'string', 'max:20'],
            'program' => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $programs = collect(config('academics.faculties'))
                        ->firstWhere('name', $this->input('faculty'))['programs'] ?? [];

                    if (! in_array($value, $programs, true)) {
                        $fail('program.in');
                    }
                },
            ],
            'faculty' => ['required', 'string', 'max:100', Rule::in(config('academics.facultyNames'))],
            'year_of_study' => ['required', 'integer', 'min:1', 'max:6'],
            'intake' => ['required', 'string', 'in:january,may,august'],
            'intake_year' => ['required', 'integer', 'min:1990', 'max:'.now()->year],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', 'max:30'],
            'residence' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:120'],
            'bio' => ['required', 'string', 'min:10', 'max:1200'],
            'specialization_track' => ['nullable', 'string', 'max:120'],
            'notable_problems_solved' => ['nullable', 'string', 'max:1200'],
            'achievements_summary' => ['nullable', 'string', 'max:1200'],
            'competition_rank' => ['nullable', 'string', 'max:120'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'github_username' => ['nullable', 'string', 'max:50'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'discord_username' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['required', 'image', 'max:5120'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_number.required' => 'Your university registration number is required.',
            'registration_number.regex' => 'Format must be like BACS/26D/U/A0000 (Course/Year+Mode/Country/Intake+Number).',
            'registration_number.unique' => 'This registration number is already registered.',
            'profile_photo.required' => 'Please upload a clear profile photo for your member account.',
            'profile_photo.image' => 'The uploaded file must be an image.',
            'profile_photo.max' => 'Your profile photo should be 5MB or smaller.',
            'terms.accepted' => 'You need to accept the club platform terms before continuing.',
            'intake.required' => 'Please select your intake (January, May or August).',
            'intake.in' => 'Intake must be January, May or August.',
            'intake_year.required' => 'Please enter the year you were admitted (your intake year).',
            'intake_year.max' => 'Your intake year cannot be in the future.',
            'intake_year.min' => 'Your intake year must be a valid four-digit year.',
            'faculty.required' => 'Please select your faculty.',
            'faculty.in' => 'Please select a valid faculty from the list.',
            'program.required' => 'Please select your programme.',
            'program.in' => 'Please select a valid programme from the list.',
        ];
    }
}
