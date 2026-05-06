@blaze(fold: true)

@props([
    'name'           => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder'    => null,
    'invalid'        => null,
    'size'           => null,
    'multiple'       => false,
    'searchable'     => false,
    'clearable'      => false,
    'filter'         => true,
    'selectedSuffix' => null,
    'indicator'      => 'check', // 'check' | 'checkbox'
    'clear'          => 'select', // 'select' | 'close'
    'button'         => null,
    'input'          => null,
    'search'         => null,
    'empty'          => null,
    'multipleDisplay'=> 'badges', // 'badges' | 'count'
    'dropdownWidth'  => null, // custom dropdown width, e.g. '280px'
    'apply'          => false, // when true: defer changes until Apply button is clicked
])

@php
$invalid ??= ($name && $errors->has($name));

$triggerClasses = Flux::classes()
    ->add('relative w-full cursor-default text-start')
    ->add('flex items-center gap-2')
    ->add('bg-white dark:bg-white/10')
    ->add('border rounded-lg')
    ->add('ps-3')
    // [BUG 1 FIX] pe-10 always, but if clearable add more space for the X icon
    ->add($clearable ? 'pe-16' : 'pe-10')
    ->add(match ($size) {
        'sm' => 'h-8 text-sm rounded-md',
        'xs' => 'h-6 text-xs rounded-md',
        default => 'h-10 text-sm',
    })
    ->add($invalid
        ? 'border-red-500'
        : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    )
    ->add('text-zinc-700 dark:text-zinc-300')
    ;

$listboxClasses = Flux::classes()
    ->add('absolute z-50 mt-1 w-full')
    ->add('rounded-lg border border-zinc-200 dark:border-zinc-600')
    ->add('bg-white dark:bg-zinc-700')
    // [BUG 2 FIX] overflow-auto on container clips sticky search; use overflow-hidden + inner scroll
    ->add('overflow-hidden')
    ->add('focus:outline-none text-sm')
    ;

$listboxStyles = $dropdownWidth ? 'min-width: ' . $dropdownWidth : '';

$searchInputClasses = Flux::classes()
    ->add('w-full ps-9 pe-3 py-2 text-sm border-b border-zinc-200 dark:border-zinc-600')
    ->add('bg-transparent focus:outline-none')
    ->add('text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400')
    ;
@endphp

