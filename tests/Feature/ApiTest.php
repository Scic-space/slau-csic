<?php

use App\Models\Announcement;
use App\Models\Badge;
use App\Models\Competition;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\Fine;
use App\Models\FineType;
use App\Models\Project;
use App\Models\StaffNotification;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

// ─── Helpers ───────────────────────────────────────────────

function approvedUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'membership_status' => 'active',
        'approved_at' => now(),
        'joined_at' => now()->subMonths(2),
        'privacy_settings' => ['show_profile' => true],
    ], $overrides));
}

function authHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
}

// ─── Public Members API ────────────────────────────────────

describe('GET /api/members', function () {
    it('lists members with expected structure', function () {
        getJson('/api/members')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    });
});

describe('GET /api/members/{user}', function () {
    it('shows an approved visible member', function () {
        $member = approvedUser(['name' => 'Charlie']);

        getJson('/api/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Charlie');
    });

    it('returns 404 for hidden profile', function () {
        $member = approvedUser(['privacy_settings' => ['show_profile' => false]]);

        getJson('/api/members/'.$member->id)->assertNotFound();
    });

    it('returns 404 for non-approved member', function () {
        $member = User::factory()->create(['approved_at' => null]);

        getJson('/api/members/'.$member->id)->assertNotFound();
    });
});

// ─── Projects API ──────────────────────────────────────────

describe('GET /api/projects', function () {
    it('lists projects', function () {
        Project::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha',
            'description' => 'A test project',
            'type' => 'development',
            'lead_id' => User::factory()->create()->id,
        ]);

        getJson('/api/projects')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Project Alpha');
    });
});

// ─── Competitions API ──────────────────────────────────────

describe('GET /api/competitions', function () {
    it('lists competitions', function () {
        Competition::create(['name' => 'Cyber Hack 2026', 'description' => 'Annual CTF', 'type' => 'ctf', 'start_date' => now(), 'end_date' => now()->addDay(), 'location' => 'Online']);

        getJson('/api/competitions')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Cyber Hack 2026');
    });
});

// ─── Announcements API ─────────────────────────────────────

describe('GET /api/announcements', function () {
    it('lists published announcements', function () {
        Announcement::create([
            'title' => 'Welcome back',
            'content' => 'Semester starts soon',
            'type' => 'general',
            'audience' => 'all',
            'is_published' => true,
            'published_at' => now(),
            'created_by' => User::factory()->create()->id,
        ]);

        getJson('/api/announcements')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Welcome back');
    });

    it('excludes unpublished announcements', function () {
        Announcement::create([
            'title' => 'Draft',
            'content' => 'Not published',
            'type' => 'general',
            'audience' => 'all',
            'is_published' => false,
            'created_by' => User::factory()->create()->id,
        ]);

        getJson('/api/announcements')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });
});

// ─── CTF API ───────────────────────────────────────────────

