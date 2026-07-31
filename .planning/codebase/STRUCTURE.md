# Codebase Structure

**Analysis Date:** 2026-04-25

## Directory Layout

```
slau-csic/
├── app/
│   ├── Console/           # Artisan commands (auto-registered)
│   ├── Exports/           # Excel export classes
│   ├── Helpers/           # Helper functions
│   ├── Http/
│   │   └── Controllers/   # HTTP controllers
│   ├── Livewire/         # Livewire components
│   ├── Models/            # Eloquent models
│   ├── Notifications/    # Notification classes
│   ├── Providers/        # Service providers
│   ├── Services/         # Business logic services
│   └── View/             # View composers
├── bootstrap/             # App initialization
├── config/              # Configuration files
├── database/
│   ├── factories/      # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/        # Seeders
├── public/              # Public assets
├── resources/
│   ├── css/            # Styles
│   ├── js/             # Scripts
│   └── views/          # Blade templates
├── routes/             # Route definitions
├── tests/              # Test files
└── vendor/             # Dependencies
```

## Directory Purposes

### app/Console/
- Purpose: Artisan console commands
- Auto-discovery: Yes (no registration needed in Laravel 12)
- Files: `Commands/` directory with command classes

### app/Exports/
- Purpose: Excel/CSV export classes
- Used by: Controllers and Livewire for download exports

### app/Helpers/
- Purpose: Global helper functions
- Files: PHP files with utility functions

### app/Http/Controllers/
- Purpose: HTTP request handling
- Contains:
  - Root controllers: `AttendanceController`, `ClubPortalController`
  - `Api/` - Mobile API controllers
  - `Auth/` - Authentication controllers
  - `Admin/` - Admin-specific controllers
  - `Frontend/` - Public website controllers
  - `Settings/` - User settings controllers

### app/Livewire/
- Purpose: Livewire page components
- Contains:
  - Root components: `EventDetails`, `EventRegistration`, `UserProfile`
  - `Admin/` - Admin dashboard and management
  - `Teaching/` - Teaching/education features
  - `Super/` - Super-admin features

### app/Models/
- Purpose: Eloquent ORM models
- Key models: `User`, `Event`, `Meeting`, `Attendance`, `Election`
- All database table representations

### app/Notifications/
- Purpose: Laravel notification classes
- Types: Mail, database, broadcast notifications

### app/Providers/
- Purpose: Service provider registration
- Files: `AppServiceProvider.php`, Filament provider

### app/Services/
- Purpose: Business logic services
- Contains: Service classes encapsulating logic

### app/View/
- Purpose: View composers and composers
- Used for: Sharing data across views

## Key File Locations

### Entry Points
- `routes/web.php` - Main application routes
- `routes/api.php` - API routes for mobile
- `routes/auth.php` - Authentication routes
- `routes/console.php` - Scheduled tasks

### Configuration
- `bootstrap/app.php` - App configuration, middleware registration
- `bootstrap/providers.php` - Service providers list
- `config/` - Laravel config files

### Core Logic
- `app/Livewire/` - Page components
- `app/Models/` - Data models
- `app/Services/` - Business services

### Testing
- `tests/Feature/` - Feature/integration tests
- `tests/Unit/` - Unit tests

## Naming Conventions

### Files
- Controllers: `PascalCase` - `EventController.php`
- Livewire components: `PascalCase` - `EventDetails.php`
- Models: `PascalCase` singular - `Event.php`
- Services: `PascalCase` with Service suffix - `EventService.php`
- Migrations: `YYYY_MM_DD_HHMMSS_description.php`

### Directories
- All lowercase, plural for collections - `app/Models/`
- Subdirectories use PascalCase for namespacing - `app/Livewire/Admin/`

### Routes
- Named routes: `kebab-case` - `admin.events`, `portal.voting`
- Route parameters: `camelCase` - `{event:slug}`

## Where to Add New Code

### New Feature (Web Page)
- Route: `routes/web.php`
- Implementation: `app/Livewire/` (for interactive pages) or `app/Http/Controllers/`
- View: `resources/views/` or inline in Livewire

### New API Endpoint
- Implementation: `app/Http/Controllers/Api/`
- Route: `routes/api.php`

### New Model
- Migration: `database/migrations/`
- Model: `app/Models/`
- Factory: `database/factories/`

### New Service
- Location: `app/Services/`

### New Livewire Component
- Location: `app/Livewire/` (root or appropriate subdirectory)

## Special Directories

### bootstrap/
- Purpose: App initialization
- Contains: `app.php` (middleware, exceptions, routing config)
- Generated: No
- Committed: Yes

### resources/views/
- Purpose: Blade templates
- Structure:
  - `layouts/` - Base layouts
  - `components/` - Reusable blade components
  - `frontend/` - Public site views
  - `livewire/` - Livewire component views (often inline)

### database/
- Purpose: Data layer
- Contains: Migrations, factories, seeders
- Organized: Chronological migrations

---

*Structure analysis: 2026-04-25*