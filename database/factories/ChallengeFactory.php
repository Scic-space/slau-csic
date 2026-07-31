<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    /** @var array<array{title: string, description: string, answer: string, points: int}> */
    private static array $challenges = [
        [
            'title' => 'Find the Hidden Flag',
            'description' => 'Explore the web application and locate the hidden flag. The flag is in the format FLAG{...} and is hidden somewhere in the page source or response headers.',
            'answer' => 'FLAG{w3lc0m3_t0_th3_ch4ll3ng3}',
            'points' => 50,
        ],
        [
            'title' => 'Decode the Secret Message',
            'description' => 'You intercepted a base64-encoded message. Decode it and find the hidden passphrase. The answer is the decoded plaintext string.',
            'answer' => 'cybersecurity',
            'points' => 30,
        ],
        [
            'title' => 'SQL Injection Basics',
            'description' => 'The login form is vulnerable to SQL injection. Bypass the authentication by exploiting the input field. The flag is revealed upon successful login.',
            'answer' => 'FLAG{sql_1nj3ct10n_m4st3r}',
            'points' => 80,
        ],
        [
            'title' => 'Network Port Scan',
            'description' => 'Perform a network scan on the target server. Identify which ports are open and determine the service running on each. Submit the most commonly used port number.',
            'answer' => '80',
            'points' => 40,
        ],
        [
            'title' => 'Password Hash Crack',
            'description' => 'A password hash was leaked. Identify the original password. Hint: common English word.',
            'answer' => 'hello',
            'points' => 60,
        ],
        [
            'title' => 'XSS Vulnerability Discovery',
            'description' => 'A comment section on the forum does not sanitize user input. Inject a JavaScript alert to prove the vulnerability exists. Submit the full payload you used.',
            'answer' => '<script>alert(1)</script>',
            'points' => 70,
        ],
        [
            'title' => 'Reverse Engineering Warmup',
            'description' => 'Analyze the provided binary logic and determine the correct input that produces the output "ACCESS GRANTED". Submit the input string.',
            'answer' => 's3cr3t_k3y',
            'points' => 100,
        ],
        [
            'title' => 'Log Analysis Challenge',
            'description' => 'Review the server access logs. Identify the IP address that attempted a brute force attack. The attacker made over 100 failed login attempts.',
            'answer' => '192.168.1.105',
            'points' => 50,
        ],
        [
            'title' => 'Steganography Puzzle',
            'description' => 'An image file contains a hidden message. Use steganography techniques to extract the embedded text. Submit the hidden message.',
            'answer' => 'hidden_message_found',
            'points' => 90,
        ],
        [
            'title' => 'API Authentication Bypass',
            'description' => 'The API endpoint /admin/data requires authentication. Identify the missing header or token that grants access. Submit the header name:value pair.',
            'answer' => 'Authorization:Bearer admin_token',
            'points' => 110,
        ],
    ];

    public function definition(): array
    {
        $data = fake()->randomElement(self::$challenges);

        return [
            'competition_id' => Competition::factory(),
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => 'flag',
            'points' => $data['points'],
            'answer' => $data['answer'],
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