describe('GET /api/ctf/competitions', function () {
    it('lists active competitions', function () {
        $user = User::factory()->create();
        CtfCompetition::create([
            'title' => 'Summer CTF',
            'slug' => 'summer-ctf',
            'status' => 'published',
            'is_public' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);

        getJson('/api/ctf/competitions')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Summer CTF');
    });
});

describe('GET /api/ctf/competitions/{competition}/scoreboard', function () {
    it('returns scoreboard', function () {
        $user = approvedUser();
        $category = \App\Models\CtfCategory::create(['name' => 'Web', 'slug' => 'web-ctf']);
        $comp = CtfCompetition::create([
            'title' => 'Test CTF',
            'slug' => 'test-ctf',
            'status' => 'published',
            'is_public' => true,
            'start_date' => now()->subDay(),
        ]);
        $challenge = CtfChallenge::create([
            'ctf_competition_id' => $comp->id,
            'ctf_category_id' => $category->id,
            'title' => 'Challenge 1',
            'slug' => 'challenge-1',
            'is_active' => true,
            'points' => 100,
            'flag_hash' => hash('sha256', 'SLAU_CSIC{correct_flag}'),
        ]);
        CtfSubmission::create([
            'ctf_challenge_id' => $challenge->id,
            'user_id' => $user->id,
            'submitted_flag' => 'SLAU_CSIC{correct_flag}',
            'is_correct' => true,
            'submitted_at' => now(),
        ]);

        getJson("/api/ctf/competitions/{$comp->id}/scoreboard")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

// ─── Leaderboard API ───────────────────────────────────────

describe('GET /api/leaderboard', function () {
    it('returns leaderboard data', function () {
        getJson('/api/leaderboard')
            ->assertOk()
            ->assertJsonStructure(['data']);
    });
});

// ─── Contact API ───────────────────────────────────────────

describe('POST /api/contact', function () {
    it('stores a contact message', function () {
        postJson('/api/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'topic' => 'Membership and joining',
            'message' => 'I would like to join the club.',
        ])->assertCreated()
            ->assertJson(['success' => true]);
    });

    it('validates required fields', function () {
        postJson('/api/contact', [])->assertUnprocessable();
    });

    it('validates email format', function () {
        postJson('/api/contact', [
            'name' => 'Test',
            'email' => 'not-an-email',
            'topic' => 'Test',
            'message' => 'Test message',
        ])->assertUnprocessable();
    });
});

// ─── Protected: Exam API ───────────────────────────────────

describe('Protected exam endpoints', function () {
    it('GET /api/exams lists published exams', function () {
        $user = approvedUser();
        \App\Models\Exam::create([
            'user_id' => $user->id,
            'title' => 'Security Basics',
            'description' => 'Test your knowledge',
            'duration_minutes' => 30,
            'passing_score' => 70,
            'status' => 'published',
        ]);

        getJson('/api/exams', authHeaders($user))
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Security Basics');
    });

    it('POST /api/exams/{exam}/start creates an attempt', function () {
        $user = approvedUser();
        $exam = \App\Models\Exam::create([
            'user_id' => $user->id,
            'title' => 'Security Basics',
            'description' => 'Test',
            'duration_minutes' => 30,
            'passing_score' => 70,
            'status' => 'published',
        ]);

        $question = \App\Models\QuestionBankQuestion::create([
            'user_id' => $user->id,
            'question_text' => 'What is SQL injection?',
            'type' => 'text',
        ]);
        $exam->questions()->create([
            'question_bank_question_id' => $question->id,
            'order' => 1,
        ]);

        postJson("/api/exams/{$exam->id}/start", [], authHeaders($user))
            ->assertCreated()
            ->assertJsonStructure(['data' => ['attempt_id', 'questions']]);
    });

    it('requires auth for exam endpoints', function () {
        getJson('/api/exams')->assertUnauthorized();
    });
});

// ─── Protected: Gamification API ───────────────────────────

describe('Protected gamification endpoints', function () {
    it('GET /api/user/points returns user points', function () {
        $user = approvedUser();

        getJson('/api/user/points', authHeaders($user))
            ->assertOk()
            ->assertJsonStructure(['data' => ['total_points', 'score']]);
    });

    it('GET /api/user/badges returns user badges', function () {
        $user = approvedUser();
        $badge = Badge::create(['name' => 'Expert', 'slug' => 'expert-badge', 'description' => 'Complete all challenges', 'icon' => 'trophy', 'criteria_type' => \App\Models\BadgeCriteriaType::TotalPoints]);
        $user->earnedBadges()->attach($badge->id, ['earned_at' => now()]);

        getJson('/api/user/badges', authHeaders($user))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('requires auth for gamification endpoints', function () {
        getJson('/api/user/points')->assertUnauthorized();
    });
});

// ─── Protected: Finance API ────────────────────────────────

describe('Protected finance endpoints', function () {
    it('GET /api/user/fines returns user fines', function () {
        $user = approvedUser();
        $fineType = FineType::factory()->create();

        Fine::factory()->create([
            'user_id' => $user->id,
            'fine_type_id' => $fineType->id,
            'amount' => 50000,
            'issue_date' => now(),
        ]);

        getJson('/api/user/fines', authHeaders($user))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('GET /api/fine-types lists fine types', function () {
        FineType::factory()->create(['name' => 'Late Fee']);

        $user = approvedUser();
        getJson('/api/fine-types', authHeaders($user))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Late Fee');
    });
});

// ─── Protected: Notifications API ──────────────────────────

describe('Protected notification endpoints', function () {
    it('GET /api/notifications lists user notifications', function () {
        $user = approvedUser();
        StaffNotification::create([
            'staff_id' => $user->id,
            'type' => 'info',
            'title' => 'Test notification',
            'message' => 'This is a test',
        ]);

        getJson('/api/notifications', authHeaders($user))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('POST /api/notifications/{id}/read marks as read', function () {
        $user = approvedUser();
        $notification = StaffNotification::create([
            'staff_id' => $user->id,
            'type' => 'info',
            'title' => 'Test',
            'message' => 'Test',
        ]);

        postJson("/api/notifications/{$notification->id}/read", [], authHeaders($user))
            ->assertOk()
            ->assertJson(['success' => true]);
    });

    it('prevents marking another user notification as read', function () {
        $user1 = approvedUser();
        $user2 = approvedUser();
        $notification = StaffNotification::create([
            'staff_id' => $user2->id,
            'type' => 'info',
            'title' => 'Test',
            'message' => 'Test',
        ]);

        postJson("/api/notifications/{$notification->id}/read", [], authHeaders($user1))
            ->assertForbidden();
    });

    it('GET /api/notifications/unread-count returns count', function () {
        $user = approvedUser();
        StaffNotification::create([
            'staff_id' => $user->id,
            'type' => 'info',
            'title' => 'Unread',
            'message' => 'Test',
        ]);

        getJson('/api/notifications/unread-count', authHeaders($user))
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    });
});

// ─── Protected: Teaching API ───────────────────────────────

describe('Protected teaching enrollment', function () {
    it('POST /api/teaching/sessions/{training}/enroll enrolls user', function () {
        $user = approvedUser();
        $training = Training::create([
            'title' => 'Cybersecurity 101',
            'description' => 'Intro',
            'category' => 'ethical_hacking',
            'instructor_id' => $user->id,
            'difficulty' => 'beginner',
            'is_published' => true,
            'max_enrollments' => 20,
        ]);

        postJson("/api/teaching/sessions/{$training->id}/enroll", [], authHeaders($user))
            ->assertCreated()
            ->assertJson(['success' => true]);
    });

    it('prevents duplicate enrollment', function () {
        $user = approvedUser();
        $training = Training::create([
            'title' => 'Cybersecurity 101',
            'description' => 'Intro',
            'category' => 'ethical_hacking',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
            'is_published' => true,
            'max_enrollments' => 20,
        ]);

        postJson("/api/teaching/sessions/{$training->id}/enroll", [], authHeaders($user));
        postJson("/api/teaching/sessions/{$training->id}/enroll", [], authHeaders($user))
            ->assertConflict();
    });
});

// ─── Public Teaching API ───────────────────────────────────

describe('GET /api/teaching/sessions', function () {
    it('lists published sessions', function () {
        $user = User::factory()->create();
        Training::create([
            'title' => 'Intro to Cybersecurity',
            'description' => 'Basics',
            'category' => 'ethical_hacking',
            'difficulty' => 'beginner',
            'instructor_id' => $user->id,
            'is_published' => true,
        ]);

        getJson('/api/teaching/sessions')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Intro to Cybersecurity');
    });
});

// ─── Protected CTF flag submission ─────────────────────────

describe('POST /api/ctf/challenges/{challenge}/submit', function () {
    it('requires authentication', function () {
        $category = \App\Models\CtfCategory::create(['name' => 'Web', 'slug' => 'web-cat']);
        $comp = CtfCompetition::create([
            'title' => 'Test',
            'slug' => 'test-comp',
            'status' => 'published',
            'is_public' => true,
            'start_date' => now()->subDay(),
        ]);
        $challenge = CtfChallenge::create([
            'ctf_competition_id' => $comp->id,
            'ctf_category_id' => $category->id,
            'title' => 'Challenge 1',
            'slug' => 'challenge-1',
            'is_active' => true,
            'flag_hash' => hash('sha256', 'SLAU_CSIC{secret}'),
        ]);

        postJson("/api/ctf/challenges/{$challenge->id}/submit", ['flag' => 'SLAU_CSIC{test}'])
            ->assertUnauthorized();
    });
});

// ─── Auth sanity ──────────────────────────────────────────

describe('GET /api/user/profile', function () {
    it('returns the authenticated user profile', function () {
        $user = approvedUser();

        $response = getJson('/api/user/profile', authHeaders($user));

        $response->assertOk();
        expect($response->json('name'))->toBe($user->name);
    });

    it('fails without token', function () {
        getJson('/api/user/profile')->assertUnauthorized();
    });
});
