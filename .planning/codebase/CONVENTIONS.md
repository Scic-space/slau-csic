# Coding Conventions

**Analysis Date:** 2026-04-25

## Code Formatter

**Laravel Pint** (v1.24) is used for code formatting. Run before committing:
```bash
vendor/bin/pint --dirty
```

No `pint.json` or `.pint.json` configuration file detected; defaults apply (PSR-12 style).

---

## Naming Patterns

### Files

- **Controllers**: `PascalCase` + `Controller` suffix
  - Example: `ClubPortalController.php`, `TreasurerDashboard.php`
- **Models**: `PascalCase` singular noun
  - Example: `Transaction.php`, `BudgetCategory.php`
- **Livewire Components**: `PascalCase` with namespace path
  - Example: `Admin/TransactionManagement.php` → `livewire/admin/transaction-management.blade.php`
- **Form Requests**: `PascalCase` + `Request` suffix
  - Example: `CastElectionVoteRequest.php`
- **Test files**: Same as class or feature description
  - Example: `TreasurerDashboardTest.php`, `ClubPortalTest.php`
- **Blade templates**: `kebab-case`
  - Example: `transaction-management.blade.php`, `treasurer-dashboard.blade.php`

### Directories

- Use **kebab-case** for view directories under `resources/views/`
  - Example: `resources/views/livewire/admin/`, `resources/views/pages/club/`
- Use **PascalCase** for PHP namespace directories
  - Example: `app/Livewire/Admin/`, `app/Models/`

### Variables and Functions

- **CamelCase** for local variables and method names
  - Example: `clubResource`, `resourcesWithProgress()`, `syncHtbIfNeeded()`
- **snake_case** for database columns and model attributes
  - Example: `club_resource_id`, `approved_at`, `membership_status`
- **SCREAMING_SNAKE_CASE** for constants
- **is/has/can** prefix for boolean methods
  - Example: `isActiveMember()`, `hasAttendedMeeting()`, `canVote()`, `canImpersonate()`

### Types (Models, Enums)

- **PascalCase** for model property names
  - Example: `$category->name`, `$transaction->amount`
- **snake_case** for database columns
- **PascalCase** for relationship method names
  - Example: `clubResourceProgress()`, `transactionAllocations()`

---

## Code Style

### PHP

- PHP 8.2+ required
- **Curly braces always used** for control structures, even single-line
- **Constructor property promotion** used throughout:
  ```php
  public function __construct(protected HtbProfileSyncService $htbProfileSyncService) {}
  ```
- **Explicit return types** on all methods:
  ```php
  public function index(): View { ... }
  protected function renderSection(string $category, string $heading, string $intro): View { ... }
  public function castVote(...): RedirectResponse { ... }
  ```
- **Named parameters** used where applicable:
  ```php
  $response->assertRedirect(route('dashboard', absolute: false));
  ```
- **No empty `__construct()`** — use property promotion or leave absent
- **PHPDoc blocks** for complex methods; inline comments for non-obvious logic only
- **Faker** uses `fake()` function ( Pest style ):

### Blade Templates

- **Kebab-case** for component names:
  ```blade
  <x-ui.button />
  <x-form.input />
  ```
- **Single root element** for Livewire components
- **`wire:key`** on loop items:
  ```blade
  @foreach ($items as $item)
      <div wire:key="item-{{ $item->id }}">
  ```
- **`wire:model.live`** for real-time reactivity (Livewire v3)
- **`wire:loading` and `wire:dirty`** for loading states
- Dark mode support via `dark:` Tailwind classes

### Models

- **`$fillable`** for mass assignment:
  ```php
  protected $fillable = ['type', 'category', 'amount', ...];
  ```
- **`casts()` method** (Laravel 12 style) over `$casts` property:
  ```php
  protected function casts(): array {
      return [
          'email_verified_at' => 'datetime',
          'password' => 'hashed',
          'amount' => 'decimal:2',
      ];
  }
  ```
- **Scopes as methods** (no `scope` prefix in usage):
  ```php
  public function scopeIncome($query) { ... }     // Usage: Transaction::income()->get()
  public function scopeExpense($query) { ... }
  public function scopePending($query) { ... }
  public function scopeApproved($query) { ... }
  public function scopeActiveMembers($query) { ... }
  ```
- **Accessors/mutators as `getXAttribute`/`setXAttribute`**:
  ```php
  public function getFormattedAmountAttribute(): string { ... }
  public function getAvatarUrlAttribute(): string { ... }
  ```
- **Relationships as methods** with explicit return types:
  ```php
  public function creator(): BelongsTo { ... }
  public function transactions(): HasMany { ... }
  public function memberProjects() { ... }
  ```
- **Activity logging** via `Spatie\Activitylog\Traits\LogsActivity`

### Controllers

- **Constructor injection** with property promotion
- **Form Request classes** for validation (not inline `validate()` calls)
- **Return type hints**: `View`, `RedirectResponse`, `JsonResponse`
- **Auth via `auth()` helper** or `$request->user()`
- **Route model binding** with type hints

### Form Requests

- `authorize()` returns `bool`
- `rules()` returns `array` with explicit types
- Custom error messages when needed

---

## Import Organization

### Order

1. Internal app classes (`App\Models\*`, `App\Services\*`, `App\Http\Requests\*`)
2. External package classes (`Livewire\*`, `Filament\*`, `Carbon\*`)
3. Laravel facades and core classes (`Illuminate\*`)
4. Standard library / PHP built-ins

Example from `app/Http/Controllers/ClubPortalController.php`:
```php
use App\Http\Requests\CastElectionVoteRequest;
use App\Http\Requests\UpdateClubResourceProgressRequest;
use App\Models\ClubResource;
use App\Models\User;
use App\Services\HtbProfileSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
```

### No path aliases in use statements — use full namespace

---

## Error Handling

- **`abort_unless()`** for authorization checks:
  ```php
  abort_unless($election->isOpen(), 403);
  ```
- **`findOrFail()`** for model lookups that should 404
- **`updateOrCreate()`** for upsert patterns
- **`try/catch`** in model workflow methods with return value:
  ```php
  public function approve(...): bool {
      try {
          $this->update([...]);
          return true;
      } catch (\Exception $e) {
          return false;
      }
  }
  ```

---

## Logging

- No direct `Log::` facade usage detected
- Activity logging via `spatie/laravel-activitylog` on models
- No manual `Log::info()` / `Log::error()` calls in application code

---

## Comments

- PHPDoc on complex model methods and relationships
- Section dividers in large files:
  ```php
  // ============================================
  // SCOPES
  // ============================================
  // ============================================
  // HELPER METHODS
  // ============================================
  ```
- Inline comments only for non-obvious business logic

---

## Function Design

- **Single responsibility** — methods do one thing
- **Small, focused methods** — helper methods extracted to `protected` controller methods
- **Method chaining** on query builders:
  ```php
  Transaction::query()
      ->where('type', 'income')
      ->where('status', 'approved')
      ->orderBy('date')
      ->get();
  ```
- **Callback in query** for complex filtering:
  ```php
  User::query()
      ->with(['progresses' => fn ($query) => $query->where('user_id', $user->id)])
      ->get();
  ```

---

## Module Design

### Exports

- Single class per file
- No barrel index files
- Models exported via autoloading under `App\Models` namespace

### Services

- Located in `app/Services/`
- Stateless, injected via constructor
- Example: `BudgetAlertService.php`, `HtbProfileSyncService.php`

---

*Convention analysis: 2026-04-25*