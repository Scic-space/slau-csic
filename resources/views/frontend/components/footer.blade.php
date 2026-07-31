<footer class="border-t border-white/8 bg-[#07101b]">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Brand --}}
            <div class="space-y-4">
                <img src="{{ asset('images/university_logo.png') }}" alt="SLAU-CSIC" class="h-10 w-auto brightness-0 invert">
                <p class="max-w-xs text-sm leading-7 text-slate-400">
                    Cybersecurity &amp; Innovations Club at St. Lawrence University, Uganda — building skills, solving challenges.
                </p>
                <div class="flex items-center gap-3">
                    <a href="https://github.com/Mr-Righteousdev/slau-csic" target="_blank" rel="noreferrer" class="text-slate-500 hover:text-white transition-colors" aria-label="GitHub repository">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Navigate --}}
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-300">Navigate</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-400">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('ctf.index') }}">CTF Arena</a></li>
                    <li><a href="{{ route('projects') }}">Projects</a></li>
                    <li><a href="{{ route('announcements.index') }}">News</a></li>
                    <li><a href="{{ route('leaderboard.index') }}">Leaderboard</a></li>
                    <li><a href="{{ route('events.index') }}">Events</a></li>
                </ul>
            </div>

            {{-- Connect --}}
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-300">Connect</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-400">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <a href="mailto:cyberclub@slau.ac.ug" class="hover:text-white transition-colors">cyberclub@slau.ac.ug</a>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>Main Lab, SLAU Campus, Kampala, Uganda</span>
                    </li>
                    <li>
                        <a href="https://github.com/Mr-Righteousdev/slau-csic" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 hover:text-white transition-colors">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            GitHub Repository
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="hover:text-white transition-colors">Contact Form</a>
                    </li>
                </ul>
            </div>

            {{-- Join the Club --}}
            <div class="space-y-4">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-300">Join the Club</p>
                <p class="text-sm leading-7 text-slate-400">
                    Ready to hack the future? Become a member and get access to CTFs, workshops, projects, and a community of like-minded innovators.
                </p>
                <a href="{{ route('auth.register') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-[#06111b] hover:bg-emerald-400 transition-colors">
                    Register Now
                </a>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-white/8 pt-6 text-xs text-slate-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} SLAU-CSIC. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline">St. Lawrence University, Uganda</span>
                <span class="hidden sm:inline">&middot;</span>
                <a href="#" class="hover:text-white transition-colors">Privacy</a>
                <span>&middot;</span>
                <a href="#" class="hover:text-white transition-colors">Terms</a>
            </div>
        </div>
    </div>
</footer>
