# Phase 8: CTF Management - Research

**Gathered:** 2026-04-28
**Researcher:** gsd-phase-researcher
**Mode:** standard

---

## Domain Understanding

### What This Phase Delivers

A formal CTF (Capture The Flag) management system with:

- **CTF Competitions** — Organized events with start/end dates, descriptions, categories
- **CTF Challenges** — Individual challenges within competitions, categorized by type (Web, Crypto, Forensics, PWN, Reversing, OSINT, Misc)
- **Challenge Submissions** — Members submit flags and get validated
- **Challenge Writeups** — Members submit solution writeups after solving
- **CTF Scoreboard** — Real-time ranked leaderboard for active competitions
- **Admin CTF Panel** — Full CRUD for competitions, challenges, categories
- **Points Integration** — Award points for solving challenges via GamificationService

### Existing Infrastructure

The codebase has a **generic competition model** (`Competition`) but it is for external/team competitions, not internal CTF challenges. It is NOT the right base for CTF management.

The existing CTF infrastructure is built on **ClubResource + ClubResourceProgress** — this is a simple track completion tracker, not a proper CTF challenge system. The current flow:
- Admin creates `ClubResource` rows with `category = 'ctf'`
- Members manually update their `ClubResourceProgress` with percentage, score, status
- No flag validation, no challenge categories, no writeups, no automatic scoring

Phase 7's gamification foundation provides:
- `GamificationService` — awards points with reason + reference
- `PointTransaction` — ledger of all point transactions
- `Badge` + `UserBadge` — badge definitions and earned badges
- User rank system (Bronze → Silver → Gold → Platinum)

### What's Missing (Gap Analysis)

| Component | Status | Gap |
|-----------|--------|-----|
| CTF Competition model | ❌ Missing (Competition != CTF) | No formal CTF event structure |
| CTF Challenge model | ❌ Missing | No per-challenge flags, categories, points |
| Challenge Category model | ❌ Missing | No Web/Crypto/Forensics categorization |
| Challenge Submission model | ❌ Missing | No flag submission + validation |
| Challenge Writeup model | ❌ Missing | No solution writeup submission/review |
| Admin CTF management UI | ❌ Missing | No CRUD for CTF competitions/challenges |
| CTF Scoreboard | ❌ Missing (only generic leaderboard) | No real-time CTF ranking |
| Points integration | ❌ Missing | Solving challenges doesn't auto-award points |
| Challenge Solves tracking | ❌ Missing | No per-challenge solve counts |

---

## Technical Approach

### Architecture Pattern

**Livewire + Service-based CTF layer** — Extend the existing Livewire patterns from Phase 7 and 6.

```
App/Models/CTF/
  ├── CtfCompetition.php      # Competition model
  ├── CtfChallenge.php        # Challenge model
  ├── CtfCategory.php         # Challenge category (Web, Crypto, etc.)
  ├── CtfSubmission.php       # Flag submission
  ├── CtfWriteup.php           # Solution writeup
  └── CtfSolve.php             # Track solves (who solved what)

App/Services/
  └── CtfService.php           # Challenge validation, scoring, points

App/Livewire/
  ├── Ctf/Details.php         # Competition details page
  ├── Ctf/Challenge.php        # Individual challenge page
  ├── Ctf/Submit.php           # Flag submission form
  ├── Ctf/Scoreboard.php       # Live scoreboard
  ├── Ctf/Writeup.php          # Writeup page
  └── Admin/Ctf.php            # Admin CTF management
```

### Key Design Decisions

#### 1. Competition Model

Create `CtfCompetition` (NOT reuse `Competition` which is for external events):

```php
CtfCompetition:
  - title, slug, description
  - start_date, end_date (nullable for always-on)
  - status: draft | published | archived
  - is_public: boolean (visible to members or hidden)
  - visibility: open | invite_only
  - max_score: integer (optional cap)
```

#### 2. Challenge Model

Create `CtfChallenge`:

