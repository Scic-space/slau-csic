# Phase 6: Question Bank Module - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-04-27
**Phase:** 6-question-bank-module
**Areas discussed:** Layout style, Question editor, Export format, Code snippets

---

## Layout style

| Option | Description | Selected |
|--------|-------------|----------|
| Table | Traditional admin table with rows/columns | |
| Cards | Visual cards with question preview | ✓ |

**User's choice:** Cards — provides better visual hierarchy for question preview
**Notes:** The app uses table-based admin lists, but question bank benefits from card layout for preview

---

## Question editor

| Option | Description | Selected |
|--------|-------------|----------|
| Single form | One form with conditional fields based on type | ✓ |
| Tabs | Separate tabs for each question type | |

**User's choice:** Single form with conditional fields
**Notes:** Type selection shows/hides relevant fields

---

## Export format

| Option | Description | Selected |
|--------|-------------|----------|
| Download button | Generates JSON file for download | ✓ |
| Inline preview | Shows JSON in page | |

**User's choice:** Download button
**Notes:** Simpler implementation

---

## Code snippets

| Option | Description | Selected |
|--------|-------------|----------|
| Plain textarea | Simple textarea for code | |
| Syntax-highlighted | Prism.js editor with language selector | ✓ |

**User's choice:** Syntax-highlighted editor using Prism.js
**Notes:** Prism.js already installed via preline

---

## Agent's Discretion

- Pagination style (number-based vs infinite scroll)
- Toast notifications for save/delete confirmation
- Search implementation details