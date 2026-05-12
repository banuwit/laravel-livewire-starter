@props([
    'placeholder' => 'Type a command or search...',
])

<div
    x-data="{
        open: false,
        query: '',
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.$refs.input?.focus());
        },
        close() { this.open = false; this.query = ''; },
    }"
    @keydown.window="e => { if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); toggle(); } }"
    @keydown.escape.window="close()"
    {{ $attributes }}
>
    {{ $slot }}

    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] px-4"
        >
            <div class="fixed inset-0 bg-black/40 dark:bg-black/60" @click="close()"></div>

            <div
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="relative w-full max-w-xl bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-white/10 overflow-hidden z-10"
            >
                <div class="flex items-center gap-3 px-4 border-b border-zinc-100 dark:border-white/10">
                    <flux:icon.magnifying-glass class="size-4 text-zinc-400 shrink-0" />
                    <input
                        x-ref="input"
                        x-model="query"
                        type="text"
                        placeholder="{{ $placeholder }}"
                        class="flex-1 py-3.5 text-sm bg-transparent border-none outline-none text-zinc-700 dark:text-zinc-200 placeholder-zinc-400"
                    />
                    <button type="button" @click="close()"
                        class="shrink-0 text-xs text-zinc-400 border border-zinc-200 dark:border-white/10 rounded px-1.5 py-0.5 hover:bg-zinc-100 dark:hover:bg-white/10">
                        ESC
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto py-1">
                    {{ $panel ?? '' }}
                </div>
            </div>
        </div>
    </template>
</div>
