<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'kevinssali23@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );

        Meeting::factory()
            ->count(5)
            ->upcoming()
            ->create(['created_by' => $admin->id]);

        Meeting::factory()
            ->count(8)
            ->past()
            ->create(['created_by' => $admin->id]);

        Meeting::factory()
            ->count(2)
            ->cancelled()
            ->create(['created_by' => $admin->id]);

        Meeting::factory()
            ->count(1)
            ->ongoing()
            ->create(['created_by' => $admin->id]);
    }
}
