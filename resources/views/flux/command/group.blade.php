@props(['heading' => null])

<div {{ $attributes->class('py-1') }}>
    @if($heading)
        <div class="px-4 py-1.5 text-xs font-semibold text-zinc-400 uppercase tracking-wider">{{ $heading }}</div>
    @endif
    {{ $slot }}
</div>
