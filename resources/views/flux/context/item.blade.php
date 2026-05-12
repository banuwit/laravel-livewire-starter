@props([
    'icon' => null,
    'variant' => 'default',
    'disabled' => false,
    'href' => null,
])

@php
$tag = $href ? 'a' : 'button';
$classes = Flux::classes()
    ->add('w-full flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-lg transition-colors text-start')
    ->add(match($variant) {
        'danger' => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10',
        default  => 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-white/5',
    })
    ->add($disabled ? 'opacity-50 pointer-events-none' : '');
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @if(!$href) type="button" @endif
    {{ $attributes->class($classes) }}
>
    @if($icon)
        <flux:icon :icon="$icon" class="size-4 shrink-0 text-zinc-400" />
    @endif
    {{ $slot }}
</{{ $tag }}>
