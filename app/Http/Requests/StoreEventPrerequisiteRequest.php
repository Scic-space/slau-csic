<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventPrerequisiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit_events') ?? false;
    }

    public function rules(): array
    {
        return [
            'prerequisite_event_id' => ['nullable', 'integer', 'exists:events,id', 'different:event'],
            'required_badge_id' => ['nullable', 'integer', 'exists:badges,id'],
            'required_skill_level' => ['nullable', 'string', 'in:beginner,intermediate,advanced'],
        ];
    }

    public function messages(): array
    {
        return [
            'prerequisite_event_id.different' => 'An event cannot be a prerequisite of itself.',
            'required_skill_level.in' => 'Skill level must be beginner, intermediate, or advanced.',
        ];
    }
}
