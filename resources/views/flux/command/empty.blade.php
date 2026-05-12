@props([])

<div {{ $attributes->class('flex flex-col items-center justify-center py-10 text-sm text-zinc-400') }}>
    <flux:icon.magnifying-glass class="size-8 mb-2 opacity-30" />
    {{ $slot->isEmpty() ? __('No results found.') : $slot }}
</div>
