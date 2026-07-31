<div class="py-6">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('exams.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                &larr; Back to Exams
            </a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 sm:p-8 text-center dark:border-gray-700 dark:bg-gray-800">
            <div class="text-5xl sm:text-6xl font-bold {{ ($attempt['passed'] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                {{ $attempt['total_score'] ?? 0 }}%
            </div>
            <p class="mt-2 text-xl font-semibold {{ ($attempt['passed'] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                {{ ($attempt['passed'] ?? false) ? 'Passed' : 'Failed' }}
            </p>
            <p class="mt-1 text-gray-500 dark:text-gray-400">
                Passing score: {{ $exam['passing_score'] }}%
            </p>

            <div class="mt-6 flex justify-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                <div><span class="font-medium text-gray-900 dark:text-white">{{ $exam['title'] }}</span></div>
                <div>Score: {{ $attempt['total_score'] }}/{{ $totalPossible }} pts</div>
            </div>
        </div>

        @if ($attempt['passed'] ?? false)
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <p class="font-medium text-green-800 dark:text-green-300">You passed! You're eligible for a certificate.</p>
                <div class="flex gap-3">
                    <a href="{{ route('exams.certificates') }}" wire:navigate class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        View Certificates
                    </a>
                </div>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">Answer Review</h2>
                <button wire:click="toggleAnswers" class="rounded-lg bg-blue-100 px-3 py-1.5 text-sm text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                    {{ $showAnswers ? 'Hide Correct Answers' : 'Show Correct Answers' }}
                </button>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($answers as $i => $answer)
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Question {{ $i + 1 }}</p>
                                <p class="text-gray-900 dark:text-white">{{ $answer['question']['question_text'] }}</p>
                                @if ($answer['question']['image'])
                                    <img src="{{ $answer['question']['image'] }}" alt="Question image" class="mt-2 max-w-full rounded-lg border border-gray-200 dark:border-gray-700" style="max-height: 300px">
                                @endif
                                @if ($answer['question']['code_block'])
                                    <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-100 p-3 text-sm dark:bg-gray-700"><code>{{ $answer['question']['code_block'] }}</code></pre>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-sm font-medium {{ $answer['is_correct'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $answer['marks_awarded'] ?? 0 }}/{{ $answer['question']['marks'] }} pts
                                </div>
                                <div class="text-xs {{ $answer['is_correct'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $answer['is_correct'] ? 'Correct' : 'Incorrect' }}
                                </div>
                            </div>
                        </div>

                        @if ($showAnswers && count($answer['question']['options']) > 0)
                            <div class="mt-3 space-y-1.5">
                                @foreach ($answer['question']['options'] as $opt)
                                    @php
                                        $isSelected = $opt['id'] === $answer['selected_option_id'];
                                    @endphp
                                    <div class="rounded-lg border px-3 py-2 text-sm
                                        {{ $opt['is_correct'] ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300' : ($isSelected ? 'border-red-300 bg-red-50 text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300' : 'border-gray-200 text-gray-700 dark:border-gray-600 dark:text-gray-300') }}">
                                        <span class="flex items-center gap-2">
                                            @if ($opt['is_correct'])<span class="text-green-600">&check;</span>@endif
                                            @if ($isSelected && !$opt['is_correct'])<span class="text-red-600">&cross;</span>@endif
                                            {{ $opt['option_text'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($showAnswers && $answer['question']['type'] === 'short_answer')
                            <div class="mt-3 space-y-2 text-sm">
                                @if ($answer['answer_text'])
                                    <div class="rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                                        <p class="mb-1 text-xs text-gray-500">Your answer:</p>
                                        <p class="text-gray-900 dark:text-white">{{ $answer['answer_text'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($showAnswers && $answer['question']['explanation'])
                            <div class="mt-2 text-sm italic text-gray-500 dark:text-gray-400">
                                {{ $answer['question']['explanation'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
