# SLAU Cybersecurity & Innovations Club — Full App Plan (GSD Reference)
**Repo:** https://github.com/Mr-Righteousdev/slau-csic

---

## ⚠️ CRITICAL INSTRUCTIONS FOR GSD — READ BEFORE DOING ANYTHING

This is a **partially implemented** application. The codebase already has code written for some features. This plan describes the **complete intended system** — not just what needs to be built from scratch.

### How GSD must handle this

1. **Always scan the existing codebase first** before implementing any module. Use `/gsd-map-codebase` to understand what already exists.
2. **Never assume a feature is missing** just because it appears in this plan. Always check the actual files.
3. **Never assume a feature is complete** just because it appears to exist. Verify that it is actually functional end-to-end, not just a stub, placeholder blade view, or partial implementation.
4. **When inconsistencies are found** between this plan and the existing code (different column names, different role names, different component structure), resolve them by: (a) checking if the existing implementation is functional and correct, (b) if yes — update this plan's assumptions to match, (c) if no — migrate the existing code to match this plan, with a migration if DB columns are involved.
5. **Never duplicate** — if a Livewire component, model, migration, or route already exists for a feature, extend it rather than creating a new one alongside it.
6. **Database migrations** — before creating any new migration, check `database/migrations/` to confirm the table does not already exist. If it does, use an `add_columns_to_*` migration rather than recreating the table.
7. **Roles and permissions** — the role names defined in this plan are the source of truth. If different role names exist in the codebase (e.g. `admin` instead of `super_admin`), flag the inconsistency and align to this plan's naming before proceeding.

### Known implemented features (may be partial — verify before skipping)
- User authentication (register, login, email verification, password reset)
- Member application flow (apply, executive review, approve/reject)
- Roles and permissions via Spatie Laravel Permission v6
- Landing page

### Known unimplemented features (build fresh)
- Gamification (points, badges, ranks, leaderboard)
- CTF management
- Exams & assessments
- Learning paths & resource library
- Research & projects
- Notifications & announcements
- Certificate system
- Executive dashboard
- SMS integration

---

## Tech Stack

| Layer | Package | Version |
|---|---|---|
| Framework | Laravel | 13.x |
| Frontend reactivity | Livewire | 4.x |
| UI components | Preline UI | 3.x |
| CSS | Tailwind CSS | 3.x |
| Roles & permissions | spatie/laravel-permission | 6.x |
| Media & file storage | spatie/laravel-medialibrary | latest |
| Database | MySQL | 8.x |
| PDF generation | barryvdh/laravel-dompdf | latest |
| SMS | africastalking/africastalking | latest |
| AI grading | Anthropic API (HTTP, no SDK) | claude-sonnet-4-20250514 |

### Preline UI 3.x usage notes for GSD
- Preline is a Tailwind component library — components are pure HTML/CSS with Alpine.js for interactivity
- Do NOT use Flux UI — the installed UI library is Preline 3.x
- Preline modals are triggered via `data-hs-overlay` attributes and Alpine.js — not Flux's `flux:modal`
- All interactive Preline components (dropdowns, modals, tabs, accordions) require `preline/plugin` in `tailwind.config.js` and the Preline JS script in the layout
- When building Livewire components, use `wire:click` and `wire:model` normally — Preline handles the visual layer only
- For confirm-delete modals in Preline, the pattern is: Livewire method stores the ID in a public property + dispatches a browser event → Alpine.js listens and opens the Preline overlay modal

---

## Roles (Spatie Laravel Permission v6)

| Role slug | Description |
|---|---|
| `super_admin` | Lead dev / system admin — bypasses all permission checks |
| `executive` | Club officials (President, VP, Secretary, Treasurer, PRO) |
| `member` | Approved club member |

> Guests are unauthenticated users — no role assigned.
> If the existing codebase uses different role slugs (e.g. `admin`, `officer`), rename them via a seeder update and a data migration before proceeding.

### Permissions (grouped by module)

