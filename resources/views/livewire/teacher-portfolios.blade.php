<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">work</span>Student Portfolios</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage student profiles, projects, skills, and certifications</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('instructor.dashboard') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-card px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-card-hover transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Dashboard
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-indigo-500" aria-hidden="true">folder_open</span>Total Projects</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $published }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-green-500" aria-hidden="true">public</span>Published</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $allSkills->count() }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-purple-500" aria-hidden="true">psychology</span>Skills Added</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $allCerts->count() }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-amber-500" aria-hidden="true">workspace_premium</span>Certifications</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Tabs --}}
        <div class="mb-3 flex gap-1 overflow-x-auto rounded-sm border border-gray-200 bg-white p-1 shadow-sm dark:border-border dark:bg-card">
            @foreach (['portfolios' => 'Projects', 'skills' => 'Skills', 'certifications' => 'Certifications', 'experience' => 'Experience'] as $key => $label)
                <button wire:click="setSection('{{ $key }}')" class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors
                    {{ $activeSection === $key ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ============================================================ --}}
        {{-- PORTFOLIOS SECTION --}}
        {{-- ============================================================ --}}
        @if ($activeSection === 'portfolios')
            <div class="mb-3 flex flex-wrap gap-3 rounded-sm border border-gray-200 bg-white p-3 shadow-sm sm:p-4 dark:border-border dark:bg-card">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search projects..." class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-card py-2 pl-10 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="w-44">
                    <select wire:model.live="categoryFilter" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-card px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <select wire:model.live="statusFilter" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-card px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                @if ($canManage)
                    <button wire:click="openCreateForm" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Project
                    </button>
                @endif
            </div>

            @if ($showForm)
                <div class="mb-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingPortfolioId ? 'Edit Project' : 'New Project' }}</h3>
                    <form wire:submit="savePortfolio">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                <input wire:model="formTitle" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Network Security Scanner">
                                @error('formTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea wire:model="formDescription" rows="3" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Describe this project..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                                <select wire:model="formCategory" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
                                @if ($canManage)
                                    <select wire:model="formStudentId" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select student...</option>
                                        @foreach ($students as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="block w-full rounded-lg border border-gray-300 dark:border-border bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ auth()->user()->name }}
                                    </div>
                                @endif
                                @error('formStudentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tech Stack (comma separated)</label>
                                <input wire:model="formTechStack" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="PHP, Laravel, Nmap">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Repo URL</label>
                                <input wire:model="formRepoUrl" type="url" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://github.com/...">
                                @error('formRepoUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Live URL</label>
                                <input wire:model="formLiveUrl" type="url" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://demo.example.com">
                                @error('formLiveUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">External Link</label>
                                <input wire:model="formExternalLink" type="url" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://...">
                                @error('formExternalLink') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File (max 10MB)</label>
                                <input wire:model="formFile" type="file" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                                @error('formFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="formIsPublished" class="rounded border-gray-300 dark:border-border text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Publish immediately</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" wire:click="closeForm" class="rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                                <span wire:loading.remove wire:target="savePortfolio">{{ $editingPortfolioId ? 'Update' : 'Create' }}</span>
                                <span wire:loading wire:target="savePortfolio">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
                @if ($portfolios->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <p class="mt-3 text-sm text-gray-500">No projects found.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-background">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($portfolios as $p)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-card-hover/50">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $p->title }}</p>
                                            @if ($p->description)
                                                <p class="mt-0.5 text-xs text-gray-500 line-clamp-1">{{ $p->description }}</p>
                                            @endif
                                            @if ($p->tech_stack && count($p->tech_stack) > 0)
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach (array_slice($p->tech_stack, 0, 4) as $tech)
                                                        <span class="inline-flex rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $tech }}</span>
                                                    @endforeach
                                                    @if (count($p->tech_stack) > 4)
                                                        <span class="text-[10px] text-gray-400">+{{ count($p->tech_stack) - 4 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->student->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ ucfirst($p->category) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($p->is_published)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Published</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400"><span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Draft</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button wire:click="togglePublish({{ $p->id }})" class="rounded-lg p-1.5 text-gray-400 hover:text-amber-600 hover:bg-gray-100 dark:hover:text-amber-400 dark:hover:bg-card-hover transition" title="Toggle publish">
                                                    @if ($p->is_published)
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59"/></svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    @endif
                                                </button>
                                                <button wire:click="openEditForm({{ $p->id }})" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-card-hover transition" title="Edit">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button wire:click="deletePortfolio({{ $p->id }})" wire:confirm="Delete this project?" class="rounded-lg p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:text-red-400 dark:hover:bg-card-hover transition" title="Delete">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- SKILLS SECTION --}}
        {{-- ============================================================ --}}
        @if ($activeSection === 'skills')
            <div class="mb-6 flex justify-end">
                <button wire:click="openSkillForm" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Skill
                </button>
            </div>

            @if ($showSkillForm)
                <div class="mb-6 rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Skill</h3>
                    <form wire:submit="saveSkill">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Skill Name</label>
                                <input wire:model="skillName" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Penetration Testing">
                                @error('skillName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                                <select wire:model="skillCategory" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($skillCategories as $cat)
                                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
                                @if ($canManage)
                                    <select wire:model="skillStudentId" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select...</option>
                                        @foreach ($students as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="block w-full rounded-lg border border-gray-300 dark:border-border bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ auth()->user()->name }}
                                    </div>
                                @endif
                                @error('skillStudentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Proficiency ({{ $skillProficiency }}/5)</label>
                                <input wire:model="skillProficiency" type="range" min="1" max="5" class="w-full">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showSkillForm', false)" class="rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Save Skill</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
                @if ($allSkills->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-sm text-gray-500">No skills added yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-background">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Skill</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Level</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($allSkills as $skill)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-card-hover/50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $skill->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ ucfirst($skill->category) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $skill->user->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <span class="h-2.5 w-2.5 rounded-full {{ $i <= $skill->proficiency ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                                @endfor
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button wire:click="deleteSkill({{ $skill->id }})" wire:confirm="Delete this skill?" class="rounded-lg p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:text-red-400 dark:hover:bg-card-hover transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- CERTIFICATIONS SECTION --}}
        {{-- ============================================================ --}}
        @if ($activeSection === 'certifications')
            <div class="mb-6 flex justify-end">
                <button wire:click="openCertForm" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Certification
                </button>
            </div>

            @if ($showCertForm)
                <div class="mb-6 rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Certification</h3>
                    <form wire:submit="saveCert">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Certification Name</label>
                                <input wire:model="certName" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. CompTIA Security+">
                                @error('certName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issuer</label>
                                <input wire:model="certIssuer" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. CompTIA">
                                @error('certIssuer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
                                @if ($canManage)
                                    <select wire:model="certStudentId" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select...</option>
                                        @foreach ($students as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="block w-full rounded-lg border border-gray-300 dark:border-border bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ auth()->user()->name }}
                                    </div>
                                @endif
                                @error('certStudentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Earned</label>
                                <input wire:model="certDateEarned" type="date" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
                                <input wire:model="certExpiryDate" type="date" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credential URL</label>
                                <input wire:model="certCredentialUrl" type="url" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://...">
                                @error('certCredentialUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showCertForm', false)" class="rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Save Certification</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
                @if ($allCerts->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-sm text-gray-500">No certifications added yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-background">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Certification</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Issuer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Earned</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Expires</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($allCerts as $cert)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-card-hover/50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $cert->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cert->issuer }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cert->user->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cert->date_earned?->format('M Y') ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            @if ($cert->expiry_date)
                                                <span class="{{ $cert->isExpired() ? 'text-red-600' : '' }}">{{ $cert->expiry_date->format('M Y') }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button wire:click="deleteCert({{ $cert->id }})" wire:confirm="Delete this certification?" class="rounded-lg p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:text-red-400 dark:hover:bg-card-hover transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- EXPERIENCE SECTION --}}
        {{-- ============================================================ --}}
        @if ($activeSection === 'experience')
            <div class="mb-6 flex justify-end">
                <button wire:click="openExpForm" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Experience
                </button>
            </div>

            @if ($showExpForm)
                <div class="mb-6 rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Experience</h3>
                    <form wire:submit="saveExp">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                <input wire:model="expTitle" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Security Intern">
                                @error('expTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization</label>
                                <input wire:model="expOrganization" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. SLAU Cyber Lab">
                                @error('expOrganization') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                                <select wire:model="expType" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($expTypes as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
                                @if ($canManage)
                                    <select wire:model="expStudentId" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select...</option>
                                        @foreach ($students as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="block w-full rounded-lg border border-gray-300 dark:border-border bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ auth()->user()->name }}
                                    </div>
                                @endif
                                @error('expStudentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input wire:model="expStartDate" type="date" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('expStartDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                <input wire:model="expEndDate" type="date" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ $expIsCurrent ? 'disabled' : '' }}>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="expIsCurrent" class="rounded border-gray-300 dark:border-border text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Currently active</span>
                                </label>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea wire:model="expDescription" rows="2" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Describe the role..."></textarea>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showExpForm', false)" class="rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Save Experience</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
                @if ($allExps->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-sm text-gray-500">No experience entries yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-background">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Organization</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($allExps as $exp)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-card-hover/50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $exp->title }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $exp->organization }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $exp->user->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $exp->start_date->format('M Y') }} -
                                            @if ($exp->is_current)
                                                <span class="text-green-600">Present</span>
                                            @else
                                                {{ $exp->end_date?->format('M Y') ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($exp->type) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button wire:click="deleteExp({{ $exp->id }})" wire:confirm="Delete this experience?" class="rounded-lg p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:text-red-400 dark:hover:bg-card-hover transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
