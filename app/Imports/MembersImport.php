<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MembersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): User
    {
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => bcrypt(Str::password(12)),
            'student_id' => $row['student_id'] ?? null,
            'phone' => $row['phone'] ?? null,
            'program' => $row['program'] ?? null,
            'faculty' => $row['faculty'] ?? null,
            'year_of_study' => $row['year_of_study'] ?? null,
            'membership_type' => $row['membership_type'] ?? 'active',
            'membership_status' => $row['membership_status'] ?? 'active',
            'joined_at' => $row['joined_at'] ?? now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'program' => ['nullable', 'string', 'max:100'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'year_of_study' => ['nullable', 'integer', 'min:1', 'max:6'],
            'membership_type' => ['nullable', 'in:active,associate,alumni'],
            'membership_status' => ['nullable', 'in:active,pending,inactive,suspended'],
        ];
    }
}
