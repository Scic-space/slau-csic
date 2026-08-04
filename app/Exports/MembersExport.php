<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MembersExport implements FromQuery, WithHeadings, WithMapping
{
    protected ?array $ids = null;

    protected $query;

    public function forIds(array $ids): static
    {
        $this->ids = $ids;

        return $this;
    }

    public function setQuery($query): static
    {
        $this->query = $query;

        return $this;
    }

    public function query()
    {
        if ($this->query) {
            return $this->query;
        }

        return User::query()
            ->when($this->ids, fn ($q) => $q->whereIn('id', $this->ids))
            ->with(['membership']);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Registration Number',
            'Program',
            'Faculty',
            'Year of Study',
            'Membership Type',
            'Membership Status',
            'Joined At',
            'Score',
            'Rank',
            'Attendance Count',
            'Current Streak',
        ];
    }

    /** @param User $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->email,
            $row->registration_number ?? '',
            $row->program ?? '',
            $row->faculty ?? '',
            (string) ($row->year_of_study ?? ''),
            $row->membership_type ?? '',
            $row->membership_status ?? '',
            $row->joined_at?->format('Y-m-d') ?? '',
            (string) ($row->score ?? '0'),
            $row->rank ?? '',
            (string) ($row->attendance_count ?? '0'),
            (string) ($row->current_streak ?? '0'),
        ];
    }
}
