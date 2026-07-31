<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invite_code' => ['nullable', 'string', 'max:255'],
            'team_id' => ['nullable', 'integer', 'exists:ctf_teams,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('invite_code') && ! $this->filled('team_id')) {
                $validator->errors()->add('invite_code', 'Provide an invite code or select a team.');
            }
        });
    }
}
