# PROJECT.md — SLAU CSIC Event Enhancements

## What This Is

Enhance the existing SLAU CSIC club management system's event module with: RSVP management, improved calendar views, recurring events, and event organization with categories/filtering/search.

## Why This Matters

- Current event system lacks RSVP tracking — can't manage attendee capacity
- No calendar view for members to see upcoming events 
- Manual event creation for recurring club meetings
- Events not organized — no way to filter by type/club

## Expected Outcomes

1. Members can RSVP to events with capacity limits
2. Interactive calendar showing all events
3. Recurring events auto-generate future instances
4. Events organized by category with filtering

---

## Context

### Brownfield Project

Existing codebase already has:
- Event creation/editing (CreateEvent, EditEvent)
- Event display (EventDetails, EventCards, EventCalendar, MyEvents)
- Event registration for events

Existing features:
- Event model with title, description, date, location
- Public event browsing
- Basic attendee tracking

### Tech Stack

- Laravel 12, Livewire 3
- PHP 8.4
- MySQL database
- Tailwind CSS

---

## Requirements

### Validated

- ✓ Event creation and editing — existing (pre-v1.0)
- ✓ Event display pages — existing (pre-v1.0)
- ✓ Basic attendee tracking — existing (pre-v1.0)
- ✓ Calendar component exists — existing (pre-v1.0)
- ✓ Event categories — v1.0
- ✓ Event filtering and search — v1.0
- ✓ RSVP system with capacity limits — v1.0
- ✓ Attendee management tools — v1.0
- ✓ Recurring event support — v1.0

### Active

- Question Bank module integration — v1.1

### Out of Scope

- Event notifications (email/push) — defer to v2
- Event RSVP waitlists — defer to v2
- Event collaboration/co-hosting — defer to v2
- Event check-in system — defer to v2
- Monthly recurrence patterns — defer to v2
- iCal export of events — defer to v2

---

## Current Milestone: v1.1 Question Bank Module

**Goal:** Integrate standalone Question Bank module into SLAU CSIC app for creating, managing, and exporting questions

**Target features:**
- Question Bank with MCQ, True/False, Short Answer, Code Snippet types
- Question CRUD (Create, Read, Update, Delete)
- JSON export for ExamShield import
- Admin-only access

---

## Current State (v1.0 Shipped)

**Milestone:** v1.0 Event Enhancements MVP — COMPLETED 2026-04-25

### What Was Built

- **Event Categories**: Category badges on cards, color coding in calendar, pill toggle filters
- **RSVP System**: Capacity limits with hard stop, X/Y spots display, confirmation toasts
- **Attendee Management**: Admin attendee list view, cancel registrations, user self-cancel
- **Calendar Views**: Month/week/day views with navigation (already existed, verified)
- **Recurring Events**: Weekly recurrence, instance generation, series editing

### New Files Created

- `app/Services/RecurrenceGenerator.php` - Recurring event instance generation
- `app/Console/Commands/GenerateRecurringEvents.php` - Daily scheduled command
- `app/Livewire/Admin/EventAttendees.php` - Admin attendee management
- Database migrations: rsvp_status, is_recurring, parent_event_id, cancelled_at

---

## Key Decisions

| Decision | Rationale | Outcome |
|----------|---------|---------|
| RSVP vs registration | Events need RSVP for capacity control | ✓ RSVP with yes/no - implemented |
| Calendar library | Already using FullCalendar | ✓ Continue with FullCalendar |
| Recurring pattern | Weekly club meetings common | ✓ Weekly patterns - implemented |
| Category storage | Use existing type field | ✓ No new columns needed |
| Filter UX | Pill toggles for categories | ✓ User-friendly filtering |

---

## Technical Notes

- Event model already has `max_participants`, `is_full`, `registered_count` attributes
- EventRegistration model extended with rsvp_status column
- RecurrenceGenerator uses hybrid approach (3 months ahead)
- Calendar already supports month/week/day views via FullCalendar

---

## Next Milestone Goals

To be defined via `/gsd-new-milestone`. Potential areas:
- Event notifications (email/push)
- Event waitlists
- Advanced recurrence (monthly patterns)
- iCal export
- Event check-in system

---

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd-complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---

*Last updated: 2026-04-27 during v1.1 milestone*