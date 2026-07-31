# Architecture

**Analysis Date:** 2026-04-25

## Pattern Overview

**Overall:** Laravel 12 with Livewire 3 - Hybrid SPA-style architecture with both server-side rendered pages and API endpoints.

**Key Characteristics:**
- Livewire components handle most user-facing pages (admin dashboard, member areas, events)
- Traditional controllers used for API routes (mobile app support)
- Filament admin panel for backend administration
- Spatie for role-based access control (RBAC)

## Layers

### HTTP Layer (Routes)
- Location: `routes/web.php`, `routes/api.php`, `routes/auth.php`, `routes/console.php`
- Routing configured in `bootstrap/app.php`
- Route groups use middleware for auth, verification, permission gates

### Controller Layer
- Location: `app/Http/Controllers/`
- Standard HTTP controllers for API (`app/Http/Controllers/Api/`)
- Auth controllers in `app/Http/Controllers/Auth/`
- Settings controllers in `app/Http/Controllers/Settings/`
- Frontend controllers in `app/Http/Controllers/Frontend/`
- Admin controllers in `app/Http/Controllers/Admin/`

### Livewire Layer
- Location: `app/Livewire/`
- Organized by namespace: `App\Livewire\Admin\`, `App\Livewire\Teaching\`, `App\Livewire\Super\`
- Components handle full page lifecycle with state on server
- Example components: `App\Livewire\Admin\Dashboard`, `App\Livewire\EventDetails`

### Model Layer
- Location: `app/Models/`
- Eloquent models with relationships
- Examples: `User`, `Event`, `Meeting`, `Attendance`, `Election`

## Data Flow

**Web Request Flow:**

1. HTTP request hits `routes/web.php`
2. Route directs to Livewire component (`EventDetails::class`) or Controller
3. Livewire/Controller processes logic, queries Models
4. Model returns data via Eloquent relationships
5. Livewire component renders Blade view
6. Response returned to browser

**API Request Flow:**

1. HTTP request hits `routes/api.php`
2. Route directs to API Controller (`Api\EventController`)
3. Controller validates, queries Models
4. Controller returns JSON response (resource or direct)

## Service Providers

**Configured in `bootstrap/providers.php`:**
- `App\Providers\AppServiceProvider` - General app services
- `App\Providers\Filament\AdminPanelProvider` - Admin panel configuration

**No custom middleware files** - Middleware registered in `bootstrap/app.php`.

## Middleware Registration

**Location:** `bootstrap/app.php` (lines 14-19)

```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

**Usage in routes:**
- `Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')`
- Permission gates in route definitions: `->middleware('can:content.view')`

## Key Abstractions

**Event System:**
- Model: `app/Models/Event.php`
- Livewire: `app/Livewire/EventDetails.php`, `app/Livewire/EventRegistration.php`
- Pattern: Slug-based routing (`/events/{event:slug}`)

**User & Auth:**
- Model: `app/Models/User.php`
- Controllers: `app/Http/Controllers/Auth/`
- Livewire: `app/Livewire/UserProfile.php`

**Admin Dashboard:**
- Livewire: `app/Livewire/Admin/Dashboard.php`
- Widgets: `app/Livewire/Admin/Widgets/`

## Entry Points

**Web Routes:**
- `routes/web.php` - Main application routes (141 lines)
- `routes/auth.php` - Authentication routes (44 lines)
- `routes/console.php` - Artisan commands (12 lines)

**API Routes:**
- `routes/api.php` - Mobile app API (45 lines)

**Admin:**
- `/admin` - Admin dashboard (Livewire)
- Filament panel at `/admin` ( Filament provider)

## Error Handling

**Strategy:** Laravel default exception handling in `bootstrap/app.php` (lines 21-22)

```php
->withExceptions(function (Exceptions $exceptions): void {
    //
})
```

Custom exception handling can be added in this closure.

## Cross-Cutting Concerns

**Authentication:** Laravel Sanctum for API, session-based for web

**Authorization:** Spatie roles/permissions with middleware aliases

**Validation:** Form Request classes (recommended) or inline validation in controllers

**Notifications:** Laravel notifications system (`app/Notifications/`)

---

*Architecture analysis: 2026-04-25*