```
# Applications
applications.view, applications.approve, applications.reject

# Members
members.view, members.suspend, members.points.award, members.badges.award

# CTF
ctf.create, ctf.manage, ctf.challenges.create, ctf.writeups.approve, ctf.submissions.view

# Exams
exams.create, exams.manage, exams.grade, exams.results.view

# Events
events.create, events.manage, events.attendance.mark

# Learning
learning.create, learning.manage, resources.approve

# Projects
projects.create, projects.manage

# Announcements
announcements.create, announcements.publish

# Certificates
certificates.issue, certificates.revoke

# Settings
settings.manage
```

> `super_admin` gets all permissions via Spatie's `super-admin` gate bypass — do not assign permissions individually.
> `executive` gets all permissions except `settings.manage` by default — adjustable per club setup.
> `member` has no permissions — access is role-based via middleware.

---

## Database Schema

> Before running any migration, check `database/migrations/` for existing tables. Extend with `add_columns_to_*` migrations where tables already exist.

```sql
-- AUTH & MEMBERS (may already exist — verify columns match)
users (
  id, name, email, password, student_number, phone, avatar,
  bio, year_of_study, course, status[pending|active|suspended],
  email_verified_at, remember_token, timestamps
)

member_applications (
  id, user_id, motivation, skills, expectations,
  status[pending|approved|rejected], reviewed_by, rejection_reason,
  reviewed_at, timestamps
)

-- GAMIFICATION (build fresh)
points (
  id, user_id, source_type, source_id, points,
  description, timestamps
)

badges (
  id, name, slug, description, icon, condition_type,
  condition_value, timestamps
)

user_badges (id, user_id, badge_id, awarded_at)

ranks (
  id, name, min_points, max_points, icon, color, timestamps
)

-- CTF (build fresh)
ctf_competitions (
  id, title, slug, description,
  platform[internal|picoctf|hackthebox|tryhackme|other],
  start_date, end_date, status[upcoming|active|ended],
  created_by, timestamps
)

ctf_challenges (
  id, competition_id, title, category, points, difficulty,
  flag, description, hints, is_active, timestamps
)

ctf_submissions (
  id, user_id, challenge_id, submitted_flag,
  is_correct, submitted_at
)

ctf_writeups (
  id, user_id, challenge_id, competition_id, title,
  content, is_public, approved_by, timestamps
)

ctf_team_members (
  id, competition_id, user_id, team_name, timestamps
)

-- EXAMS (build fresh)
exams (
  id, title, description, duration_minutes, total_marks,
  pass_marks, status[draft|published|active|closed],
  is_certificate_eligible, created_by, starts_at, ends_at, timestamps
)

questions (
  id, exam_id, question_text,
  type[mcq|true_false|short_answer|code_snippet],
  points, expected_answer, order, timestamps
)

question_options (
  id, question_id, option_text, is_correct, order
)

exam_attempts (
  id, user_id, exam_id, started_at, submitted_at,
  total_score, status[in_progress|submitted|graded]
)

exam_answers (
  id, attempt_id, question_id, selected_option_id,
  text_answer, is_correct, marks_awarded,
  ai_score, ai_feedback, ai_graded_at,
  graded_by, manually_graded_at, timestamps
)

-- EVENTS (build fresh)
events (
  id, title, slug, description,
  type[session|workshop|seminar|hackathon|social|other],
  venue, is_online, meeting_link, starts_at, ends_at,
  capacity, status[draft|published|cancelled],
  is_public, is_certificate_eligible, created_by,
  banner_image, timestamps
)

event_registrations (
  id, event_id, user_id,
  status[registered|attended|no_show],
  registered_at, checked_in_at
)

event_feedback (
  id, event_id, user_id, rating, comment, timestamps
)

-- PROJECTS (build fresh)
projects (
  id, title, slug, description,
  type[research|innovation|tool|other],
  status[ideation|active|completed|archived],
  visibility[public|members], github_url, demo_url,
  thumbnail, created_by, timestamps
)

project_members (
  id, project_id, user_id,
  role[lead|contributor|reviewer], joined_at
)

project_updates (
  id, project_id, user_id, title, content, timestamps
)

-- LEARNING (build fresh)
learning_paths (
  id, title, slug, description,
  difficulty[beginner|intermediate|advanced],
  category, thumbnail, is_published,
  is_certificate_eligible, created_by, timestamps
)

modules (
  id, learning_path_id, title, description, order, timestamps
)

lessons (
  id, module_id, title, content,
  type[text|video|quiz], video_url,
  duration_minutes, order, timestamps
)

lesson_progress (
  id, user_id, lesson_id, completed_at
)

resources (
  id, title, description,
  type[writeup|tool|cheatsheet|article|video|link],
  url, file_path, category, tags, uploaded_by,
  is_public, is_approved, download_count, timestamps
)

-- NOTIFICATIONS (build fresh)
notifications (
  id, user_id, type, title, body,
  data, read_at, timestamps
)

announcements (
  id, title, body, type[info|warning|danger],
  target[all|members|executives],
  published_by, published_at, expires_at, timestamps
)

-- CERTIFICATES (build fresh)
certificates (
  id, user_id,
  type[exam|event|ctf|learning_path|membership],
  reference_id, title, certificate_number,
  issued_at, file_path, is_revoked, timestamps
)
```

