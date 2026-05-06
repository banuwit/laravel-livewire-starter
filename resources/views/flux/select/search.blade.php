@blaze(fold: true)

@props([
    'placeholder' => 'Search...',
    'invalid'     => null,
])

@php
$invalid ??= false;

$classes = Flux::classes()
    ->add('w-full ps-9 pe-3 py-2 text-sm border-b border-zinc-200 dark:border-zinc-600')
    ->add('bg-transparent focus:outline-none')
    ->add('text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400')
    ;
@endphp

<div class="relative">
    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-zinc-400">
        <flux:icon.magnifying-glass variant="mini" class="size-4" />
    </div>

    <input
        type="text"
        x-model="search"
        x-on:keydown.stop
        placeholder="{{ $placeholder }}"
        class="{{ $classes }}"
        {{ $attributes->except(['placeholder']) }}
        autocomplete="off"
        x-ref="searchInput"
        x-init="$watch('open', v => v && $nextTick(() => $refs.searchInput?.focus()))"
        @if ($invalid) aria-invalid="true" data-invalid @endif
    />
</div>
