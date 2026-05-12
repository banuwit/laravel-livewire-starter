@props([
    'value' => null,
    'selected' => false,
])

<button
    type="button"
    @click="close()"
    {{ $attributes->class('w-full text-start px-3 py-2 text-sm text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-white/5 flex items-center gap-2 transition-colors') }}
>
    @if($selected)
        <flux:icon.check class="size-4 text-accent shrink-0" />
    @else
        <span class="size-4 shrink-0"></span>
    @endif
    {{ $slot }}
</button>
