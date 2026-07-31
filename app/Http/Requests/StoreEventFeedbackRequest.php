<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content_quality' => ['nullable', 'integer', 'min:1', 'max:5'],
            'instructor_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'pace_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'feedback_text' => ['nullable', 'string', 'max:5000'],
            'suggestions' => ['nullable', 'string', 'max:5000'],
            'is_anonymous' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Please provide an overall rating.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating must be at most 5.',
        ];
    }
}
