# Phase 6: Question Bank Module - Context

**Gathered:** 2026-04-27
**Status:** Ready for planning

<domain>
## Phase Boundary

Integrate the standalone Question Bank module from `/online/` into the main SLAU CSIC application. Admin users can create, edit, delete, and export questions with support for MCQ, True/False, Short Answer, and Code Snippet types.

</domain>

<decisions>
## Implementation Decisions

### UI Layout
- **D-01:** Question list displayed as **cards** (not table) — provides better visual hierarchy for question preview

### Question Editor
- **D-02:** Single form with **conditional fields** — type selection shows/hides relevant fields (options for MCQ, code block for Code Snippet)

### Export
- **D-03:** Export as **download button** — generates JSON file for download, simpler than inline preview

### Code Snippets
- **D-04:** Syntax-highlighted editor using **Prism.js** — code block textarea with language dropdown and live syntax highlighting

### Agent's Discretion
- Pagination style (number-based vs infinite scroll)
- Toast notifications for save/delete confirmation
- Search implementation details

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Implementation Guide
- `online/IMPLEMENTATION-GUIDE.md` — Original standalone module specification
- `online/app/Models/QuestionBankQuestion.php` — Existing question model
- `online/app/Models/QuestionBankOption.php` — Existing option model
- `online/app/Livewire/QuestionBank/Index.php` — Existing index component (to be adapted)
- `online/app/Livewire/QuestionBank/Create.php` — Existing create component
- `online/app/Livewire/QuestionBank/Edit.php` — Existing edit component
- `online/app/Http/Controllers/QuestionBankExportController.php` — Export controller

### Project Context
- `.planning/PROJECT.md` — Project goals and current milestone
- `.planning/REQUIREMENTS.md` — v1.1 requirements (QB, QT, EXP, PERM categories)
- `.planning/research/SUMMARY.md` — Domain research findings

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `online/app/Models/QuestionBankQuestion.php` — Base model with toExamShieldFormat() method
- `online/app/Models/QuestionBankOption.php` — Option model
- Prism.js (via preline) — Already installed for syntax highlighting

### Established Patterns
- Admin-only access via role middleware (like EventAttendees)
- Livewire components in `app/Livewire/QuestionBank/`
- Filament Actions for admin operations

### Integration Points
- Routes: Add to `routes/web.php` under auth middleware
- Navigation: Add link in admin sidebar
- Models: Move from `online/app/Models/` to `app/Models/`
- Components: Move from `online/app/Livewire/QuestionBank/` to `app/Livewire/QuestionBank/`

</code_context>

<specifics>
## Specific Ideas

- Use existing `QuestionBankQuestion::toExamShieldFormat()` for JSON export
- Prism.js via preline already installed — use for syntax highlighting
- Soft delete already implemented in base model
- Admin role check via Spatie (same as other admin features)

</specifics>

<deferred>
## Deferred Ideas

None — discussion stayed within phase scope

</deferred>

---

*Phase: 06-question-bank-module*
*Context gathered: 2026-04-27*