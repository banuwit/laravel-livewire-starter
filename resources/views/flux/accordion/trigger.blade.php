@props([])

<div @click="open = !open" {{ $attributes }}>
    {{ $slot }}
</div>
