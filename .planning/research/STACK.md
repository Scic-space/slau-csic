# Stack Research: Question Bank Module

**Domain:** Question/Quiz Management in Laravel  
**Researched:** 2026-04-27  
**Confidence:** HIGH

---

## Executive Summary

The Question Bank module **already exists** in this codebase. It uses standard Laravel 12 + Livewire 3 patterns with no external dependencies beyond the core framework. The implementation supports multiple question types (MCQ, true/false, coding), option management with correct answer marking, code block support with syntax highlighting, JSON export, and soft deletes.

**No new stack additions are required** — the existing implementation uses vanilla Laravel/Livewire patterns.

---

## Current Implementation

### Technologies Already in Use

| Technology | Version | Purpose | Status |
|------------|---------|---------|--------|
| Laravel | ^12.0 | Core framework | Existing |
| Livewire | ^3.7 | Dynamic UI components | Existing |
| MySQL/SQLite | - | Database | Existing |
| Tailwind CSS | ^4.x | Styling | Existing |
| PHP | ^8.2 | Runtime | Existing |

### Question Bank Models

| Model | Purpose | Relationships |
|-------|---------|---------------|
| `QuestionBankQuestion` | Main question entity | HasMany → `QuestionBankOption`, BelongsTo → `User` |
| `QuestionBankOption` | Answer options for questions | BelongsTo → `QuestionBankQuestion` |

### Current Database Schema

**`question_bank_questions` table:**

- `id` (bigint)
- `user_id` (foreign key → users, nullable, cascade delete)
- `type` (string) — question type (mcq, true_false, coding)
- `question_text` (text)
- `code_block` (text, nullable) — for coding questions
- `code_language` (string, nullable) — programming language
- `marks` (integer, default 1)
- `explanation` (text, nullable) — answer explanation
- `created_at`, `updated_at`
- `deleted_at` (soft deletes)

**`question_bank_options` table:**

- `id` (bigint)
- `question_id` (foreign key → question_bank_questions, cascade delete)
- `option_text` (text)
- `is_correct` (boolean)
- `order` (integer)

### Livewire Components

| Component | Route | Purpose |
|-----------|-------|---------|
| `QuestionBank\Index` | `/question-bank` | List, search, filter, delete |
| `QuestionBank\Create` | `/question-bank/create` | Create new questions |
| `QuestionBank\Edit` | `/question-bank/{question}/edit` | Edit existing questions |

### Controller

| Controller | Method | Purpose |
|------------|--------|---------|
| `QuestionBankExportController` | `export()` | JSON export for external systems |

---

## Question Types Supported

| Type | Storage | Options | Code Block | Notes |
|------|---------|---------|------------|-------|
| `mcq` | question_text | Multiple, one+ correct | Optional | Standard multiple choice |
| `true_false` | question_text | Two options | No | Boolean questions |
| `coding` | question_text | Test cases as options | Required | Code with language |

---

## No New Dependencies Required

The Question Bank module uses **only Laravel and Livewire** — no additional packages needed:

- **No package required** for question management (standard Eloquent)
- **No package required** for search (Livewire query string + Eloquent where)
- **No package required** for export (native JSON response)
- **No package required** for forms (standard Livewire form handling)
- **No package required** for validation (Laravel Form Requests or inline rules)

---

## Potential Enhancements (Optional)

If you want to extend the Question Bank module later, these are optional:

| Enhancement | Package | When to Use |
|-------------|---------|-------------|
| PDF export | `barryvdh/laravel-dompdf` | Already installed (v3.1) |
| Excel import/export | `maatwebsite/excel` | Already installed (v3.1) |
| Syntax highlighting | `prismjs` | Already installed (v1.30) |
| Code execution | Sandboxed runner (custom) | For coding question validation |
| Quiz attempts | New model | Track user attempts |
| Tagging | `spatie/laravel-tags` | Categorize questions |

---

## Integration Points

### With Existing Modules

| Module | Integration | Status |
|--------|-------------|--------|
| Users | `QuestionBankQuestion.user_id` → creator tracking | Implemented |
| Activity Log | Spatie (already on User model) | Can extend |
| Filament Admin | Can add Question Bank resource | Optional |
| Export | JSON format (toExamShieldFormat) | Implemented |

### Routes

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/question-bank', Index::class)->name('question-bank.index');
    Route::get('/question-bank/create', Create::class)->name('question-bank.create');
    Route::get('/question-bank/{question}/edit', Edit::class)->name('question-bank.edit');
    Route::get('/question-bank/export', [QuestionBankExportController::class, 'export'])->name('question-bank.export');
});
```

---

## Security Considerations

Current implementation includes:

- **Auth required** — All routes behind `auth` middleware
- **Soft deletes** — Questions can be restored
- **Creator tracking** — `user_id` foreign key
- **Mass assignment protection** — `$fillable` on models

---

## Testing

| Test Type | Location | Status |
|-----------|----------|--------|
| Unit | `tests/Unit/Models/` | Can add |
| Feature | `tests/Feature/` | Can add |
| Browser | `tests/Browser/` | Optional |

---

## Version Compatibility

| Package | Version | Compatible |
|---------|---------|------------|
| PHP | ^8.2 | ✓ |
| Laravel | ^12.0 | ✓ |
| Livewire | ^3.7 | ✓ |
| Tailwind CSS | ^4.x | ✓ |

---

## Sources

- Existing codebase — `/app/Models/QuestionBankQuestion.php`
- Existing codebase — `/app/Models/QuestionBankOption.php`
- Existing codebase — `/app/Livewire/QuestionBank/`
- Existing codebase — `/database/migrations/2024_01_01_000001_create_question_bank_questions_table.php`

---

*Stack research for: Question Bank Module*  
*Researched: 2026-04-27*