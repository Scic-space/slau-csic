<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Dashboard</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">My Applications</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                Track the status of all your cabinet applications.
            </p>
            <a href="{{ route('voting.nominations') }}" wire:navigate class="mt-4 inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors">
                Submit new application
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
            $statusOrder = ['submitted', 'under_review', 'shortlisted', 'approved'];
        @endphp

        @if ($applications->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">No applications yet</h2>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    <a href="{{ route('voting.nominations') }}" wire:navigate class="text-emerald-500 hover:underline">Submit an application</a>
                    for an open cabinet position.
                </p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($applications as $app)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-500">
                                    {{ $app['election']['position'] }}
                                </p>
                                <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ $app['election']['title'] }}
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
                                            <a href="{{ $doc }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-300 dark:hover:bg-gray-800">
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
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/60">
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
    </div>
</div>
