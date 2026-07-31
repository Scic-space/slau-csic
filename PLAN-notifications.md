# Professional Notification System — Implementation Plan

## Problem
The notification system is fragmented: two disconnected databases (`notifications` and `staff_notifications`), no real-time toast feedback, a basic notifications page, and an inconsistent header dropdown. Users can't tell what's new.

## Goal
HackTheBox/Cisco-style professional notification experience:
- Bell icon with animated unread count badge
- Dropdown showing all recent unseen notifications with type icons
- Toast notifications for immediate action feedback
- Polished notifications center page with categories and filters

---

## Architecture Decision

**Unify on Laravel's `notifications` table** (database channel). This is where all 41 existing notification classes already write. The `staff_notifications` table and `StaffNotification` model become unused but won't be deleted (backward compatibility for API/mobile).

---

## Implementation Steps

### Step 1: Global Toast Notification Component
Create a Livewire toast system for immediate feedback on all actions.

**Create:** `app/Livewire/Toast.php`
- Public properties: `$message`, `$type` (success/error/warning/info), `$show`
- Listens for `toast` event via `#[On('toast')]` attribute
- Auto-hides after 4 seconds via `setTimeout` in the view
- Methods: `show($message, $type)`, `hide()`

**Create:** `resources/views/livewire/toast.blade.php`
- Fixed bottom-right position, z-50
- Type-based colors: green (success), red (error), amber (warning), blue (info)
- Slide-in animation, auto-dismiss with progress bar
- Close button
- Dark mode support

**Modify:** `resources/views/layouts/app.blade.php`
- Add `@livewire('toast')` after `@livewire('notifications')` (which stays for Filament)

**Modify:** All Livewire components that use `dispatch('flash', ...)` or `dispatch('notify', ...)`
- Standardize to `$this->dispatch('toast', message: '...', type: 'success|error|warning|info')`
- Files: `EventDetails.php`, `MemberProfile.php`, `TreasurerDashboard.php`, `MyFines.php`, `CompetitionShow.php`, `CompetitionChallenges.php`

**Modify:** `NotificationCenterController.php`
- Standardize controller redirects to dispatch toast via session flash
- Or: add a `toast` middleware/helper that reads session and dispatches

### Step 2: Unified Header Notification Dropdown
Switch from `StaffNotification` to Laravel's `$user->unreadNotifications`.

**Modify:** `resources/views/components/header/notification-dropdown.blade.php`
- Query: `$user->unreadNotifications()->latest()->take(10)->get()` instead of `StaffNotification`
- Unread count badge: Show actual number (e.g., "12") when > 0, animated ping dot when > 0
- Type-based icons with colored circles:
  - Events (calendar icon, blue)
  - Elections (ballot icon, indigo)
  - Fines (dollar icon, red)
  - Membership (user icon, green)
  - Exams/Academic (academic cap, purple)
  - System/Broadcast (megaphone, amber)
- Each notification: icon, title (bold if unread), message preview (truncated), time ago
- "Mark as read" on click (navigate to action_url if present)
- "Mark all read" button in dropdown header
- "View all notifications" link at bottom
- Dark mode support throughout
- Smooth enter/leave transitions

**Modify:** `app/View/Components/header/NotificationDropdown.php`
- Change query from `StaffNotification` to user's unread notifications count
- Or: move query to the Blade template (current approach)

**Modify:** `resources/views/layouts/app-header.blade.php`
- Ensure `<x-header.notification-dropdown />` is rendered (already is)

### Step 3: Polished Notifications Center Page
Rewrite the notifications page with professional UI.

**Modify:** `app/Livewire/MemberNotifications.php`
- Add `$activeTab` property (all/unread)
- Add `$activeCategory` property (all/events/elections/fines/membership/exams/system)
- Filter by category based on notification type prefix
- Methods: `markAsRead($id)`, `markAllAsRead()`, `deleteNotification($id)`, `setTab($tab)`, `setCategory($category)`
- Paginate with category/tab filters applied

**Rewrite:** `resources/views/livewire/member-notifications.blade.php`
- Page header: "Notifications" title + unread count + "Mark all as read" button
- Category tabs (horizontal scrollable): All, Unread, Events, Elections, Fines, Membership, Exams, System
  - Each tab shows count of items in that category
- Notification list:
  - Each item: left color accent bar (type-based), type icon (colored circle), title (bold if unread), message, timestamp, action button
  - Unread items: subtle blue/indigo left border + slightly tinted background
  - Read items: normal styling
  - Hover effect
- Empty state: Illustration + "All caught up!" message
- Pagination at bottom
- Dark mode support throughout

### Step 4: Notification Type Mapping
Define consistent type-to-category mapping for icons and colors.

**Create:** `app/Notifications/NotificationType.php` (enum or config)
```php
// Type prefix => [category, icon, color]
'App\\Notifications\\EventReminderNotification' => ['events', 'calendar', 'blue']
'App\\Notifications\\EventCancelledNotification' => ['events', 'calendar-x', 'red']
'App\\Notifications\\ElectionOpenedNotification' => ['elections', 'ballot', 'indigo']
'App\\Notifications\\FineIssuedNotification' => ['fines', 'dollar-sign', 'red']
'App\\Notifications\\MemberApprovalNotification' => ['membership', 'user-check', 'green']
// ... etc for all 41 notification classes
```

### Step 5: Standardize Flash/Toast Dispatch
Ensure all Livewire actions provide immediate feedback.

**Modify these Livewire components to dispatch toast:**
- `app/Livewire/EventDetails.php` — RSVP success/error, feedback submitted
- `app/Livewire/MemberProfile.php` — Profile updated, preferences saved
- `app/Livewire/TreasurerDashboard.php` — Transaction actions
- `app/Livewire/MyFines.php` — Fine payment, appeal submitted
- `app/Livewire/CompetitionShow.php` — Join competition
- `app/Livewire/CompetitionChallenges.php` — Submit flag, solve challenge
- Any other Livewire components with `dispatch('flash', ...)` or `dispatch('notify', ...)`

---

## Files to Create
| File | Purpose |
|------|---------|
| `app/Livewire/Toast.php` | Toast notification Livewire component |
| `resources/views/livewire/toast.blade.php` | Toast UI with animations |
| `app/Notifications/NotificationType.php` | Type-to-category/icon/color mapping |

## Files to Modify
| File | Change |
|------|--------|
| `resources/views/layouts/app.blade.php` | Add `@livewire('toast')` |
| `resources/views/components/header/notification-dropdown.blade.php` | Unify to use `$user->unreadNotifications`, add type icons, unread badge |
| `app/View/Components/header/NotificationDropdown.php` | Update count query |
| `app/Livewire/MemberNotifications.php` | Add category/tab filtering |
| `resources/views/livewire/member-notifications.blade.php` | Professional rewrite with categories, icons, visual hierarchy |
| `app/Livewire/EventDetails.php` | Standardize flash to toast |
| `app/Livewire/MemberProfile.php` | Standardize flash to toast |
| `app/Livewire/TreasurerDashboard.php` | Standardize flash to toast |
| `app/Livewire/MyFines.php` | Standardize flash to toast |
| `app/Livewire/CompetitionShow.php` | Standardize notify to toast |
| `app/Livewire/CompetitionChallenges.php` | Standardize notify to toast |

## Files NOT Modified
- `app/Models/StaffNotification.php` — Kept for backward compatibility
- `app/Notifications/*.php` — No changes needed, they already write to `notifications` table
- Filament notification system — Untouched, uses its own toast system
