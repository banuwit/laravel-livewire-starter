@props([
    'heading' => null,
    'id'      => null,
    'count'   => null,
    'color'   => null,
])

<div
    {{ $attributes->class('flex flex-col gap-2 w-64 min-w-64 shrink-0') }}
    @dragover.prevent
    @drop.prevent="$dispatch('kanban-drop', { to: '{{ $id }}', from: dragCol, card: dragging }); endDrag()"
    :class="dragCol && dragCol !== '{{ $id }}' ? 'ring-2 ring-accent/30 ring-inset rounded-xl' : ''"
    data-column-id="{{ $id }}"
>
    <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2">
            @if($color)
                <span class="size-2 rounded-full bg-{{ $color }}-400 shrink-0"></span>
            @endif
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ $heading }}</h3>
            @if($count !== null)
                <span class="text-xs text-zinc-400 bg-zinc-100 dark:bg-white/10 px-1.5 py-0.5 rounded-full tabular-nums">{{ $count }}</span>
            @endif
        </div>
        {{ $actions ?? '' }}
    </div>

    <div class="flex flex-col gap-2 min-h-16 rounded-xl bg-zinc-100/60 dark:bg-white/5 p-2 transition-colors">
        {{ $slot }}
    </div>
</div>
