@blaze(fold: true)

@props([
    'name'        => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid'     => null,
    'size'        => null,
    'filter'      => true,
    'button'      => null,
    'input'       => null,
    'empty'       => null,
])

@php
$invalid ??= ($name && $errors->has($name));

$inputClasses = Flux::classes()
    ->add('w-full h-10 px-3 pe-10 text-sm rounded-lg border')
    ->add('bg-white dark:bg-white/10')
    ->add('text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400')
    ->add('focus:outline-none focus:ring-2 focus:ring-accent/50')
    ->add($invalid
        ? 'border-red-500'
        : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    )
    ;

$listboxClasses = Flux::classes()
    ->add('absolute z-50 mt-1 w-full overflow-hidden')
    ->add('rounded-lg border border-zinc-200 dark:border-zinc-600')
    ->add('bg-white dark:bg-zinc-700')
    ->add('text-sm')
    ;
@endphp

<div
    x-data="{
        open: false,
        search: '',
        value: null,
        filterEnabled: {{ $filter ? 'true' : 'false' }},

        init() {
            this.$nextTick(() => { this.updateDisplayValue() });
            this.$watch('value', () => { this.updateDisplayValue() });
        },

        updateDisplayValue() {
            const current = this.value;
            if (current !== null && current !== undefined && current !== '') {
               const opt = this.$el.querySelector(`[data-flux-select-option][data-value="${current}"]`);
               this.search = opt ? (opt.dataset.label || opt.textContent.trim()) : '';
            } else {
               this.search = '';
            }
        },

        isSelected(val) {
            if (!val) return false;
            const current = this.value;
            if (current === null || current === undefined || current === '') return false;
            return String(current) === String(val);
        },

        selectValue(val) {
            this.value = val;
            this.open = false;
        },

        filterOption(text) {
            if (!this.filterEnabled || !this.search) return true;
            return text.toLowerCase().includes(this.search.toLowerCase());
        }
    }"
    x-modelable="value"
    {{ $attributes->whereStartsWith('wire:model') }}
    {{ $attributes->whereStartsWith('x-model') }}
    x-on:keydown.escape="open = false"
    {{-- [BUG FIX] prevent premature close when interacting with dropdown --}}
    x-on:click.outside="open = false"
    class="relative w-full"
>
    {{-- Support regular form submissions --}}
    @if ($name)
        <input type="hidden" name="{{ $name }}" :value="value !== null ? value : ''" />
    @endif

    {{-- Input trigger --}}
    @isset($input)
        {{-- [BUG FIX] stop keydown propagation so Livewire hotkeys don't fire --}}
        <div x-on:keydown.stop>{{ $input }}</div>
    @else
        <div class="relative">
            <input
                type="text"
                x-model="search"
                x-on:focus="open = true"
                x-on:click="open = true"
                {{-- [BUG FIX] stop propagation so click.outside doesn't immediately close --}}
                x-on:click.stop
                x-on:keydown.stop
                placeholder="{{ $placeholder ?? 'Search...' }}"
                class="{{ $inputClasses }}"
                autocomplete="off"
                @if ($invalid) aria-invalid="true" data-invalid @endif
                x-on:input="open = true"
            />
            <span class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3 text-zinc-400">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
            </span>
        </div>
    @endisset

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="{{ $listboxClasses }}"
        style="display: none;"
        {{-- [BUG FIX] stop click from bubbling to click.outside handler --}}
        x-on:click.stop
    >
        {{-- Options with scroll --}}
        <div class="max-h-60 overflow-y-auto p-1" role="listbox">
            {{ $slot }}
        </div>

        @isset($empty)
            {{ $empty }}
        @else
            <div
                class="px-3 py-2 text-sm text-zinc-400 text-center"
                x-show="[...$el.previousElementSibling.querySelectorAll('[data-flux-select-option]')].every(o => o.style.display === 'none')"
                style="display: none;"
            >
                No results found.
            </div>
        @endisset
    </div>
</div>
