# Question Bank — Online System (Standalone)

**Location:** `/online/` directory in ExamShield root
**Purpose:** Standalone question bank that exports JSON compatible with ExamShield import
**Stack:** Plain Tailwind CSS (no component library) + Livewire

This is a **separate module** designed to be copied into the existing online examination platform. It is fully self-contained — its own models, components, controllers, views, routes, and migrations.

---

## Directory Structure

```
online/
├── app/
│   ├── Models/
│   │   ├── QuestionBankQuestion.php
│   │   └── QuestionBankOption.php
│   ├── Livewire/
│   │   └── QuestionBank/
│   │       ├── Index.php
│   │       ├── Create.php
│   │       └── Edit.php
│   └── Http/
│       └── Controllers/
│           └── QuestionBankExportController.php
├── resources/
│   └── views/
│       ├── livewire/
│       │   └── question-bank/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       └── edit.blade.php
│       └── layouts/
│           └── app.blade.php
├── routes/
│   └── web.php
└── database/
    └── migrations/
        ├── 2024_01_01_000001_create_question_bank_questions_table.php
        └── 2024_01_01_000002_create_question_bank_options_table.php
```

---

## Step-by-Step: How to Add to Existing System

### Step 1: Copy Files

Copy the entire `online/` directory into the root of the existing online system. The structure is designed to match a standard Laravel app.

```
cp -r online/* /path/to/online-exam-system/
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

This creates two tables:
- `question_bank_questions`
- `question_bank_options`

### Step 3: Add Routes

Open `routes/web.php` in the existing system and add:

```php
use App\Livewire\QuestionBank\Index;
use App\Livewire\QuestionBank\Create;
use App\Livewire\QuestionBank\Edit;
use App\Http\Controllers\QuestionBankExportController;

// Question Bank routes (requires auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/question-bank', Index::class)->name('question-bank.index');
    Route::get('/question-bank/create', Create::class)->name('question-bank.create');
    Route::get('/question-bank/{question}/edit', Edit::class)->name('question-bank.edit');
    Route::get('/question-bank/export', [QuestionBankExportController::class, 'export'])->name('question-bank.export');
});
```

### Step 4: Link to Navigation

Add a link in the existing system's navigation (sidebar or navbar):

```blade
<a href="{{ route('question-bank.index') }}" class="...">
    Question Bank
</a>
```

### Step 5: Ensure Tailwind is Configured

The views use plain Tailwind CSS classes. Ensure Tailwind is set up:

```bash
# If Tailwind is not installed
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

Add to `tailwind.config.js`:
```js
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
  ],
  theme: { extend: {} },
  plugins: [],
}
```

Add to `resources/css/app.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

Add to your layout's `<head>`:
```html
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
```

Or use the CDN in `resources/views/layouts/app.blade.php`:
```html
<script src="https://cdn.tailwindcss.com"></script>
```

---

## JSON Export Format

The export endpoint (`GET /question-bank/export`) produces JSON in **ExamShield's import format**:

```json
{
  "exam_title": "Question Bank Export",
  "version": 1,
  "questions": [
    {
      "type": "mcq",
      "question_text": "What is 2 + 2?",
      "marks": 5,
      "code_block": null,
      "language": null,
      "options": [
        { "option_text": "3", "is_correct": false },
        { "option_text": "4", "is_correct": true },
        { "option_text": "5", "is_correct": false }
      ]
    },
    {
      "type": "true_false",
      "question_text": "The earth is flat.",
      "marks": 2,
      "code_block": null,
      "language": null,
      "options": [
        { "option_text": "True", "is_correct": false },
        { "option_text": "False", "is_correct": true }
      ]
    },
    {
      "type": "short_answer",
      "question_text": "What is the capital of France?",
      "marks": 5,
      "code_block": null,
      "language": null,
      "options": []
    },
    {
      "type": "code_snippet",
      "question_text": "Fix the bug in this function",
      "marks": 10,
      "code_block": "function add(a, b) {\n    return a - b;\n}",
      "language": "javascript",
      "options": []
    }
  ]
}
```

This JSON can be directly imported into ExamShield at `/admin/exams/{id}/stats`.

---

## Question Types

| Type | Options | Correct Answer |
|------|---------|----------------|
| `mcq` | 2-6 options, checkboxes | Multiple can be correct |
| `true_false` | 2 fixed options | One is correct (radio) |
| `short_answer` | No options | Graded manually |
| `code_snippet` | No options, has code block | Graded manually |

---

## Workflow

1. Admin goes to `/question-bank`
2. Clicks "Create Question" → fills form → saves
3. Repeats for all questions
4. When done, clicks **Export JSON** button
5. Downloads the `.json` file
6. Sends to ExamShield admin
7. ExamShield admin imports at `/admin/exams/{id}/stats`

---

## Key Implementation Notes

- **No exam binding** — questions are exam-agnostic
- **No ordering** — questions are a flat pool; ordering happens during ExamShield import
- **`user_id`** tracks who created each question
- **Soft deletes** — questions can be restored
- **Consistency** — the `QuestionBankQuestion` model's `toExamShieldFormat()` method ensures the JSON always matches what ExamShield's `ImportQuestionsJob` expects