@props([
    'value' => '#6366f1',
    'name' => null,
    'disabled' => false,
])

<div
    x-data="{ open: false, color: '{{ $value }}' }"
    @click.outside="open = false"
    class="relative inline-block"
    {{ $attributes }}
>
    <button
        type="button"
        @click="!{{ $disabled ? 'true' : 'false' }} && (open = !open)"
        @disabled($disabled)
        class="flex items-center gap-2 h-10 px-3 border border-zinc-200 dark:border-white/10 rounded-lg bg-white dark:bg-white/10 hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <span class="size-5 rounded border border-black/10 shrink-0" :style="'background-color: ' + color"></span>
        <span class="text-sm text-zinc-700 dark:text-zinc-200 font-mono uppercase" x-text="color"></span>
        <flux:icon.chevron-down class="size-4 text-zinc-400" />
    </button>

    <input type="hidden" :value="color" @if($name) name="{{ $name }}" @endif />

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 start-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-lg p-3 w-56"
    >
        <input
            type="color"
            x-model="color"
            @input="$dispatch('color-change', { color: color })"
            class="w-full h-28 rounded-lg border-0 cursor-pointer p-0 mb-3"
        />

        <div class="flex items-center gap-2 mb-3">
            <span class="text-xs font-medium text-zinc-500 uppercase">HEX</span>
            <input
                type="text"
                x-model="color"
                maxlength="7"
                class="flex-1 text-xs border border-zinc-200 dark:border-white/10 rounded-lg px-2 py-1.5 font-mono bg-white dark:bg-white/10 text-zinc-700 dark:text-zinc-200 focus:outline-hidden focus:ring-2 focus:ring-accent/40"
            />
        </div>

        @if(!$slot->isEmpty())
            <div class="flex flex-wrap gap-1.5 pt-2 border-t border-zinc-100 dark:border-white/10">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
