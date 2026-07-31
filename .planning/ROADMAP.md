# ROADMAP.md — v1.1 Question Bank Module

## Overview

**Milestone:** v1.1 Question Bank Module
**Goal:** Integrate standalone Question Bank module into SLAU CSIC app for creating, managing, and exporting questions
**Granularity:** Standard
**Phases:** 1

---

## Phases

- [x] **Phase 6: Question Bank Module** - Complete question management with CRUD, types, and JSON export

---

## Phase Details

### Phase 6: Question Bank Module

**Goal:** Admin users can fully manage a question bank with multiple question types and JSON export capability

**Depends on:** Phase 5 (v1.0 last phase)

**Requirements:** QB-01, QB-02, QB-03, QB-04, QB-05, QT-01, QT-02, QT-03, QT-04, QT-05, EXP-01, EXP-02, EXP-03, PERM-01, PERM-02

**Success Criteria** (what must be TRUE):
1. Admin can view paginated list of all questions with type and marks display
2. Admin can create questions selecting from MCQ, True/False, Short Answer, Code Snippet types
3. Admin can edit any field of existing questions including type, options, correct answer, marks
4. Admin can soft-delete questions (moves to trash, can restore)
5. Admin can search questions by text content (title, body, options)
6. MCQ questions support 2-6 options with multiple correct answer selection
7. True/False questions have fixed 2 options (True, False)
8. Short Answer questions have no options (manual grading)
9. Code Snippet questions include code block textarea and language dropdown
10. Each question has editable marks/points field (integer)
11. Only admin role users can access question bank pages and operations
12. Non-admin users receive 403 error attempting to access any question bank route
13. Export generates valid JSON file with all question data and options
14. Exported JSON follows ExamShield import format schema

**Plans:** 1 plan (COMPLETED)

Plans:
- [x] 06-01-PLAN.md — Full CRUD, multiple question types, export ✓

**UI hint:** yes

---

## Progress

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 6. Question Bank Module | 1/1 | Complete | 2026-04-27 |

### Phase 7: Gamification - points, badges, ranks, leaderboard

**Goal:** Members earn points from activities, badges unlock automatically on milestones, ranks auto-upgrade, leaderboard ranks members by total points. Admins can manually award points and manage badges.

**Depends on:** Phase 6

**Requirements:** GAM-01, GAM-02, GAM-03, GAM-04, GAM-05, GAM-06, GAM-07, BADGE-01, BADGE-02, BADGE-03, BADGE-04, RANK-01, RANK-02, RANK-03, LEADER-01, LEADER-02, LEADER-03

**Success Criteria** (what must be TRUE):
1. Users earn points from activities (events, CTF completions, teaching sessions)
2. Points transactions are logged in a ledger with reason and reference
3. Badges unlock automatically when criteria thresholds are met
4. Each badge has criteria_type, criteria_value, and points_bonus
5. Ranks upgrade automatically when point thresholds are reached (Silver: 200, Gold: 500, Platinum: 1000)
6. Leaderboard displays top 100 users sorted by total points
7. Leaderboard is cached for 5 minutes to avoid expensive queries
8. Admins can manually award points from the admin panel
9. Admins can view, create, edit, and delete badge definitions
10. Admins can configure rank thresholds
11. Members can view the public leaderboard page
12. Members can see their earned badges on their profile

**Plans:** 1 plan

Plans:
- [x] 07-01-PLAN.md — Points ledger, badges, ranks, leaderboard foundation

### Phase 8: CTF Management - competitions, challenges, submissions, writeups, scoreboard

**Goal:** Admins can create CTF competitions with challenges across categories. Members browse active competitions, solve challenges by submitting flags (hashed and validated), earn points via the gamification system, view the live scoreboard, and submit writeups for solved challenges.

**Depends on:** Phase 7

**Requirements:** CTF-01, CTF-02, CTF-03, CTF-04, CTF-05, CTF-06, CTF-07, CTF-08, CTF-09, CTF-10, CTF-11, CTF-12

**Success Criteria** (what must be TRUE):
1. Admins can create CTF competitions with title, description, start/end dates, status, and visibility
2. Admins can create challenges within competitions with category, difficulty, points, and flag (stored as SHA256 hash)
3. Admins can create and manage challenge categories (Web, Crypto, Forensics, PWN, Reversing, OSINT, Misc)
4. Members can view a list of active, published CTF competitions
5. Members can view competition detail with challenges grouped by category
6. Members can submit flags for challenges
7. Correct flags award points via GamificationService and mark challenge as solved
8. Incorrect flags are rejected with error message; already-solved challenges show "already solved"
9. Each user can only have one successful solve per challenge (unique constraint)
10. Scoreboard displays top users by total points for the competition (correct submissions only)
11. Members can submit writeups for solved challenges (pending review by admin)
12. Admins can view all CTF submissions as an audit log

