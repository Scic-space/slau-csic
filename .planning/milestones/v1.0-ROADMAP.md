# ROADMAP.md — SLAU CSIC Event Enhancements

## Overview

**Project:** SLAU CSIC Event Enhancements  
**Milestone:** v1  
**Granularity:** Standard (5 phases)

## Phase Summary

- [ ] **Phase 1: Event Categories & Organization** - Categories, color coding, filtering, search
- [x] **Phase 2: RSVP System** - RSVP with capacity limits, confirmation feedback (completed 2026-04-25)
- [ ] **Phase 3: Attendee Management** - Attendee list view, management tools
- [ ] **Phase 4: Calendar Views** - Week/day views, navigation
- [ ] **Phase 5: Recurring Events** - Weekly recurrence, instance generation

## Dependency Flow

```
Phase 1 (Categories)
    ↓
Phase 2 (RSVP) ← depends on Phase 1 (filtering foundation)
    ↓
Phase 3 (Attendee Mgmt) ← depends on Phase 2 (RSVP system)
    ↓
Phase 4 (Calendar Views) ← independent
    ↓
Phase 5 (Recurring Events) ← depends on Phase 2 (RSVP ready)
```

---

## Phase Details

### Phase 1: Event Categories & Organization

**Goal**: Events organized with categories enabling filtering and search

**Depends on**: Nothing (first phase)

**Requirements**: CAT-01, CAT-02, CAT-03, CAT-04

**Success Criteria** (what must be TRUE):
  1. Events display with category assignment and visible category tag
  2. Calendar displays events with category color coding
  3. User can filter event list by category
  4. User can search events by keyword

**Plans**: [ ] 01-01-PLAN.md — Event categories & organization

### Phase 2: RSVP System

**Goal**: Members can RSVP to events with capacity limit enforcement

**Depends on**: Phase 1 (uses category infrastructure)

**Requirements**: RSVP-01, RSVP-02, RSVP-03, RSVP-04

**Success Criteria** (what must be TRUE):
  1. Logged-in user can click RSVP and see confirmation feedback
  2. Event shows "Full" indicator when capacity reached
  3. Event detail page shows "X/Y spots filled" count
  4. User cannot RSVP to full event

**Plans**: [x] 02-01-PLAN.md — RSVP with capacity limits and confirmation

### Phase 3: Attendee Management

**Goal**: Admin can manage attendees per event

**Depends on**: Phase 2 (RSVP system in place)

**Requirements**: ATT-01, ATT-02, ATT-03

**Success Criteria** (what must be TRUE):
  1. Admin can view list of attendees per event
  2. Admin can cancel/modify attendee registrations
  3. User can cancel their own RSVP

**Plans**: [ ] 03-01-PLAN.md — Admin attendee list with management

### Phase 4: Calendar Views

**Goal**: Interactive calendar with multiple views and navigation

**Depends on**: Nothing (can be independent)

**Requirements**: CAL-01, CAL-02, CAL-03, CAL-04

**Success Criteria** (what must be TRUE):
  1. Calendar shows month view with event indicators
  2. Calendar shows week view with time slots
  3. Calendar shows day view for single day
  4. Calendar has navigation and "Today" quick link

**Plans**: TBD

### Phase 5: Recurring Events

**Goal**: Events recurring weekly auto-generate future instances

**Depends on**: Phase 2 (RSVP needs to work on occurrences)

**Requirements**: REC-01, REC-02, REC-03

**Success Criteria** (what must be TRUE):
  1. Admin can create event with weekly recurrence
  2. System generates next occurrence instances
  3. Editing series updates all future occurrences

**Plans**: [x] 05-01-PLAN.md — Recurring events with weekly generation

---

## Coverage

- Phase 1: CAT-01, CAT-02, CAT-03, CAT-04 (4 requirements)
- Phase 2: RSVP-01, RSVP-02, RSVP-03, RSVP-04 (4 requirements)
- Phase 3: ATT-01, ATT-02, ATT-03 (3 requirements)
- Phase 4: CAL-01, CAL-02, CAL-03, CAL-04 (4 requirements)
- Phase 5: REC-01, REC-02, REC-03 (3 requirements)

**Total**: 18/18 requirements mapped ✓

---

## Progress

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Event Categories & Organization | 1/1 | Planning complete | - |
| 2. RSVP System | 1/1 | Complete   | 2026-04-25 |
| 3. Attendee Management | 1/1 | Planning complete | - |
| 4. Calendar Views | 0/1 | Not started | - |
| 5. Recurring Events | 0/1 | Not started | - |

---

*Last updated: 2026-04-25*