@blaze(fold: true)

@props([
    'placeholder' => 'Select...',
    'invalid'     => null,
    'size'        => null,
])

@php
$invalid ??= false;

$classes = Flux::classes()
    ->add('relative w-full cursor-default text-start flex items-center gap-2')
    ->add('bg-white dark:bg-white/10 border shadow-xs rounded-lg')
    ->add('ps-3 pe-10')
    ->add(match ($size) {
        'sm'  => 'h-8 text-sm rounded-md',
        'xs'  => 'h-6 text-xs rounded-md',
        default => 'h-10 text-sm',
    })
    ->add($invalid
        ? 'border-red-500'
        : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    )
    ->add('text-zinc-700 dark:text-zinc-300')
    ;
@endphp

<button
    type="button"
    class="{{ $classes }}"
    {{ $attributes->except(['placeholder', 'invalid', 'size']) }}
    x-on:click="open = !open"
    x-bind:aria-expanded="open"
    @if ($invalid) aria-invalid="true" data-invalid @endif
>
    <span x-text="displayValue || ''" x-bind:class="displayValue ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-400'" class="flex-1 truncate">
        <span class="text-zinc-400">{{ $placeholder }}</span>
    </span>

    <span class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3 text-zinc-400">
        <svg class="size-4 transition-transform duration-150" x-bind:class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </span>
</button>
