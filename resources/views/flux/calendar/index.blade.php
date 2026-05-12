@props([
    'events' => '[]',
])

<div
    x-data="{
        today: new Date(),
        current: new Date(),
        events: {{ $events }},

        get year()     { return this.current.getFullYear(); },
        get month()    { return this.current.getMonth(); },
        get monthName(){ return this.current.toLocaleString('default', { month: 'long' }); },

        get days() {
            const first = new Date(this.year, this.month, 1).getDay();
            const last  = new Date(this.year, this.month + 1, 0).getDate();
            const prev  = new Date(this.year, this.month, 0).getDate();
            let d = [];
            for (let i = first - 1; i >= 0; i--) d.push({ day: prev - i, cur: false, date: new Date(this.year, this.month - 1, prev - i) });
            for (let i = 1; i <= last; i++)       d.push({ day: i, cur: true,  date: new Date(this.year, this.month, i) });
            for (let i = 1; d.length < 42; i++)   d.push({ day: i, cur: false, date: new Date(this.year, this.month + 1, i) });
            return d;
        },

        prev()   { this.current = new Date(this.year, this.month - 1, 1); },
        next()   { this.current = new Date(this.year, this.month + 1, 1); },
        goToday(){ this.current = new Date(); },

        isToday(d) {
            const t = this.today;
            return d.cur && d.date.getDate() === t.getDate() && d.date.getMonth() === t.getMonth() && d.date.getFullYear() === t.getFullYear();
        },

        dayEvents(d) {
            return this.events.filter(e => {
                const ed = new Date(e.date);
                return ed.toDateString() === d.date.toDateString();
            });
        }
    }"
    {{ $attributes->class('flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-xl overflow-hidden') }}
>
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-100 dark:border-white/10">
        <div class="flex items-center gap-2">
            <button type="button" @click="prev()" class="p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-500 transition-colors">
                <flux:icon.chevron-left class="size-4" />
            </button>
            <h2 class="text-sm font-semibold text-zinc-800 dark:text-white min-w-32 text-center" x-text="monthName + ' ' + year"></h2>
            <button type="button" @click="next()" class="p-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-500 transition-colors">
                <flux:icon.chevron-right class="size-4" />
            </button>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" type="button" @click="goToday()">Today</flux:button>
            {{ $actions ?? '' }}
        </div>
    </div>

    {{-- Day labels --}}
    <div class="grid grid-cols-7 border-b border-zinc-100 dark:border-white/10">
        <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']">
            <div class="py-2 text-center text-xs font-medium text-zinc-400 uppercase tracking-wide" x-text="d"></div>
        </template>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-7 flex-1">
        <template x-for="(d, i) in days" :key="i">
            <div
                @click="$dispatch('calendar-day-click', { date: d.date })"
                :class="{ 'opacity-30': !d.cur, 'bg-accent/5 dark:bg-accent/10': isToday(d) }"
                class="min-h-20 border-b border-e border-zinc-100 dark:border-white/10 p-1.5 cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors"
            >
                <div
                    :class="isToday(d) ? 'bg-accent text-white' : 'text-zinc-500 dark:text-zinc-400'"
                    class="flex items-center justify-center size-6 rounded-full text-xs font-medium mb-1"
                    x-text="d.day"
                ></div>
                <div class="space-y-0.5 overflow-hidden">
                    <template x-for="ev in dayEvents(d)" :key="ev.id ?? ev.title">
                        <div
                            class="text-xs px-1.5 py-0.5 rounded truncate"
                            :style="`background:${(ev.color||'var(--color-accent)')}20;color:${ev.color||'var(--color-accent)'}`"
                            x-text="ev.title"
                        ></div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
