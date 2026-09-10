@php
    $dashboardUser = auth()->user();
    $initialGreeting = \App\Support\TimeOfDayGreeting::forHour((int) now()->format('G'));
@endphp

<span
    class="admin-dashboard-greeting"
    x-data="{
        greeting: @js($initialGreeting),
        greetingTimer: null,
        updateGreeting() {
            const hour = new Date().getHours();

            this.greeting = hour >= 5 && hour < 12
                ? 'Good morning'
                : hour >= 12 && hour < 17
                    ? 'Good afternoon'
                    : hour >= 17 && hour < 21
                        ? 'Good evening'
                        : 'Good night';
        },
        init() {
            this.updateGreeting();
            this.greetingTimer = window.setInterval(() => this.updateGreeting(), 60_000);
        },
        destroy() {
            window.clearInterval(this.greetingTimer);
        },
    }"
    aria-label="Authenticated user greeting"
>
    <span class="admin-dashboard-greeting-copy" x-text="greeting">{{ $initialGreeting }}</span>
    <span class="admin-dashboard-greeting-name">{{ $dashboardUser->name }}</span>
    <span class="admin-dashboard-title">Dashboard</span>
</span>
