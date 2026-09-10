@php
    $unreadCount = $unreadCount ?? 0;
    $recentNotifications = auth()->check()
        ? auth()->user()->unreadNotifications()->latest()->take(10)->get()
        : collect();
@endphp
<div class="relative" x-data="{
    dropdownOpen: false,
    unreadCount: {{ $unreadCount }},
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">

    <!-- Notification Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-border dark:bg-card dark:text-gray-400 dark:hover:bg-card-hover dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
        aria-label="Notifications"
        aria-haspopup="true"
        :aria-expanded="dropdownOpen"
    >
        <!-- Unread Count Badge -->
        <span
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 z-10 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-gray-900"
        ></span>

        <!-- Bell Icon -->
        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z" fill=""/>
        </svg>
    </button>

    <!-- Dropdown -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute -right-[240px] mt-[17px] flex w-[380px] flex-col rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-border dark:bg-card lg:right-0"
        role="menu"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-border">
            <div class="flex items-center gap-2">
                <h5 class="text-base font-semibold text-gray-900 dark:text-white">Notifications</h5>
                @if ($unreadCount > 0)
                    <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600 dark:bg-red-900/30 dark:text-red-400">{{ $unreadCount }} new</span>
                @endif
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Mark all read</button>
                </form>
            @endif
        </div>

        <!-- Notification List -->
        <div class="max-h-[400px] overflow-y-auto">
            <ul class="divide-y divide-gray-50 dark:divide-gray-800/50">
                @forelse ($recentNotifications as $notification)
                    @php
                        $config = \App\Notifications\NotificationTypeConfig::for($notification->type);
                        $data = $notification->data;
                        $message = $data['message'] ?? $data['subject'] ?? 'Notification';
                        $actionUrl = $data['action_url'] ?? $data['event_slug'] ?? null;
                    @endphp
                    <li>
                        <a wire:navigate class="flex items-start gap-3 px-5 py-3.5 transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                           href="{{ $actionUrl ? url($actionUrl) : route('notifications.index') }}">
                            {{-- Type Icon --}}
                            <div class="flex-shrink-0 mt-0.5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $config['iconBg'] }}">
                                    @if ($config['icon'] === 'calendar')
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif ($config['icon'] === 'ballot')
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    @elseif ($config['icon'] === 'dollar')
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif ($config['icon'] === 'user')
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @elseif ($config['icon'] === 'academic')
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                    @elseif ($config['icon'] === 'trophy')
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    @else
                                        <svg class="h-4 w-4 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $message }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                            {{-- Unread dot --}}
                            <div class="flex-shrink-0 mt-1.5">
                                <span class="block h-2 w-2 rounded-full bg-indigo-500"></span>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">All caught up!</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">No new notifications right now.</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-100 px-3 py-2.5 dark:border-border">
            <a href="{{ route('notifications.index') }}" wire:navigate
               class="flex items-center justify-center gap-2 rounded-xl py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]"
               @click="isOpen = false">
                View all notifications
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>
