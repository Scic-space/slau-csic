# Project Research Summary

**Project:** SLAU CSIC Event Enhancements
**Domain:** Question/Quiz Management in Laravel (Question Bank Module)
**Researched:** 2026-04-27
**Confidence:** HIGH

## Executive Summary

A Question Bank module **already exists** in this codebase, implemented using standard Laravel 12 + Livewire 3 patterns with no external dependencies beyond the core framework. The module supports multiple question types (MCQ, true/false, coding), option management with correct answer marking, code block support with syntax highlighting, JSON export, and soft deletes. No new stack additions are required.

For SLAU CSIC's club management system, this Question Bank module enables certification programs, member assessments, training evaluations, and knowledge checks. The existing implementation provides solid table stakes functionality, but critical architectural pitfalls exist around question type flexibility (single `is_correct` boolean breaks multi-select questions), missing taxonomy (no categories/topics), hardcoded type strings, and soft-delete scoping that could orphan questions in published exams.

**Recommended approach:** Build out from the existing foundation with a phased strategy that addresses core data model issues first, then quiz functionality, then advanced features. Avoid the temptation to add bulk import before the taxonomy foundation is laid.

## Key Findings

### Recommended Stack

The existing implementation uses only Laravel and Livewire — no additional packages needed. Existing technologies already in the codebase handle all required functionality.

**Core technologies:**
- **Laravel 12** — Core framework (existing)
- **Livewire 3** — Dynamic UI components (existing, handles form state and reactivity)
- **Eloquent ORM** — Database models (existing, no package required for questions)
- **Tailwind CSS 4** — Styling (existing)
- **Spatie Permissions** — Role-based access control (existing in stack)
- **Spatie Activitylog** — Audit trails (existing in stack, can extend to questions)

**Optional enhancements available in existing stack:**
- `barryvdh/laravel-dompdf` (v3.1) — PDF export for question papers
- `maatwebsite/excel` (v3.1) — Excel import/export
- `prismjs` (v1.30) — Syntax highlighting for code questions

### Expected Features

**Must have (table stakes):**
- **Question CRUD** — Create, read, update, delete questions (core — without this, nothing works)
- **Question Categories** — Organize questions by subject/topic (hierarchical structure)
- **Question Types** — MCQ, True/False, Fill-in-Blank are essential
- **Question Bank View** — Central library with search and filter
- **Quiz Creation** — Build assessments from question bank
- **Quiz Taking Interface** — Student-facing quiz experience
- **Auto-Grading** — Automatic scoring for objective questions
- **Results/Scores** — Basic score display for students

**Should have (competitive):**
- **Question Tags** — Flexible cross-cutting categorization beyond categories
- **Difficulty Levels** — Easy/Medium/Hard tagging for balanced assessments
- **Randomized Questions** — Different question sets per attempt
- **Question Pool Quiz** — Pull X random questions from category
- **Bulk Import** — Import questions from CSV/Excel (high complexity)
- **Per-Question Analytics** — Correct/incorrect rates per question

**Defer (v2+):**
- Essay Questions (requires manual grading)
- Advanced Question Types (Matching, Ordering)
- Proctoring/Video Recording
- Full Gradebook Integration

### Architecture Approach

The module follows established SLAU CSIC patterns: Eloquent models in `app/Models/`, Livewire components for user-facing pages, and Filament Actions for admin management. The architecture mirrors the existing Event system with model-scoped business logic, relationship-based data access, and Filament-powered admin UI.

**Major components:**
1. **Quiz** — Core quiz/exam entity with metadata, timing, visibility, status workflow (draft → published → archived)
2. **QuizQuestion** — Individual question with answer validation, point weighting, explanation storage
3. **QuizQuestionOption** — Answer options for multiple choice, supports multiple correct answers
4. **QuizAttempt** — User quiz attempts with scoring, pass/fail determination, timeout handling
5. **QuizAnswer** — Individual answer per question per attempt, validates against correct answer

### Critical Pitfalls

1. **`is_correct` as single boolean** — Breaks multi-select questions and free-response. Use `correct_answer` JSON on question model instead.

2. **Hardcoded question type strings** — Adding new types requires migrations and scattered string matching. Create a `QuestionType` enum.

3. **No topic/category taxonomy** — Questions are a flat list, filtering requires fragile `LIKE` queries. Add `question_categories` table with self-referential hierarchy.

4. **Soft-deleting orphans exam references** — Deleted questions still appear in published exams. Always scope exam queries to `withoutTrashed()`.

5. **No bulk import validation** — Malformed rows fail silently with no error reporting. Return detailed import report with row-level errors.

