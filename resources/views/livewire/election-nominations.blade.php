<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Applications</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Apply for a cabinet position
            </h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                Submit your candidacy for open positions. Include your manifesto, agenda, and a personal
                statement. Your application will be reviewed by the administration.
            </p>
            <a href="{{ route('voting.my-applications') }}" wire:navigate class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600 hover:underline dark:text-emerald-400">
                Track your submitted applications &rarr;
            </a>
        </div>

        @php
            $statusBadges = [
                'submitted' => ['color' => 'text-gray-600 bg-gray-100 dark:text-gray-300 dark:bg-gray-800', 'label' => 'Submitted'],
                'under_review' => ['color' => 'text-amber-600 bg-amber-100 dark:text-amber-300 dark:bg-amber-900/30', 'label' => 'Under Review'],
                'shortlisted' => ['color' => 'text-blue-600 bg-blue-100 dark:text-blue-300 dark:bg-blue-900/30', 'label' => 'Shortlisted'],
                'approved' => ['color' => 'text-emerald-600 bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/30', 'label' => 'Approved'],
                'rejected' => ['color' => 'text-red-600 bg-red-100 dark:text-red-300 dark:bg-red-900/30', 'label' => 'Rejected'],
                'withdrawn' => ['color' => 'text-red-600 bg-red-100 dark:text-red-300 dark:bg-red-900/30', 'label' => 'Withdrawn'],
            ];
        @endphp

        @if ($elections->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">No positions open for applications</h2>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Check back later when new elections are announced.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($elections as $election)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-500">
                                    {{ $election['position'] }}
                                </p>
                                <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $election['title'] }}
                                </h2>
                                @if ($election['description'])
                                    <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                        {{ $election['description'] }}
                                    </p>
                                @endif
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $election['candidates_count'] }} candidate(s) so far
                                </p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-right dark:border-gray-700 dark:bg-gray-900/60">
                                <div class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">Status</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ ucfirst($election['status']) }}
                                </div>
                            </div>
                        </div>

                        @if ($election['user_nomination'])
                            <div class="mt-6 space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Your Application</p>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadges[$election['user_nomination']['status']]['color'] ?? 'text-gray-500 bg-gray-100' }}">
                                        {{ $statusBadges[$election['user_nomination']['status']]['label'] ?? $election['user_nomination']['status'] }}
                                    </span>
                                </div>
                                @if ($election['user_nomination']['photo'])
                                    <img src="{{ asset('storage/' . $election['user_nomination']['photo']) }}" alt="Nomination photo" class="h-16 w-16 rounded-lg object-cover" />\n                                @endif\n                                @if ($election['user_nomination']['statement'])
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Personal Statement</p>
                                        <p class="mt-1 text-sm italic text-gray-700 dark:text-gray-300">"{{ $election['user_nomination']['statement'] }}"</p>
                                    </div>
                                @endif
                                @if ($election['user_nomination']['manifesto'])
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Manifesto</p>
                                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $election['user_nomination']['manifesto'] }}</p>
                                    </div>
                                @endif
                                @if ($election['user_nomination']['agenda'])
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Agenda</p>
                                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $election['user_nomination']['agenda'] }}</p>
                                    </div>
                                @endif
                                @if ($election['user_nomination']['admin_notes'])
                                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                                        <p class="text-xs font-medium text-amber-800 dark:text-amber-200">Admin Notes</p>
                                        <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">{{ $election['user_nomination']['admin_notes'] }}</p>
                                    </div>
                                @endif
                                @if ($election['user_nomination']['submitted_at'])
                                    <p class="text-xs text-gray-400">Submitted {{ \Carbon\Carbon::parse($election['user_nomination']['submitted_at'])->format('M j, Y') }}</p>
                                @endif
                            </div>
                        @else
                            <div class="mt-6 space-y-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Photo</p>
                                    <div class="mt-1.5 flex items-center gap-4">
                                        @if (isset($photoPreviews[$election['id']]))
                                            <img src="{{ $photoPreviews[$election['id']] }}" alt="Photo preview" class="h-16 w-16 rounded-lg object-cover" />
                                        @else
                                            <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-800">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <input type="file" accept="image/jpeg,image/png,image/webp" wire:model="photos.{{ $election['id'] }}" class="text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-emerald-600 hover:file:bg-emerald-100 dark:text-gray-400 dark:file:bg-emerald-900/30 dark:file:text-emerald-300">
                                    </div>
                                    @error("photos.{$election['id']}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supporting Documents</p>
                                    <p class="mt-0.5 text-xs text-gray-400">Upload your CV, portfolio, or other documents (PDF, DOC, max 10MB each)</p>
                                    <input type="file" multiple accept=".pdf,.doc,.docx" wire:model="documentFiles.{{ $election['id'] }}" class="mt-1.5 text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-emerald-600 hover:file:bg-emerald-100 dark:text-gray-400 dark:file:bg-emerald-900/30 dark:file:text-emerald-300">
                                    @error("documentFiles.{$election['id']}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-900">
                                    <button wire:click="$set('activeTab', 'statement')" class="flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $activeTab === 'statement' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                        Personal Statement
                                    </button>
                                    <button wire:click="$set('activeTab', 'manifesto')" class="flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $activeTab === 'manifesto' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                                        Manifesto &amp; Agenda
                                    </button>
                                </div>

                                @if ($activeTab === 'statement')
                                    <div>
                                        <textarea wire:model="statements.{{ $election['id'] }}" placeholder="Why are you running for this position? Tell members about yourself..." rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                                        @error("statements.{$election['id']}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                @else
                                    <div class="space-y-4">
                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Manifesto</p>
                                            <textarea wire:model="manifestos.{{ $election['id'] }}" placeholder="Your vision, values, and what you stand for..." rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                                            @error("manifestos.{$election['id']}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Agenda</p>
                                            <textarea wire:model="agendas.{{ $election['id'] }}" placeholder="Specific goals, projects, and plans you will pursue..." rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                                            @error("agendas.{$election['id']}") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @endif

                                <button wire:click="submitNomination({{ $election['id'] }})" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 transition-colors">
                                    <span wire:loading.remove wire:target="submitNomination({{ $election['id'] }})">Submit Application</span>
                                    <span wire:loading wire:target="submitNomination({{ $election['id'] }})">Submitting...</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
