# Phase 7: Gamification - Research

**Gathered:** 2026-04-28
**Researcher:** gsd-phase-researcher
**Mode:** standard

---

## Domain Understanding

### What This Phase Delivers

A gamification system with:
- **Points** — Users earn points from activities (events attended, CTF completions, training sessions)
- **Badges** — Achievements unlocked by milestones (complete N events, score threshold, etc.)
- **Ranks** — Tiers users progress through based on accumulated points (Bronze → Silver → Gold → Platinum)
- **Leaderboard** — Ranked display of top members by points

### Existing Infrastructure

The codebase already has partial gamification infrastructure that this phase will **formalize and extend**:

1. **User model fields** (already exist):
   - `bonus_points` (integer) — Bonus points from manual awards
   - `score` (decimal 8,2) — Overall score
   - `total_sessions_attended` (integer)
   - `current_streak` (integer) — Consecutive days
   - `longest_streak` (integer)
   - `achievements_summary` (text)
   - `competition_rank` (string)

2. **ClubResource + ClubResourceProgress** (already exist):
   - Tracks per-track progress with `score`, `status`, `progress_percentage`
   - Used for CTF tracking

3. **ClubPortalController::memberBadges()** — Hardcoded badge logic (needs formalization):
   ```php
   // Current badges are simple threshold checks:
   if ($completed >= 1) $badges[] = 'Lab Starter';
   if ($completed >= 3) $badges[] = 'Challenge Finisher';
   if ($score >= 200) $badges[] = 'Score Builder';
   if ($htbCompletion >= 40) $badges[] = 'HTB Connected';
   if ($completed >= 5 && $score >= 400) $badges[] = 'Internal Ranked Player';
   ```

4. **ClubPortalController::ctfLeaderboard()** — Hardcoded leaderboard logic:
   - Sorts by `score`, `completed`, `average_progress`
   - Returns top 10 users
   - Computed from `ClubResourceProgress`, not a dedicated model

5. **CTF blade** (`pages/club/ctf.blade.php`) — Displays:
   - Badges (from `memberBadges()`)
   - Internal leaderboard (from `ctfLeaderboard()`)
   - Challenge tracks with progress update forms

### What's Missing (Gap Analysis)

| Component | Status | Gap |
|-----------|--------|-----|
| Points model | ❌ Missing | No dedicated `GamificationPoint` model or service |
| Badges model | ❌ Missing | No `Badge` model, badges hardcoded in controller |
| Badge definitions | ❌ Missing | No structured badge criteria |
| Ranks model | ❌ Missing | No `Rank` model with tier thresholds |
| Leaderboard model | ❌ Missing | Leaderboard computed ad-hoc from ClubResourceProgress |
| Points earning events | ❌ Missing | No event-driven point awards |
| Points history | ❌ Missing | No ledger of point transactions |
| Admin management UI | ❌ Missing | No admin panel for managing points/badges |
| Public leaderboard | ❌ Missing | Only CTF leaderboard exists |
| Member profile display | ❌ Partial | CTF blade exists but no standalone gamification UI |

---

## Technical Approach

### Architecture Pattern

**Service-based gamification layer** — Keep gamification logic in service classes, not controllers.

```
App/Services/
  └── GamificationService.php       ← Core: award points, check badges
  └── PointsLedgerService.php       ← Transaction log
  └── LeaderboardService.php         ← Ranked queries

App/Models/
  ├── PointTransaction.php          ← Points earned/spent (ledger)
  ├── Badge.php                     ← Badge definitions
  └── UserBadge.php                 ← Earned badges (pivot)

App/Livewire/
  ├── Leaderboard.php               ← Public leaderboard page
  └── Admin/Gamification.php        ← Admin panel
```

### Database Schema

**New tables needed:**

1. **`point_transactions`** — Ledger of all point changes
   - `id`, `user_id`, `points` (can be negative for deductions), `reason`, `reference_type`, `reference_id`, `created_at`
   - Indexes on `user_id`, `created_at`

2. **`badges`** — Badge definitions
   - `id`, `name`, `slug`, `description`, `icon`, `criteria_type`, `criteria_value`, `points_bonus`, `created_at`

3. **`user_badges`** — Earned badges
   - `id`, `user_id`, `badge_id`, `earned_at`, unique constraint on (`user_id`, `badge_id`)

4. **User model enhancements:**
   - Add `rank` field with enum (`bronze`, `silver`, `gold`, `platinum`)
   - Add `rank_changed_at` timestamp
   - Replace `score` computation with SUM of point_transactions

### Points Earning Events

| Event | Points | Notes |
|-------|--------|-------|
| Event attendance | 10 | When admin marks attendance |
| Teaching session attendance | 5 | When member checks in |
| CTF track completion | 20 | When ClubResourceProgress status = completed |
| Badge earned | Badge.points_bonus | Automatic on badge unlock |
| Manual award | Variable | Admin gives from admin panel |

### Badge Criteria Types

Store criteria as structured rules in `badges.criteria_type` field:

