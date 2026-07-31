<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'type' => 'required|string|in:ctf,hackathon,coding,cybersecurity',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_team_based' => 'boolean',
            'max_team_size' => 'nullable|integer|min:2|max:50',
            'participation_status' => 'nullable|string|in:registered,participating,completed',
            'club_ranking' => 'nullable|integer|min:1',
            'achievements' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Competition type must be one of: ctf, hackathon, coding, cybersecurity.',
            'participation_status.in' => 'Status must be one of: registered, participating, completed.',
        ];
    }
}
