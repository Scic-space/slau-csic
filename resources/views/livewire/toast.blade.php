<div
    x-data="{
        show: @js($show),
        message: @js($message),
        type: @js($type),
        init() {
            Livewire.on('toast-auto-hide', (timeout) => {
                setTimeout(() => {
                    this.show = false;
                }, timeout);
            });
            Livewire.on('toast-show', (message, type) => {
                this.message = message;
                this.type = type || 'success';
                this.show = true;
                setTimeout(() => {
                    this.show = false;
                }, 4000);
            });
        }
    }"
    x-on:toast-show.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 4000)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-x-4"
    class="pointer-events-auto fixed bottom-4 right-4 z-[99999] w-full max-w-sm overflow-hidden rounded-xl border shadow-2xl sm:bottom-6 sm:right-6"
    :class="{
        'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950': type === 'success',
        'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950': type === 'error',
        'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950': type === 'warning',
        'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950': type === 'info',
    }"
    x-cloak
>
    <div class="flex items-start gap-3 p-4">
        {{-- Icon --}}
        <div class="flex-shrink-0 mt-0.5">
            {{-- Success --}}
            <template x-if="type === 'success'">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/50">
                    <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>
            {{-- Error --}}
            <template x-if="type === 'error'">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50">
                    <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </template>
            {{-- Warning --}}
            <template x-if="type === 'warning'">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50">
                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </template>
            {{-- Info --}}
            <template x-if="type === 'info'">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50">
                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </template>
        </div>

        {{-- Message --}}
        <div class="flex-1 min-w-0 pt-0.5">
            <p class="text-sm font-medium"
               :class="{
                   'text-green-800 dark:text-green-200': type === 'success',
                   'text-red-800 dark:text-red-200': type === 'error',
                   'text-amber-800 dark:text-amber-200': type === 'warning',
                   'text-blue-800 dark:text-blue-200': type === 'info',
               }"
               x-text="message"></p>
        </div>

        {{-- Close button --}}
        <button wire:click="hide" @click="show = false"
                class="flex-shrink-0 rounded-lg p-1 transition-colors"
                :class="{
                    'text-green-500 hover:bg-green-100 dark:hover:bg-green-900/50': type === 'success',
                    'text-red-500 hover:bg-red-100 dark:hover:bg-red-900/50': type === 'error',
                    'text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/50': type === 'warning',
                    'text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900/50': type === 'info',
                }">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Progress bar --}}
    <div class="h-0.5 w-full"
         :class="{
             'bg-green-200 dark:bg-green-800': type === 'success',
             'bg-red-200 dark:bg-red-800': type === 'error',
             'bg-amber-200 dark:bg-amber-800': type === 'warning',
             'bg-blue-200 dark:bg-blue-800': type === 'info',
         }">
        <div class="h-full rounded-full transition-all duration-100 ease-linear"
             x-init="$nextTick(() => { $el.style.width = '100%'; setTimeout(() => $el.style.width = '0%', 50) })"
             :class="{
                 'bg-green-500 dark:bg-green-400': type === 'success',
                 'bg-red-500 dark:bg-red-400': type === 'error',
                 'bg-amber-500 dark:bg-amber-400': type === 'warning',
                 'bg-blue-500 dark:bg-blue-400': type === 'info',
             }"
             style="width: 0%; transition: width 4s linear;"></div>
    </div>
</div>
