@props([
    'placeholder' => null,
    'name' => null,
    'disabled' => false,
])

<div
    x-data="{
        open: false,
        query: '',
        close() { this.open = false; this.query = ''; },
    }"
    @click.outside="close()"
    class="relative w-full"
    {{ $attributes }}
>
    <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 text-zinc-400 pointer-events-none">
            <flux:icon.magnifying-glass class="size-4" />
        </div>
        <input
            type="text"
            x-model="query"
            @input="open = query.length > 0"
            @focus="open = query.length > 0"
            @keydown.escape="close()"
            @keydown.tab="close()"
            placeholder="{{ $placeholder }}"
            @if($name) name="{{ $name }}" @endif
            @disabled($disabled)
            class="w-full border border-zinc-200 dark:border-white/10 rounded-lg h-10 ps-10 pe-3 text-sm text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 bg-white dark:bg-white/10 focus:outline-hidden focus:ring-2 focus:ring-accent/40"
            data-flux-control
        />
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-50 mt-1 w-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-md overflow-hidden"
    >
        <div class="max-h-60 overflow-y-auto py-1">
            {{ $slot }}
        </div>
    </div>
</div>
