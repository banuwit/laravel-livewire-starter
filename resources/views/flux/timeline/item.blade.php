@props([
    'icon' => null,
    'color' => 'zinc',
    'date' => null,
    'title' => null,
])

@php
$dotColor = match($color) {
    'green', 'emerald' => 'bg-emerald-500',
    'blue'             => 'bg-blue-500',
    'red'              => 'bg-red-500',
    'yellow', 'amber'  => 'bg-amber-500',
    'purple', 'violet' => 'bg-violet-500',
    'accent'           => 'bg-accent',
    default            => 'bg-zinc-400 dark:bg-zinc-500',
};
@endphp

<div {{ $attributes->class('relative flex gap-4 pb-6 last:pb-0') }}>
    {{-- Dot + line --}}
    <div class="flex flex-col items-center shrink-0">
        <div @class([
            $dotColor,
            'rounded-full flex items-center justify-center',
            $icon ? 'size-8' : 'size-2.5 mt-1.5',
        ])>
            @if($icon)
                <flux:icon :icon="$icon" class="size-4 text-white" />
            @endif
        </div>
        <div class="w-px flex-1 bg-zinc-200 dark:bg-white/10 mt-1.5 [.last>*>&]:hidden"></div>
    </div>

    {{-- Content --}}
    <div class="flex-1 pb-2 min-w-0">
        <div class="flex items-start justify-between gap-4">
            @if($title)
                <p class="text-sm font-semibold text-zinc-800 dark:text-white">{{ $title }}</p>
            @endif
            @if($date)
                <span class="text-xs text-zinc-400 whitespace-nowrap shrink-0">{{ $date }}</span>
            @endif
        </div>
        @if(!$slot->isEmpty())
            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
