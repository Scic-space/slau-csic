<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_attendance') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', 'string', 'in:present,absent,excused'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Attendance status must be present, absent, or excused.',
        ];
    }
}
