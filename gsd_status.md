# SLAU CSIC Event Enhancements — Status

## Objective
- Eliminated `MemberLayout.tsx` by converting all remaining Inertia member pages to Livewire, then cleaned up orphaned files

## Completed
- **Created 5 Livewire components + Blade views**: `TeacherPortfolios`, `ElectionVoting`, `ElectionNominations`, `ExamTake`, `ExamResult`
- **Updated routes** — GET routes for all converted pages point to Livewire; POST routes kept for backward compatibility
- **Deleted 32 orphaned Inertia page files** no longer referenced by any route
- **Deleted 17 dead controllers** no longer referenced by any route
- **Deleted `MemberLayout.tsx`** — fully eliminated from the codebase
- **Deleted empty directories**: `elections/`, `attendance/`, `teaching/admin/`, `exams/` under `resources/js/pages/`
- **Fixed bugs**: missing `]` bracket in `election-nominations.blade.php`; `bool`-typed properties in `EventEdit` needed `?? false`
- **Tests updated**: 9 `assertInertia` calls in `ElectionFeatureTest.php` → `Livewire::test()`; 2 tests in `InertiaPagesTest.php` for `EventEdit`
- **Test results**: ExamsTest 29/29 PASS, InertiaPagesTest 36/36 PASS, ElectionFeatureTest 76/76 PASS (all 3 at 100%)
- **`vendor/bin/pint --dirty`**: PASS, 543 files clean

## Remaining Inertia Pages (not importing MemberLayout)
9 Inertia pages still exist in `resources/js/pages/` but none reference `MemberLayout`:
- `auth/Login.tsx`, `auth/Register.tsx`, `auth/VerifyEmail.tsx`
- `events/Show.tsx`
- `public/Home.tsx`, `public/About.tsx`, `public/Contact.tsx`, `public/Team.tsx`, `public/Projects.tsx`
- `members/Show.tsx`

These are standalone Inertia pages with their own layouts — no blocker to cleaning up the old system.

## Pre-existing Failures (unrelated)
- `AssignmentAuthorizationTest` — 3 tests fail (404 vs expected 403/200 on `/admin/assignments`)
- `TreasurerDashboardTest`, `EventModelTest`, `EventFeedbackTest`, etc. — 25 other pre-existing failures in Filament admin tests (not related to conversions)
