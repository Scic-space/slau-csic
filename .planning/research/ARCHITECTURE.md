# Architecture Patterns: Question Bank Module Integration

**Domain:** Laravel/Livewire Question Bank (Quiz/Exam) System
**Researched:** 2026-04-27
**Overall confidence:** HIGH

## Executive Summary

Question Bank modules integrate seamlessly with the existing SLAU CSIC Laravel architecture using established patterns: Eloquent models in `app/Models/`, Livewire components for user-facing pages, and Filament Actions for admin management. The module follows the same conventions as the existing Event system with model-scoped business logic, relationship-based data access, and Filament-powered admin UI.

## Integration Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                      ROUTE LAYER                                           │
├─────────────────────────────────────────────────────────────────────────────┤
│  Public:     /quizzes/{quiz:slug}        → QuizDetails (Livewire)            │
│  Auth:       /quizzes/{quiz:slug}/take   → QuizTaking (Livewire)            │
│  Auth:       /my-quizzes                → MyQuizzes (Livewire)            │
│  Admin:      /admin/quizzes             → QuizManagement (Livewire)        │
│  Admin:      /admin/quizzes/{quiz}/questions → QuestionManager (Livewire) │
│  Admin:      /admin/quizzes/{quiz}/results → QuizResults (Livewire)       │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                      LIVEWIRE COMPONENTS                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│  QuizDetails          → Quiz info, question preview                        │
│  QuizTaking          → Active quiz session with answers                  │
│  MyQuizzes           → User's taken/completed quizzes                  │
│  Admin/QuizManagement → CRUD with FilamentActions                         │
│  Admin/QuestionManager → Question CRUD within quiz                     │
│  Admin/QuizResults   → Results analytics                               │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                        MODEL LAYER                                          │
├──────────────────────────────────────────��──────────────────────────────────┤
│  Quiz                  → Core quiz/exam entity                             │
│  QuizQuestion         → Individual questions with answers                   │
│  QuizQuestionOption  → Multiple choice options                            │
│  QuizAttempt        → User quiz attempts with scoring                    │
│  QuizAnswer         → User's answers per attempt                        │
│  User               → Member, quiz taker roles                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                                         │
├─────────────────────────────────────────────────────────────────────────────┤
│  quizzes                   → Core quiz metadata                          │
│  quiz_questions             → Questions with correct answer             │
│  quiz_question_options      → MCQ options                                │
│  quiz_attempts              → User attempts with score                  │
│  quiz_answers               → Individual answers                        │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Component Boundaries

### Quiz Component

**Responsibility:** Core quiz/exam entity with metadata, timing, and visibility
**Communicates With:** QuizQuestion, QuizAttempt, User (creator)
**Key Fields:**
- title, description, slug
- time_limit (minutes, null for untimed)
- passing_score (percentage)
- shuffle_questions, shuffle_options
- show_results (immediate/after/never)
- is_published, status
- creator_id
**Responsibilities:**
- Title, description, type, dates, visibility
- Time limits and scoring thresholds
- Status workflow (draft → published → archived)
- Slug generation on create
- Computed: question_count, attempt_count, average_score

### QuizQuestion Component

**Responsibility:** Individual question with answer validation
**Communicates With:** Quiz, QuizQuestionOption, QuizAnswer
**Key Fields:**
- quiz_id (foreign key)
- question_text (rich text)
- question_type (multiple_choice, true_false, short_answer)
- points (individual question weight)
- explanation (optional explanation for answers)
- order (sorting)
**Responsibilities:**
- Question text and type validation
- Correct answer identification
- Point weighting
- Explanation storage for results

### QuizQuestionOption Component

**Responsibility:** Answer options for multiple choice questions
**Communicates With:** QuizQuestion
**Key Fields:**
- question_id (foreign key)
- option_text
- is_correct (boolean)
- order
**Responsibilities:**
- Store multiple choices
- Mark correct answer(s)
- Support multiple correct answers

### QuizAttempt Component

**Responsibility:** Track user quiz attempts with scoring
**Communicates With:** Quiz, User, QuizAnswer
**Key Fields:**
- quiz_id, user_id
- started_at, completed_at
- score, percentage, passed
- time_spent (seconds)
- status (in_progress, completed, timed_out)
**Responsibilities:**
- Track attempt lifecycle
- Calculate final score
- Determine pass/fail
- Handle timeout scenarios

