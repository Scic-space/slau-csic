<div>
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" wire:model.live="search" placeholder="Search questions..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
        </div>
        <div>
            <select wire:model.live="typeFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                <option value="">All Types</option>
                <option value="mcq">MCQ</option>
                <option value="true_false">True/False</option>
                <option value="short_answer">Short Answer</option>
                <option value="code_snippet">Code Snippet</option>
            </select>
        </div>
        <a href="{{ route('question-bank.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700">
            Export JSON
        </a>
    </div>

    @if($questions->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <p class="text-gray-500">No questions found.</p>
            <a href="{{ route('question-bank.create') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900">Create your first question</a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Options</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($questions as $question)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">{{ str_replace('_', ' ', $question->type) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($question->question_text, 80) }}</div>
                                @if($question->code_block)
                                    <pre class="mt-1 text-xs text-gray-500 bg-gray-100 p-1 rounded overflow-x-auto max-w-xs"><code>{{ Str::limit($question->code_block, 50) }}</code></pre>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $question->marks }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $question->options->count() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('question-bank.edit', $question) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <button wire:click="delete({{ $question->id }})" wire:confirm="Delete this question?" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    @endif
</div>