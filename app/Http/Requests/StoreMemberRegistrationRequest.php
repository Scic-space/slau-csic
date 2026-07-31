<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
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
            'student_id' => ['required', 'string', 'max:100', 'unique:users,student_id'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:users,registration_number', 'regex:/^[A-Za-z]+\/\d{2}[DW]\/[A-Za-z]\/[A-Za-z]\d+$/'],
            'phone' => ['required', 'string', 'max:20'],
            'program' => ['required', 'string', 'max:100'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'year_of_study' => ['required', 'integer', 'min:1', 'max:6'],
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
            'registration_number.regex' => 'Format must be like BACS/24D/U/A0160 (Course/Year+Mode/Country/Intake+Number).',
            'registration_number.unique' => 'This registration number is already registered.',
            'student_id.required' => 'A student identification number is required for club records.',
            'profile_photo.required' => 'Please upload a clear profile photo for your member account.',
            'profile_photo.image' => 'The uploaded file must be an image.',
            'profile_photo.max' => 'Your profile photo should be 5MB or smaller.',
            'terms.accepted' => 'You need to accept the club platform terms before continuing.',
        ];
    }
}
