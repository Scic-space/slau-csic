@php
    $categories = \App\Notifications\NotificationTypeConfig::categories();
@endphp
<div class="py-6 lg:py-8">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Notifications</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($unreadCount > 0)
                        You have {{ $unreadCount }} unread {{ Str::plural('notification', $unreadCount) }}
                    @else
                        All caught up!
                    @endif
                </p>
            </div>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        wire:confirm="Mark all notifications as read?"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark All as Read
                </button>
            @endif
        </div>

        {{-- Category Tabs --}}
        <div class="mb-6 overflow-x-auto scrollbar-none">
            <div class="flex gap-1.5 border-b border-gray-200 dark:border-border pb-px">
                @foreach ($categories as $key => $label)
                    @php
                        $count = $categoryCounts[$key] ?? 0;
                        $isActive = $key === 'all'
                            ? $activeTab === 'all' && $activeCategory === 'all'
                            : ($key === 'unread' ? $activeTab === 'unread' : $activeCategory === $key);
                    @endphp
                    <button wire:click="{{ $key === 'unread' ? "setTab('unread')" : "setCategory('{$key}')" }}"
                            class="relative flex items-center gap-1.5 whitespace-nowrap rounded-t-lg px-4 py-2.5 text-sm font-medium transition-colors
                                   {{ $isActive
                                       ? 'text-indigo-600 dark:text-indigo-400'
                                       : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        {{ $label }}
                        @if ($count > 0)
                            <span class="inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold
                                         {{ $isActive
                                             ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                             : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                {{ $count > 99 ? '99+' : $count }}
                            </span>
                        @endif
                        @if ($isActive)
                            <span class="absolute bottom-0 left-0 right-0 h-0.5 rounded-full bg-indigo-500 dark:bg-indigo-400"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Notifications List --}}
        @if ($notifications->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-8 sm:p-16 text-center shadow-sm dark:border-border dark:bg-card">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">No notifications here</p>
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">When you receive notifications, they'll appear here.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($notifications as $notification)
                    @php
                        $config = \App\Notifications\NotificationTypeConfig::for($notification->type);
                        $data = $notification->data;
                        $message = $data['message'] ?? $data['subject'] ?? 'Notification';
                        $isUnread = is_null($notification->read_at);
                        $shortType = class_basename($notification->type);
                    @endphp
                    <a href="{{ route('notifications.show', $notification->id) }}" wire:navigate
                       class="group relative block overflow-hidden rounded-xl border bg-white shadow-sm transition-all hover:shadow-md dark:bg-card
                                {{ $isUnread
                                    ? 'border-indigo-200 dark:border-indigo-800/50'
                                    : 'border-gray-200 dark:border-border' }}"
                         wire:key="notification-{{ $notification->id }}">

                        {{-- Left accent bar for unread --}}
                        @if ($isUnread)
                            <div class="absolute inset-y-0 left-0 w-1 bg-indigo-500 dark:bg-indigo-400"></div>
                        @endif

                        <div class="flex items-start gap-4 p-5 pl-6">
                            {{-- Type Icon --}}
                            <div class="flex-shrink-0 mt-0.5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $config['iconBg'] }}">
                                    @if ($config['icon'] === 'calendar')
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif ($config['icon'] === 'ballot')
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    @elseif ($config['icon'] === 'dollar')
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif ($config['icon'] === 'user')
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @elseif ($config['icon'] === 'academic')
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                    @elseif ($config['icon'] === 'trophy')
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    @else
                                        <svg class="h-5 w-5 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="text-sm {{ $isUnread ? 'font-semibold' : 'font-medium' }} text-gray-900 dark:text-white">
                                            {{ $message }}
                                        </h4>
                                        @if (!empty($data['event_title']))
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $data['event_title'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center gap-3">
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium {{ $config['iconBg'] }} {{ $config['textColor'] }}">
                                        {{ ucfirst($config['category']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
