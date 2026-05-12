@props([
    'placeholder' => 'Add...',
    'name' => null,
    'disabled' => false,
])

<div
    x-data="{
        pills: [],
        input: '',
        add() {
            const val = this.input.trim();
            if (val && !this.pills.includes(val)) {
                this.pills.push(val);
                this.$dispatch('pillbox-change', { pills: this.pills });
            }
            this.input = '';
        },
        remove(index) {
            this.pills.splice(index, 1);
            this.$dispatch('pillbox-change', { pills: this.pills });
        }
    }"
    {{ $attributes->class('flex flex-wrap gap-1.5 items-center min-h-10 w-full border border-zinc-200 dark:border-white/10 rounded-lg px-2 py-1.5 bg-white dark:bg-white/10 focus-within:ring-2 focus-within:ring-accent/40 transition-shadow') }}
>
    <template x-for="(pill, index) in pills" :key="index">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-white/10 text-zinc-700 dark:text-zinc-300">
            <span x-text="pill"></span>
            <button
                type="button"
                @click="remove(index)"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 leading-none"
                aria-label="Remove"
            >
                <flux:icon.x-mark class="size-3" />
            </button>
        </span>
    </template>

    <input
        type="text"
        x-model="input"
        @keydown.enter.prevent="add()"
        @keydown.backspace="input === '' && remove(pills.length - 1)"
        @keydown.comma.prevent="add()"
        @blur="add()"
        placeholder="{{ $placeholder }}"
        @disabled($disabled)
        class="flex-1 min-w-20 text-sm text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 bg-transparent outline-none border-none focus:ring-0 py-0.5 px-1"
        data-flux-control
    />

    @if($name)
        <template x-for="pill in pills">
            <input type="hidden" :name="'{{ $name }}[]'" :value="pill" />
        </template>
    @endif
</div>
