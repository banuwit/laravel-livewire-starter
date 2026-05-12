@props([
    'title' => null,
    'time' => null,
    'color' => 'accent',
])

@php
$colorVar = match($color) {
    'accent'           => 'var(--color-accent)',
    'red'              => 'var(--color-red-500)',
    'green','emerald'  => 'var(--color-emerald-500)',
    'blue'             => 'var(--color-blue-500)',
    'yellow','amber'   => 'var(--color-amber-500)',
    'purple','violet'  => 'var(--color-violet-500)',
    default            => 'var(--color-zinc-500)',
};
@endphp

<div
    {{ $attributes->class('flex flex-col text-xs px-1.5 py-0.5 rounded truncate cursor-pointer hover:opacity-90 transition-opacity') }}
    style="background-color: {{ $colorVar }}20; color: {{ $colorVar }}"
>
    @if($time)
        <span class="font-semibold">{{ $time }}</span>
    @endif
    <span class="truncate">{{ $title ?? $slot }}</span>
</div>
