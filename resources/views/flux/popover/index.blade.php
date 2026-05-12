@props([])

<div
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative inline-block"
    {{ $attributes }}
>
    {{ $slot }}
</div>