**Plans:** 1/2 plans executed

Plans:
- [x] 08-01-PLAN.md — CTF foundation: migrations, models, services, admin CRUD
- [ ] 08-02-PLAN.md — CTF member UI: competition pages, flag submission, scoreboard, writeups

### Phase 9: Exams & Assessments - exam creation, question bank, timed tests, AI grading, results

**Goal:** Members can take timed exams with questions from the Question Bank, with auto-grading for MCQ/TF, AI grading for short answers, and certificate eligibility on passing.

**Depends on:** Phase 8

**Requirements:** EXAM-01, EXAM-02, EXAM-03, EXAM-04, EXAM-05, EQ-01, EQ-02, EQ-03, EQ-04, EQ-05, ATT-01, ATT-02, ATT-03, ATT-04, ATT-05, ATT-06, GRAD-01, GRAD-02, GRAD-03, GRAD-04, GRAD-05, RES-01, RES-02, RES-03, RES-04, CERT-01, CERT-02, CERT-03, PERM-EXAM-01, PERM-EXAM-02, PERM-EXAM-03

**Success Criteria** (what must be TRUE):
1. Admin can create exams with title, description, duration, passing score, and status
2. Admin can edit exam details and publish/unpublish exams
3. Admin can add questions from Question Bank to exam with custom marks
4. Admin can reorder and remove questions within an exam
5. Members can view available (published) exams
6. Members can start a timed exam attempt with countdown timer
7. Members can answer all question types (MCQ, True/False, Short Answer, Code Snippet)
8. Exam auto-submits when timer expires
9. MCQ and True/False answers auto-graded immediately
10. Short answer questions sent to AI (OpenAI) for grading
11. Total score calculated and pass/fail determined
12. Members can view their exam results with answer breakdown
13. Admin can view all exam submissions with scores
14. Admin can manually adjust grades if needed
15. Exam pass records certificate eligibility
16. Members can see certificate eligibility status
17. Admin can view all certificate-eligible members per exam
18. All routes protected by appropriate auth and role middleware

**Plans:** 1/5 plans executed

Plans:
- [x] 09-01-PLAN.md — Exam foundation: migrations, models, admin CRUD (Wave 1)
- [ ] 09-02-PLAN.md — Exam questions: add from Question Bank, reorder, custom marks (Wave 1)
- [ ] 09-03-PLAN.md — Exam attempts: timed tests, answering, results display (Wave 2)
- [ ] 09-04-PLAN.md — Grading system: auto-grading, AI grading, admin override (Wave 3, has checkpoint)
- [ ] 09-05-PLAN.md — Certificate eligibility: tracking, member/admin views (Wave 3)

### Phase 10: Learning & Training - learning paths, modules, lessons, progress tracking, resource library

**Goal:** Members can browse, enroll in, and complete training modules with progress tracking. Admins can create and manage training content.
**Requirements**: TBD
**Depends on:** Phase 9
**Plans:** 3 plans

Plans:
- [x] 10-01-PLAN.md — Models + Admin CRUD (Wave 1)
- [x] 10-02-PLAN.md — Member UI: Catalog, Detail, Learning Path (Wave 2)
- [x] 10-03-PLAN.md — Resource Library + Progress Tracking (Wave 2)

### Phase 11: Certificate System - auto-generated certificates, PDF generation, QR verification, public verification page

**Goal:** [To be planned]
**Requirements**: TBD
**Depends on:** Phase 10
**Plans:** 0 plans

Plans:
- [ ] TBD (run /gsd-plan-phase 11 to break down)

### Phase 12: Executive Dashboard - club stats, member management, application queue, settings for points, ranks, badges, notifications

**Goal:** [To be planned]
**Requirements**: TBD
**Depends on:** Phase 11
**Plans:** 0 plans

Plans:
- [ ] TBD (run /gsd-plan-phase 12 to break down)

### Phase 13: Research & Projects - members browse join projects, executives create manage, project timeline updates, public portfolio

**Goal:** [To be planned]
**Requirements**: TBD
**Depends on:** Phase 12
**Plans:** 0 plans

Plans:
- [ ] TBD (run /gsd-plan-phase 13 to break down)

### Phase 14: Notifications & Announcements - in-app notifications, announcements with type and target, email SMTP, SMS Africa Talking

**Goal:** [To be planned]
**Requirements**: TBD
**Depends on:** Phase 13
**Plans:** 0 plans

Plans:
- [ ] TBD (run /gsd-plan-phase 14 to break down)

---

*Generated: 2026-04-27*
*Next: `/gsd-complete-milestone` — milestone complete*