# Testing Patterns

**Analysis Date:** 2026-04-25

## Test Framework

**Runner:**
- Pest v4 (`pestphp/pest` v4) with `pest-plugin-laravel` v4
- PHPUnit under the hood (v12)
- Config: `phpunit.xml`

**Assertion Library:**
- Pest expectations: `expect()->toBeTrue()`, `expect()->not->toBeNull()`, etc.
- PHPUnit assertions in some files: `$this->assertEquals()`, `$this->assertStringContainsString()`
- Livewire testing: `Livewire::test()`, `->assertSeeLivewire()`

**Run Commands:**
```bash
php artisan test              # Run all tests
php artisan test tests/Unit   # Unit tests only
php artisan test tests/Feature # Feature tests only
npm run test                  # Alias via composer scripts
vendor/bin/pest               # Direct pest
```

---

## Test File Organization

**Location:** `tests/` directory

```
tests/
├── Pest.php                  # Global Pest configuration
├── TestCase.php              # Base test case
├── Unit/
│   ├── ExampleTest.php
│   ├── BudgetCategoryTest.php
│   ├── TransactionTest.php
│   └── BudgetAlertServiceTest.php
└── Feature/
    ├── ExampleTest.php
    ├── ClubPortalTest.php
    ├── TreasurerDashboardTest.php
    ├── TransactionManagementTest.php
    ├── PublicSitePagesTest.php
    └── RegistrationControllerTest.php
```

**Naming:**
- Feature tests: `{FeatureName}Test.php` (e.g., `TransactionManagementTest.php`)
- Unit tests: `{ModelName}Test.php` or `{ServiceName}Test.php`
- Uses `PascalCase` class names matching the file

---

## Test Structure

### Two Patterns Coexist

**Pattern 1: Pest closure syntax** (modern, used in `tests/Pest.php` base):
```php
// tests/Feature/ClubPortalTest.php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the club portal to authenticated verified members', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Club Portal');
});
```

**Pattern 2: PHPUnit class syntax** (traditional, extends TestCase):
```php
// tests/Feature/TreasurerDashboardTest.php
class TreasurerDashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_dashboard_displays_correctly_for_treasurer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('treasurer');

        $response = $this->actingAs($user)
            ->get('/admin/treasurer-dashboard');

        $response->assertStatus(200);
        $response->assertSeeLivewire('treasurer-dashboard');
    }
}
```

**Both patterns are acceptable** — the codebase uses both. Pest-style closure tests use `it()` and `test()` functions. PHPUnit-style tests use `/** @test */` docblock annotations.

### Pest.php Global Configuration

```php
// tests/Pest.php
pest()->extend(Tests\TestCase::class)
    ->in('Feature');
```

Note: `RefreshDatabase::class` trait is **commented out** globally in Pest.php, so individual test files must opt-in via `uses()` or `use RefreshDatabase`.

### Base TestCase

```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    // No custom setup by default
}
```

---

## Mocking

**Framework:** Mockery (via Pest's `mock()` or PHPUnit's `$this->mock()`)

**Mocking in tests:**
```php
// tests/Unit/BudgetAlertServiceTest.php
$this->mock(BudgetCategory::class, function ($mock) {
    $mock->shouldReceive('where')
        ->with('is_active', true)
        ->andReturnSelf()
        ->shouldReceive('get')
        ->andReturn([...]);
});

$this->mock(Transaction::class, function ($mock) {
    $mock->shouldReceive('where')
        ->with('category', 'Over Budget Category')
        ->andReturnSelf()
        ->...
        ->shouldReceive('sum')
        ->with('amount')
        ->andReturn(1200.00);
});
```

**Partial mocks via `mock()` or `$this->mock()`**:
```php
use function Pest\Laravel\mock;  // Optional import
```

---

## Fixtures and Factories

**Factory location:** `database/factories/`

**Factory states detected:**
```php
Transaction::factory()->income()->create();
Transaction::factory()->expense()->create();
Transaction::factory()->pending()->create();
Transaction::factory()->approved()->create();
BudgetCategory::factory()->income()->create();
BudgetCategory::factory()->expense()->create();
```

