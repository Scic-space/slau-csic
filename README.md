# SLAU Cybersecurity & Innovations Club

Website for the **St. Lawrence University Cybersecurity & Innovations Club (SLAU-CSIC)** — a full-stack Laravel application for managing club operations, events, CTF competitions, elections, polls, exams, attendance, fines, finances, and member management.

Built with **Laravel 12**, **Livewire 3**, **Filament 4**, **Inertia.js (React)**, **Tailwind CSS v4**, and **Alpine.js**.

---

## Tech Stack

| Layer       | Technology                                                    |
|-------------|---------------------------------------------------------------|
| Backend     | PHP 8.2+, Laravel 12, Livewire 3                              |
| Admin Panel | Filament 4 (custom pages, resources, widgets)                 |
| Frontend    | Tailwind CSS v4, Alpine.js 3, Inertia.js (React), Vite 7     |
| Database    | SQLite / MySQL / PostgreSQL                                   |
| Auth        | Laravel Sanctum, Spatie Permissions                           |
| Testing     | Pest 4 / PHPUnit 12                                           |
| Tooling     | Laravel Pint, Laravel Boost (MCP)                             |

---

## Quick Start

```bash
git clone https://github.com/Mr-Righteousdev/slau-csic.git
cd slau-csic

cp .env.example .env
php artisan key:generate

composer install
npm install

php artisan migrate --seed

composer run dev
```

Visit `http://localhost:8000`. Default admin credentials are seeded.

---

## Development

### Setup

**Requirements:** PHP 8.2+, Composer, Node.js 18+, npm.

```bash
composer install
npm install
php artisan migrate --seed
```

### Run locally

```bash
composer run dev    # starts both Laravel (php artisan serve) & Vite HMR
```

### Code formatting

```bash
vendor/bin/pint
```

### Testing

```bash
php artisan test
```

---

## Project Structure

```
├── app/
│   ├── Filament/               # Admin panel (Filament 4)
│   │   ├── Pages/              # Custom dashboard & analytics pages
│   │   ├── Resources/          # CRUD resources (Users, Events, CTFs, etc.)
│   │   └── Widgets/            # Dashboard widgets & charts
│   ├── Http/
│   │   ├── Controllers/        # API controllers, Auth, frontend controllers
│   │   └── Requests/           # Form request validation
│   ├── Livewire/               # Livewire 3 components
│   │   ├── Admin/              # Admin dashboard widgets
│   │   └── Election, Event, Poll, Exam, etc.
│   └── Models/                 # Eloquent models
├── resources/
│   ├── js/                     # React (Inertia) pages & components
│   │   ├── components/         # Shared UI components
│   │   └── pages/              # Public pages (Home, CTF Arena, etc.)
│   └── views/
│       ├── filament/           # Filament custom page views
│       ├── frontend/           # Blade public website pages
│       └── livewire/           # Livewire component views
├── routes/
│   ├── api.php                 # Mobile app API (Sanctum)
│   ├── inertia.php             # Web routes (Livewire + Inertia)
│   ├── auth.php                # Authentication routes
│   └── console.php             # Artisan commands
└── tests/
    ├── Feature/                # Feature tests (Pest)
    └── Unit/                   # Unit tests (Pest)
```

---

## Features

### Site Frontend
- Landing page with animated hero, event cards, announcements, and CTA
- CTF Arena portal with competition stats and testimonials
- Workshop listings, leaderboard, member directory
- Public events calendar and news/articles

### Member Dashboard
- Profile management with social links, photo, bio
- Privacy controls (show/hide email, phone, Discord, attendance stats, etc.)
- Notification preferences
- My events, grades, attendance, transactions
- Membership card, fines & appeals

### Admin Panel (Filament `/admin`)
- **Membership** — Users, pending approvals, alumni, badges
- **Events** — Calendar, CRUD, categories, registrations, attendance, analytics
- **Meetings** — CRUD, attendance, agenda items, analytics
- **Finance** — Transactions, budget categories
- **Fines** — Manage fines, fine types, appeals
- **Exams** — Exam bank, attempts, certificates
- **CTF** — Dashboard, competitions, categories, submissions, writeups
- **Elections** — Manage elections, candidates, nominations, votes
- **Assignments** — Role templates & assignment wizard
- **Projects** — Club project management
- **System** — Overview, roles/permissions, announcements, news, settings, audit log

### Events
- Public event listing with categories, date, location, skill level
- RSVP and registration with waitlist
- Event certificates and check-in (QR code)
- Feedback collection
- Recurring event support

### Competitions / CTF
- Competition listing with type (CTF/Hackathon/Coding/Cybersecurity), date range, filtering
- CTF dashboard, flag submissions, writeups
- Club rankings and achievements

### Elections & Polls
- Election management with nominations, candidates, voting
- Allow vote changes, voter eligibility
- Polls with single/multiple choice

### Fines & Finance
- Fine types, member fines with partial payment
- Appeals workflow
- Transaction tracking, budget categories, financial reports (PDF/Excel export)

### Notifications
- Event reminders, cancellations, membership updates, fine notices
- Broadcast announcements

---

## License

This project is based on the TailAdmin Laravel template. See `LICENSE` file for details.
