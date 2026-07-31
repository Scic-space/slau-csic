<?php

namespace Database\Seeders;

use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CtfChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $competition = CtfCompetition::firstOrCreate(
            ['slug' => 'default-ctf'],
            [
                'title' => 'SLAU CSIC Beginner CTF',
                'description' => 'A beginner-friendly CTF designed for new members to learn cybersecurity basics. All challenges can be solved using just your browser! Open the developer console (F12) and start learning.',
                'status' => 'published',
                'is_public' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(90),
            ]
        );

        $challenges = [
            // Web (category_id: 1)
            [
                'title' => 'Base64 Decoder',
                'description' => "Base64 encoding represents binary data as ASCII text. It's used everywhere in cybersecurity, from APIs to cookies.\n\nYour browser has a built-in base64 decoder. Open the developer console (F12 \u{2192} Console) and type:\n\natob('U0xBVV9DU0lDe2I2NF9iMzRzdH0=')",
                'flag' => 'SLAU_CSIC{b64_b34st}',
                'points' => 10,
                'difficulty' => 'easy',
                'category_id' => 1,
                'hint' => 'atob() decodes base64, btoa() encodes it. Try pasting the exact command from the description into your browser console!',
                'hint_cost' => 2,
                'max_attempts' => 0,
                'tags' => ['base64', 'encoding'],
            ],
            [
                'title' => 'URL Decoder',
                'description' => "URLs can't contain spaces or special characters directly, so they get 'percent-encoded'. Each byte becomes a % followed by two hex digits.\n\nDecode this using your browser console:\n\ndecodeURIComponent('%53%4c%41%55%5f%43%53%49%43%7b%75%72%6c%5f%64%33%63%30%64%33%72%7d')",
                'flag' => 'SLAU_CSIC{url_d3c0d3r}',
                'points' => 20,
                'difficulty' => 'easy',
                'category_id' => 1,
                'hint' => 'Just paste the full decodeURIComponent(...) call into the browser console and press Enter!',
                'hint_cost' => 3,
                'max_attempts' => 0,
                'tags' => ['url', 'encoding', 'percent'],
            ],
            // Crypto (category_id: 2)
            [
                'title' => 'ROT13 Cipher',
                'description' => "ROT13 shifts each letter by 13 positions in the alphabet. Since there are 26 letters, applying ROT13 twice brings you back \u{2014} so to decode, just apply ROT13 again!\n\nDecode: FYNH_PFVP{e3i34y_f3pe3g}",
                'flag' => 'SLAU_CSIC{r3v34l_s3cr3t}',
                'points' => 15,
                'difficulty' => 'easy',
                'category_id' => 2,
                'hint' => "ROT13 function for your console: (s) => s.replace(/[a-zA-Z]/g, c => String.fromCharCode(c.charCodeAt(0) + (c <= 'Z' ? 13 : -13)))",
                'hint_cost' => 3,
                'max_attempts' => 0,
                'tags' => ['rot13', 'caesar', 'cipher'],
            ],
            [
                'title' => 'Hex Decoder',
                'description' => "Hexadecimal (hex) represents each byte as two characters using 0-9 and a-f. It's a compact way to show binary data, and you'll see it everywhere in CTFs.\n\nDecode:\n534c41555f435349437b6833785f7233346433727d",
                'flag' => 'SLAU_CSIC{h3x_r34d3r}',
                'points' => 25,
                'difficulty' => 'easy',
                'category_id' => 2,
                'hint' => "In your console:\n'534c41555f435349437b6833785f7233346433727d'.match(/.{2}/g).map(h => String.fromCharCode(parseInt(h, 16))).join('')",
                'hint_cost' => 5,
                'max_attempts' => 0,
                'tags' => ['hex', 'hexadecimal', 'encoding'],
            ],
            [
                'title' => 'Binary Code',
                'description' => "Binary uses only 0s and 1s to represent data. Each group of 8 bits represents one character (a byte). Understanding binary is fundamental to cybersecurity.\n\nDecode:\n01010011 01001100 01000001 01010101 01011111 01000011 01010011 01001001 01000011 01111011 01100010 00110001 01101110 00110100 01110010 01111001 01111101",
                'flag' => 'SLAU_CSIC{b1n4ry}',
                'points' => 35,
                'difficulty' => 'medium',
                'category_id' => 2,
                'hint' => "In your console:\n'01010011 01001100 01000001 01010101 01011111 01000011 01010011 01001001 01000011 01111011 01100010 00110001 01101110 00110100 01110010 01111001 01111101'.split(' ').map(b => String.fromCharCode(parseInt(b, 2))).join('')",
                'hint_cost' => 5,
                'max_attempts' => 0,
                'tags' => ['binary', 'bits', 'encoding'],
            ],
            // Misc (category_id: 7)
            [
                'title' => 'Reverse String',
                'description' => "Sometimes the answer is right in front of you, just backwards. In programming, reversing a string is a common operation.\n\nReverse this string to find the flag:\n}esrever{CISC_UALS",
                'flag' => 'SLAU_CSIC{reverse}',
                'points' => 20,
                'difficulty' => 'easy',
                'category_id' => 7,
                'hint' => "In your console:\n'}esrever{CISC_UALS'.split('').reverse().join('')",
                'hint_cost' => 3,
                'max_attempts' => 0,
                'tags' => ['string', 'reverse'],
            ],
            [
                'title' => 'Simple Arithmetic',
                'description' => "Basic math is a surprisingly useful skill in CTFs. Everything from hash analysis to network calculations comes back to simple arithmetic.\n\nWhat is 7 \u{00d7} 8 + 6?\n\nThe flag format is SLAU_CSIC{answer}. For example, if the answer were 42, the flag would be SLAU_CSIC{42}.",
                'flag' => 'SLAU_CSIC{62}',
                'points' => 10,
                'difficulty' => 'easy',
                'category_id' => 7,
                'hint' => "7 \u{00d7} 8 = 56, then 56 + 6 = ?",
                'hint_cost' => 1,
                'max_attempts' => 0,
                'tags' => ['math', 'arithmetic'],
            ],
            // Forensics (category_id: 3)
            [
                'title' => 'Hidden Text',
                'description' => "CTF flags often hide in places you don't expect. This challenge includes extra data in its hint that looks like metadata. Try buying the hint to see if there's more than meets the eye!\n\nEvery forensics challenge teaches you to look closer at everything.",
                'flag' => 'SLAU_CSIC{l00k_cl0s3r}',
                'points' => 15,
                'difficulty' => 'easy',
                'category_id' => 3,
                'hint' => 'In forensics, you examine every detail. The flag for this challenge is: SLAU_CSIC{l00k_cl0s3r}',
                'hint_cost' => 1,
                'max_attempts' => 0,
                'tags' => ['forensics', 'observation'],
            ],
        ];

        $count = 0;
        foreach ($challenges as $cData) {
            $slug = Str::slug($cData['title']);
            $existing = CtfChallenge::where('ctf_competition_id', $competition->id)
                ->where('slug', $slug)
                ->exists();

            if ($existing) {
                continue;
            }

            $challenge = CtfChallenge::create([
                'ctf_competition_id' => $competition->id,
                'ctf_category_id' => $cData['category_id'],
                'title' => $cData['title'],
                'slug' => $slug,
                'description' => $cData['description'],
                'flag_hash' => hash('sha256', strtolower($cData['flag'])),
                'points' => $cData['points'],
                'difficulty' => $cData['difficulty'],
                'is_active' => true,
                'max_attempts' => $cData['max_attempts'],
                'sort_order' => $cData['points'],
                'tags' => $cData['tags'] ?? [],
            ]);

            $challenge->hints()->create([
                'tier' => 0,
                'content' => $cData['hint'],
                'cost' => $cData['hint_cost'],
            ]);

            $count++;
        }

        $total = $competition->challenges()->count();
        $this->command->info("Created {$count} new challenges. Total challenges: {$total}");
    }
}
