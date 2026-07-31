<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitWriteupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:100', 'max:50000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.min' => 'Writeup must be at least 100 characters.',
        ];
    }
}
