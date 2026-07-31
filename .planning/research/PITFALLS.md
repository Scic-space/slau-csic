# Pitfalls Research

**Domain:** Laravel Question Bank module integration
**Researched:** 2026-04-27
**Confidence:** MEDIUM

*Note: A partial Question Bank implementation already exists in `/online/` (models, migrations, Livewire components). This research covers pitfalls relevant to integrating and extending that module into the main SLAU CSIC application.*

---

## Critical Pitfalls

### Pitfall 1: Storing `is_correct` as a single boolean on options

**What goes wrong:**
Only one option can be correct — but MCQ questions with multiple correct answers (select-all-that-apply) silently break, and true/fill-in-the-blank questions have no correct answer field at all.

**Why it happens:**
The existing `online/QuestionBankOption` model uses a single `is_correct` boolean. Real exam questions need: multi-select MCQ (multiple `true` options), single-select MCQ (exactly one `true`), and free-response (no correct answer field).

**How to avoid:**
Use a `correct_answer` column on `QuestionBankQuestion` instead, or use `correct_answer` JSON on the question model for flexible types:

```php
// Question model — flexible per type
protected $casts = [
    'correct_answer' => 'array', // ['A', 'C'] for multi-select, 'B' for single
];
```

For option-level correctness, consider a nullable `is_correct` on options plus a `correct_answer` field on the question.

**Warning signs:**
- Questions where users expect multiple correct answers save with data loss
- Grading logic has hardcoded `where('is_correct', true)->first()`
- `fill_in_blank` type questions have no storage for the expected answer

**Phase to address:**
Core data model phase — question type handling.

---

### Pitfall 2: Hardcoded question type strings

**What goes wrong:**
`type` column stores raw strings (`'multiple_choice'`, `'true_false'`, `'fill_in_blank'`). Adding a new type requires database migrations and scattered string matching throughout the codebase.

**Why it happens:**
No enum or constants for question types. Searching for `'multiple_choice'` yields dozens of hardcoded matches.

**How to avoid:**
Create a `QuestionType` enum (PHP 8.1+ backed by strings):

```php
enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case FillInBlank = 'fill_in_blank';
    case ShortAnswer = 'short_answer';
    case CodeQuestion = 'code_question';
}
```

Use the enum everywhere — casts, factories, Livewire components, grading logic.

**Warning signs:**
- `whereIn('type', ['multiple_choice', 'single_choice'])` queries scattered across components
- New question type requires migration and multiple file edits
- No type validation at form submission

**Phase to address:**
Core data model phase — question type enum.

---

### Pitfall 3: No topic/category taxonomy for questions

**What goes wrong:**
Questions are a flat list. Filtering by topic, subject, or difficulty requires complex `LIKE` queries on `question_text`, which is slow and fragile. Reusing questions across multiple exams/assessments is impossible.

**Why it happens:**
The existing `online` migration has no taxonomy tables. Questions exist in isolation with no `category_id`, `topic_id`, or `tags` relation.

**How to avoid:**
Add a `categories` table with self-referential hierarchy (topics can have subtopics):

```php
// Migration
Schema::create('question_categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')->nullable()->constrained('question_categories');
    $table->string('name');
    $table->string('slug');
    $table->timestamps();
});

// QuestionBankQuestion
public function category(): BelongsTo
{
    return $this->belongsTo(QuestionCategory::class);
}

public function tags(): BelongsToMany(Tag::class);
```

**Warning signs:**
- Questions displayed in a flat paginated list with no grouping
- Search relies on `LIKE %text%` across all questions
- Reusing a question requires duplicating it (not linking it)

**Phase to address:**
Core data model phase — question taxonomy.

---

### Pitfall 4: Soft-deleting questions orphans exam references

**What goes wrong:**
A soft-deleted question still appears in published exams. Grading fails silently or serves the wrong answer to students. The question bank list hides it but exam-taking code does not filter soft deletes.

**Why it happens:**
The existing `online/QuestionBankQuestion` uses `SoftDeletes`, but exam/assessment queries don't scope to `withoutTrashed()`. Students see questions that admins think are deleted.

