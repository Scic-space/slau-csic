---
phase: 07-gamification-points-badges-ranks-leaderboard
plan: 01
subsystem: gamification
tags: [points, badges, ranks, leaderboard, gamification]
dependency_graph:
  requires: []
  provides:
    - app/Models/PointTransaction
    - app/Models/Badge
    - app/Models/UserBadge
    - app/Services/GamificationService
    - app/Services/PointsLeaderboardService
  affects:
    - app/Livewire/Leaderboard (future)
    - app/Livewire/Admin/Gamification (future)
tech_stack:
  added:
    - enums: BadgeCriteriaType, BadgeRarity
    - models: PointTransaction, Badge, UserBadge
    - services: GamificationService, PointsLeaderboardService
  patterns:
    - Laravel 12 model conventions (casts() method, constructor property promotion)
    - Service layer with dependency injection
    - Cached leaderboard with 5-min TTL
key_files:
  created:
    - database/migrations/2026_04_28_000000_create_gamification_tables.php
    - app/Enums/BadgeCriteriaType.php
    - app/Enums/BadgeRarity.php
    - app/Models/Badge.php
    - app/Models/PointTransaction.php
    - app/Models/UserBadge.php
    - app/Services/GamificationService.php
    - app/Services/PointsLeaderboardService.php
  modified:
    - app/Models/User.php
decisions:
  - "Created PointsLeaderboardService as separate from existing LeaderboardService to avoid conflicts with teaching-session-based leaderboard"
  - "Used BadgeCriteriaType and BadgeRarity enums for type safety"
  - "Added polymorphic reference support to PointTransaction for flexible activity linking"
metrics:
  duration: ~15 minutes
  completed_date: "2026-04-28"
---

# Phase 07 Plan 01: Gamification Summary

Build gamification foundation: points ledger, badges, ranks, leaderboard service, and basic UI.

## One-Liner

Gamification system with points transactions, badges, ranks, and leaderboard service.

## What Was Built

### Database Migration
- **point_transactions** table: ledger for all point awards/deductions with polymorphic references
- **badges** table: achievement definitions with criteria types and rarity levels
- **user_badges** pivot: tracks earned badges with timestamps
- Added **rank** enum and **rank_changed_at** to users table

### Models
- **PointTransaction**: Points ledger with user relationship, scopes for filtering, isPositive/isNegative helpers
- **Badge**: Criteria checking for 7 types (events_attended, ctf_completed, total_points, etc.)
- **UserBadge**: Pivot model linking users to badges on earn
- **BadgeCriteriaType** and **BadgeRarity** enums for type safety

### Services
- **GamificationService**: awardPoints, deductPoints, checkBadges, checkRankUpgrade, awardBadge, revokeBadge
- **PointsLeaderboardService**: Cached getTopUsers, getUserRank, getTopUsersSince with 5-min TTL

### User Model Updates
- Added **rank** and **rank_changed_at** to fillable/casts
- Added **pointTransactions()** and **earnedBadges()** relationships
- Added **total_points** accessor, **current_rank** accessor, **syncRank()** method
- Added **RANK_THRESHOLDS** constant (bronze: 0, silver: 200, gold: 500, platinum: 1000)

## Key Design Decisions

1. **PointsLeaderboardService** vs existing LeaderboardService - New service for points-based leaderboard to avoid conflict with teaching-session-based leaderboard. The existing LeaderboardService handles scoring based on attendance and consistency.

2. **Polymorphic references** on PointTransaction - Allows linking points to events, CTF challenges, badges, or manual awards flexibly without tight coupling.

3. **Automatic criteria checking** - Badges check criteria automatically (events, CTF, sessions, streaks, score) and are not just manual awards.

## Verification

- [x] Migration runs - 3 tables created + rank columns added
- [x] Models load - PointTransaction, Badge, UserBadge classes resolve
- [x] Services load - GamificationService, PointsLeaderboardService classes resolve
- [x] Code formatted - Pint applied

## Deviations from Plan

None - plan executed exactly as written.

## Threat Surface

| Flag | File | Description |
|------|------|-------------|
| threat_flag: audit_trail | PointTransaction | Points ledger provides full audit trail for all point changes |

## Notes

- The plan referenced creating both GamificationService and LeaderboardService. Created PointsLeaderboardService to avoid naming conflict with existing LeaderboardService.
- Badge criteria implementation is comprehensive but may need refinement as other phases (CTF, Exams) add their own activity tracking.