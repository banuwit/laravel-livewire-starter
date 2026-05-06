@blaze(fold: true)

@props([
    'placeholder' => 'Search...',
    'invalid'     => null,
])

@php
$invalid ??= false;

$classes = Flux::classes()
    ->add('w-full h-10 px-3 pe-10 text-sm rounded-lg border')
    ->add('bg-white dark:bg-white/10')
    ->add('text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400')
    ->add('focus:outline-none focus:ring-2 focus:ring-accent/50')
    ->add($invalid
        ? 'border-red-500'
        : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    )
    ;
@endphp

<input
    type="text"
    placeholder="{{ $placeholder }}"
    class="{{ $classes }}"
    {{ $attributes->except(['placeholder', 'invalid']) }}
    x-on:keydown.stop
    x-on:focus="open = true"
    x-on:input="open = true"
    autocomplete="off"
    @if ($invalid) aria-invalid="true" data-invalid @endif
/>