**How to avoid:**
Always scope exam-taking queries explicitly:

```php
// In exam-taking Livewire component
$questions = $exam->questions()->withoutTrashed()->get();
```

Consider a `status` field (`draft`, `published`, `archived`) as well — soft delete is for data recovery, not workflow state.

**Warning signs:**
- Deleted questions still appear in exam preview or student view
- `SoftDeletes` trait used but `withoutTrashed()` not called in exam-taking code
- No distinction between "hidden from bank" and "removed from exams"

**Phase to address:**
Exam/assessment integration phase — data isolation.

---

### Pitfall 5: Question order managed by `order` column but no reordering UX

**What goes wrong:**
Options have an `order` column but no UI for drag-and-drop or explicit ordering. Options are created in random order and displayed arbitrarily. Same for questions in exams.

**Why it happens:**
The `online/QuestionBankOption` model has an `order` column, but the Livewire create/edit components have no ordering controls. Options save in creation order.

**How to avoid:**
Add a `position` or `order` column with a drag-and-drop UI (Livewire + Alpine sort plugin). Use Spatie's `OrderableTrait` or a simple `order` integer with `saving` event to recalculate:

```php
protected static function boot()
{
    parent::boot();

    static::saving(function ($option) {
        if (empty($option->order)) {
            $option->order = $option->where('question_id', $option->question_id)->max('order') + 1;
        }
    });
}
```

**Warning signs:**
- Options appear in random order between page loads
- Drag-and-drop reordering is requested after initial build
- `orderBy('order')` in queries but no UI to change order

**Phase to address:**
Question management phase — option ordering UI.

---

### Pitfall 6: No bulk import validation or error reporting

**What goes wrong:**
CSV bulk import skips malformed rows silently. Admins upload 500 questions, don't realize 47 failed, and wonder why counts don't match. No feedback on which rows failed or why.

**Why it happens:**
Bulk import is implemented as a fire-and-forget file upload with no row-level validation feedback. The `jaygaha/laravel-ai-quiz-engine` reference project notes this explicitly.

**How to avoid:**
Return a detailed import report:

```php
// Report structure
return [
    'total_rows' => 500,
    'successful' => 453,
    'failed' => 47,
    'errors' => [
        ['row' => 12, 'reason' => 'Missing required field: question_text'],
        ['row' => 34, 'reason' => 'Invalid type: expected one of...'],
    ],
];
```

Store failed imports as a downloadable error CSV. Use a queued job for large imports with status tracking.

**Warning signs:**
- Bulk import accepts any CSV format without validation
- No success/failure count returned to admin after import
- No way to see which specific rows failed

**Phase to address:**
Question management phase — bulk import with validation.

---

### Pitfall 7: No caching of question bank queries

**What goes wrong:**
The question bank index paginates 20 questions but runs N+1 queries — each question loads its options, creator, and category separately. With 1,000+ questions, page load exceeds 2 seconds.

**Why it happens:**
`QuestionBankQuestion::with('options')` is the only eager load. `creator()` and `category()` relations are loaded lazily. No caching of search/filter results.

**How to avoid:**
Use query caching with cache tags for the question bank:

```php
$questions = Cache::tags(['question-bank'])
    ->remember("questions.page.{$page}.{$search}.{$type}", 3600, function () {
        return QuestionBankQuestion::with(['options', 'creator', 'category'])
            ->search($search)
            ->byType($type)
            ->latest()
            ->paginate(20);
    });
```

Invalidate cache on question create/update/delete.

**Warning signs:**
- N+1 queries visible in debug bar on question bank page
- `explain()` on the index query shows >5 joins
- No `select()` limiting columns to what's needed

**Phase to address:**
Question management phase — query optimization.

---

### Pitfall 8: Code block storage without syntax or language validation

**What goes wrong:**
`code_block` and `code_language` columns store raw strings. No validation that `code_language` is a supported language, no syntax highlighting library integration, no escaping for HTML output.

**Why it happens:**
The existing `online` model stores these as plain strings with no validation rules or output handling.

