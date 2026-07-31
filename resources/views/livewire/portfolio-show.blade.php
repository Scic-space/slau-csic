<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Profile Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-50"></div>
        <div class="relative mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center gap-8 sm:flex-row">
                <div class="relative flex-shrink-0">
                    @if ($student->profile_photo)
                        <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->name }}" class="h-32 w-32 rounded-2xl border-4 border-white/20 object-cover shadow-2xl">
                    @else
                        <div class="flex h-32 w-32 items-center justify-center rounded-2xl border-4 border-white/20 bg-white/10 text-4xl font-bold text-white shadow-2xl">
                            {{ strtoupper(substr($student->name, 0, 2)) }}
                        </div>
                    @endif
                    <span class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full bg-green-500 text-white shadow-lg">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>

                <div class="text-center sm:text-left">
                    <h1 class="text-3xl font-bold text-white">{{ $student->name }}</h1>
                    @if ($student->headline)
                        <p class="mt-1 text-lg text-indigo-100">{{ $student->headline }}</p>
                    @endif
                    @if ($student->program || $student->specialization_track)
                        <p class="mt-1 text-sm text-indigo-200">
                            @if ($student->program) {{ $student->program }} @endif
                            @if ($student->program && $student->specialization_track) &middot; @endif
                            @if ($student->specialization_track) {{ $student->specialization_track }} @endif
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                        @if ($student->github_username)
                            <a href="https://github.com/{{ $student->github_username }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-sm font-medium text-white backdrop-blur-sm hover:bg-white/20 transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                {{ $student->github_username }}
                            </a>
                        @endif
                        @if ($student->linkedin_url)
                            <a href="{{ $student->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-sm font-medium text-white backdrop-blur-sm hover:bg-white/20 transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                LinkedIn
                            </a>
                        @endif
                        @if ($student->personal_website)
                            <a href="{{ $student->personal_website }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-sm font-medium text-white backdrop-blur-sm hover:bg-white/20 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                Website
                            </a>
                        @endif
                        @if ($student->htb_username)
                            <a href="https://app.hackthebox.com/profile/{{ $student->htb_username }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-green-500/20 px-3 py-1.5 text-sm font-medium text-green-100 backdrop-blur-sm hover:bg-green-500/30 transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.996 0C5.373 0 0 5.373 0 12s5.373 12 11.996 12C18.629 24 24 18.627 24 12S18.629 0 11.996 0zm5.643 18.36H8.362V5.64h9.277v12.72z"/></svg>
                            {{ $student->htb_username }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-bold text-white">{{ $student->portfolioEntries->count() }}</p>
                    <p class="text-xs text-indigo-200">Projects</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-bold text-white">{{ $student->portfolioSkills->count() }}</p>
                    <p class="text-xs text-indigo-200">Skills</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-bold text-white">{{ $student->portfolioCertifications->count() }}</p>
                    <p class="text-xs text-indigo-200">Certifications</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm">
                    <p class="text-2xl font-bold text-white">{{ $student->score }}</p>
                    <p class="text-xs text-indigo-200">Club Score</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Bio --}}
        @if ($student->bio)
            <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $student->bio }}</p>
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="mb-8 flex gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @foreach (['projects' => 'Projects & Work', 'skills' => 'Skills', 'certifications' => 'Certifications', 'experience' => 'Experience'] as $key => $label)
                <button wire:click="setTab('{{ $key }}')" class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors
                    {{ $tab === $key ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Tab Content --}}
        <div wire:loading.class="opacity-50" wire:loading.delay>
            {{-- Projects Tab --}}
            @if ($tab === 'projects')
                @if ($student->portfolioEntries->isEmpty())
                    <div class="rounded-2xl border border-gray-200 bg-white py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No projects published yet.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        @foreach ($student->portfolioEntries as $project)
                            <div class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                                @if ($project->screenshot_path)
                                    <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-900">
                                        <img src="{{ $project->screenshot_url }}" alt="{{ $project->title }}" class="h-full w-full object-cover transition-transform group-hover:scale-105">
                                    </div>
                                @else
                                    <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600">
                                        <svg class="h-16 w-16 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                @endif

                                <div class="p-5">
                                    <div class="mb-2 flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ match($project->category) {
                                                'project' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                'ctf' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                                'tool' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                'research' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'writeup' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                'achievement' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                default => 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                            } }}">
                                            {{ ucfirst($project->category) }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $project->title }}</h3>
                                    @if ($project->description)
                                        <p class="mt-2 line-clamp-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $project->description }}</p>
                                    @endif

                                    @if ($project->tech_stack && count($project->tech_stack) > 0)
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach ($project->tech_stack as $tech)
                                                <span class="inline-flex rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $tech }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if ($project->live_url)
                                            <a href="{{ $project->live_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                Live Demo
                                            </a>
                                        @endif
                                        @if ($project->repo_url)
                                            <a href="{{ $project->repo_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                                Source
                                            </a>
                                        @endif
                                        @if ($project->file_path)
                                            <a href="{{ $project->file_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                Download
                                            </a>
                                        @endif
                                        @if ($project->external_link)
                                            <a href="{{ $project->external_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                Link
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- Skills Tab --}}
            @if ($tab === 'skills')
                @if ($student->portfolioSkills->isEmpty())
                    <div class="rounded-2xl border border-gray-200 bg-white py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No skills added yet.</p>
                    </div>
                @else
                    @php $grouped = $student->portfolioSkills->groupBy('category'); @endphp
                    <div class="space-y-6">
                        @foreach ($grouped as $category => $skills)
                            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ ucfirst($category) }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($skills as $skill)
                                        <div class="group relative">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-700 transition-all hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300">
                                                {{ $skill->name }}
                                                <span class="flex gap-0.5">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $i <= $skill->proficiency ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                                    @endfor
                                                </span>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- Certifications Tab --}}
            @if ($tab === 'certifications')
                @if ($student->portfolioCertifications->isEmpty())
                    <div class="rounded-2xl border border-gray-200 bg-white py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No certifications yet.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach ($student->portfolioCertifications as $cert)
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl {{ $cert->isExpired() ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                                        <svg class="h-6 w-6 {{ $cert->isExpired() ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $cert->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $cert->issuer }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                                            @if ($cert->date_earned)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    {{ $cert->date_earned->format('M Y') }}
                                                </span>
                                            @endif
                                            @if ($cert->expiry_date)
                                                <span class="inline-flex items-center gap-1 {{ $cert->isExpired() ? 'text-red-500' : ($cert->isExpiringSoon() ? 'text-amber-500' : '') }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Expires {{ $cert->expiry_date->format('M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($cert->credential_url)
                                            <a href="{{ $cert->credential_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                Verify Credential
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- Experience Tab --}}
            @if ($tab === 'experience')
                @if ($student->portfolioExperiences->isEmpty())
                    <div class="rounded-2xl border border-gray-200 bg-white py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No experience listed yet.</p>
                    </div>
                @else
                    <div class="relative space-y-0">
                        <div class="absolute left-6 top-0 bottom-0 w-px bg-gray-200 dark:bg-gray-700"></div>
                        @foreach ($student->portfolioExperiences as $exp)
                            <div class="relative flex gap-6 py-6">
                                <div class="relative z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border-2 border-white bg-indigo-100 shadow-sm dark:border-gray-800 dark:bg-indigo-900/30">
                                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="flex-1 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $exp->title }}</h3>
                                            <p class="text-sm text-indigo-600 dark:text-indigo-400">{{ $exp->organization }}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ match($exp->type) {
                                                'experience' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                'education' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                'volunteer' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                'leadership' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                default => 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                            } }}">
                                            {{ ucfirst($exp->type) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        {{ $exp->start_date->format('M Y') }} &mdash;
                                        @if ($exp->is_current)
                                            <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Present
                                            </span>
                                        @elseif ($exp->end_date)
                                            {{ $exp->end_date->format('M Y') }}
                                        @endif
                                    </p>
                                    @if ($exp->description)
                                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $exp->description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
