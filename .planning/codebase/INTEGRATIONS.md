# External Integrations

**Analysis Date:** 2026-04-25

## APIs & External Services

**Email Services:**
- Configured: SMTP, SES, Postmark, Resend
- Default: `log` mailer (development)
- Env vars: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- Also supports: `MAIL_SCHEME`, `MAIL_URL`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`

**AWS Services:**
- SES (Simple Email Service) - Email delivery
- S3 - File storage
- Configuration: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`

**Notification Services:**
- Slack: `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL`
- Postmark: `POSTMARK_TOKEN`
- Resend: `RESEND_KEY`

## Data Storage

**Databases:**
- SQLite (default)
- MySQL/MariaDB supported
- PostgreSQL supported
- SQL Server supported
- Client: Laravel Eloquent ORM
- Connection: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

**File Storage:**
- Local filesystem (default): `storage/app`
- S3 supported: via `FILESYSTEM_DISK` env
- Public storage: `storage/app/public`

**Caching:**
- Database (default)
- Redis supported: `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`
- File, Memcached, Array drivers also supported
- Env: `CACHE_STORE`

## Authentication & Identity

**Auth Provider:**
- Laravel Sanctum ^4.0
- Session-based authentication (web guard)
- API token authentication
- Configuration: `config/sanctum.php`

**Permissions:**
- Spatie Laravel Permission ^6.23
- Role-based access control (RBAC)
- Middleware aliases: `role`, `permission`, `role_or_permission`

**User Impersonation:**
- Laravel Impersonate ^1.7 (lab404)

**Activity Logging:**
- Spatie Activitylog ^4.10

## Monitoring & Observability

**Error Tracking:**
- Laravel Debugbar (dev only): `barryvdh/laravel-debugbar` ^3.16

**Logs:**
- Default: `stack` channel
- Config: `LOG_CHANNEL`, `LOG_LEVEL`
- Stack driver with daily rotation supported
- Slack/webhook alerting supported

**Queue:**
- Queue driver configurable via `config/queue.php`
- Sync (default), database, Redis, Beanstalkd supported

## CI/CD & Deployment

**Hosting:**
- Self-hosted (Apache/Nginx with PHP-FPM)
- Laravel Sail for containerized development

**Development Tools:**
- Laravel Boost MCP server (dev):
- Laravel Pail (log tailing)
- Laravel Pint (code formatting)

## Environment Configuration

**Required env vars:**
- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_DATABASE`
- `MAIL_MAILER` (default: log)
- `CACHE_STORE` (default: database)
- `LOG_CHANNEL` (default: stack)

**Optional env vars:**
- `AWS_*` for SES/S3
- `REDIS_*` for Redis
- `MAIL_*` for SMTP
- `POSTMARK_TOKEN`, `RESEND_KEY`
- `SLACK_BOT_USER_OAUTH_TOKEN`

## Payment Integrations

**None detected** - No Stripe, PayPal, Razorpay, or similar payment packages installed.

## Webhooks & Callbacks

**Not explicitly configured** - Standard Laravel HTTP routing available via:
- `routes/web.php` - Web routes
- `routes/api.php` - API routes
- `routes/console.php` - Console commands

## Additional Integrations

**Data Export:**
- Maatwebsite Excel ^3.1 - Spreadsheet export
- DomPDF ^3.1 - PDF generation
- Simple QRCode ^4.2 - QR code generation

**Admin Panel:**
- Filament ^4.0 (Full admin panel framework)
- Filament Actions, Forms, Notifications, Tables, Schemas, Infolists, Widgets

**UI Components:**
- Livewire Calendar - Calendar component
- ApexCharts - Charts
- JSVectorMap - Maps
- Swiper - Carousel
- Flatpickr - Date picker
- Preline - UI components
- PrismJS - Syntax highlighting

---

*Integration audit: 2026-04-25*