### QuizAnswer Component

**Responsibility:** Individual answer per question per attempt
**Communicates With:** QuizAttempt, QuizQuestion
**Key Fields:**
- attempt_id, question_id
- selected_option_id (if MCQ)
- text_answer (if short answer)
- is_correct, points_earned
- answered_at
**Responsibilities:**
- Store user response
- Validate against correct answer
- Calculate points

## Data Flow

### Quiz Taking Flow

```
1. User visits /quizzes/{slug}/take (authenticated)
2. QuizTaking mount() checks:
   - Existing in-progress attempt
   - Time limit (if any)
   - Quiz published status
3. User starts quiz:
   - Create QuizAttempt (status=in_progress)
   - Load questions (shuffled if enabled)
4. User answers each question:
   - Save QuizAnswer on selection
   - Update progress indicator
5. User submits / timer expires:
   - Calculate score
   - Mark QuizAttempt completed
   - Show results (if enabled)
```

### Quiz Creation Flow (Admin)

```
1. Admin visits /admin/quizzes
2. Creates Quiz with basic metadata
3. Adds questions one-by-one:
   - Select question type
   - Enter question text
   - Add options (for MCQ)
   - Mark correct answer(s)
   - Set points
4. Admin publishes quiz:
   - Set is_published=true
   - Quiz becomes available
```

### Results Flow

```
1. Admin visits /admin/quizzes/{quiz}/results
2. Loads all attempts for quiz
3. Displays:
   - Total attempts, pass rate
   - Average score
   - Question-by-question analysis
4. User views own results:
   - /my-quizzes shows attempt history
   - Click to see detailed results
```

## Patterns to Follow

### Pattern 1: Livewire Component Organization

**What:** Each feature area has dedicated Livewire component in App\Livewire namespace
**When:** All new quiz features
**Example:**
```php
namespace App\Livewire;

use App\Models\Quiz;
use Livewire\Component;

class QuizDetails extends Component
{
    public Quiz $quiz;

    public function mount(Quiz $quiz)
    {
        $this->quiz = $quiz->load('questions.options');
    }

    #[Route]
    public function render()
    {
        return view('livewire.quiz-details');
    }
}
```

### Pattern 2: Filament Actions in Admin

**What:** Use Filament Actions for CRUD in admin components
**When:** Admin quiz management UI
**Example:**
```php
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

// In QuizManagement component
protected function getQuizFormSchema(): array
{
    return [
        TextInput::make('title')->required(),
        RichEditor::make('description'),
        IntegerInput::make('time_limit')->label('Time limit (minutes)'),
        IntegerInput::make('passing_score')->min(0)->max(100),
        Toggle::make('shuffle_questions'),
        Toggle::make('shuffle_options'),
        Toggle::make('is_published'),
    ];
}
```

### Pattern 3: Notification Dispatch

**What:** Use Livewire dispatch for UI feedback
**When:** Action completion feedback
**Example:**
```php
$this->dispatch('show-notification', message: 'Quiz submitted!', type: 'success');
```

### Pattern 4: Model-Scoped Business Logic

**What:** Business logic in model methods, not controllers
**When:** Quiz scoring, pass/fail calculations
**Example:**
```php
// In Quiz model
public function calculateScore(array $answers): array
{
    $totalPoints = 0;
    $earnedPoints = 0;

    foreach ($this->questions as $question) {
        $totalPoints += $question->points;
        if ($this->isCorrect($question, $answers[$question->id] ?? null)) {
            $earnedPoints += $question->points;
        }
    }

    return [
        'score' => $earnedPoints,
        'total' => $totalPoints,
        'percentage' => $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0,
    ];
}
```

### Pattern 5: Route Model Binding

**What:** Route using quiz:slug for automatic model resolution
**When:** Quiz routes
**Example:**
```php
Route::get('/quizzes/{quiz:slug}', QuizDetails::class);
Route::get('/quizzes/{quiz:slug}/take', QuizTaking::class)->middleware('auth');
```

### Pattern 6: Transactional Scoring

