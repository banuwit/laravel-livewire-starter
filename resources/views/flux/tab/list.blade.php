@props([
    'variant' => null,
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'text-xs',
        default => 'text-sm',
    };

    $staticClasses = match($variant) {
        'segmented' => 'self-start inline-flex flex-wrap gap-1 p-1 rounded-lg bg-zinc-100 dark:bg-white/5 ' . $sizeClasses,
        'pills' => 'self-start inline-flex flex-wrap gap-1 ' . $sizeClasses,
        'default' => 'flex flex-wrap gap-4 border-b border-zinc-200 dark:border-zinc-700 ' . $sizeClasses,
        default => null,
    };
@endphp

<div
    role="tablist"
    @if ($staticClasses === null)
        :class="{
            'flex flex-wrap gap-4 border-b border-zinc-200 dark:border-zinc-700 {{ $sizeClasses }}': variant === 'default',
            'self-start inline-flex flex-wrap gap-1 p-1 rounded-lg bg-zinc-100 dark:bg-white/5 {{ $sizeClasses }}': variant === 'segmented',
            'self-start inline-flex flex-wrap gap-1 {{ $sizeClasses }}': variant === 'pills',
        }"
    @endif
    {{ $attributes->class($staticClasses ?? '') }}
>
    {{ $slot }}
</div>