{{-- [BUG 3 FIX] Pass $multiple, $indicator, $searchable as @aware() vars to child options via data attributes on the wrapper --}}
<div
    x-data="{
        open: false,
        search: '',
        value: null,
        displayValue: '',
        displayExtra: '',
        selectedItems: [],
        multiple: {{ $multiple ? 'true' : 'false' }},
        multipleDisplay: '{{ $multipleDisplay }}',
        clearOnClose: '{{ $clear }}' === 'close',
        filterEnabled: {{ $filter ? 'true' : 'false' }},
        hasSelectedSuffix: {{ $selectedSuffix ? 'true' : 'false' }},
        applyMode: {{ $apply ? 'true' : 'false' }},
        committedValue: null,

        cloneValue(v) {
            if (v === null || v === undefined) return v;
            return JSON.parse(JSON.stringify(v));
        },

        snapshotCommitted() {
            this.committedValue = this.cloneValue(this.value);
        },

        revertToCommitted() {
            this.value = this.cloneValue(this.committedValue);
        },

        init() {
            this.$nextTick(() => {
                this.updateDisplayValue();
                if (this.applyMode) this.snapshotCommitted();
            });
            this.$watch('value', () => { this.updateDisplayValue() });
            this.$watch('open', (isOpen) => {
                if (!this.applyMode) return;
                if (isOpen) this.snapshotCommitted();
            });
        },

        applyChanges() {
            this.snapshotCommitted();
            this.open = false;
            if (this.clearOnClose) this.search = '';
            if (window.Livewire && this.$root && this.$root.closest('[wire\\:id]')) {
                const wireEl = this.$root.closest('[wire\\:id]');
                const component = window.Livewire.find(wireEl.getAttribute('wire:id'));
                if (component) component.$refresh();
            }
        },

        cancelChanges() {
            this.revertToCommitted();
            this.open = false;
            if (this.clearOnClose) this.search = '';
        },

        updateDisplayValue() {
            const opts = this.$el.querySelectorAll('[data-flux-select-option]');
            const selected = [];
            const selectedItemsRaw = [];
            opts.forEach(opt => {
                const val = opt.dataset.value;
                if (this.isSelected(val)) {
                    const label = opt.dataset.label || opt.textContent.trim();
                    selected.push(label);
                    selectedItemsRaw.push({ value: val, label: label });
                }
            });
            this.selectedItems = selectedItemsRaw;

            if (selected.length === 0) {
                this.displayValue = '';
                this.displayExtra = '';
                return;
            }

            if (this.multiple && this.multipleDisplay !== 'badges') {
                if (selected.length > 1) {
                    @if ($selectedSuffix)
                        this.displayValue = selected.length + ' {{ $selectedSuffix }}';
                    @else
                        this.displayValue = '{{ $placeholder ?? 'Select' }}';
                        this.displayExtra = selected.length;
                    @endif
                    return;
                }
            }

            this.displayValue = selected.join(', ');
            this.displayExtra = '';
        },

        isSelected(val) {
            if (!val) return false;
            const current = this.value;
            if (current === null || current === undefined || current === '') return false;
            if (this.multiple) {
                return Array.isArray(current) && current.map(String).includes(String(val));
            }
            return String(current) === String(val);
        },

        selectValue(val) {
            if (this.multiple) {
                let current = this.value || [];
                if (!Array.isArray(current)) current = [];
                const strVal = String(val);
                if (current.map(String).includes(strVal)) {
                    current = current.filter(v => String(v) !== strVal);
                } else {
                    current = [...current, val];
                }
                this.value = current;
                if (this.clearOnClose) this.search = '';
            } else {
                this.value = val;
                if (!this.applyMode) {
                    this.open = false;
                    if (this.clearOnClose) this.search = '';
                }
            }
        },

        clear() {
            this.value = this.multiple ? [] : '';
            this.search = '';
            if (this.applyMode) {
                this.snapshotCommitted();
                if (window.Livewire && this.$root) {
                    const wireEl = this.$root.closest('[wire\\:id]');
                    if (wireEl) {
                        const component = window.Livewire.find(wireEl.getAttribute('wire:id'));
                        if (component) component.$refresh();
                    }
                }
            }
        },

        filterOption(text) {
            if (!this.filterEnabled || !this.search) return true;
            return text.toLowerCase().includes(this.search.toLowerCase());
        }
    }"
    x-modelable="value"
    {{ $attributes->whereStartsWith('wire:model') }}
    {{ $attributes->whereStartsWith('x-model') }}
    x-on:keydown.escape="if (applyMode && open) revertToCommitted(); open = false; search = ''"
    {{-- [BUG 8 FIX] click.outside is evaluated in wrong scope; reference open explicitly --}}
    x-on:click.outside="if (applyMode && open) revertToCommitted(); open = false; if (clearOnClose) search = ''"
    class="relative w-full"
    {{-- [BUG 9 FIX] Pass indicator to options via data attr (Blade @aware doesn't cross Alpine boundary correctly) --}}
    data-indicator="{{ $indicator }}"
    data-multiple="{{ $multiple ? 'true' : 'false' }}"
>
    {{-- Hidden input that holds regular form submissions if name is present --}}
    @if ($name)
        <template x-if="!multiple">
            <input type="hidden" name="{{ $name }}" :value="value !== null ? value : ''" />
        </template>
        <template x-if="multiple">
            <template x-for="v in (Array.isArray(value) ? value : [])">
                <input type="hidden" name="{{ $name }}[]" :value="v" />
            </template>
        </template>
    @endif

    {{-- Trigger Button --}}
    @isset($button)
        {{ $button }}
    @else
        <button
            type="button"
            class="{{ $triggerClasses }}"
            x-on:click="open = !open"
            x-bind:aria-expanded="open"
            @if ($invalid) aria-invalid="true" data-invalid @endif
        >
            {{-- [BUG 10 FIX] displayValue/badges rendering --}}
            <div
                class="flex-1 flex flex-wrap gap-1 text-start overflow-hidden"
                x-show="displayValue !== '' && multiple && multipleDisplay === 'badges'"
                style="display: none;"
            >
                <template x-for="item in selectedItems" :key="item.value">
                    <span class="inline-flex items-center gap-1 rounded bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 text-xs font-medium text-zinc-800 dark:text-zinc-200">
                        <span x-text="item.label" class="pointer-events-none"></span>
                        {{-- Add click.stop to remove an item directly from the badge --}}
                        <button type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 focus:outline-none" x-on:click.stop="selectValue(item.value)">
                            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
            <div
                class="flex-1 truncate text-start overflow-hidden"
                x-show="displayValue !== '' && (!multiple || multipleDisplay !== 'badges')"
                style="display: none;"
            >
                <span class="truncate" x-text="displayValue"></span>
                <span
                    x-show="displayExtra && !hasSelectedSuffix"
                    class="inline-flex items-center rounded bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 text-xs font-medium text-zinc-800 dark:text-zinc-200 ml-1"
                    x-text="'+' + displayExtra"
                ></span>
            </div>
            <span
                class="flex-1 truncate text-zinc-400"
                x-show="displayValue === ''"
            >{{ $placeholder ?? 'Select...' }}</span>

            @if ($clearable)
                <span
                    x-show="displayValue !== ''"
                    x-on:click.stop="clear()"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer shrink-0"
                    title="Clear"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                </span>
            @endif

            <span class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3 text-zinc-400">
                <svg class="size-4 transition-transform duration-150" x-bind:class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
            </span>
        </button>
    @endisset

    {{-- Dropdown Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="{{ $listboxClasses }}"
        style="{{ $listboxStyles }}"
        x-on:click.stop
    >
        {{-- Search --}}
        @if ($searchable)
            @isset($search)
                {{ $search }}
            @else
                <div class="sticky top-0 bg-white dark:bg-zinc-700 z-10">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-zinc-400">
                            <flux:icon.magnifying-glass variant="mini" class="size-4" />
                        </div>

                        <input
                            type="text"
                            x-model="search"
                            x-on:keydown.stop
                            placeholder="Search..."
                            class="{{ $searchInputClasses }}"
                            x-ref="searchInput"
                            {{-- [BUG 11 FIX] $watch inside x-init inside the input causes scope issues; use x-effect instead --}}
                            x-effect="if (open) $nextTick(() => $refs.searchInput?.focus())"
                            autocomplete="off"
                        />
                    </div>
                </div>
            @endisset
        @endif

        @isset($input)
            <div class="px-2 pb-1" x-on:keydown.stop>{{ $input }}</div>
        @endisset

        {{-- Options container with scroll --}}
        <div class="max-h-52 overflow-y-auto" role="listbox" @if($multiple) aria-multiselectable="true" @endif>
            {{ $slot }}
        </div>

        {{-- Empty state --}}
        @isset($empty)
            {{ $empty }}
        @else
            <div
                class="px-3 py-2 text-sm text-zinc-400 text-center"
                x-show="$el.previousElementSibling.querySelectorAll('[data-flux-select-option]').length > 0
                    && [...$el.previousElementSibling.querySelectorAll('[data-flux-select-option]')].every(o => o.style.display === 'none')"
                style="display: none;"
            >
                No results found.
            </div>
        @endisset

        @if ($apply)
            {{-- Apply / Cancel footer --}}
            <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-600 px-2 py-2 bg-white dark:bg-zinc-700">
                <flux:button size="xs" variant="ghost" x-on:click="cancelChanges()">Cancel</flux:button>
                <flux:button size="xs" variant="primary" x-on:click="applyChanges()">Apply</flux:button>
            </div>
        @endif
    </div>
</div>
