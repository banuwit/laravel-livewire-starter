@props([
    'position' => 'bottom',
    'align' => 'start',
])

@php
$positionClasses = match($position) {
    'top'   => 'bottom-full mb-2',
    'left'  => 'right-full top-0 me-2',
    'right' => 'left-full top-0 ms-2',
    default => 'top-full mt-2',
};
$alignClasses = match($align) {
    'end'    => 'end-0',
    'center' => 'left-1/2 -translate-x-1/2',
    default  => 'start-0',
};
@endphp

<div
    x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    {{ $attributes->class(["absolute z-50 min-w-48 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-lg", $positionClasses, $alignClasses]) }}
>
    {{ $slot }}
</div>