```php
CtfChallenge:
  - ctf_competition_id FK
  - ctf_category_id FK
  - title, slug
  - description (markdown)
  - flag (hashed in DB)
  - points (integer, e.g. 100, 200, 300)
  - difficulty: easy | medium | hard | insane
  - is_active: boolean
  - hint (optional)
  - hint_cost (points deducted for using hint)
  - max_attempts: integer (0 = unlimited)
  - tags: JSON array
  - sort_order: integer
```

**Flag validation:** Store `hash('sha256', $submittedFlag)` in DB. On submission, hash input and compare. This prevents flag leaking from DB dumps.

**Points:** Challenges have fixed point values (like real CTFs). Points awarded via `GamificationService::awardPoints()` with `reference_type = CtfChallenge::class`.

#### 3. Submission Model

Create `CtfSubmission`:

```php
CtfSubmission:
  - ctf_challenge_id FK
  - user_id FK
  - submitted_flag: string
  - is_correct: boolean
  - points_awarded: integer (0 if wrong)
  - attempt_number: integer
  - ip_address: string
  - submitted_at: datetime
```

Unique constraint: `(ctf_challenge_id, user_id)` — each user gets ONE successful solve per challenge. Wrong submissions are logged but don't create new solve records.

#### 4. Writeup Model

Create `CtfWriteup`:

```php
CtfWriteup:
  - ctf_challenge_id FK
  - user_id FK
  - content: text (markdown)
  - status: pending | approved | rejected
  - reviewed_by: user FK (nullable)
  - reviewed_at: datetime (nullable)
```

#### 5. Category Model

Create `CtfCategory`:

```php
CtfCategory:
  - name: string (Web, Crypto, Forensics, PWN, Reversing, OSINT, Misc)
  - slug: string
  - color: string (hex color for UI)
  - icon: string (emoji or icon class)
  - sort_order: integer
```

### Points Integration

When a challenge is solved correctly:

```php
// In CtfService::submitFlag()
if (Hash::check($submittedFlag, $challenge->flag_hash)) {
    // Award points via GamificationService
    $gamificationService->awardPoints(
        $user,
        $challenge->points,
        "CTF Challenge solved: {$challenge->title}",
        CtfChallenge::class,
        $challenge->id
    );

    // Also check badges — CTF-specific badges
    $this->checkCtfBadges($user);

    return ['success' => true, 'points' => $challenge->points];
}
```

### Scoreboard Service

Build a `CtfScoreboardService` that:
- Queries `CtfSubmission` for the competition (where `is_correct = true`)
- Aggregates by `user_id`, sums `points_awarded`
- Returns sorted top N
- Cache with tag for 1-minute TTL (not 5 min like general leaderboard — CTF scoreboards need to update faster)

```php
public function getScoreboard(CtfCompetition $competition, int $limit = 100): Collection
{
    return Cache::tags(['ctf-scoreboard-'.$competition->id])
        ->remember('scoreboard', 60, function () use ($competition) {
            return User::query()
                ->withSum(['ctfSubmissions' => fn ($q) =>
                    $q->where('is_correct', true)
                      ->whereHas('challenge', fn ($q) => $q->where('ctf_competition_id', $competition->id))
                ], 'points_awarded')
                ->orderBy('ctf_submissions_sum_points_awarded', 'desc')
                ->limit($limit)
                ->get();
        });
}
```

### Admin Panel

Create `App\Livewire\Admin\Ctf.php` with tabs:
1. **Competitions** — List/create/edit/clone/archive competitions
2. **Challenges** — List challenges by competition, create/edit, set flag/points/difficulty
3. **Categories** — Manage challenge categories
4. **Submissions** — View all submissions (audit log)
5. **Scoreboard** — View live scoreboard for any competition

---

## Database Schema

### Migration: create_ctf_tables

