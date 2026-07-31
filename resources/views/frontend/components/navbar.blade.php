@php
    $links = [
        ['label' => 'Home', 'route' => 'home', 'match' => ['home']],
        ['label' => 'CTF Portal', 'route' => 'ctf.index', 'match' => ['ctf.*']],
        ['label' => 'Inno-Weeks', 'route' => 'events.index', 'match' => ['events.index', 'events-out']],
        ['label' => 'Research', 'route' => 'projects', 'match' => ['projects']],
    ];
@endphp

<nav class="fixed inset-x-0 top-0 z-50">
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div class="home-header">
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(26,226,143,0.55)]"></span>
                    <span class="text-sm font-bold tracking-[0.18em] text-emerald-300 sm:text-base">SLAU-CSIC</span>
                </a>
            </div>

            <div class="hidden items-center gap-8 lg:flex">
                @foreach ($links as $link)
                    @php
                        $isActive = request()->routeIs(...$link['match']);
                    @endphp
                    <a href="{{ route($link['route']) }}" class="home-nav-link {{ $isActive ? 'active' : '' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="https://github.com/Mr-Righteousdev/slau-csic" class="home-nav-link" target="_blank" rel="noreferrer">
                    GitHub
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="home-login-button">
                    Club Login
                </a>

                <div x-data="{ open: false }" class="lg:hidden">
                    <button
                        @click="open = !open"
                        type="button"
                        class="inline-flex items-center justify-center rounded-md border border-white/10 bg-white/5 p-2 text-slate-200"
                    >
                        <span class="sr-only">Open main menu</span>
                        <svg x-show="!open" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="open" x-cloak class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition.origin.top.right
                        class="absolute inset-x-4 top-20 rounded-xl border border-white/10 bg-[#0b1421]/95 p-4 shadow-2xl backdrop-blur-xl"
                    >
                        <div class="space-y-2">
                            @foreach ($links as $link)
                                @php
                                    $isActive = request()->routeIs(...$link['match']);
                                @endphp
                                <a href="{{ route($link['route']) }}" class="block rounded-lg px-3 py-2 text-sm {{ $isActive ? 'text-emerald-300' : 'text-slate-300' }}">
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                            <a href="https://github.com/Mr-Righteousdev/slau-csic" class="block rounded-lg px-3 py-2 text-sm text-slate-300" target="_blank" rel="noreferrer">
                                GitHub
                            </a>
                            <div class="pt-2">
                                <a href="{{ route('login') }}" class="home-login-button w-full justify-center">
                                    Club Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
