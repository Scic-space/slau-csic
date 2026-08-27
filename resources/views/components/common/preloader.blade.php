{{-- <div
  x-show="loaded"
  x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 350)})"
  class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
>
  <div
    class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
  ></div>
</div> --}}

{{-- resources/views/components/loader.blade.php
<div
    x-data="{
        loaded: true,
        init() {
            // Hide loader after page is fully loaded
            window.addEventListener('load', () => {
                setTimeout(() => {
                    this.loaded = false;
                }, 500);
            });
        }
    }"
    x-show="loaded"
    x-transition.opacity.duration.300ms
    class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-card"
    style="display: none;"
    x-cloak
>
    <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
</div> --}}
