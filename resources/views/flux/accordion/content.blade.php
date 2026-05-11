@props([])

<div
    x-show="open"
    x-collapse
    {{ $attributes }}
>
    {{ $slot }}
</div>