**Custom factory data in tests:**
```php
$category = BudgetCategory::factory()->create([
    'allocated_amount' => 1000.00,
    'is_active' => true,
]);

$transaction = Transaction::factory()->create([
    'status' => 'pending',
    'amount' => 150.00,
]);
```

---

## Test Database Setup

**phpunit.xml configuration:**
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Tests use SQLite in-memory database. `RefreshDatabase` trait resets the database between tests.

---

## Livewire Component Testing

```php
// TransactionManagementTest.php
Livewire::test('transaction-management')
    ->actingAs($user)
    ->call('mount')
    ->set('formData.type', 'income')
    ->set('formData.category', 'Membership Dues')
    ->set('formData.amount', 100)
    ->call('create')
    ->assertDispatched('notification')
    ->assertDatabaseHas('transactions', [...]);

// Filter testing
Livewire::test('transaction-management')
    ->actingAs($user)
    ->call('mount')
    ->assertSet('table.filters.type', 'income')
    ->call('clearFilter', 'type')
    ->assertSet('table.filters.type', 'expense');
```

**Key patterns:**
- `->actingAs($user)` for authentication
- `->call('mount')` to initialize the component
- `->call('methodName', ...args)` for actions
- `->set('property', value)` for property assignment
- `->assertDispatched('event-name')` for event assertions
- `->assertSeeLivewire('component-name')` for page-level assertions

---

## Common Patterns

### Authentication in Tests

```php
$this->actingAs($user)->get(route('dashboard'));
```

### Database Assertions

```php
$response->assertDatabaseHas('transactions', [
    'type' => 'income',
    'category' => 'Membership Dues',
]);
```

### Role Assignment in Tests

```php
$user = User::factory()->create();
$user->assignRole('treasurer');
```

### Pest Expectation Style

```php
expect($user)->not->toBeNull()
    ->and($user->student_id)->toBe('SLAU/2026/001')
    ->and($user->membership_status)->toBe('pending');

expect($progress->progress_percentage)->toBe(55)
    ->and($progress->ranking)->toBe('Top 10')
    ->and($progress->status)->toBe('in_progress');
```

### PHPUnit Assertion Style

```php
$this->assertEquals('income', $transaction->type);
$this->assertTrue($transaction->requires_approval);
$this->assertFalse($category->is_active);
$this->assertCount(2, $activeCategories);
$this->assertStringContainsString('Budget Alert:', $message);
```

### Factory Count

```php
Transaction::factory()->income()->count(3)->create();
BudgetCategory::factory()->create(['is_active' => true]);
```

---

## Coverage

**No enforced coverage target detected.**

Run coverage report:
```bash
php artisan test --coverage
```

---

## Test Types

### Unit Tests

- Test single model/method logic
- Use `RefreshDatabase` for database access
- Mock external dependencies (services, models)
- Located in `tests/Unit/`

Examples: `BudgetCategoryTest.php` (model scopes, accessors), `TransactionTest.php` (model methods), `BudgetAlertServiceTest.php` (service logic)

### Feature Tests

- Test full HTTP request/response cycles
- Test Livewire component interactions
- Use `actingAs()` for authentication simulation
- Located in `tests/Feature/`

Examples: `ClubPortalTest.php` (HTTP routes), `TreasurerDashboardTest.php` (page + Livewire), `TransactionManagementTest.php` (Livewire actions)

---

## Key Files Reference

| File | Pattern | Focus |
|------|---------|-------|
| `tests/Pest.php` | Pest global config | Extends TestCase, sets up Feature suite |
| `tests/Feature/ClubPortalTest.php` | Pest closure style | Portal pages, progress tracking |
| `tests/Feature/TreasurerDashboardTest.php` | PHPUnit class style | Dashboard pages, role guards |
| `tests/Feature/TransactionManagementTest.php` | PHPUnit class + Livewire | Livewire actions, bulk operations |
| `tests/Unit/BudgetCategoryTest.php` | PHPUnit class + RefreshDatabase | Model scopes and accessors |
| `tests/Unit/TransactionTest.php` | PHPUnit class + factory states | Model methods and relationships |
| `tests/Unit/BudgetAlertServiceTest.php` | Mockery mocking | Service with mocked model dependencies |

---

*Testing analysis: 2026-04-25*