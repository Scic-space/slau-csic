<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_attendance') ?? false;
    }

    public function rules(): array
    {
        return [
            'attendance_data' => ['required', 'array', 'min:1'],
            'attendance_data.*.member_id' => ['required', 'integer', 'exists:users,id'],
            'attendance_data.*.status' => ['required', 'string', 'in:present,absent,excused'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_data.*.status.in' => 'Each attendance status must be present, absent, or excused.',
        ];
    }
}
