@props(['color' => '#000000'])

<button
    type="button"
    @click="color = '{{ $color }}'; open = false; $dispatch('color-change', { color: color })"
    class="size-6 rounded border border-black/10 transition-transform hover:scale-110 focus:outline-hidden focus:ring-2 focus:ring-offset-1 focus:ring-accent/50"
    style="background-color: {{ $color }}"
    title="{{ $color }}"
    {{ $attributes }}
></button>
