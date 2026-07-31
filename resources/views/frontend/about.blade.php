@extends('layouts.frontend')

@section('content')
    @php
        $pillars = [
            [
                'label' => 'Institutional Role',
                'title' => 'A club that belongs inside university life',
                'text' => 'The club is positioned as part of St. Lawrence University’s student learning environment, giving technical curiosity a clear place to grow on campus.',
            ],
            [
                'label' => 'Ethical Position',
                'title' => 'Technical growth with responsible boundaries',
                'text' => 'The club’s identity is built around lawful, constructive, and disciplined practice. Curiosity is encouraged, but it is guided by ethics and accountability.',
            ],
            [
                'label' => 'Student Value',
                'title' => 'A bridge between interest and practical action',
                'text' => 'Students need more than theory. The club creates a route from curiosity to participation through regular learning, collaboration, and visible involvement.',
            ],
        ];

        $proofNotes = [
            'Real club photography is used throughout the site instead of anonymous stock branding.',
            'Public participation is documented through actual club imagery and recognition moments.',
            'The club is described consistently as a student-led SLAU community, not as a vague technology group.',
        ];

        $journey = [
            'Enter as a curious student, regardless of starting level.',
            'Attend practical sessions and begin working with tools, concepts, and team activity.',
            'Take part in club visibility, events, or collaborative technical work.',
            'Grow into a contributor, organizer, mentor, or representative of the community.',
        ];
    @endphp

    <section class="hero-backdrop border-b bg-cover bg-center" style="border-color: var(--page-border); background-image: url('{{ asset('images/club/certificate-team.jpg') }}');">
        <div class="mx-auto max-w-6xl px-4 pb-14 pt-12 sm:px-6 lg:px-8">
            <div class="grid items-center gap-8 lg:grid-cols-[0.92fr_1.08fr]">
                <div class="space-y-5">
                    <p class="eyebrow">About the Club</p>
                    <h1 class="page-hero-title">A university club for students who want cybersecurity learning to feel real.</h1>
                    <p class="page-hero-copy">
                        The Cybersecurity and Innovations Club exists to give St. Lawrence University students a more practical, visible, and community-based way to grow in technology. It is not just a page about interest. It is a page about participation, responsibility, and presence.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="cyber-button">Join the Club</a>
                        <a href="{{ route('events.index') }}" class="cyber-outline-button">See the Operating Model</a>
                    </div>
                </div>

                <article class="spotlight-panel overflow-hidden rounded-md">
                    <img src="{{ asset('images/club/certificate-team.jpg') }}" alt="Club members holding a certificate" class="h-[420px] w-full object-cover">
                </article>
            </div>
        </div>
    </section>

    <section class="reveal-fade py-18">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 max-w-3xl">
                <p class="eyebrow">Club Dossier</p>
                <h2 class="section-title">The identity of the club should be understood before anything else.</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($pillars as $pillar)
                    <article class="dossier-card rounded-md p-6">
                        <p class="dossier-label">{{ $pillar['label'] }}</p>
                        <h3 class="dossier-title">{{ $pillar['title'] }}</h3>
                        <p class="dossier-copy">{{ $pillar['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cyber-section reveal-fade">
        <div class="mx-auto max-w-6xl px-4 py-18 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                <div class="space-y-5">
                    <p class="eyebrow">Proof and Credibility</p>
                    <h2 class="section-title">A club website should not rely on claims alone.</h2>
                    <p class="lead-copy">
                        This page now supports its message with real visual evidence, a clear university identity, and an explanation of why the club matters to students.
                    </p>
                </div>

                <div class="grid gap-4">
                    @foreach ($proofNotes as $note)
                        <article class="proof-card rounded-md px-5 py-5">
                            <p class="body-copy">{{ $note }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="reveal-fade py-18">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1fr_1fr] lg:items-center">
                <article class="story-panel overflow-hidden rounded-md">
                    <div class="story-panel-image-wrap">
                        <img src="{{ asset('images/club/cyber-team.jpg') }}" alt="SLAU club members together" class="story-panel-image">
                    </div>
                </article>

                <div class="space-y-5">
                    <p class="eyebrow">Growth Path</p>
                    <h2 class="section-title">The club should show a path, not just an invitation.</h2>

                    <div class="grid gap-4">
                        @foreach ($journey as $index => $step)
                            <article class="route-card rounded-md px-5 py-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold" style="background: color-mix(in srgb, var(--page-primary) 16%, var(--page-surface-strong)); color: var(--page-text);">
                                        {{ $index + 1 }}
                                    </div>
                                    <p class="body-copy">{{ $step }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cyber-section reveal-fade">
        <div class="mx-auto max-w-6xl px-4 py-18 sm:px-6 lg:px-8">
            <article class="identity-block">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="eyebrow">Next Step</p>
                        <h2 class="dossier-title">If the identity is clear, the next action should be clear too.</h2>
                        <p class="dossier-copy">Students can now move from understanding to action by attending events, reviewing leadership, or joining the club directly.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="cyber-button">Create Member Access</a>
                        <a href="{{ route('team') }}" class="cyber-outline-button">Meet the Team</a>
                        <a href="{{ route('contact') }}" class="cyber-outline-button">Contact the Club</a>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection
