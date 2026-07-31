---
phase: 08
slug: ctf-management-competitions-challenges-submissions-writeups
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-04-28
---

# Phase 8 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest (Laravel Feature + Unit tests) |
| **Config file** | `phpunit.xml` (Laravel default) |
| **Quick run command** | `php artisan test --filter=Ctf` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~30 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter=Ctf`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd-verify-work`:** Full suite must be green
- **Max feedback latency:** 30 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 08-01-01 | 01 | 1 | CTF-01 | T-08-01 | Flag hashed in DB, not plaintext | unit | `test --filter=CtfService_submitFlag_correct` | ✅ | ⬜ pending |
| 08-01-01 | 01 | 1 | CTF-02 | T-08-02 | Points awarded only on correct flag | unit | `test --filter=CtfService_submitFlag_incorrect` | ✅ | ⬜ pending |
| 08-01-01 | 01 | 1 | CTF-03 | T-08-03 | One successful solve per user per challenge | unit | `test --filter=CtfSubmission_uniqueSolve` | ✅ | ⬜ pending |
| 08-01-02 | 01 | 1 | CTF-04 | — | Migration creates all 5 tables | unit | `test --filter=create_ctf_tables` | ✅ | ⬜ pending |
| 08-01-03 | 01 | 1 | CTF-05 | T-08-04 | Admin-only route protection | feature | `test --filter=adminCtfRoutes` | ✅ | ⬜ pending |
| 08-01-04 | 01 | 1 | CTF-06 | T-08-05 | Scoreboard excludes incorrect submissions | unit | `test --filter=CtfScoreboard_correctOnly` | ✅ | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/CtfServiceTest.php` — unit tests for CtfService
- [ ] `tests/Feature/CtfSubmissionTest.php` — submission flow tests
- [ ] `tests/Feature/CtfScoreboardTest.php` — scoreboard tests
- [ ] `tests/Feature/AdminCtfTest.php` — admin CRUD tests

*If none: "Existing infrastructure covers all phase requirements."*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| UI rendering of challenge descriptions | CTF-07 | Markdown rendering needs visual check | Navigate to challenge page, verify description renders with code blocks |
| Scoreboard real-time updates | CTF-08 | Polling/livewire reactivity not testable in unit tests | Submit flag, verify scoreboard updates within 2 seconds |

*If none: "All phase behaviors have automated verification."*

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 30s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending