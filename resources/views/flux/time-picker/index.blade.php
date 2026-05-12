@props([
    'placeholder' => 'Pick a time',
    'name' => null,
    'disabled' => false,
])

<div
    x-data="{
        open: false,
        hours:   Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0')),
        minutes: Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0')),
        selectedHour: null,
        selectedMinute: null,
        get display() {
            if (this.selectedHour === null) return '';
            return this.selectedHour + ':' + (this.selectedMinute ?? '00');
        },
        selectHour(h) { this.selectedHour = h; this.$dispatch('input', this.display); },
        selectMinute(m) { this.selectedMinute = m; this.open = false; this.$dispatch('input', this.display); },
        clear() { this.selectedHour = null; this.selectedMinute = null; this.$dispatch('input', null); }
    }"
    @click.outside="open = false"
    class="relative w-full"
    {{ $attributes }}
>
    <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 text-zinc-400 pointer-events-none">
            <flux:icon.clock class="size-4" />
        </div>
        <input
            type="text"
            x-model="display"
            @click="!{{ $disabled ? 'true' : 'false' }} && (open = !open)"
            readonly
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            class="w-full border border-zinc-200 dark:border-white/10 rounded-lg h-10 ps-10 pe-8 text-sm text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 bg-white dark:bg-white/10 cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-accent/40 disabled:opacity-50 disabled:cursor-not-allowed"
            data-flux-control
        />
        <button type="button" x-show="display" @click.stop="clear()"
            class="absolute inset-y-0 end-0 flex items-center pe-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
            <flux:icon.x-mark class="size-4" />
        </button>
        <input type="hidden" :value="display" @if($name) name="{{ $name }}" @endif />
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-50 mt-1 start-0 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-lg overflow-hidden"
    >
        <div class="flex divide-x divide-zinc-100 dark:divide-white/10">
            {{-- Hours --}}
            <div class="flex flex-col w-16 h-48 overflow-y-auto py-1">
                <div class="px-2 py-1 text-xs font-medium text-zinc-400 text-center sticky top-0 bg-white dark:bg-zinc-800">HH</div>
                <template x-for="h in hours" :key="h">
                    <button type="button" @click="selectHour(h)"
                        :class="selectedHour === h ? 'bg-accent text-white' : 'hover:bg-zinc-50 dark:hover:bg-white/5 text-zinc-700 dark:text-zinc-200'"
                        class="px-3 py-1.5 text-sm text-center transition-colors" x-text="h">
                    </button>
                </template>
            </div>
            {{-- Minutes --}}
            <div class="flex flex-col w-16 h-48 overflow-y-auto py-1">
                <div class="px-2 py-1 text-xs font-medium text-zinc-400 text-center sticky top-0 bg-white dark:bg-zinc-800">MM</div>
                <template x-for="m in minutes" :key="m">
                    <button type="button" @click="selectMinute(m)"
                        :class="selectedMinute === m ? 'bg-accent text-white' : 'hover:bg-zinc-50 dark:hover:bg-white/5 text-zinc-700 dark:text-zinc-200'"
                        class="px-3 py-1.5 text-sm text-center transition-colors" x-text="m">
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
