# Phase 8: CTF Management - Context

**Gathered:** 2026-04-28
**Status:** Ready for planning
**Source:** Skipped discuss-phase — using research and requirements only

<decisions>
## Implementation Decisions

No locked decisions from discuss-phase. Planning proceeds from research and established codebase patterns.

### Researcher's Discretion

The researcher made the following design decisions based on codebase patterns:

- Using Livewire components (same as Phase 6 Question Bank, Phase 7 Gamification)
- Using GamificationService for point awards (Phase 7 foundation)
- Using Laravel 12 conventions (constructor property promotion, `$casts()` method)
- Using hashed flags (SHA256) to prevent DB dump flag leaks
- Using unique constraint on `(challenge_id, user_id)` for submissions
- Scoreboard cached 1 minute (faster than general leaderboard's 5 min — CTF needs real-time feel)
- Challenge categories as separate model (not enum — allows admin management)
</decisions>

<deferred>
## Deferred Ideas

None identified in research.
</deferred>