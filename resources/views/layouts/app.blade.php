<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    @endauth

    <script>
        (function() {
            var savedTheme = localStorage.getItem('slau-theme');
            var systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            var theme = savedTheme || systemTheme;
            document.documentElement.dataset.theme = theme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <style>
        html, body { background-color: #f9fafb; }
        html.dark, html.dark body { background-color: oklch(18% 0.035 255); }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet">
    @livewireStyles
    @filamentStyles
    <title>{{ $title ?? 'Dashboard' }} | SLAU Cybersecurity & Innovations Club</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- User Permissions for JavaScript (minimal exposure) -->
    @php
        $canCreateEvents = false;

        if (auth()->check() && $user = auth()->user()) {
            $canCreateEvents = $user->hasAnyRole(['admin', 'super-admin', 'member']);
        }
    @endphp

    <script>
        window.appConfig = {
            canCreateEvents: {{ $canCreateEvents ? 'true' : 'false' }}
        };
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('slau-theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('slau-theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        html.dataset.theme = 'dark';
                        body.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                        html.dataset.theme = 'light';
                        body.classList.remove('dark');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                isOpen: window.innerWidth >= 1280,
                collapsedGroups: {},

                init() {
                    const savedState = localStorage.getItem('sidebar-expanded');
                    this.isExpanded = window.innerWidth >= 1280 && (savedState === null || savedState === 'true');
                    this.isMobileOpen = false;
                },

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                    localStorage.setItem('sidebar-expanded', String(this.isExpanded));
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                },

                open() {
                    this.isOpen = true;
                },

                close() {
                    this.isOpen = false;
                },

                groupIsCollapsed(group) {
                    return this.collapsedGroups[group] ?? false;
                },

                toggleCollapsedGroup(group) {
                    this.collapsedGroups[group] = !(this.collapsedGroups[group] ?? false);
                }
            });

            Alpine.data('notificationBell', () => ({
                dropdownOpen: false,
                toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                },
                closeDropdown() {
                    this.dropdownOpen = false;
                }
            }));
        });
    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('slau-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.dataset.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.dataset.theme = 'light';
            }

            const applyBodyTheme = () => {
                if (!document.body) return;
                if (theme === 'dark') {
                    document.body.classList.add('dark');
                } else {
                    document.body.classList.remove('dark');
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyBodyTheme);
            } else {
                applyBodyTheme();
            }
        })();

        document.addEventListener('livewire:navigating', () => {
            document.documentElement.classList.add('is-page-navigating');
            const savedTheme = localStorage.getItem('slau-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.dataset.theme = 'dark';
                document.body.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.dataset.theme = 'light';
                document.body.classList.remove('dark');
            }
        });

        document.addEventListener('livewire:navigated', () => {
            const savedTheme = localStorage.getItem('slau-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.dataset.theme = 'dark';
                document.body.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.dataset.theme = 'light';
                document.body.classList.remove('dark');
            }

            requestAnimationFrame(() => document.documentElement.classList.remove('is-page-navigating'));
        });
    </script>
</head>

<body class="bg-background text-foreground"
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.init();
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = localStorage.getItem('sidebar-expanded') !== 'false';
        }
    };
    window.addEventListener('resize', checkMobile);">

    <a href="#main-content" class="absolute left-[-9999px] z-[9999] bg-black p-4 text-white no-underline focus:left-0 focus:top-0">Skip to main content</a>

    <x-common.preloader/>

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex min-h-screen flex-1 flex-col transition-[margin] duration-300 ease-in-out"
            :class="{
                'xl:ml-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[80px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            @include('layouts.app-header')

            @php
                $isAdminArea = request()->routeIs('admin.*')
                    || request()->routeIs('filament.admin.*')
                    || request()->is('admin/*');
            @endphp

            <main id="main-content" @class(['flex-1', 'pb-20' => ! $isAdminArea]) tabindex="-1">
                <div @class([
                    'mx-auto max-w-(--breakpoint-2xl)',
                    'p-4 md:p-6' => $isAdminArea,
                    'px-4 pb-4 sm:px-5 sm:pb-5 md:px-6 md:pb-6' => ! $isAdminArea,
                ])>
                    @if(!request()->routeIs('filament.admin.pages.dashboard') && (request()->routeIs('admin.*') || str_starts_with(request()->path(), 'admin/')))
                        <a href="{{ route('filament.admin.pages.dashboard') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-900/20 dark:text-violet-400 dark:hover:bg-violet-900/30 mb-5 transition-colors shadow-sm">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Back to Dashboard
                        </a>
                    @endif
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>

            @include('layouts.system-footer', ['isFixed' => ! $isAdminArea])
        </div>
    </div>

    @livewireScripts
    @filamentScripts
    @livewire('notifications')
    @livewire('toast')
    @stack('scripts')
</body>

</html>