---

## Modules & Features

---

### Module 1 — Public Landing & Auth
**Status: Partially implemented — verify before touching**

GSD instructions:
- Scan existing auth controllers/components and landing page views
- Check if `student_number` and `phone` columns exist on `users` table — add via migration if missing
- Check if email verification is wired up and working end-to-end
- Check if password reset flow is complete
- Do not rebuild what works — only fill gaps

Features:
- Landing page: hero, about, upcoming public events, recent CTF results, club stats, executives section
- Apply to join form (public)
- Register, login, email verify, password reset
- Profile completion prompt on first login (avatar, bio, course, year of study)

---

### Module 2 — Member Application Flow
**Status: Partially implemented — verify before touching**

GSD instructions:
- Check `member_applications` table exists with all required columns
- Check executive review UI exists and is functional (approve/reject with reason)
- Check email notification fires on approval and rejection
- Verify approved applicants get `member` role assigned automatically
- Fill any gaps found

Features:
- Application form: motivation, skills, what they want to learn
- Executive reviews queue (approve / reject with reason)
- Email notification on status change
- On approval: `member` role assigned, welcome email, onboarding checklist on first dashboard load

---

### Module 3 — Member Dashboard & Profile
**Status: Not implemented**

Features:
- Dashboard: XP bar, current rank, recent activity, upcoming events, active exams, ongoing CTFs, quick stats
- Activity feed (points earned, badges awarded, events attended)
- Notifications bell with unread count dropdown
- Profile page: avatar (via Spatie Media Library), bio, course, year, social links (GitHub, LinkedIn, TryHackMe, HackTheBox)
- Profile stats: total XP, rank, badges, exams taken, CTFs participated, events attended, lessons completed
- Certificates section (downloadable PDFs)
- CTF writeups authored
- Projects contributed to

---

### Module 4 — Gamification
**Status: Not implemented**

Features:

**Points system:**
- Points awarded for: solving CTF challenge, passing exam, attending event, completing learning path, approved writeup submission, joining a project, daily login streak
- Point values configurable by executives in settings
- Every point award logged in `points` table with `source_type` and `source_id` for traceability

**Badges:**
- Auto-awarded when condition is met (checked after every relevant action)
- Example badges: First Blood (first CTF solve), Scholar (pass 5 exams), Event Regular (attend 10 events), Contributor (join 3 projects), Completionist (finish a learning path)
- Badge showcase on member profile

**Ranks:**
- Tier system based on cumulative XP
- Default tiers: Recruit (0–99), Analyst (100–499), Specialist (500–1499), Expert (1500–3999), Elite (4000+)
- Rank icons and colors configurable in settings
- Rank displayed on profile, leaderboard, and member cards

**Leaderboard:**
- Global all-time leaderboard
- Monthly leaderboard (resets 1st of each month)
- Per-category leaderboards: CTF, Learning, Events
- Filter by rank tier

