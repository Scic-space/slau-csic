<div class="py-6"
     x-data="{
         currentIndex: 0,
         timeRemaining: {{ $attemptData['time_remaining_seconds'] }},
         answers: @js($answers),
         submitting: false,
         saving: false,
         completed: false,

         get questions() {
             return @js($questions);
         },

         get currentQuestion() {
             return this.questions[this.currentIndex] ?? null;
         },

         get answeredCount() {
             return Object.keys(this.answers).length;
         },

         get progress() {
             return this.questions.length > 0 ? Math.round((this.answeredCount / this.questions.length) * 100) : 0;
         },

         get timerColor() {
             return this.timeRemaining < 60 ? 'text-red-600' : this.timeRemaining < 300 ? 'text-yellow-600' : 'text-gray-700 dark:text-gray-300';
         },

         formatTime(seconds) {
             const m = Math.floor(seconds / 60);
             const s = seconds % 60;
             return `${m}:${s.toString().padStart(2, '0')}`;
         },

         setAnswer(questionId, value) {
             this.answers[questionId] = { ...(this.answers[questionId] || {}), ...value };
             this.saving = true;
             $wire.saveAnswer(questionId, value.text ?? null, value.option_id ?? null)
                 .then(() => { this.saving = false; });
         },

         goNext() {
             if (this.currentIndex < this.questions.length - 1) this.currentIndex++;
         },

         goPrev() {
             if (this.currentIndex > 0) this.currentIndex--;
         },

         handleSubmit() {
             if (this.submitting || this.completed) return;
             const unanswered = this.questions.filter(q => !this.answers[q.id]);
             if (unanswered.length > 0) {
                 if (!confirm(`You have ${unanswered.length} unanswered question(s). Are you sure you want to submit?`)) return;
             }
             this.submitting = true;
             this.completed = true;
             $wire.submitExam();
         }
     }"
     x-init="() => {
         const timer = setInterval(() => {
             if (timeRemaining <= 0 || completed) return;
             timeRemaining--;
             if (timeRemaining <= 0) handleSubmit();
         }, 1000);

         const autoSave = setInterval(() => {
             if (completed) return;
             const q = currentQuestion;
             if (!q) return;
             const data = answers[q.id];
             if (!data || (!data.text && !data.option_id)) return;
             saving = true;
             $wire.saveAnswer(q.id, data.text ?? null, data.option_id ?? null)
                 .then(() => { saving = false; });
         }, 10000);

         return () => { clearInterval(timer); clearInterval(autoSave); };
     }">
    <div class="mx-auto max-w-3xl space-y-4">
        <template x-if="completed">
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <p class="text-lg text-gray-500 dark:text-gray-400">Submitting your exam...</p>
                </div>
            </div>
        </template>

        <template x-if="!completed">
            <div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-border dark:bg-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="font-semibold text-gray-900 dark:text-white">{{ $examTitle }}</h1>
                            <p class="text-xs text-gray-500">
                                Question <span x-text="currentIndex + 1"></span> of <span x-text="questions.length"></span>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold tabular-nums" x-bind:class="timerColor" x-text="formatTime(timeRemaining)"></div>
                            <div class="text-xs text-gray-500" x-text="`${answeredCount}/${questions.length} answered`"></div>
                        </div>
                    </div>
                </div>

                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-2 rounded-full bg-blue-600 transition-all" x-bind:style="`width: ${progress}%`"></div>
                </div>

                <template x-if="currentQuestion">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-border dark:bg-card">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <span x-text="{
                                        multiple_choice: 'Multiple Choice',
                                        true_false: 'True / False',
                                        code_snippet: 'Code Snippet',
                                        short_answer: 'Short Answer',
                                    }[currentQuestion.question.type] || ''"></span>
                                    &middot; <span x-text="currentQuestion.question.marks"></span> pts
                                </p>
                                <p class="text-gray-900 dark:text-white" x-text="currentQuestion.question.question_text"></p>
                            </div>
                        </div>

                        <template x-if="currentQuestion.question.image">
                            <img :src="currentQuestion.question.image" alt="Question image" class="mb-4 max-w-full rounded-lg border border-gray-200 dark:border-border" style="max-height: 300px">
                        </template>

                        <template x-if="currentQuestion.question.code_block && currentQuestion.question.type !== 'code_snippet'">
                            <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-4 text-sm text-green-400"><code x-text="currentQuestion.question.code_block"></code></pre>
                        </template>

                        <template x-if="currentQuestion.question.type === 'multiple_choice' || currentQuestion.question.type === 'true_false'">
                            <div class="space-y-2">
                                <template x-for="opt in currentQuestion.question.options" :key="opt.id">
                                    <button @click="setAnswer(currentQuestion.id, { option_id: opt.id })"
                                            class="w-full rounded-lg border px-4 py-3 text-left text-sm transition-colors"
                                            :class="(answers[currentQuestion.id]?.option_id === opt.id)
                                                ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'
                                                : 'border-gray-200 text-gray-700 hover:border-gray-300 dark:border-border dark:text-gray-300 dark:hover:border-gray-500'"
                                            x-text="opt.option_text"></button>
                                </template>
                            </div>
                        </template>

                        <template x-if="currentQuestion.question.type === 'short_answer'">
                            <textarea @input.debounce.500ms="setAnswer(currentQuestion.id, { text: $event.target.value })"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm dark:border-border dark:bg-gray-700"
                                      rows="4" placeholder="Type your answer..."
                                      x-model="answers[currentQuestion.id]?.text ?? ''"></textarea>
                        </template>

                        <template x-if="currentQuestion.question.type === 'code_snippet'">
                            <div class="space-y-3">
                                <template x-if="currentQuestion.question.code_block">
                                    <pre class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-sm text-green-400"><code x-text="currentQuestion.question.code_block"></code></pre>
                                </template>
                                <div class="space-y-2">
                                    <template x-for="opt in currentQuestion.question.options" :key="opt.id">
                                        <button @click="setAnswer(currentQuestion.id, { option_id: opt.id })"
                                                class="w-full rounded-lg border px-4 py-3 text-left text-sm transition-colors"
                                                :class="(answers[currentQuestion.id]?.option_id === opt.id)
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'
                                                    : 'border-gray-200 text-gray-700 hover:border-gray-300 dark:border-border dark:text-gray-300 dark:hover:border-gray-500'"
                                                x-text="opt.option_text"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="flex items-center justify-between gap-3">
                    <button @click="goPrev()"
                            :disabled="currentIndex === 0"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-40 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Previous
                    </button>

                    <div class="flex gap-2">
                        <template x-if="saving">
                            <span class="self-center text-xs text-gray-400">Saving...</span>
                        </template>
                        <template x-if="currentIndex < questions.length - 1">
                            <button @click="goNext()"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Next
                            </button>
                        </template>
                        <template x-if="currentIndex >= questions.length - 1">
                            <button @click="handleSubmit()"
                                    :disabled="submitting"
                                    class="rounded-lg bg-green-600 px-6 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50">
                                <span x-text="submitting ? 'Submitting...' : 'Submit Exam'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-1.5">
                    <template x-for="(q, i) in questions" :key="q.id">
                        <button @click="$wire.saveAnswer(currentQuestion?.id, answers[currentQuestion?.id]?.text ?? null, answers[currentQuestion?.id]?.option_id ?? null); currentIndex = i"
                                class="h-8 w-8 rounded-lg text-xs font-medium transition-colors"
                                :class="i === currentIndex
                                    ? 'bg-blue-600 text-white'
                                    : answers[q.id]
                                        ? 'border border-green-300 bg-green-100 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        : 'border border-gray-200 bg-gray-100 text-gray-500 dark:border-border dark:bg-gray-700 dark:text-gray-400'"
                                x-text="i + 1"></button>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
