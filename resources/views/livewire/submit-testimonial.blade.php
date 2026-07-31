<div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm">
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4">
            <p class="text-sm text-emerald-400">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 p-4">
            <p class="text-sm text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    <label for="quote" class="block text-sm font-medium text-white/70 mb-2">
        Share your CTF experience with future members
    </label>
    <textarea
        wire:model.live="quote"
        id="quote"
        rows="3"
        maxlength="500"
        placeholder="I joined SLAU-CSIC with zero knowledge and now..."
        class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-white/30 backdrop-blur-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none"
    ></textarea>

    <div class="mt-3 flex items-center justify-between">
        <span class="text-xs text-white/30">
            {{ strlen($quote) }}/500 characters
        </span>

        <button
            wire:click="submit"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-50"
            class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="submit">Submit Testimonial</span>
            <span wire:loading wire:target="submit">Submitting...</span>
        </button>
    </div>

    <p class="mt-3 text-xs text-white/20">
        Submissions are reviewed before appearing on the page.
    </p>
</div>