---

### Module 5 — CTF Management
**Status: Not implemented**

Features:

**Members:**
- Browse active, upcoming, past competitions
- Internal: submit flags per challenge, instant correct/wrong feedback, see live scoreboard
- External (picoCTF, HTB, TryHackMe): log participation and final score manually
- Submit writeups for challenges (go to approval queue)
- Form and join teams per competition

**Executives:**
- Create competitions (internal or external), set time window
- Create challenges: title, category (Web/Crypto/Forensics/Pwn/Reversing/Misc), difficulty, points, flag, hints
- View per-competition scoreboard
- Approve or reject writeup submissions

**Scoring:**
- First blood bonus (configurable extra points for first correct solve)
- Points sync automatically to gamification system on correct submission

---

### Module 6 — Exams & Assessments
**Status: Not implemented**

Features:

**Members:**
- Browse available exams with status, duration, date window
- Take exam: questions shown one at a time or all at once (configurable per exam)
- Question types: MCQ, True/False, Short Answer, Code Snippet
- Countdown timer — auto-submits on expiry
- View results after grading is complete

**Executives:**
- Create/manage exams through full lifecycle: draft → published → active → closed
- Add questions manually or bulk import via JSON
- Set duration, pass mark, certificate eligibility, availability window
- View all attempts, scores, and individual answer breakdowns

**Grading:**
- MCQ + True/False: instant auto-grade on submission
- Short Answer: sent to AI grading queue when executive triggers "Run AI Grading"
- Code Snippet: manual grading queue — executive reads and awards marks

**AI Grading (Short Answer):**
- HTTP call to Anthropic API — model: `claude-sonnet-4-20250514`
- Prompt includes: question text, expected answer, student response, max marks
- Response: score awarded (int) + reasoning (string)
- Results written to `exam_answers.ai_score` and `exam_answers.ai_feedback`
- Executive reviews AI decisions in a grading UI, can override before finalizing
- Only fires when internet is available — no offline requirement

**Results:**
- Score, pass/fail, per-question breakdown shown to member
- Points awarded to gamification on pass
- Certificate auto-generated if exam is marked `is_certificate_eligible`

---

### Module 7 — Events & Sessions
**Status: Not implemented**

Features:

**Guests:** Public events listing with details

**Members:**
- Browse and register for events
- Check-in via unique code on event day
- View post-event materials (slides, recordings links)
- Submit post-event feedback and rating

**Executives:**
- Create and manage events through full lifecycle
- Set capacity, venue or online meeting link, public/members-only visibility
- Mark attendance manually or via check-in code
- Upload post-event materials
- View attendance report per event
- Toggle certificate eligibility per event
- Attendance triggers points in gamification

---

### Module 8 — Research & Projects
**Status: Not implemented**

Features:

**Members:**
- Browse active and completed projects
- Request to join a project (executive/lead approves)
- View project timeline and updates
- Post updates if a project member

**Executives:**
- Create projects: title, type, visibility, GitHub, demo URL, team
- Add/remove members, assign roles (lead/contributor/reviewer)
- Post project updates
- Mark projects as completed or archived

**Public:**
- Completed public projects shown on landing page as club portfolio

---

### Module 9 — Learning & Training
**Status: Not implemented**

**Structured Learning Paths:**
- Paths → Modules → Lessons hierarchy
- Lesson types: text (markdown rendered), video (YouTube embed), quiz (MCQ questions)
- Progress tracking: lesson-level checkmarks, module completion, path completion percentage
- Path completion awards points + optional certificate
- Difficulty: Beginner / Intermediate / Advanced
- Categories: Web Security, Network Security, Cryptography, Forensics, OSINT, General IT

**Resource Library:**
- Upload files or link external resources: writeups, tools, cheatsheets, articles, videos
- Tags and category filtering
- Download count tracking
- Public resources visible to guests
- Members submit resources → go to executive approval queue before going public

---

### Module 10 — Notifications & Announcements
**Status: Not implemented**

