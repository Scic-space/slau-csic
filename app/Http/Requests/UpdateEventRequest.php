<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:workshop,competition,ctf,bootcamp,awareness_campaign,talk,social,hackathon'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'registration_required' => ['boolean'],
            'waitlist_enabled' => ['boolean'],
            'is_public' => ['boolean'],
            'registration_deadline' => ['nullable', 'date', 'before_or_equal:start_date'],
            'requirements' => ['nullable', 'string'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'external_link' => ['nullable', 'url', 'max:2048'],
            'recurrence_enabled' => ['boolean'],
            'recurrence_pattern' => ['required_if:recurrence_enabled,true', 'string', 'in:weekly,biweekly,monthly'],
            'recurrence_ends_at' => ['nullable', 'date', 'after_or_equal:start_date'],
            'selectedCategories' => ['nullable', 'array'],
            'selectedCategories.*' => ['integer', 'exists:event_categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The event type must be one of: workshop, competition, CTF, bootcamp, awareness campaign, talk, social, or hackathon.',
            'recurrence_pattern.required_if' => 'A recurrence pattern is required when recurrence is enabled.',
            'recurrence_pattern.in' => 'Recurrence pattern must be weekly, biweekly, or monthly.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'registration_deadline.before_or_equal' => 'Registration deadline must be before or on the start date.',
        ];
    }
}
