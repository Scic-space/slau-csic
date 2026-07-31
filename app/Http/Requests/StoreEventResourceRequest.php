<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:slide,code,document,video,link,other'],
            'file' => ['nullable', 'file', 'max:10240'],
            'url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the material.',
            'type.required' => 'Please select a material type.',
            'type.in' => 'Material type must be one of: slide, code, document, video, link, other.',
            'file.max' => 'File size must not exceed 10MB.',
        ];
    }
}
