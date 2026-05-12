@props([])

<div {{ $attributes->class('px-3 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500') }}>
    {{ $slot->isEmpty() ? __('No results found.') : $slot }}
</div>
