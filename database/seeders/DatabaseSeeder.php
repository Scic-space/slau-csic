<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $admin = User::firstOrCreate(
            ['email' => 'kevinssali23@gmail.com'],
            [
                'name' => 'Admin',
                'membership_status' => 'active',
                'membership_type' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        if (! $admin->membership) {
            Membership::create([
                'user_id' => $admin->id,
                'type' => 'active',
                'status' => 'active',
                'approved_at' => now(),
                'joined_at' => now(),
            ]);
        }

        $this->call(MeetingSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        // $this->call(ClubPortalSeeder::class);
        // $this->call(EventCategorySeeder::class);
        // $this->call(ElectionSeeder::class);
        // $this->call(BadgeSeeder::class);
        // $this->call(CtfChallengeSeeder::class);
        // $this->call(FineTypeSeeder::class);
        // $this->call(CompetitionSeeder::class);
        // $this->call(AnnouncementSeeder::class);
        // $this->call(PollSeeder::class);
        // $this->call(DiscordWebhookSettingSeeder::class);
        // $this->call(ProjectSeeder::class);
        // $this->call(TestimonialSeeder::class);
        // $this->call(NewsSeeder::class);
    }
}
