@extends('layouts.frontend')

@section('content')
    @php
        $contactNotes = [
            'This page is for students who want to join, visitors who need clarification, and collaborators who want to connect with the club.',
            'Contact information should reduce hesitation by making the route to the club feel legitimate and simple.',
            'A strong contact page is part of institutional trust because it proves the club can be reached by real people.',
        ];
    @endphp

    <section class="hero-backdrop border-b bg-cover bg-center" style="border-color: var(--page-border); background-image: url('{{ asset('images/club/certificate-team.jpg') }}');">
        <div class="mx-auto max-w-6xl px-4 pb-14 pt-12 sm:px-6 lg:px-8">
            <div class="grid items-center gap-8 lg:grid-cols-[0.92fr_1.08fr]">
                <div class="space-y-5">
                    <p class="eyebrow">Contact the Club</p>
                    <h1 class="page-hero-title">A serious club should be easy to reach for the right reasons.</h1>
                    <p class="page-hero-copy">
                        The contact page is part of the site’s credibility. It helps students, speakers, collaborators, and campus visitors understand how to start a real conversation with the club.
                    </p>
                </div>

                <article class="spotlight-panel overflow-hidden rounded-md" style="background: color-mix(in srgb, var(--page-surface) 92%, transparent);">
                    <img src="{{ asset('images/club/kevin-sharon.jpg') }}" alt="SLAU club members" class="h-[420px] w-full object-contain object-center">
                </article>
            </div>
        </div>
    </section>

    <section class="reveal-fade py-18">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-[minmax(0,_3fr)_minmax(0,_2fr)] items-start">
                <form method="POST" action="{{ route('contact') }}" class="dossier-card rounded-md p-6 sm:p-8 space-y-5">
                    @csrf

                    @if (session('success'))
                        <div class="rounded-sm border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div>
                        <p class="eyebrow">Message Route</p>
                        <h2 class="dossier-title">Questions, collaboration, or membership interest</h2>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium" style="color: var(--page-text-soft);">Full name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-sm border px-4 py-3 text-sm" style="border-color: var(--page-border); background: var(--page-surface-strong); color: var(--page-text);" placeholder="Your name">
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium" style="color: var(--page-text-soft);">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-sm border px-4 py-3 text-sm" style="border-color: var(--page-border); background: var(--page-surface-strong); color: var(--page-text);" placeholder="you@example.com">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="topic" class="mb-2 block text-sm font-medium" style="color: var(--page-text-soft);">Topic</label>
                        <select id="topic" name="topic" class="w-full rounded-sm border px-4 py-3 text-sm" style="border-color: var(--page-border); background: var(--page-surface-strong); color: var(--page-text);">
                            <option value="">Select a topic</option>
                            <option value="Membership and joining" @selected(old('topic') === 'Membership and joining')>Membership and joining</option>
                            <option value="Event attendance or inquiry" @selected(old('topic') === 'Event attendance or inquiry')>Event attendance or inquiry</option>
                            <option value="Collaboration or partnership" @selected(old('topic') === 'Collaboration or partnership')>Collaboration or partnership</option>
                            <option value="Speaker invitation" @selected(old('topic') === 'Speaker invitation')>Speaker invitation</option>
                        </select>
                        @error('topic') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="message" class="mb-2 block text-sm font-medium" style="color: var(--page-text-soft);">Message</label>
                        <textarea id="message" name="message" rows="5" class="w-full rounded-sm border px-4 py-3 text-sm" style="border-color: var(--page-border); background: var(--page-surface-strong); color: var(--page-text);" placeholder="Tell the club how it can help">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <p class="body-copy">
                        This form structure supports membership questions, event inquiries, collaboration requests, partnership ideas, and general communication.
                    </p>

                    <button type="submit" class="cyber-button">Send Message</button>
                </form>

                <aside class="space-y-5">
                    <article class="proof-card rounded-md p-6">
                        <p class="eyebrow">Contact Notes</p>
                        <ul class="mt-4 space-y-3 text-sm" style="color: var(--page-text-soft);">
                            <li><span style="color: var(--page-text-muted);">Email:</span> cyberclub@slau.ac.ug</li>
                            <li><span style="color: var(--page-text-muted);">Location:</span> St. Lawrence University, Kampala</li>
                            <li><span style="color: var(--page-text-muted);">Audience:</span> students, collaborators, and campus visitors</li>
                        </ul>
                    </article>

                    @foreach ($contactNotes as $note)
                        <article class="dossier-card rounded-md p-5">
                            <p class="body-copy">{{ $note }}</p>
                        </article>
                    @endforeach
                </aside>
            </div>
        </div>
    </section>
@endsection