**What:** Wrap scoring in database transaction
**When:** Calculating quiz results
**Example:**
```php
public function submitQuiz(array $answers): QuizAttempt
{
    return DB::transaction(function () use ($answers) {
        $attempt = $this->createAttempt();
        $score = $this->gradeAttempt($attempt, $answers);
        $attempt->update([
            'score' => $score['earned'],
            'percentage' => $score['percentage'],
            'passed' => $score['percentage'] >= $this->passing_score,
            'completed_at' => now(),
        ]);
        return $attempt->fresh();
    });
}
```

## Anti-Patterns to Avoid

### Anti-Pattern 1: Storing Answers Only at End

**What:** Waiting until quiz completion to save answers
**Why:** Lost progress on timeout, no draft saving
**Instead:** Save each answer immediately via AJAX/Livewire

### Anti-Pattern 2: Client-Side Scoring

**What:** Calculating score in JavaScript
**Why:** Can be manipulated, insecure
**Instead:** Calculate server-side in Laravel, validate answers server-side

### Anti-Pattern 3: Single Correct Answer

**What:** Limiting to single correct answer for MCQ
**Why:** Some questions have multiple valid answers
**Instead:** Support multiple correct options with is_multiple flag

### Anti-Pattern 4: No Time Limit Enforcement

**What:** Only advising user of time limit
**Why:** User can exceed limit
**Instead:** Enforce via server-side timestamp check + client countdown

### Anti-Pattern 5: Hardcoded Question Types

**What:** Switching on question_type strings throughout
**Why:** Maintenance burden, error-prone
**Instead:** Use polymorphic relationships or question type classes

## Scalability Considerations

| Concern | At 100 Questions | At 10K Questions | At 1M Questions |
|---------|------------------|------------------|-----------------|
| Query | Eager load relationships | Paginate questions | Separate question table |
| Scoring | Synchronous | Queue scoring job | Async worker pool |
| Storage | JSON answers OK | Individual rows | Partition by quiz |
| Time Limit | Single check | Batch timestamp check | Redis timeout |

## Integration Points

### With Spatie Permissions

```php
// Routes use permission middleware
Route::middleware(['auth', 'can:quiz.create'])->group(function () {
    Route::get('/admin/quizzes/create', CreateQuiz::class);
});

Route::middleware(['auth', 'can:quiz.view_results'])->group(function () {
    Route::get('/admin/quizzes/{quiz}/results', QuizResults::class);
});
```

### With Training Integration

```php
// Quiz can be linked to TrainingModule
public function training(): BelongsTo
{
    return $this->belongsTo(TrainingModule::class);
}

// Quiz completion updates module progress
public function syncModuleProgress(QuizAttempt $attempt): void
{
    if ($this->training) {
        $attempt->user->moduleProgress()
            ->syncExisting([$this->training->id => ['completed' => $attempt->passed]]);
    }
}
```

### With Notifications

```php
// Quiz completion notification
Notification::make()
    ->title('Quiz Completed')
    ->body("You scored {$attempt->percentage}% on {$attempt->quiz->title}")
    ->send();
```

### With Activity Log

```php
// Log quiz events
activity()
    ->causedBy($user)
    ->performedOn($quiz)
    ->log('completed_quiz');
```

## Recommended Project Structure

```
app/
├── Models/
│   ├── Quiz.php
│   ├── QuizQuestion.php
│   ├── QuizQuestionOption.php
│   ├── QuizAttempt.php
│   └── QuizAnswer.php
├── Livewire/
│   ├── QuizDetails.php
│   ├── QuizTaking.php
│   ├── MyQuizzes.php
│   └── Admin/
│       ├── QuizManagement.php
│       ├── QuestionManager.php
│       └── QuizResults.php
│
database/migrations/
│   {date}_create_quiz_tables.php
│
routes/web.php
│   // Add quiz routes
│
resources/views/
│   ├── livewire/
│   │   ├── quiz-details.blade.php
│   │   ├── quiz-taking.blade.php
│   │   ├── my-quizzes.blade.php
│   │   └── admin/
│   │       ├── quiz-management.blade.php
│   │       ├── question-manager.blade.php
│   │       └── quiz-results.blade.php
```

## Sources

- `app/Models/Event.php` — Core entity patterns (reference for model structure)
- `app/Models/EventRegistration.php` — Registration/attempt patterns
- `app/Livewire/EventDetails.php` — Public display component patterns
- `app/Livewire/Admin/EventsManagement.php` — Filament-based admin patterns
- `routes/web.php` — Route definition patterns
- `app/Livewire/EventRegistration.php` — Form handling patterns