6. **No caching on question bank queries** — N+1 queries with 1000+ questions. Use query caching with cache tags.

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: Core Question Data Model
**Rationale:** The existing foundation has critical schema issues that will break multi-select questions and exam integrity. Fix the data model before building features on top.

**Delivers:**
- Question type enum with all supported types
- `correct_answer` field on questions (flexible per type) instead of single boolean
- Question categories with taxonomy (parent/child hierarchy)
- Tags relationship (Spatie laravel-tags)
- Indices on `type`, `category_id`, `user_id`

**Addresses:** Features from FEATURES.md — Categories, Question Types
**Avoids:** Pitfalls 1 (is_correct schema), 2 (hardcoded types), 3 (no taxonomy)

### Phase 2: Question Management UI
**Rationale:** With data model fixed, build the admin interface for managing questions, options, and categories.

**Delivers:**
- CRUD Livewire components for questions with categories
- Option ordering UI (drag-and-drop)
- Question cloning action
- Category management
- Search/filter with proper taxonomy queries
- Query optimization with eager loading and caching

**Addresses:** Features from FEATURES.md — Question CRUD, Question Bank View
**Avoids:** Pitfalls 5 (option ordering), 7 (caching)

### Phase 3: Quiz Functionality
**Rationale:** Questions are organized; now build the quiz system that uses them.

**Delivers:**
- Quiz model and migrations
- Quiz creation UI (manual question selection)
- Quiz taking interface (Livewire)
- Question shuffling
- Auto-grading engine (server-side)
- Results display
- Time limit enforcement with server validation

**Addresses:** Features from FEATURES.md — Quiz Creation, Quiz Taking, Auto-Grading, Results
**Avoids:** Pitfall 4 (soft-delete orphaning), client-side scoring anti-pattern

### Phase 4: Quiz Enhancements
**Rationale:** Core quiz loop complete — add differentiators.

**Delivers:**
- Question pool quizzes (random X from category)
- Attempt limits
- Negative marking
- Hint system
- Per-question analytics
- Explanation/feedback post-submission

**Addresses:** Features from FEATURES.md — Differentiators
**Avoids:** Performance traps with proper indexing

### Phase 5: Advanced Features (Optional)
**Rationale:** Only if justified by usage patterns.

**Delivers:**
- Bulk import with validation and error reporting
- Question versioning
- Export functionality (GIFT, CSV, JSON)

### Phase Ordering Rationale

- **Data model first** — Schema issues cascade. You cannot build working quiz functionality on a broken foundation.
- **Categories before questions** — Questions need taxonomy to be organized. Defer until Phase 1.
- **Quiz after question management** — Cannot create quizzes without questions organized in categories.
- **Enhancements last** — Randomized questions, pools, and analytics all depend on having sufficient question volume and organized taxonomy first.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 4 (Quiz Enhancements):** Question randomization algorithms need validation for performance at scale
- **Phase 5 (Advanced):** Bulk import parsing edge cases, GIFT format compatibility

Phases with standard patterns (skip research-phase):
- **Phase 1:** Well-understood Laravel patterns
- **Phase 2:** Follows existing Event/Livewire conventions
- **Phase 3:** Established quiz-taking patterns

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | Existing codebase with verified implementations |
| Features | HIGH | Industry-standard patterns from Canvas/Moodle/Quiz documentation |
| Architecture | HIGH | Follows existing SLAU CSIC patterns (Event model structure) |
| Pitfalls | MEDIUM | Community-sourced patterns, partial existing implementation |

**Overall confidence:** HIGH

### Gaps to Address

- **Existing `/online/` code integration:** The research notes a partial implementation exists. Need to verify current state during Phase 1 to avoid duplicating or breaking existing code.
- **Fill-in-Blank implementation:** This question type was mentioned in FEATURES.md but is not in existing models. Need to confirm grading approach during Phase 1.
- **Permissions integration:** How existing Spatie permissions map to question CRUD (create/edit/delete/view).

## Sources

### Primary (HIGH confidence)
- Existing codebase — `/app/Models/QuestionBankQuestion.php`, `/app/Models/QuestionBankOption.php`
- Existing codebase — `/app/Livewire/QuestionBank/` components
- Existing migrations — `database/migrations/2024_01_01_000001_create_question_bank_questions_table.php`

### Secondary (MEDIUM confidence)
- Canvas LMS Question Bank documentation (2026)
- Moodle Question Bank documentation (2025)
- Laravel Quiz community projects — `jaygaha/laravel-ai-quiz-engine`, `harishdurga/laravel-quiz`

### Tertiary (LOW confidence)
- Community GitHub projects — patterns need validation against production usage

---
*Research completed: 2026-04-27*
*Ready for roadmap: yes*