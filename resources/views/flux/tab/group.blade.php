@props([
    'variant' => 'default',
    'default' => null,
])

<div
    x-data="{ tab: @js($default), variant: @js($variant) }"
    x-init="$nextTick(() => { if (!tab) { const first = $el.querySelector('[data-tab-name]'); if (first) tab = first.dataset.tabName; } })"
    {{ $attributes->class('flex flex-col gap-4') }}
>
    {{ $slot }}
</div>