**In-app notifications:**
- Bell icon with unread count in nav
- Types: exam published, event reminder, CTF starts, application approved/rejected, badge earned, points awarded, new announcement, writeup approved

**Announcements:**
- Executives create announcements with type (info/warning/danger) and target (all/members/executives)
- Expiry date — auto-hides after expiry
- Shown as banner or in a dedicated announcements page

**Email (SMTP):**
- Application approved/rejected
- Exam result published
- Event reminder (24hr before)
- Certificate issued
- Welcome email on membership approval

**SMS (Africa's Talking):**
- Urgent announcements
- Exam starting soon (1hr before window opens)
- Event day reminder

---

### Module 11 — Certificate System
**Status: Not implemented**

Features:
- Auto-generated on: exam pass (if eligible), learning path completion (if eligible), event attendance (if eligible), CTF placement, membership approval
- Unique `certificate_number` (format: `CSIC-YYYY-XXXXXX`)
- PDF generated via `barryvdh/laravel-dompdf` with club branding
- QR code embedded on PDF linking to public verification URL
- Public verification page: `/verify/{certificate_number}` — shows certificate details without requiring login
- Member downloads PDF from their profile
- Executives can revoke certificates (`is_revoked = true`) — verification page shows revoked status

---

### Module 12 — Executive Dashboard & Admin
**Status: Not implemented**

Features:

**Overview:**
- Club stats: total members, active this month, pending applications, upcoming events, open exams
- Recent activity feed
- Quick action buttons

**Member management:**
- View all members, filter by status/rank/year/course
- Suspend or reactivate accounts
- Manually award points or badges with reason
- View full member profile and activity history

**Application management:**
- Pending applications queue
- Approve or reject with reason
- Bulk approve

**Settings:**
- Point values per action (editable table)
- Rank tier definitions (name, min/max XP, icon, color)
- Badge conditions
- AI grading toggle (enable/disable per exam)
- SMS/email toggle per notification type
- Certificate template settings

---

## Build Phases (GSD Roadmap)

| Phase | Module | Notes |
|---|---|---|
| 1 | Verify & patch Auth + Landing | Scan existing code, fill gaps only |
| 2 | Verify & patch Member Application | Scan existing code, fill gaps only |
| 3 | Member Dashboard + Profile | Build fresh |
| 4 | Gamification | Build fresh |
| 5 | Events | Build fresh |
| 6 | CTF Management | Build fresh |
| 7 | Exams & Assessments | Build fresh |
| 8 | Learning & Training | Build fresh |
| 9 | Research & Projects | Build fresh |
| 10 | Notifications & Announcements | Build fresh |
| 11 | Certificate System | Build fresh |
| 12 | Executive Dashboard & Settings | Build fresh |

---

## External Services & Integration Notes

| Service | Package | Purpose |
|---|---|---|
| Africa's Talking | `africastalking/africastalking` | SMS notifications |
| Anthropic API | HTTP (no SDK) — `claude-sonnet-4-20250514` | AI exam grading |
| DomPDF | `barryvdh/laravel-dompdf` | PDF certificate generation |
| Spatie Media Library | `spatie/laravel-medialibrary` | Avatars, resource files, certificates |
| Spatie Permissions | `spatie/laravel-permission` v6 | Roles and permissions |
| SMTP | Laravel Mail | Email notifications |

---

## General GSD Rules for This Project

- Use Livewire 4 component syntax — `#[Computed]` attributes, `#[On]` event listeners, `$this->dispatch()` not `$this->emit()`
- Use Preline 3.x for all UI — no Flux components anywhere
- All modals follow the Preline overlay pattern with Alpine.js — Livewire stores state, Alpine triggers the visual open/close
- All file uploads go through Spatie Media Library — no manual `Storage::put()` for user-facing files
- Roles are checked via `$user->hasRole()` and permissions via `$user->can()` — no manual role string comparisons in blade
- Every point award must go through a single `PointService::award($user, $points, $source)` method — never write directly to the `points` table from controllers or components
- Queue all emails and SMS — never send synchronously in a request cycle
