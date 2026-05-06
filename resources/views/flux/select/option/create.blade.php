@blaze(fold: true)

@props([
    'modal'     => null,
    'minLength' => 0,
])

@php
$classes = Flux::classes()
    ->add('flex items-center w-full gap-2 px-3 py-2 rounded-md text-sm cursor-pointer')
    ->add('text-accent dark:text-accent-content font-medium')
    ->add('hover:bg-zinc-50 dark:hover:bg-zinc-600')
    ;
@endphp

@if ($modal)
    {{-- Create via modal --}}
    <div
        class="{{ $classes }} border-t border-zinc-100 dark:border-zinc-600 mt-1 pt-1"
        x-on:click="open = false; $flux.modal('{{ $modal }}').show()"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
        </svg>
        {{ $slot }}
    </div>
@else
    {{-- Create with inline trigger --}}
    <div
        class="{{ $classes }} border-t border-zinc-100 dark:border-zinc-600 mt-1 pt-1"
        x-show="search.length >= {{ (int) $minLength }} && search.length > 0"
        style="display:none;"
        {{ $attributes->except(['modal', 'min-length']) }}
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
        </svg>
        {{ $slot }}
    </div>
@endif
