<?php

namespace App\Http\Requests;

use App\Models\CtfChallenge;
use Illuminate\Foundation\Http\FormRequest;

class SubmitFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flag' => ['required', 'string', 'max:255', 'regex:'.CtfChallenge::FLAG_PATTERN],
        ];
    }

    public function messages(): array
    {
        return [
            'flag.regex' => 'Flag must match the format SLAU_CSIC{...}',
        ];
    }
}