**How to avoid:**
Validate language against an allowlist, sanitize code blocks for HTML output, and use a client-side syntax highlighter (Prism.js is already in the stack via preline):

```php
// In Form Request
Rule::in(['php', 'javascript', 'python', 'sql', 'html', 'css', 'bash', 'json'])

// In Blade — escape and highlight
<pre><code class="language-{{ $question->code_language }}">
    {{ htmlspecialchars($question->code_block) }}
</code></pre>
```

**Warning signs:**
- `code_language` contains arbitrary strings like `"c++"` or `"JS"` (non-standardized)
- Code blocks rendered without HTML escaping (XSS risk)
- No syntax highlighting library integrated for code questions

**Phase to address:**
Core data model phase — code question validation.

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Store `is_correct` on options as single boolean | Simple schema, easy form | Breaks multi-select questions, no free-response support | Never for a real exam system |
| Use raw string `type` column without enum | No migration needed | String matching everywhere, hard to add types | Only in throwaway prototypes |
| No `category_id` on questions — use `LIKE` search | No schema change | Catastrophic performance at 500+ questions | Never |
| Skip SoftDeletes scoping in exam-taking code | "It works for admins" | Students see deleted questions | Never |
| Bulk import with no error reporting | Faster initial build | Admin loses trust when counts don't match | Only for <50 row imports |
| No cache on question bank queries | Simpler code | Slow at scale, DB overload | Only with <100 questions |

---

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Spatie permissions | No permission gates on question CRUD | Add `create questions`, `edit questions`, `delete questions` permissions and gate Livewire actions |
| Livewire file uploads | Uploading question images without proper form encoding | Use `wire:enhance` or multipart form encoding for image uploads in Livewire |
| Existing Events module | Duplicating permission checks manually | Extract a reusable `QuestionBankPolicy` that mirrors `EventPolicy` patterns |
| CSIC members (Users) | Tying `creator_id` to Laravel's default User model | The existing app uses `organizer_id` on Events — follow the same `user_id` pattern consistently |
| Filament admin panel | Building duplicate admin UI in both Filament and Livewire | Prefer Filament Resource pages for CRUD; use Livewire only for public-facing exam taking |

---

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| N+1 on options in question bank list | 20 questions → 21 queries (1 + 20 options) | `with('options')` on all list queries | >50 questions visible |
| N+1 on creator/category in list | Each row loads creator separately | `with(['options', 'creator', 'category'])` | >100 questions |
| Unindexed `type` filter column | Type filter queries scan full table | Add index on `type` column | >500 questions |
| `LIKE %search%` on large question bank | Full table scan, no index use | Add full-text index or use database native search | >1,000 questions |
| Loading all options for exam-taking | Exam with 100 questions loads 400 options | Scope options to exam context only | Large exams |
| No pagination on exam-taking questions | Exam with 500 questions loads all at once | Paginate and lazy-load question detail | Large assessments |

---

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Exposing correct answers to students before submission | Students see answers before completing exam | Never include `is_correct` or `correct_answer` in exam-taking query; only join for grading post-submission |
| No rate limiting on exam submission endpoint | Automated answer scraping or brute-force grading | Apply Laravel throttle: `->middleware('throttle:5,1')` on submission endpoint |
| Storing question answers in plain JSON without encryption | Sensitive exam content readable in DB dump | Use Laravel's `encryption()` cast on `correct_answer` column |
| Allowing any authenticated user to create questions | Non-admin users spam the question bank | Gate CRUD with `can('create questions')` gate, not just `auth()` |
| No audit trail on question changes | Cannot track who modified or deleted questions | Use Spatie's `LogsActivity` trait on `QuestionBankQuestion` (already in stack) |

---

## UX Pitfalls

