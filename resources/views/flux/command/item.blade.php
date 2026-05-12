@props([
    'icon' => null,
    'kbd' => null,
    'href' => null,
    'value' => null,
])

@php $tag = $href ? 'a' : 'button'; @endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @if(!$href) type="button" @endif
    @click="close()"
    {{ $attributes->class('w-full flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-white/5 cursor-pointer transition-colors') }}
>
    @if($icon)
        <span class="flex items-center justify-center size-7 rounded-lg bg-zinc-100 dark:bg-white/10 shrink-0">
            <flux:icon :icon="$icon" class="size-4 text-zinc-500" />
        </span>
    @endif

    <span class="flex-1 text-start truncate">{{ $slot }}</span>

    @if($kbd)
        <kbd class="shrink-0 text-xs text-zinc-400 border border-zinc-200 dark:border-white/10 rounded px-1.5 py-0.5">{{ $kbd }}</kbd>
    @endif
</{{ $tag }}>
