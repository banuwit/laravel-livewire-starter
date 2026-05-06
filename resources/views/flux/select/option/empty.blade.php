@blaze(fold: true)

@props([
    'whenLoading' => null,
])

<div
    class="px-3 py-2 text-sm text-zinc-400 text-center"
    {{ $attributes->except(['when-loading']) }}
>
    @if ($whenLoading)
        <span wire:loading>{{ $whenLoading }}</span>
        <span wire:loading.remove>{{ $slot }}</span>
    @else
        {{ $slot }}
    @endif
</div>
