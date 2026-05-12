@props([
    'id'          => null,
    'title'       => null,
    'badge'       => null,
    'badgeColor'  => 'zinc',
    'avatarName'  => null,
    'date'        => null,
    'draggable'   => true,
])

<div
    {{ $attributes->class('bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-lg p-3 shadow-xs hover:shadow-sm transition-all') }}
    @if($draggable)
        draggable="true"
        @dragstart="startDrag('{{ $id }}', $el.closest('[data-column-id]')?.dataset.columnId)"
        @dragend="endDrag()"
        class="cursor-grab active:cursor-grabbing active:opacity-60"
    @endif
>
    @if($badge)
        <div class="mb-2">
            <flux:badge size="sm" color="{{ $badgeColor }}">{{ $badge }}</flux:badge>
        </div>
    @endif

    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200 leading-snug">
        {{ $title ?? $slot }}
    </p>

    @if($date || $avatarName)
        <div class="flex items-center justify-between mt-3 pt-2 border-t border-zinc-100 dark:border-white/10">
            @if($date)
                <span class="text-xs text-zinc-400">{{ $date }}</span>
            @endif
            @if($avatarName)
                <flux:avatar size="xs" :name="$avatarName" class="ms-auto" />
            @endif
        </div>
    @endif
</div>
