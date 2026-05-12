@props([
    'placeholder' => 'Pick a date',
    'name'        => null,
    'label'       => null,
    'description' => null,
    'disabled'    => false,
    'value'       => null,
])

<div class="flex flex-col gap-1.5 w-full">
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        x-data="{
            open: false,
            value: @js($value),
            display: '',
            today: new Date(),
            current: new Date(),
            selected: null,

            formatDate(d) {
                const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                const p = n => String(n).padStart(2, '0');
                return `${p(d.getDate())} ${months[d.getMonth()]} ${d.getFullYear()}`;
            },

            init() {
                if (this.value) {
                    const d = new Date(this.value + 'T00:00:00');
                    this.selected = d;
                    this.current  = new Date(d.getFullYear(), d.getMonth(), 1);
                    this.display  = this.formatDate(d);
                }
            },

            get year()     { return this.current.getFullYear(); },
            get month()    { return this.current.getMonth(); },
            get monthName(){ return this.current.toLocaleString('default', { month: 'long' }); },

            get days() {
                const first = new Date(this.year, this.month, 1).getDay();
                const last  = new Date(this.year, this.month + 1, 0).getDate();
                const prev  = new Date(this.year, this.month, 0).getDate();
                let days = [];
                for (let i = first - 1; i >= 0; i--)  days.push({ day: prev - i, current: false });
                for (let i = 1; i <= last; i++)         days.push({ day: i, current: true });
                for (let i = 1; days.length < 42; i++) days.push({ day: i, current: false });
                return days;
            },

            prevMonth() { this.current = new Date(this.year, this.month - 1, 1); },
            nextMonth() { this.current = new Date(this.year, this.month + 1, 1); },

            selectDay(d) {
                if (!d.current) return;
                this.selected = new Date(this.year, this.month, d.day);
                const p = n => String(n).padStart(2, '0');
                this.value   = `${this.selected.getFullYear()}-${p(this.selected.getMonth()+1)}-${p(this.selected.getDate())}`;
                this.display = this.formatDate(this.selected);
                this.open    = false;
                this.$dispatch('input', this.value);
            },

            isSelected(d) {
                if (!this.selected || !d.current) return false;
                return this.selected.getDate() === d.day
                    && this.selected.getMonth()    === this.month
                    && this.selected.getFullYear() === this.year;
            },

            isToday(d) {
                if (!d.current) return false;
                const t = this.today;
                return t.getDate() === d.day && t.getMonth() === this.month && t.getFullYear() === this.year;
            },

            clear() {
                this.value = null; this.display = ''; this.selected = null;
                this.$dispatch('input', null);
            }
        }"
        @click.outside="open = false"
        class="relative w-full"
        {{ $attributes->except(['label', 'description', 'value', 'name', 'disabled', 'placeholder']) }}
    >
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 text-zinc-400 pointer-events-none">
                <flux:icon.calendar class="size-4" />
            </div>
            <input
                type="text"
                x-model="display"
                @click="!{{ $disabled ? 'true' : 'false' }} && (open = !open)"
                @keydown.escape="open = false"
                readonly
                placeholder="{{ $placeholder }}"
                @disabled($disabled)
                class="w-full border border-zinc-200 dark:border-white/10 rounded-lg h-10 ps-10 pe-8 text-sm text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 bg-white dark:bg-white/10 cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-accent/40 disabled:opacity-50 disabled:cursor-not-allowed"
                data-flux-control
            />
            <button
                type="button"
                x-show="value"
                @click.stop="clear()"
                class="absolute inset-y-0 end-0 flex items-center pe-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
            >
                <flux:icon.x-mark class="size-4" />
            </button>
            <input type="hidden" :value="value" @if($name) name="{{ $name }}" @endif />
        </div>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute z-50 mt-1 start-0 w-72 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-lg p-3"
        >
            <div class="flex items-center justify-between mb-3">
                <button type="button" @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-500 transition-colors">
                    <flux:icon.chevron-left class="size-4" />
                </button>
                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200" x-text="monthName + ' ' + year"></span>
                <button type="button" @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-500 transition-colors">
                    <flux:icon.chevron-right class="size-4" />
                </button>
            </div>

            <div class="grid grid-cols-7 mb-1">
                <template x-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']">
                    <div class="text-center text-xs font-medium text-zinc-400 py-1" x-text="d"></div>
                </template>
            </div>

            <div class="grid grid-cols-7 gap-y-0.5">
                <template x-for="(d, i) in days" :key="i">
                    <button
                        type="button"
                        @click="selectDay(d)"
                        :disabled="!d.current"
                        :class="{
                            'opacity-25 cursor-default pointer-events-none': !d.current,
                            'bg-accent text-white font-semibold': isSelected(d),
                            'text-accent font-semibold ring-1 ring-accent ring-inset': isToday(d) && !isSelected(d),
                            'hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-700 dark:text-zinc-200': d.current && !isSelected(d),
                        }"
                        class="text-center text-sm py-1.5 rounded-lg transition-colors"
                        x-text="d.day"
                    ></button>
                </template>
            </div>

            <div class="mt-2 pt-2 border-t border-zinc-100 dark:border-white/10 flex justify-between items-center">
                <button type="button" @click="clear()" class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">Clear</button>
                <button type="button" @click="current = new Date(today); selectDay({ day: today.getDate(), current: true })" class="text-xs text-accent hover:underline">Today</button>
            </div>
        </div>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif
</div>
