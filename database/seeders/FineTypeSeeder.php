<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FineTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Missed Meeting',
                'default_amount' => 5000,
                'description' => 'Failure to attend a scheduled club meeting without prior notice.',
                'auto_apply_trigger' => 'missed_meetings',
                'is_active' => true,
            ],
            [
                'name' => 'Event No-Show',
                'default_amount' => 10000,
                'description' => 'Registered for an event but failed to attend without cancellation.',
                'auto_apply_trigger' => 'event_no_show',
                'is_active' => true,
            ],
            [
                'name' => 'Late Submission',
                'default_amount' => 3000,
                'description' => 'Project or assignment submitted after the deadline.',
                'auto_apply_trigger' => 'late_submission',
                'is_active' => true,
            ],
            [
                'name' => 'Lab Violation',
                'default_amount' => 15000,
                'description' => 'Violation of lab rules and regulations.',
                'auto_apply_trigger' => 'lab_violation',
                'is_active' => true,
            ],
            [
                'name' => 'Library Fine',
                'default_amount' => 2000,
                'description' => 'Overdue or lost library materials.',
                'auto_apply_trigger' => null,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            \App\Models\FineType::firstOrCreate(
                ['name' => $type['name']],
                $type,
            );
        }
    }
}
