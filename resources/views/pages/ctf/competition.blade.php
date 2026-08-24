@extends('layouts.app')

@section('content')
<div class="space-y-6"
     x-data="{
        search: '',
        category: '',
        selectedChallenge: null,
        modalOpen: false,
        flagInput: '',
        submitting: false,

        openChallenge(challenge) {
            this.selectedChallenge = challenge;
            this.flagInput = '';
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.modalOpen = false;
            this.selectedChallenge = null;
            document.body.classList.remove('overflow-hidden');
        },

        matchesChallenge(challenge, categoryName) {
            if (this.category && this.category !== categoryName) return false;
            if (!this.search) return true;
            const q = this.search.toLowerCase();
            return challenge.title.toLowerCase().includes(q)
                || categoryName.toLowerCase().includes(q)
                || challenge.tags?.some(t => t.toLowerCase().includes(q));
        },

        get solvedIds() { return {{ Js::from($userSolved) }}; },
        get attemptMap() { return {{ Js::from($userAttempts) }}; },
        get purchasedHints() { return {{ Js::from($purchasedHintTiers ?? []) }}; },
        get categories() { return {{ Js::from($categories) }}; },
        get firstBloods() { return {{ Js::from($firstBloods ?? []) }}; },
        get solveDistribution() { return {{ Js::from($solveDistribution ?? []) }}; },

        renderMarkdown(text) {
            if (!text) return '';
            let html = text
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code class=&quot;language-$1&quot;>$2</code></pre>')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.+?)\*/g, '<em>$1</em>')
                .replace(/^### (.+)$/gm, '<h3>$1</h3>')
                .replace(/^## (.+)$/gm, '<h2>$1</h2>')
                .replace(/^# (.+)$/gm, '<h1>$1</h1>')
                .replace(/^- (.+)$/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
                .replace(/\n{2,}/g, '</p><p>')
                .replace(/\n/g, '<br>');
            return '<p>' + html + '</p>';
        },
    }"
    @keydown.escape.window="closeModal()">

    {{-- Preview banner --}}
    @if ($isPreview ?? false)
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
        <div class="flex items-center gap-2 text-sm font-medium text-amber-700 dark:text-amber-300">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Preview Mode — this is how members see the competition
        </div>
    </div>
    @endif

    {{-- Competition header --}}
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{
            remaining: '',
            startDate: '{{ $competition->start_date?->timestamp }}',
            endDate: '{{ $competition->end_date?->timestamp }}',
            init() {
                if (!this.startDate && !this.endDate) return;
                const update = () => {
                    const now = Math.floor(Date.now() / 1000);
                    const start = parseInt(this.startDate);
                    const end = parseInt(this.endDate);
                    if (start && now < start) {
                        const diff = start - now;
                        this.remaining = `Starts in ${Math.floor(diff/86400)}d ${Math.floor((diff%86400)/3600)}h ${Math.floor((diff%3600)/60)}m ${diff%60}s`;
                    } else if (end && now < end) {
                        const diff = end - now;
                        this.remaining = `${Math.floor(diff/86400)}d ${Math.floor((diff%86400)/3600)}h ${Math.floor((diff%3600)/60)}m ${diff%60}s remaining`;
                    } else if (end && now >= end) {
                        this.remaining = 'Competition ended';
                    }
                };
                update();
                setInterval(update, 1000);
            }
        }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-500">CTF Competition</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $competition->title }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $competition->description }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($competition->start_date) <span>Start: {{ $competition->start_date->format('M d, H:i') }}</span> @endif
                    @if ($competition->end_date) <span>End: {{ $competition->end_date->format('M d, H:i') }}</span> @endif
                    @if ($competition->max_score) <span>Max: {{ $competition->max_score }} pts</span> @endif
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="remaining"></span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('ctf.scoreboard', $competition) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/[0.04]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Scoreboard
                </a>
            </div>
        </div>
        @if ($competition->allow_teams)
        <div class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Team Competition — max {{ $competition->max_team_size }} per team
        </div>
        @endif
    </section>

    {{-- Flash messages --}}
    @if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
        <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</p>
    </div>
    @endif
    @if (session('error'))
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Stats bar --}}
    @php
        $totalChallenges = collect($challengesByCategory)->flatten()->count();
        $solvedCount = count($userSolved);
        $totalPoints = collect($challengesByCategory)->flatten()->sum(fn($c) => $c->points);
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Challenges</p>
            <p class="mt-0.5 text-xl font-semibold text-gray-900 dark:text-white">{{ $totalChallenges }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Solved</p>
            <p class="mt-0.5 text-xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $solvedCount }}/{{ $totalChallenges }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Points</p>
            <p class="mt-0.5 text-xl font-semibold text-gray-900 dark:text-white">{{ collect($challengesByCategory)->flatten()->filter(fn($c) => in_array($c->id, $userSolved))->sum('points') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Progress</p>
            <p class="mt-0.5 text-xl font-semibold text-gray-900 dark:text-white">{{ $totalChallenges > 0 ? round(($solvedCount / $totalChallenges) * 100) : 0 }}%</p>
        </div>
    </div>

    {{-- Search & filter bar --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px] max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="Search challenges..."
                   class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm dark:border-gray-700 dark:bg-gray-800">
        </div>
        <select x-model="category" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
            <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
        <button x-show="search || category" @click="search = ''; category = ''" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            Clear Filters
        </button>
    </div>

    {{-- Category colors map --}}
    @php
        $categoryColors = [
            'Web' => ['bg' => 'bg-blue-500', 'light' => 'bg-blue-50 dark:bg-blue-500/5', 'border' => 'border-blue-200 dark:border-blue-800', 'text' => 'text-blue-600 dark:text-blue-400', 'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400'],
            'Crypto' => ['bg' => 'bg-purple-500', 'light' => 'bg-purple-50 dark:bg-purple-500/5', 'border' => 'border-purple-200 dark:border-purple-800', 'text' => 'text-purple-600 dark:text-purple-400', 'badge' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400'],
            'Forensics' => ['bg' => 'bg-amber-500', 'light' => 'bg-amber-50 dark:bg-amber-500/5', 'border' => 'border-amber-200 dark:border-amber-800', 'text' => 'text-amber-600 dark:text-amber-400', 'badge' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400'],
            'Binary' => ['bg' => 'bg-red-500', 'light' => 'bg-red-50 dark:bg-red-500/5', 'border' => 'border-red-200 dark:border-red-800', 'text' => 'text-red-600 dark:text-red-400', 'badge' => 'bg-red-500/10 text-red-600 dark:text-red-400'],
            'Reversing' => ['bg' => 'bg-orange-500', 'light' => 'bg-orange-50 dark:bg-orange-500/5', 'border' => 'border-orange-200 dark:border-orange-800', 'text' => 'text-orange-600 dark:text-orange-400', 'badge' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400'],
            'OSINT' => ['bg' => 'bg-cyan-500', 'light' => 'bg-cyan-50 dark:bg-cyan-500/5', 'border' => 'border-cyan-200 dark:border-cyan-800', 'text' => 'text-cyan-600 dark:text-cyan-400', 'badge' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400'],
            'Misc' => ['bg' => 'bg-gray-500', 'light' => 'bg-gray-50 dark:bg-gray-500/5', 'border' => 'border-gray-200 dark:border-gray-700', 'text' => 'text-gray-600 dark:text-gray-400', 'badge' => 'bg-gray-500/10 text-gray-600 dark:text-gray-400'],
        ];
    @endphp

    {{-- Challenges grid by category --}}
    <div class="space-y-8">
        @forelse ($challengesByCategory as $categoryName => $challenges)
            @php
                $cc = $categoryColors[$categoryName] ?? $categoryColors['Misc'];
                $solvedInCategory = $challenges->filter(fn($c) => in_array($c->id, $userSolved))->count();
                $totalInCategory = $challenges->count();
            @endphp
            <section x-show="!category || category === '{{ $categoryName }}'">
                {{-- Category header --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-1 rounded-full {{ $cc['bg'] }}"></div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $categoryName }}</h2>
                        <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">{{ $solvedInCategory }}/{{ $totalInCategory }}</span>
                    </div>
                </div>

                {{-- Challenge cards grid --}}
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($challenges as $challenge)
                        @php
                            $isSolved = in_array($challenge->id, $userSolved);
                            $attemptsUsed = $userAttempts[$challenge->id] ?? 0;
                            $maxAttempts = $challenge->max_attempts;
                            $solvedCount = $challenge->solve_count ?? 0;
                            $difficultyColors = [
                                'easy' => 'text-emerald-600 bg-emerald-500/10',
                                'medium' => 'text-amber-600 bg-amber-500/10',
                                'hard' => 'text-orange-600 bg-orange-500/10',
                                'insane' => 'text-red-600 bg-red-500/10',
                            ];
                            $dc = $difficultyColors[$challenge->difficulty] ?? $difficultyColors['easy'];
                        @endphp
                        <button type="button"
                            @click="openChallenge({{ Js::from([
                                'id' => $challenge->id,
                                'title' => $challenge->title,
                                'description' => $challenge->description,
                                'points' => $challenge->dynamic_scoring ? $challenge->getDynamicPoints() : $challenge->points,
                                'original_points' => $challenge->points,
                                'difficulty' => $challenge->difficulty,
                                'category' => $categoryName,
                                'dynamic_scoring' => $challenge->dynamic_scoring,
                                'min_points' => $challenge->min_points,
                                'max_attempts' => $challenge->max_attempts,
                                'solve_count' => $solvedCount,
                                'tags' => $challenge->tags ?? [],
                                'has_files' => $challenge->relationLoaded('files') && $challenge->files->count() > 0,
                                'files' => $challenge->relationLoaded('files') ? $challenge->files->map(fn($f) => ['id' => $f->id, 'name' => $f->original_name, 'size' => $f->getSizeForHumans(), 'url' => $f->getDownloadUrl()]) : [],
                                'has_hints' => $challenge->relationLoaded('hints') && $challenge->hints->count() > 0,
                                'hints' => $challenge->relationLoaded('hints') ? $challenge->hints->map(fn($h) => ['id' => $h->id, 'tier' => $h->tier, 'content' => $h->content, 'cost' => $h->cost]) : [],
                                'purchased_hint_tiers' => $purchasedHintTiers[$challenge->id] ?? [],
                                'submit_url' => route('ctf.submit', ['competition' => $competition, 'challenge' => $challenge]),
                                'writeup_url' => route('ctf.writeup', ['competition' => $competition, 'challenge' => $challenge]),
                                'writeups_url' => route('ctf.writeups', ['competition' => $competition, 'challenge' => $challenge]),
                                'hint_purchase_url' => route('ctf.hint.purchase', ['competition' => $competition, 'challenge' => $challenge]),
                                'first_blood' => $firstBloods[$challenge->id] ?? null,
                                'solve_distribution' => $solveDistribution[$challenge->id] ?? [],
                            ]) }})"
                            x-show="matchesChallenge({{ Js::from(['title' => $challenge->title, 'tags' => $challenge->tags ?? []]) }}, '{{ $categoryName }}')"
                            class="group relative flex flex-col rounded-lg border-2 p-4 text-left transition-all duration-150
                                {{ $isSolved
                                    ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900 dark:bg-emerald-900/10 opacity-70 hover:opacity-100'
                                    : 'border-gray-200 bg-white hover:border-emerald-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-900/40 dark:hover:border-emerald-700'
                                }}">

                            {{-- Solved badge --}}
                            @if ($isSolved)
                            <div class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500">
                                <svg class="h-3.5 w-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            @endif

                            {{-- First blood indicator --}}
                            @if (isset($firstBloods[$challenge->id]) && $firstBloods[$challenge->id]['user_id'] !== auth()->id())
                            <div class="absolute left-2 top-2" title="First blood: {{ $firstBloods[$challenge->id]['user_name'] }}">
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-500/10 px-2 py-0.5 text-[10px] font-semibold text-red-600 dark:text-red-400">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ Str::limit($firstBloods[$challenge->id]['user_name'], 8) }}
                                </span>
                            </div>
                            @endif

                            {{-- Difficulty indicator line --}}
                            <div class="mb-3 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $dc }}">
                                    {{ $challenge->difficulty }}
                                </span>
                                @if ($challenge->dynamic_scoring)
                                <span class="inline-flex items-center rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-600">Dynamic</span>
                                @endif
                                @if ($solvedCount > 0)
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $solvedCount }} solves</span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-snug {{ $isSolved ? 'line-through decoration-emerald-400/50' : '' }}">
                                {{ $challenge->title }}
                            </h3>

                            {{-- Tags --}}
                            @if ($challenge->tags && count($challenge->tags) > 0)
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach (array_slice($challenge->tags, 0, 3) as $tag)
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-500 dark:bg-gray-800 dark:text-gray-400">#{{ $tag }}</span>
                                @endforeach
                                @if (count($challenge->tags) > 3)
                                <span class="text-[10px] text-gray-400">+{{ count($challenge->tags) - 3 }}</span>
                                @endif
                            </div>
                            @endif

                            {{-- Spacer --}}
                            <div class="mt-auto pt-3 flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $challenge->dynamic_scoring ? $challenge->getDynamicPoints() : $challenge->points }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">pts</span>
                                </div>
                                @if ($maxAttempts > 0)
                                <div class="flex items-center gap-1 text-[10px] {{ $attemptsUsed >= $maxAttempts ? 'text-red-500' : 'text-gray-400' }}">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ $attemptsUsed }}/{{ $maxAttempts }}
                                </div>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>
        @empty
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-12 text-center dark:border-gray-800 dark:bg-gray-900/60">
            <p class="text-gray-600 dark:text-gray-400">No active challenges in this competition.</p>
        </div>
        @endforelse
    </div>

    {{-- Teams section --}}
    @if ($competition->allow_teams && isset($teams))
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Teams</h2>
            @if (!$userTeam)
            <button type="button" onclick="document.getElementById('create-team-form-competition').classList.toggle('hidden')"
                    class="rounded-md bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-600">
                + Create Team
            </button>
            @endif
        </div>

        {{-- Create team form --}}
        <form id="create-team-form-competition" method="POST" action="{{ route('ctf.team.create', $competition) }}" class="hidden mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            @csrf
            <input type="text" name="name" placeholder="Team name" required
                   class="mb-2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
            <textarea name="description" rows="2" placeholder="Team description (optional)"
                      class="mb-2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></textarea>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-indigo-500 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-600">Create</button>
                <button type="button" onclick="this.closest('form').classList.add('hidden')" class="rounded-md border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
            </div>
        </form>

        {{-- Current user's team --}}
        @if ($userTeam)
        <div class="mb-4 rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-900/20">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-indigo-700 dark:text-indigo-300">{{ $userTeam->name }}</h3>
                <div class="flex gap-2">
                    @if ($userTeam->isCaptain(auth()->user()))
                    <button type="button" onclick="document.getElementById('team-settings-form').classList.toggle('hidden')" class="text-xs text-indigo-600 hover:text-indigo-500">Settings</button>
                    <form method="POST" action="{{ route('ctf.team.disband', $competition) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-red-600 hover:text-red-500" onclick="return confirm('Disband this team? This cannot be undone.')">Disband</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('ctf.team.leave', $competition) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-red-600 hover:text-red-500">Leave</button>
                    </form>
                    @endif
                </div>
            </div>

            @if ($userTeam->isCaptain(auth()->user()))
            <form id="team-settings-form" method="POST" action="{{ route('ctf.team.settings', $competition) }}" class="hidden mb-3 rounded-md border border-indigo-200 bg-indigo-100/50 p-3 dark:border-indigo-800 dark:bg-indigo-900/30">
                @csrf
                <label class="block text-xs font-medium text-indigo-700 dark:text-indigo-300 mb-1">Team Name</label>
                <input type="text" name="name" value="{{ $userTeam->name }}" required class="mb-2 w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800">
                <label class="flex items-center gap-2 text-xs text-indigo-700 dark:text-indigo-300 mb-2">
                    <input type="checkbox" name="is_open" value="1" {{ $userTeam->is_open ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700">
                    Open for new members
                </label>
                <button type="submit" class="rounded-md bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-600">Save</button>
            </form>
            @endif

            <p class="text-xs text-indigo-600/70 dark:text-indigo-400/70">Invite code: <code class="font-mono bg-indigo-200/50 px-1 rounded dark:bg-indigo-800/50">{{ $userTeam->invite_code }}</code></p>

            <div class="mt-3 space-y-1.5">
                @foreach ($userTeam->members as $member)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-700 dark:text-gray-300">{{ $member->user->name }}</span>
                    <div class="flex items-center gap-2">
                        @if ($member->role === 'captain')
                        <span class="rounded-md bg-indigo-500/10 px-1.5 py-0.5 text-indigo-600">Captain</span>
                        @endif
                        @if ($userTeam->isCaptain(auth()->user()) && $member->role !== 'captain')
                        <form method="POST" action="{{ route('ctf.team.transfer-captaincy', $competition) }}" class="inline" onsubmit="return confirm('Transfer captaincy to {{ $member->user->name }}?')">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                            <button type="submit" class="text-indigo-600 hover:text-indigo-500">Make Captain</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <form method="POST" action="{{ route('ctf.team.join', $competition) }}" class="mb-4 flex gap-2">
            @csrf
            <input type="text" name="invite_code" placeholder="Invite code" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800">
            <button type="submit" class="rounded-md bg-indigo-500 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-600">Join</button>
        </form>
        @endif

        <div class="space-y-2">
            @forelse ($teams as $team)
            <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/60">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">
                        {{ $team->name }}
                        @if ($team->isOpen())
                        <span class="ml-1.5 rounded-md bg-green-500/10 px-1.5 py-0.5 text-xs text-green-600">Open</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $team->members_count ?? $team->activeMembers()->count() }} members</p>
                </div>
                <div class="text-right">
                    @if ($userTeam && !$team->isMember(auth()->user()))
                    <form method="POST" action="{{ route('ctf.team.join', $competition) }}" class="inline">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-500">Request Join</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-500 dark:text-gray-400">No teams yet. Create one!</p>
            @endforelse
        </div>
    </section>
    @endif

    {{-- Challenge detail drawer --}}
    <template x-teleport="body">
        <x-ui.drawer
            show="modalOpen"
            on-close="closeModal()"
            width="lg"
            x-data="{
                submitted: false,
                submitError: '',
                revealingHint: null,
                submitFlag() {
                    const challenge = this.selectedChallenge;
                    if (!this.flagInput.trim() || !challenge) return;
                    this.submitted = true;
                    this.submitError = '';
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = challenge.submit_url;
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = {{ Js::from(csrf_token()) }};
                    form.appendChild(csrf);
                    const flag = document.createElement('input');
                    flag.type = 'hidden';
                    flag.name = 'flag';
                    flag.value = this.flagInput;
                    form.appendChild(flag);
                    document.body.appendChild(form);
                    form.submit();
                }
            }"
        >
                {{-- Modal content bound to selectedChallenge --}}
                <template x-if="selectedChallenge">
                    <div class="p-6">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                                          x-bind:class="{
                                            'text-emerald-600 bg-emerald-500/10': selectedChallenge.difficulty === 'easy',
                                            'text-amber-600 bg-amber-500/10': selectedChallenge.difficulty === 'medium',
                                            'text-orange-600 bg-orange-500/10': selectedChallenge.difficulty === 'hard',
                                            'text-red-600 bg-red-500/10': selectedChallenge.difficulty === 'insane',
                                          }"
                                          x-text="selectedChallenge.difficulty"></span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                                          x-bind:class="{
                                            'bg-blue-500/10 text-blue-600': selectedChallenge.category === 'Web',
                                            'bg-purple-500/10 text-purple-600': selectedChallenge.category === 'Crypto',
                                            'bg-amber-500/10 text-amber-600': selectedChallenge.category === 'Forensics',
                                            'bg-red-500/10 text-red-600': selectedChallenge.category === 'Binary',
                                            'bg-orange-500/10 text-orange-600': selectedChallenge.category === 'Reversing',
                                            'bg-cyan-500/10 text-cyan-600': selectedChallenge.category === 'OSINT',
                                            'bg-gray-500/10 text-gray-600': !['Web','Crypto','Forensics','Binary','Reversing','OSINT'].includes(selectedChallenge.category),
                                          }"
                                          x-text="selectedChallenge.category"></span>
                                    <span x-show="selectedChallenge.dynamic_scoring"
                                          class="rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-600">Dynamic</span>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedChallenge.title"></h2>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="selectedChallenge.points"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">points</p>
                                <p x-show="selectedChallenge.dynamic_scoring" class="text-[10px] text-purple-500">
                                    <span x-text="selectedChallenge.original_points"></span> max · min <span x-text="selectedChallenge.min_points || Math.round(selectedChallenge.original_points * 0.5)"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Solve count --}}
                        <div x-show="selectedChallenge.solve_count > 0" class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="selectedChallenge.solve_count + ' solves'"></span>
                            </span>
                            <span x-show="selectedChallenge.first_blood" class="inline-flex items-center gap-1 text-red-500 dark:text-red-400">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                                </svg>
                                <span>First blood: <span x-text="selectedChallenge.first_blood?.user_name"></span></span>
                            </span>
                        </div>

                        {{-- Tags --}}
                        <div x-show="selectedChallenge.tags?.length > 0" class="mt-3 flex flex-wrap gap-1.5">
                            <template x-for="tag in selectedChallenge.tags" :key="tag">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400" x-text="'#' + tag"></span>
                            </template>
                        </div>

                        {{-- Description --}}
                        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50 prose prose-sm dark:prose-invert max-w-none prose-p:my-1 prose-pre:my-2 prose-code:before:content-none prose-code:after:content-none prose-code:bg-gray-200/50 dark:prose-code:bg-gray-700/50 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-code:text-[13px] prose-code:font-mono" x-html="renderMarkdown(selectedChallenge.description)"></div>

                        {{-- Solve distribution --}}
                        <div x-show="selectedChallenge.solve_distribution?.length > 0" class="mt-3">
                            <p class="mb-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Solve Timeline</p>
                            <div class="flex items-end gap-0.5 h-8">
                                <template x-for="(solve, idx) in selectedChallenge.solve_distribution" :key="idx">
                                    <div class="flex-1 rounded-t bg-emerald-400 dark:bg-emerald-600 transition-all hover:bg-emerald-300 dark:hover:bg-emerald-500"
                                         :style="'height: ' + Math.max(20, (solve.points / (selectedChallenge.original_points || selectedChallenge.points)) * 100) + '%'"
                                         :title="'#' + solve.order + ' — ' + solve.points + ' pts'">
                                    </div>
                                </template>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Solves over time (height = points awarded)</p>
                        </div>

                        {{-- Attempt count --}}
                        <div x-show="selectedChallenge.max_attempts > 0" class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Attempts: <span x-text="(attemptMap[selectedChallenge.id] || 0) + '/' + selectedChallenge.max_attempts"></span>
                        </div>

                        {{-- Files --}}
                        <div x-show="selectedChallenge.has_files" class="mt-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Attachments</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="file in selectedChallenge.files" :key="file.id">
                                    <a :href="file.url" target="_blank"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span x-text="file.name"></span>
                                        <span class="text-gray-400" x-text="'(' + file.size + ')'"></span>
                                    </a>
                                </template>
                            </div>
                        </div>

                        {{-- Hints --}}
                        <div x-show="selectedChallenge.has_hints" class="mt-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Hints</p>
                            <div class="space-y-2">
                                <template x-for="(hint, hintIdx) in selectedChallenge.hints" :key="hint.id">
                                    <div x-data="{ open: false }" class="rounded-lg border border-amber-200 dark:border-amber-800">
                                        <button @click="open = !open"
                                            class="flex w-full items-center justify-between px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-900/20 transition-colors">
                                            <span x-text="'Hint ' + (hintIdx + 1) + (hint.cost === 0 ? ' (Free)' : '')"></span>
                                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        {{-- Free or purchased hints: show content --}}
                                        <div x-show="open && (hint.cost === 0 || selectedChallenge.purchased_hint_tiers.includes(hint.tier))"
                                             x-collapse
                                             class="border-t border-amber-200 dark:border-amber-800 bg-amber-50 px-3 py-3 dark:bg-amber-900/20">
                                            <p class="text-sm text-amber-800 dark:text-amber-200" x-text="hint.content"></p>
                                        </div>
                                        {{-- Locked hints: show purchase button --}}
                                        <div x-show="open && hint.cost > 0 && !selectedChallenge.purchased_hint_tiers.includes(hint.tier)"
                                             x-collapse
                                             class="border-t border-amber-200 dark:border-amber-800 bg-amber-50 px-3 py-3 dark:bg-amber-900/20">
                                            <p class="text-xs text-amber-600 dark:text-amber-400 mb-2">This hint costs <strong x-text="hint.cost"></strong> points.</p>
                                            <form method="POST" :action="selectedChallenge.hint_purchase_url" class="inline">
                                                @csrf
                                                <input type="hidden" name="hint_tier" :value="hint.tier">
                                                <button type="submit"
                                                    class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600"
                                                    onclick="return confirm('Spend ' + this.form.querySelector('[name=hint_tier]').value + ' points to reveal this hint?')">
                                                    Unlock Hint (<span x-text="hint.cost"></span> pts)
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Flag submission --}}
                        <div class="mt-5 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Submit Flag</p>
                            <div class="flex gap-2">
                                <input type="text"
                                       x-model="flagInput"
                                       placeholder="SLAU_CSIC{flag}"
                                       class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder:text-gray-500"
                                       @keydown.enter.prevent="submitFlag()">
                                <button type="button" @click="submitFlag()" :disabled="!flagInput.trim()"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Submit
                                </button>
                            </div>
                        </div>

                        {{-- Writeup --}}
                        <div class="mt-4 flex gap-2">
                            <a :href="selectedChallenge.writeups_url"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                </svg>
                                View Writeups
                            </a>
                            <a :href="selectedChallenge.writeup_url"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 px-3.5 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Submit Writeup
                            </a>
                        </div>
                    </div>
                </template>
        </x-ui.drawer>
    </template>
</div>
@endsection