| Criteria Type | Description | Example |
|--------------|-------------|---------|
| `events_attended` | Count of event registrations attended | 10 → badge after 10 events |
| `ctf_completed` | Count of completed CTF tracks | 3 → badge after 3 CTF completions |
| `total_points` | Cumulative points threshold | 500 → badge at 500 points |
| `teaching_sessions` | Teaching session count | 5 → badge after 5 sessions |
| `streak_days` | Consecutive activity days | 7 → badge at 7-day streak |
| `ctf_score` | CTF progress score sum | 200 → badge at 200 CTF score |
| `custom` | Manual admin award | Admin grants manually |

### Rank Thresholds

| Rank | Points Required | Badge |
|------|----------------|-------|
| Bronze | 0 | Default |
| Silver | 200 | Auto-upgrade at 200 points |
| Gold | 500 | Auto-upgrade at 500 points |
| Platinum | 1000 | Auto-upgrade at 1000 points |

### Leaderboard Strategy

Compute leaderboard as a **materialized view** from point_transactions:

```sql
SELECT user_id, SUM(points) as total_points
FROM point_transactions
GROUP BY user_id
ORDER BY total_points DESC
LIMIT 100;
```

Cache with Laravel's cache facade for 5-minute TTL to avoid expensive queries on every page load.

### Integration Points

1. **Event Attendance** → `Attendance` model creation → award points via `GamificationService::awardPoints()`
2. **CTF Progress** → `ClubResourceProgress::update()` → check badges via `GamificationService::checkBadges()`
3. **Teaching Check-in** → `TeachingSessionCommands::markAttendance()` → award points
4. **Admin Panel** → `Livewire/Admin/Gamification.php` → manual awards, badge management
5. **Leaderboard Page** → `Livewire/Leaderboard.php` → public leaderboard with filters

### UI Implementation

Following existing patterns (Livewire + Tailwind):

- `resources/views/livewire/leaderboard.blade.php` — Leaderboard table
- `resources/views/livewire/leaderboard-row.blade.php` — Individual row
- `resources/views/livewire/admin/gamification.blade.php` — Admin panel
- Navigation: Add to sidebar under admin section

### Key Files to Create/Modify

**New files:**
- `app/Models/PointTransaction.php`
- `app/Models/Badge.php`
- `app/Models/UserBadge.php`
- `app/Services/GamificationService.php`
- `app/Services/PointsLedgerService.php`
- `app/Services/LeaderboardService.php`
- `app/Livewire/Leaderboard.php`
- `app/Livewire/Admin/Gamification.php`
- `database/migrations/2026_04_28_000000_create_gamification_tables.php`
- `resources/views/livewire/leaderboard.blade.php`
- `resources/views/livewire/admin/gamification.blade.php`

**Modify:**
- `app/Models/User.php` — Add rank field, relations
- `app/Http/Controllers/ClubPortalController.php` — Use LeaderboardService
- `routes/web.php` — Add `/leaderboard` route, admin routes
- `resources/views/layouts/sidebar.blade.php` — Add nav links

---

## Patterns to Follow

1. **Eloquent models** — Standard Laravel 12 conventions with `$fillable`, `casts()`, relationships
2. **Livewire components** — `App\Livewire\` namespace, single root element in Blade
3. **SoftDeletes** — Not needed for gamification (transactions are immutable ledger)
4. **Spatie activity log** — Log point awards and badge unlocks on User and Badge models
5. **Form Requests** — Validate admin inputs (award points form, badge creation form)
6. **Tailwind** — Use existing color conventions (emerald for primary actions)
7. **Dark mode** — Apply `dark:` variants consistently (per existing patterns)

---

## Common Pitfalls

1. **Don't compute scores in real-time** — Use `point_transactions` as source of truth, cache computed totals
2. **Don't duplicate point logic** — Centralize in `GamificationService`, call from all entry points
3. **Don't hardcode badge criteria** — Store in `badges` table for admin-editable criteria
4. **Don't forget race conditions** — Use DB transactions when awarding points + checking badges
5. **Don't skip rank migration** — Add `rank` column via migration, set initial rank based on existing score

---

## Validation Architecture

### Verification Strategy

| Dimension | Method |
|-----------|--------|
| Points awarded correctly | Unit test: GamificationService::awardPoints |
| Badge unlock logic | Unit test: GamificationService::checkBadges with mock data |
| Leaderboard ordering | Integration test: top users sorted by total points |
| Rank upgrades | Unit test: rank threshold boundaries |
| Points ledger integrity | Test: sum of transactions matches user score |
| Admin manual award | Feature test: POST award → point_transaction created |
| Duplicate badge prevention | Test: earning same badge twice creates only one user_badge |

---

## Out of Scope

- Gamification notifications (badge earned emails)
- Points redemption system (spending points for perks)
- Social sharing of badges
- Leaderboard time filters (weekly/monthly) — MVP scope only
- Gamification analytics dashboard

---

## Research Complete