| Pitfall | User Impact | Better Approach |
|---------|-------------|------------------|
| No question preview before saving | Admins save broken questions and must edit them | Add a "Preview as student" toggle in the create/edit Livewire component |
| Options deleted in-place without confirmation | Accidental option removal with no undo | Use a soft-delete undo pattern (Livewire's `withMeta(['wire:confirm'])`) |
| No "difficulty" or "tags" filtering in bank | Finding relevant questions is manual scroll work | Add chips/filter sidebar for category + difficulty + search |
| Exam results show only score, not per-question review | Students don't know what they got wrong | Post-submission review shows questions, selected answers, correct answers, explanations |
| No "clone question" action | Creating a similar question requires re-entering everything | Add "Clone" action that copies question + options to a new draft |

---

## "Looks Done But Isn't" Checklist

- [ ] **Question creation:** Always has `type`, `question_text`, `marks` validated — verify `explanation` is also saved (students need feedback)
- [ ] **Multiple choice:** Always has at least 2 options — verify `is_correct` count matches question type expectations (1 for single-select, ≥1 for multi-select)
- [ ] **Fill in blank:** Always has a stored `correct_answer` — verify the grading logic handles null answers gracefully
- [ ] **Code questions:** Always has sanitized output — verify `htmlspecialchars()` on `code_block` in Blade to prevent XSS
- [ ] **Question bank list:** Always uses `withoutTrashed()` — verify soft-deleted questions don't appear in any view
- [ ] **Bulk import:** Always validates required fields — verify malformed rows produce a downloadable error report
- [ ] **Exam grading:** Always scopes questions to exam context — verify students can't access other students' questions
- [ ] **Permissions:** Always checks `can()` gates — verify guests cannot create/edit/delete questions

---

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Wrong `is_correct` schema choice | HIGH | Migration to change single boolean → array; rewrite grading logic; update all existing questions |
| Missing question taxonomy | MEDIUM | Add `question_categories` table, write migration, backfill existing questions with `uncategorized` |
| Orphaned exam references after soft delete | HIGH | Add `withoutTrashed()` scoping everywhere; audit all exam-taking queries |
| No bulk import validation | LOW | Add row-level validation and error report; re-run import for failed rows |
| Missing indexes on question bank queries | MEDIUM | Add composite index on `(type, deleted_at)`; add full-text index on `question_text` |

---

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| `is_correct` schema design | Core data model phase | Create questions of each type; verify grading handles multi-select and free-response correctly |
| Hardcoded type strings | Core data model phase | Add `QuestionType` enum; grep for hardcoded type strings (should return zero) |
| Missing taxonomy | Core data model phase | Filter question bank by category; verify questions appear in correct groups |
| Soft delete orphaning | Exam/assessment phase | Soft-delete a question used in a published exam; verify it doesn't appear to students |
| Option ordering UX | Question management phase | Drag-reorder options; reload page; verify order persisted |
| Bulk import validation | Question management phase | Upload CSV with bad rows; verify error report lists every failure with row number |
| N+1 queries | Question management phase | Enable query log; load question bank page; verify ≤5 queries for 20 questions |
| Code block XSS | Code question phase | Inject `<script>` into `code_block`; verify it's escaped in rendered output |
| Correct answer exposure | Exam-taking phase | Inspect exam-taking network responses; verify `correct_answer` never sent to client before submission |

---

## Sources

- Community patterns from: `jaygaha/laravel-ai-quiz-engine` (GitHub, 2026-03) — AI question generation, batch import, server-enforced timers, CSV import validation
- Community patterns from: `AmrYami/assessments` (GitHub, 2025-11) — reusable assessment module with attempt lifecycle, exposure enforcement, token-based activation
- Community patterns from: `harishdurga/laravel-quiz` (GitHub, 2021) — flexible question types, negative marking, topic-based bank filtering
- Community patterns from: `codezelat/laravel-mcq-exam-system` (GitHub, 2025-08) — troubleshooting guide, file upload issues, cache/session handling
- Existing implementation in `/online/` — partial models (`QuestionBankQuestion`, `QuestionBankOption`) with SoftDeletes, basic Livewire Index
- Existing SLAU CSIC patterns — `Event` model conventions (organizer_id, slug boot, casts, relationships), Spatie permission usage, Livewire component patterns
- Laravel production mistakes: `laravel.io/articles/common-laravel-mistakes` — fat controllers, N+1, missing indexes, soft delete scoping, authorization

---

*Pitfalls research for: Laravel Question Bank module integration*
*Researched: 2026-04-27*