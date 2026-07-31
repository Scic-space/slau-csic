<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(5)->get();

        if ($users->count() < 3) {
            return;
        }

        $testimonials = [
            [
                'user_id' => $users[0]->id,
                'quote' => 'I joined SLAU-CSIC with zero cybersecurity knowledge. After my first CTF season, I solved 12 challenges and ranked in the top 10. The mentorship here is unreal.',
                'is_approved' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'user_id' => $users[2]->id,
                'quote' => 'The weekly workshops changed everything for me. I went from barely knowing what a terminal was to writing my own security scripts in 3 months.',
                'is_approved' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'user_id' => $users[3]->id,
                'quote' => 'CTF competitions taught me more about web security than any textbook. I now freelance as a penetration tester — and it all started with a club CTF.',
                'is_approved' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'user_id' => $users[4]->id,
                'quote' => 'The team challenges built my collaboration skills. We competed as a team of 4 and placed 2nd nationally. That experience landed me my first internship.',
                'is_approved' => false,
                'is_featured' => false,
                'sort_order' => 0,
            ],
            [
                'user_id' => $users[1]->id,
                'quote' => 'Being part of the club gave me hands-on experience I could never get in lectures. The projects, the people, the challenges — everything built real skills.',
                'is_approved' => false,
                'is_featured' => false,
                'sort_order' => 0,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