```php
// ctf_competitions
Schema::create('ctf_competitions', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->timestamp('start_date')->nullable();
    $table->timestamp('end_date')->nullable();
    $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
    $table->boolean('is_public')->default(true);
    $table->integer('max_score')->nullable();
    $table->timestamps();
});

// ctf_categories
Schema::create('ctf_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('color', 7)->default('#10b981');
    $table->string('icon')->default('🏴');
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

// ctf_challenges
Schema::create('ctf_challenges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ctf_competition_id')->constrained('ctf_competitions')->cascadeOnDelete();
    $table->foreignId('ctf_category_id')->constrained('ctf_categories');
    $table->string('title');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->string('flag_hash'); // SHA256 of the real flag
    $table->integer('points')->default(100);
    $table->enum('difficulty', ['easy', 'medium', 'hard', 'insane'])->default('medium');
    $table->boolean('is_active')->default(true);
    $table->text('hint')->nullable();
    $table->integer('hint_cost')->default(0);
    $table->integer('max_attempts')->default(0);
    $table->json('tags')->nullable();
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    $table->unique(['ctf_competition_id', 'slug']);
});

// ctf_submissions
Schema::create('ctf_submissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ctf_challenge_id')->constrained('ctf_challenges')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('submitted_flag');
    $table->boolean('is_correct');
    $table->integer('points_awarded')->default(0);
    $table->integer('attempt_number')->default(1);
    $table->string('ip_address', 45)->nullable();
    $table->timestamp('submitted_at');
    $table->unique(['ctf_challenge_id', 'user_id']);
    $table->index(['ctf_challenge_id', 'is_correct']);
    $table->index(['user_id', 'is_correct']);
});

// ctf_writeups
Schema::create('ctf_writeups', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ctf_challenge_id')->constrained('ctf_challenges')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->longText('content');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->foreignId('reviewed_by')->nullable()->constrained('users');
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamps();
    $table->unique(['ctf_challenge_id', 'user_id']);
});
```

---

## Routes

```php
// Public CTF routes
Route::middleware(['auth'])->group(function () {
    Route::get('/ctf', [CtfController::class, 'index'])->name('ctf.index');
    Route::get('/ctf/{competition:slug}', [CtfController::class, 'show'])->name('ctf.competition');
    Route::get('/ctf/{competition:slug}/challenges/{challenge:slug}', [CtfController::class, 'challenge'])->name('ctf.challenge');
    Route::post('/ctf/{competition:slug}/challenges/{challenge:slug}/submit', [CtfController::class, 'submit'])->name('ctf.submit');
    Route::get('/ctf/{competition:slug}/scoreboard', [CtfController::class, 'scoreboard'])->name('ctf.scoreboard');
    Route::get('/ctf/{competition:slug}/challenges/{challenge:slug}/writeup', [CtfController::class, 'writeup'])->name('ctf.writeup');
    Route::post('/ctf/{competition:slug}/challenges/{challenge:slug}/writeup', [CtfController::class, 'submitWriteup'])->name('ctf.writeup.submit');
});

// Admin routes
Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->group(function () {
    Route::get('/ctf', [AdminCtfController::class, 'index'])->name('admin.ctf');
    Route::resource('ctf-competitions', AdminCtfController::class);
    Route::resource('ctf-challenges', AdminCtfChallengeController::class);
    Route::resource('ctf-categories', AdminCtfCategoryController::class);
});
```

---

## Security Considerations

1. **Flag storage:** Hash flags with SHA256 — never store plain text
2. **Rate limiting:** Limit submissions per user per challenge (e.g., 10 per minute)
3. **IP logging:** Log IP on submissions for abuse detection
4. **Points race condition:** Use `firstOrCreate` or `updateOrCreate` pattern for solve records to prevent double points
5. **Admin-only challenges:** Support hidden challenges visible only to admins

---

## Implementation Order (for planning)

1. **Phase 8.1 — Foundation:** Migrations, models, CtfService
2. **Phase 8.2 — Admin Panel:** Full CRUD for competitions, challenges, categories
3. **Phase 8.3 — Member UI:** Competition listing, challenge pages, flag submission, scoreboard
4. **Phase 8.4 — Writeups:** Writeup submission and review workflow

**This research covers the full scope. The plan should break this into 2 plans maximum (Foundation + UI).**

---

## Validation Architecture

**How to verify this feature works:**

1. **Unit tests:** CtfService::submitFlag() with correct/incorrect flags
2. **Integration:** Submit flag → verify points transaction created → verify scoreboard updates
3. **Feature tests:** Admin creates competition + challenge → member submits flag → sees scoreboard update
4. **E2E:** Full flow from admin setup to member solving to points receipt