<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Dashboard</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">assignment_ind</span>My Applications</h1>
            <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                Track the status of all your cabinet applications.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <button type="button" wire:click="openApplicationForm" class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors">
                    Submit new application
                </button>
                <a href="{{ route('voting.nominations') }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Or browse open positions
                </a>
            </div>
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
            $statusOrder = ['submitted', 'under_review', 'shortlisted', 'approved'];
        @endphp

        @if ($applications->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-border dark:bg-card">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">No applications yet</h2>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    <button type="button" wire:click="openApplicationForm" class="text-emerald-500 hover:underline">Submit an application</button>
                    for an open cabinet position.
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($applications as $app)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-border dark:bg-card">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-500">
                                    {{ $app['election']['position'] }}
                                </p>
                                <h2 class="mt-1 flex items-center gap-2 text-xl font-semibold text-gray-900 dark:text-white">
                                    <span class="material-symbols-outlined text-emerald-500" aria-hidden="true">assignment</span>{{ $app['election']['title'] }}
                                </h2>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1.5 text-xs">
                                    @php
                                        $currentIdx = array_search($app['status'], $statusOrder);
                                        $isRejected = $app['status'] === 'rejected' || $app['status'] === 'withdrawn';
                                    @endphp
                                    @foreach ($statusOrder as $i => $step)
                                        @php $done = $i <= $currentIdx && !$isRejected; @endphp
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $done ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400 dark:bg-gray-700' }}">
                                                {{ $isRejected && $i === 1 ? '!' : ($done && $i < $currentIdx ? '✓' : $i + 1) }}
                                            </div>
                                            <span class="{{ $done ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">{{ $statusBadges[$step]['label'] }}</span>
                                            @if ($i < count($statusOrder) - 1)
                                                <div class="h-px w-4 {{ $done && $i < $currentIdx ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if ($isRejected)
                                        <div class="h-px w-4 bg-red-400"></div>
                                        <div class="flex h-5 w-7 items-center justify-center rounded-full text-[10px] font-bold text-white {{ $app['status'] === 'withdrawn' ? 'bg-gray-500' : 'bg-red-500' }}">X</div>
                                        <span class="font-medium text-red-500">{{ $app['status'] === 'withdrawn' ? 'Withdrawn' : 'Rejected' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @if ($app['photo'])
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Photo</p>
                                    <img src="{{ $app['photo'] }}" alt="Application photo" class="mt-1 h-20 w-20 rounded-lg object-cover" />
                                </div>
                            @endif
                            @if ($app['score_average'] !== null)
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Average Score</p>
                                    <p class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $app['score_average'] }}/5</p>
                                </div>
                            @endif
                            @if ($app['statement'])
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Statement</p>
                                    <p class="mt-1 text-sm italic text-gray-700 dark:text-gray-300">"{{ $app['statement'] }}"</p>
                                </div>
                            @endif
                            @if ($app['manifesto'])
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Manifesto</p>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $app['manifesto'] }}</p>
                                </div>
                            @endif
                            @if ($app['agenda'])
                                <div class="sm:col-span-2">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Agenda</p>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $app['agenda'] }}</p>
                                </div>
                            @endif
                            @if (count($app['documents']) > 0)
                                <div class="sm:col-span-2">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Documents</p>
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        @foreach ($app['documents'] as $i => $doc)
                                            <a href="{{ $doc }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-background px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-border dark:bg-background/60 dark:text-gray-300 dark:hover:bg-card-hover">
                                                Document {{ $i + 1 }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($app['interview_scheduled_at'])
                            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
                                <p class="text-xs font-medium text-blue-800 dark:text-blue-200">Interview Scheduled</p>
                                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                    {{ \Carbon\Carbon::parse($app['interview_scheduled_at'])->format('l, F j, Y g:i A') }}
                                </p>
                                @if ($app['interview_location'])
                                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ $app['interview_location'] }}</p>
                                @endif
                                @if ($app['interview_notes'])
                                    <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">{{ $app['interview_notes'] }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
                            @if ($app['submitted_at'])
                                <span>Submitted {{ \Carbon\Carbon::parse($app['submitted_at'])->format('F j, Y') }}</span>
                            @endif
                            @if ($app['reviewed_at'])
                                <span>Reviewed {{ \Carbon\Carbon::parse($app['reviewed_at'])->format('F j, Y') }}</span>
                            @endif
                            @if ($app['reviewer_name'])
                                <span>Reviewer: {{ $app['reviewer_name'] }}</span>
                            @endif
                        </div>

                        @if ($app['admin_notes'])
                            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                                <p class="text-xs font-medium text-amber-800 dark:text-amber-200">Admin Notes</p>
                                <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">{{ $app['admin_notes'] }}</p>
                            </div>
                        @endif

                        @if (count($app['reviews']) > 0)
                            <div class="mt-4" x-data="{ open: false }">
                                <button @click="open = !open" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <span x-show="!open">Show</span><span x-show="open">Hide</span> review history ({{ count($app['reviews']) }})
                                </button>
                                <div x-show="open" class="mt-2 space-y-2">
                                    @foreach ($app['reviews'] as $review)
                                        <div class="rounded-lg border border-gray-200 bg-background p-3 dark:border-border dark:bg-background/60">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $review['reviewer_name'] }}</span>
                                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($review['created_at'])->format('M j, Y') }}</span>
                                            </div>
                                            <div class="mt-0.5 text-xs">
                                                @if ($review['from_status'])
                                                    <span class="text-gray-500">{{ $review['from_status'] }}</span>
                                                @endif
                                                @if ($review['from_status'])
                                                    <span class="text-gray-500"> &rarr; </span>
                                                @endif
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $review['to_status'] }}</span>
                                            </div>
                                            @if ($review['notes'])
                                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $review['notes'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-3">
                            @if ($app['can_withdraw'])
                                <button wire:click="withdrawNomination({{ $app['election']['id'] }})" wire:confirm="Are you sure you want to withdraw your application?" class="inline-flex items-center rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20">
                                    Withdraw Application
                                </button>
                            @endif
                            @if ($app['can_reapply'])
                                <a href="{{ route('voting.nominations') }}" wire:navigate class="inline-flex items-center rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors">
                                    Re-apply
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div x-data="{ open: @entangle('showForm'), photoPreview: null }" wire:key="new-application-drawer">
            <x-ui.drawer show="open" on-close="$wire.closeApplicationForm()" title="Submit New Application">
                <form wire:submit="submitApplication" class="flex min-h-0 flex-1 flex-col">
                    <div class="flex-1 space-y-4 overflow-y-auto p-5">
                        <div>
                            <label for="application-position" class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Position</label>
                            <select id="application-position" wire:model="selectedElectionId" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                                <option value="">Select an open position...</option>
                                @foreach ($openElections as $election)
                                    <option value="{{ $election->id }}">{{ $election->position }} &mdash; {{ $election->title }}</option>
                                @endforeach
                            </select>
                            @error('selectedElectionId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @if ($openElections->isEmpty())
                                <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">No positions are currently open for applications.</p>
                            @endif
                        </div>

                        <div>
                            <label for="application-statement" class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Personal Statement</label>
                            <textarea id="application-statement" wire:model="statement" rows="3" placeholder="Why are you running for this position? Tell members about yourself..." class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white"></textarea>
                            @error('statement') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="application-manifesto" class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Manifesto</label>
                            <textarea id="application-manifesto" wire:model="manifesto" rows="3" placeholder="Your vision, values, and what you stand for..." class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white"></textarea>
                            @error('manifesto') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="application-agenda" class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Agenda</label>
                            <textarea id="application-agenda" wire:model="agenda" rows="3" placeholder="Specific goals, projects, and plans you will pursue..." class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white"></textarea>
                            @error('agenda') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Photo</p>
                            <div class="mt-1.5 flex items-center gap-4">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" alt="Photo preview" class="h-16 w-16 rounded-lg object-cover" />
                                </template>
                                <template x-if="!photoPreview">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-800">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                </template>
                                <input type="file" accept="image/jpeg,image/png,image/webp" wire:model="photo" @change="photoPreview = URL.createObjectURL($event.target.files[0])" class="text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-emerald-600 hover:file:bg-emerald-100 dark:text-gray-400 dark:file:bg-emerald-900/30 dark:file:text-emerald-300">
                            </div>
                            @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supporting Documents</p>
                            <p class="mt-0.5 text-xs text-gray-400">Upload your CV, portfolio, or other documents (PDF, DOC, max 10MB each, up to 5 files)</p>
                            <input type="file" multiple accept=".pdf,.doc,.docx" wire:model="documentFiles" class="mt-1.5 text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-emerald-600 hover:file:bg-emerald-100 dark:text-gray-400 dark:file:bg-emerald-900/30 dark:file:text-emerald-300">
                            @error('documentFiles') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @error('documentFiles.*') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 dark:border-border">
                        <button type="button" wire:click="closeApplicationForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-border dark:text-gray-300 dark:hover:bg-card-hover">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="submitApplication">Submit Application</span>
                            <span wire:loading wire:target="submitApplication">Submitting...</span>
                        </button>
                    </div>
                </form>
            </x-ui.drawer>
        </div>
    </div>
</div>
