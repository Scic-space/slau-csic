<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">menu_book</span>Course Materials</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage teaching resources and course materials</p>
            </div>
            <button wire:click="openCreateForm" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Material
            </button>
        </div>

        <div class="mb-3 flex flex-wrap gap-3 rounded-sm border border-gray-200 bg-white p-3 shadow-sm sm:p-4 dark:border-border dark:bg-card">
            <div class="flex-1 min-w-0 sm:min-w-[200px]">
                <input wire:model.live.debounce="search" type="text" placeholder="Search materials..." class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-card px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-40">
                <select wire:model.live="typeFilter" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-card px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="video">Video</option>
                    <option value="document">Document</option>
                    <option value="slide">Slide</option>
                    <option value="code">Code</option>
                    <option value="link">Link</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        @if ($showForm)
            <div class="mb-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-indigo-500" aria-hidden="true">note_add</span>{{ $editingId ? 'Edit Material' : 'Add Material' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="formTitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input wire:model="formTitle" id="formTitle" type="text" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Introduction to Algebra">
                            @error('formTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="formType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select wire:model="formType" id="formType" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="document">Document</option>
                                <option value="video">Video</option>
                                <option value="slide">Slide</option>
                                <option value="code">Code</option>
                                <option value="link">Link</option>
                                <option value="other">Other</option>
                            </select>
                            @error('formType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="formDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea wire:model="formDescription" id="formDescription" rows="2" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional description..."></textarea>
                            @error('formDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="formTrainingId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Training (optional)</label>
                            <select wire:model="formTrainingId" id="formTrainingId" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">No training</option>
                                @foreach ($trainings as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            @error('formTrainingId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if ($formType === 'link')
                            <div>
                                <label for="formUrl" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                                <input wire:model="formUrl" id="formUrl" type="url" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://example.com/resource">
                                @error('formUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <div>
                                <label for="formFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File (max 10MB)</label>
                                <input wire:model="formFile" id="formFile" type="file" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                                @error('formFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" wire:click="closeForm" class="rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="dashboard-card overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
            @if ($materials->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No course materials found.</p>
                    <button wire:click="openCreateForm" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add your first material
                    </button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-background">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Training</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">File/Link</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($materials as $material)
                                <tr class="hover:bg-gray-50 dark:hover:bg-card-hover/50 transition">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $material->title }}</p>
                                        @if ($material->description)
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $material->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ $material->type === 'video' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                            {{ $material->type === 'document' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                            {{ $material->type === 'slide' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : '' }}
                                            {{ $material->type === 'code' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                            {{ $material->type === 'link' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}
                                            {{ $material->type === 'other' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                            @if ($material->type === 'video')
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            @elseif ($material->type === 'document')
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
                                            @elseif ($material->type === 'slide')
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10l2 2 3-3 4 4 2-2.5V10H7z"/></svg>
                                            @elseif ($material->type === 'code')
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>
                                            @elseif ($material->type === 'link')
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                            @endif
                                            {{ ucfirst($material->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if ($material->training)
                                            <a href="{{ route('training.show', $material->training) }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                {{ Str::limit($material->training->title, 30) }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($material->file_path)
                                            <a href="{{ asset('storage/'.$material->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                Download
                                            </a>
                                        @elseif ($material->url)
                                            <a href="{{ $material->url }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                Open Link
                                            </a>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $material->created_at->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="openEditForm({{ $material->id }})" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-card-hover transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="delete({{ $material->id }})" wire:confirm="Are you sure you want to delete this material?" class="rounded-lg p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:text-red-400 dark:hover:bg-card-hover transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($materials->hasPages())
                    <div class="border-t border-gray-200 dark:border-border px-6 py-3">
                        {{ $materials->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
