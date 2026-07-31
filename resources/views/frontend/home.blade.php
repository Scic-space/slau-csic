@extends('layouts.frontend')

@section('content')
    @php
        $tickerItems = [
            'New research paper published on Indigenous Datasets',
            "Member @kamau_sec solved 'Enigma-X' level 5",
            'Commit: Updated Sovereign-Stack documentation',
            'New digital CV verified for 12 members',
            'Inno-Weeks stage 02: deployment phase initiated',
        ];

        $missionCards = [
            [
                'eyebrow' => 'Elite Community',
                'title' => 'A network of high-performing students, mentors, and builders.',
                'text' => 'The club is shaped around collaboration, challenge, and real-world engineering habits that help members grow with confidence.',
                'meta' => 'Members active: 248',
            ],
            [
                'eyebrow' => 'Skill-Building Hub',
                'title' => 'Hands-on workshops, labs, and technical deep dives.',
                'text' => 'Capture the Flag, secure development, and practical sessions give members a repeatable path from curiosity to capability.',
                'meta' => 'Workshops/year: 52',
            ],
            [
                'eyebrow' => 'Local Impact',
                'title' => 'Driving real solutions for Uganda and the region.',
                'text' => 'The club exists to support local innovation, student visibility, and technical confidence that reaches beyond the campus.',
                'meta' => 'Sovereignty level: High',
            ],
        ];

        $curriculum = [
            [
                'code' => 'CORE_MODULE_01',
                'title' => 'Software Engineering',
                'text' => 'Building scalable, secure systems using industry-standard frameworks and agile methodologies.',
                'accent' => 'border-l-emerald-400',
            ],
            [
                'code' => 'CORE_MODULE_02',
                'title' => 'Information Tech',
                'text' => 'Networks, cloud infrastructure, and enterprise systems management for the digital era.',
                'accent' => 'border-l-sky-400',
            ],
            [
                'code' => 'CORE_MODULE_03',
                'title' => 'Computer Science',
                'text' => 'Algorithms, data structures, and the mathematics of complex computing.',
                'accent' => 'border-l-amber-400',
            ],
        ];

    @endphp

    <section class="home-hero relative overflow-hidden">
        <div class="home-grid-overlay"></div>
        <div class="home-grid-glow"></div>

        <div class="mx-auto grid min-h-[calc(100vh-6rem)] max-w-7xl gap-12 px-4 pb-16 pt-28 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:px-8 lg:pt-32">
            <div class="relative z-10 max-w-3xl">
                <p class="home-eyebrow">Cybersecurity &amp; Innovations Club</p>
                <h1 class="home-hero-title">
                    Architecting East Africa's <span>Tech Identity</span>
                </h1>
                <p class="home-hero-copy">
                    A student-led sovereign technical powerhouse at St. Lawrence University. We bridge the academia-industry gap through community-driven research, practical security training, and locally grounded innovation.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="home-primary-button">
                        Join the Club
                    </a>
                    <a href="{{ route('about') }}" class="home-secondary-button">
                        View Roadmap
                    </a>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('about') }}" class="home-tertiary-button">
                        Read the Club Dossier
                    </a>
                    <a href="{{ route('members.public') }}" class="home-tertiary-button">
                        See Member Profiles
                    </a>
                    <a href="{{ route('contact') }}" class="home-tertiary-button">
                        Contact the Club
                    </a>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="home-stat-card">
                        <span class="home-stat-label">Active Nodes</span>
                        <span class="home-stat-value">248</span>
                    </div>
                    <div class="home-stat-card">
                        <span class="home-stat-label">Solved Today</span>
                        <span class="home-stat-value">154</span>
                    </div>
                    <div class="home-stat-card sm:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <span class="home-stat-label">Skill Radar</span>
                            <span class="home-stat-label text-[11px] text-emerald-300">Web Sec</span>
                        </div>
                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/8">
                            <div class="h-full w-[84%] rounded-full bg-emerald-400"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex items-center justify-end">
                <div class="home-logo-stage w-full">
                    <div class="home-logo-glow"></div>
                    <img src="{{ asset('images/club/logo1-removebg-preview.png') }}" alt="SLAU Cybersecurity & Innovations Club logo" class="home-logo-image">
                </div>
            </div>
        </div>

        <div class="home-ticker border-y border-white/8">
            <div class="home-ticker-track">
                @foreach (array_merge($tickerItems, $tickerItems) as $item)
                    <span>{{ $item }}</span>
                    <span class="home-ticker-separator">|</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="reveal-fade border-b border-white/6 bg-[#0a1320]">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-20 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8">
            <div class="max-w-2xl">
                <p class="home-eyebrow">Our Pillars</p>
                <h2 class="home-section-title">Club Mission &amp; Identity</h2>
                <p class="home-section-copy mt-4">
                    We aren't just building code. We are cultivating a community of ambition, mentorship, and respect for local constraints and capabilities on regional opportunities.
                </p>
            </div>

            <div class="flex items-start justify-end">
                <div class="home-id-block">
                    <p class="font-mono text-[12px] uppercase tracking-[0.28em] text-emerald-300">// Club ID: SLAU_CSIC</p>
                    <p class="mt-2 font-mono text-[12px] uppercase tracking-[0.28em] text-cyan-300">// Role: Skill Accelerator</p>
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-7xl gap-4 px-4 pb-20 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach ($missionCards as $card)
                <article class="home-info-card">
                    <p class="home-card-eyebrow">{{ $card['eyebrow'] }}</p>
                    <h3 class="home-card-title">{{ $card['title'] }}</h3>
                    <p class="home-card-copy">{{ $card['text'] }}</p>
                    <p class="home-card-meta">{{ $card['meta'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="reveal-fade border-b border-white/6 bg-[#0c1624]">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:items-center">
            <div class="home-image-panel">
                <img src="{{ asset('images/club/certificate-team.jpg') }}" alt="SLAU club members holding a certificate" class="home-image">
                <div class="home-image-badge">
                    <span>Flagship Activity</span>
                    <strong>Inno-Weeks The Ultimate Test</strong>
                </div>
            </div>

            <div class="max-w-2xl">
                <p class="home-eyebrow">Activity Hub</p>
                <h2 class="home-section-title">Bridging the Industry Gap</h2>
                <p class="home-section-copy mt-4">
                    Our flagship Inno-Weeks is where club members turn theoretical knowledge into market-ready prototypes. We don't just graduate, we certify vetted innovators with photographic proof and practical evidence.
                </p>

                <div class="mt-8 space-y-5">
                    <div class="home-feature-row">
                        <span class="home-feature-icon">◉</span>
                        <div>
                            <h3 class="home-feature-title">Weekly Hackings Sessions</h3>
                            <p class="home-feature-copy">Members engage in collaborative coding, security auditing, and hardware prototyping every Saturday.</p>
                        </div>
                    </div>

                    <div class="home-feature-row">
                        <span class="home-feature-icon">◉</span>
                        <div>
                            <h3 class="home-feature-title">Vetted Talent Pipeline</h3>
                            <p class="home-feature-copy">Every line of code is traceable to a member's journey from curiosity to leadership.</p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('events.index') }}" class="home-secondary-button mt-8 inline-flex">
                    View Inno-Weeks Schedule
                </a>
            </div>
        </div>
    </section>

    <section class="reveal-fade border-b border-white/6 bg-[#09111d]">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="max-w-3xl text-center mx-auto">
                <p class="home-eyebrow">Academic Foundation</p>
                <h2 class="home-section-title">Sovereign Tech Curricula</h2>
                <p class="home-section-copy mt-4">
                    The club operates as a practical extension of SLAU's core academic departments.
                </p>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                @foreach ($curriculum as $course)
                    <article class="home-curriculum-card border-l-2 {{ $course['accent'] }}">
                        <p class="home-card-eyebrow">{{ $course['code'] }}</p>
                        <h3 class="home-card-title mt-3">{{ $course['title'] }}</h3>
                        <p class="home-card-copy">{{ $course['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="reveal-fade bg-[#0a1320]">
        <div class="mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="home-cta-card">
                <p class="home-eyebrow text-center">Living Laboratory</p>
                <h2 class="home-section-title text-center">Join the Living Laboratory</h2>
                <p class="home-section-copy mt-4 text-center">
                    Whether you're a student, a partner, or a future mentor, your journey begins with the club.
                </p>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('register') }}" class="home-primary-button">Apply for Club Membership</a>
                    <a href="{{ route('contact') }}" class="home-secondary-button">Partner with the Lab</a>
                </div>

                <p class="mt-6 text-center font-mono text-[12px] uppercase tracking-[0.28em] text-emerald-300">
                    deploy. iterate. secure. build. for uplift.
                </p>
            </div>
        </div>
    </section>
@endsection
