<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Member Access' }} | SLAU Cybersecurity & Innovations Club</title>
    @livewireStyles
    @filamentStyles
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])

    <script>
        (function () {
            const savedTheme = localStorage.getItem('slau-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.dataset.theme = theme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <style>
        :root {
            --portal-bg: #07101d;
            --portal-bg-soft: #0b1628;
            --portal-surface: rgba(11, 22, 40, 0.92);
            --portal-surface-alt: rgba(9, 22, 39, 0.88);
            --portal-border: rgba(255, 255, 255, 0.1);
            --portal-text: #f8fafc;
            --portal-text-soft: #cbd5e1;
            --portal-muted: #94a3b8;
            --portal-primary: #19e28f;
            --portal-secondary: #34b6ff;
        }

        html[data-theme='light'] {
            --portal-bg: #eef4fb;
            --portal-bg-soft: #f7fafe;
            --portal-surface: rgba(255, 255, 255, 0.96);
            --portal-surface-alt: rgba(255, 255, 255, 0.88);
            --portal-border: rgba(15, 23, 42, 0.1);
            --portal-text: #0f172a;
            --portal-text-soft: #334155;
            --portal-muted: #64748b;
        }

        .portal-shell {
            background:
                radial-gradient(circle at top left, rgba(52, 182, 255, 0.12), transparent 24%),
                radial-gradient(circle at bottom right, rgba(25, 226, 143, 0.1), transparent 22%),
                linear-gradient(180deg, var(--portal-bg) 0%, var(--portal-bg-soft) 52%, var(--portal-bg) 100%);
            color: var(--portal-text);
        }

        .portal-grid {
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.06) 1px, transparent 1px);
            background-size: 84px 84px;
        }

        .portal-orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(12px);
            opacity: 0.7;
            pointer-events: none;
        }

        .portal-orb-primary {
            background: radial-gradient(circle, rgba(25, 226, 143, 0.38) 0%, rgba(25, 226, 143, 0.08) 48%, transparent 74%);
        }

        .portal-orb-secondary {
            background: radial-gradient(circle, rgba(52, 182, 255, 0.32) 0%, rgba(52, 182, 255, 0.06) 50%, transparent 76%);
        }

        .portal-card {
            position: relative;
            background: var(--portal-surface);
            border: 1px solid var(--portal-border);
            box-shadow: 0 20px 48px rgba(2, 8, 23, 0.16);
            backdrop-filter: blur(18px);
        }

        .portal-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background:
                linear-gradient(135deg, rgba(52, 182, 255, 0.08), transparent 34%),
                linear-gradient(315deg, rgba(25, 226, 143, 0.06), transparent 32%);
            pointer-events: none;
        }

        .portal-field {
            border: 1px solid var(--portal-border);
            background: var(--portal-surface-alt);
            color: var(--portal-text);
        }

        .portal-copy {
            color: var(--portal-text-soft);
        }

        .portal-muted {
            color: var(--portal-muted);
        }

        .portal-button {
            background: var(--portal-primary);
            color: #06111b;
        }

        .portal-button:hover {
            background: color-mix(in srgb, var(--portal-primary) 92%, white);
        }

        .portal-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border: 1px solid var(--portal-border);
            background: var(--portal-surface-alt);
            color: var(--portal-text-soft);
            border-radius: 9999px;
            padding: 0.6rem 0.85rem;
        }

        .portal-backlink {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 0.82rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--portal-muted);
            transition: color 160ms ease;
        }

        .portal-backlink:hover {
            color: var(--portal-text);
        }

        .portal-shell-panel {
            position: relative;
            overflow: hidden;
        }

        .portal-shell-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at center, rgba(7, 16, 29, 0.08) 12%, rgba(7, 16, 29, 0.45) 54%, rgba(7, 16, 29, 0.92) 100%),
                linear-gradient(180deg, rgba(7, 16, 29, 0.15) 0%, rgba(7, 16, 29, 0.78) 100%);
        }

        .portal-shell-panel > * {
            position: relative;
            z-index: 1;
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.08);
            padding: 0.55rem 0.95rem;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
        }

        .portal-badge::before {
            content: '';
            height: 0.45rem;
            width: 0.45rem;
            border-radius: 9999px;
            background: var(--portal-primary);
        }

        .portal-side-copy {
            color: rgba(226, 232, 240, 0.9);
        }

        .portal-side-grid {
            display: grid;
            gap: 1rem;
        }

        .portal-side-stat {
            border-radius: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(7, 16, 29, 0.54);
            padding: 1rem;
            backdrop-filter: blur(14px);
        }

        .portal-side-stat-value {
            font-size: 1.55rem;
            font-weight: 700;
            color: #fff;
        }

        .portal-side-stat-label {
            margin-top: 0.35rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: rgba(226, 232, 240, 0.76);
        }
    </style>


    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('slau-theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
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
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                isOpen: window.innerWidth >= 1280,
                collapsedGroups: {},

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
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
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('slau-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
</head>

<body x-data="{ 'loaded': true}" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
const checkMobile = () => {
    if (window.innerWidth < 1280) {
        $store.sidebar.setMobileOpen(false);
        $store.sidebar.isExpanded = false;
    } else {
        $store.sidebar.isMobileOpen = false;
        $store.sidebar.isExpanded = true;
    }
};
window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @yield('content')
    
    @livewireScripts
    @filamentScripts

</body>

@stack('scripts')

</html>
