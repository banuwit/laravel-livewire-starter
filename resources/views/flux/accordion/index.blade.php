@props([
    'open' => false,
    'transition' => true,
])

<div
    x-data="{ open: @js($open) }"
    {{ $attributes }}
>
    {{ $slot }}
</div>
