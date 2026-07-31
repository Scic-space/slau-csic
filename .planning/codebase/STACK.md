# Technology Stack

**Analysis Date:** 2026-04-25

## Languages

**Primary:**
- PHP 8.2+ - Core application logic

**Secondary:**
- JavaScript (ES Modules) - Frontend interactivity

## Runtime

**Environment:**
- PHP 8.2+ (minimum from composer.json)
- Node.js (via Vite for frontend bundling)

**Package Manager:**
- Composer (PHP): ^2.x
- npm: For frontend dependencies
- Lockfile: `composer.lock` present

## Frameworks

**Core:**
- Laravel Framework ^12.0 - Application framework
- Livewire ^3.7 - Full-stack framework for dynamic UIs
- Filament ^4.0 - Admin panel framework

**Testing:**
- Pest ^4.0 - Testing framework
- PHPUnit ^12.0 - Underlying test runner
- Laravel Pint ^1.24 - Code formatter

**Build/Dev:**
- Vite ^7.0.4 - Frontend build tool
- Laravel Vite Plugin ^2.0.0 - Vite integration
- Tailwind CSS ^4.1.12 - CSS framework

## Key Dependencies

**Critical:**
- `laravel/framework` ^12.0 - Core framework
- `livewire/livewire` ^3.7 - Dynamic UI components
- `filament/filament` ^4.0 - Admin panel
- `laravel/sanctum` ^4.0 - API authentication

**Admin & UI:**
- `filament/actions` ^4.0
- `filament/forms` ^4.0
- `filament/notifications` ^4.0
- `filament/tables` ^4.0
- `asantibanez/livewire-calendar` - Calendar component

**Data & Reports:**
- `maatwebsite/excel` ^3.1 - Excel export
- `barryvdh/laravel-dompdf` ^3.1 - PDF generation
- `simplesoftwareio/simple-qrcode` ^4.2 - QR code generation

**Permissions & Security:**
- `spatie/laravel-permission` ^6.23 - Role-based permissions
- `spatie/laravel-activitylog` ^4.10 - Activity logging
- `lab404/laravel-impersonate` ^1.7 - User impersonation

**Development:**
- `laravel/boost` ^1.8 - Laravel Boost MCP
- `barryvdh/laravel-debugbar` ^3.16 - Debug bar

## Frontend Packages

**Dependencies (package.json):**
- `alpinejs` ^3.14.9 - JS framework
- `@floating-ui/dom` ^1.7.4 - Floating UI
- `@popperjs/core` ^2.11.8 - Popper.js
- `@preline/theme-switch` ^4.1.3 - Theme switcher
- `apexcharts` ^5.3.5 - Charts
- `flatpickr` ^4.6.13 - Date picker
- `jsvectormap` ^1.7.0 - Maps
- `preline` ^4.1.3 - UI components
- `prismjs` ^1.30.0 - Syntax highlighting
- `swiper` ^12.0.3 - Carousel

**Dev Dependencies:**
- `tailwindcss` ^4.1.12
- `@tailwindcss/vite` ^4.1.12
- `@tailwindcss/forms` ^0.5.11
- `axios` ^1.11.0
- `vite` ^7.0.4

## Configuration

**Environment:**
- Configured via `.env` file
- Key configs: `config/app.php`, `config/database.php`, `config/mail.php`

**Build:**
- Vite config via Laravel plugin
- Tailwind v4 uses CSS-first config via `@theme` directive

## Platform Requirements

**Development:**
- PHP 8.2+
- Composer
- Node.js/npm for frontend

**Production:**
- Web server with PHP support (Apache/Nginx)
- Database: SQLite (default), MySQL, MariaDB, PostgreSQL supported
- Redis (optional, for queues/cache)

---

*Stack analysis: 2026-04-25*