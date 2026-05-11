@blaze(fold: true)

@props([
    'sticky' => null,
    'container' => null,
])

@php
$classes = Flux::classes('[grid-area:header]')
    ->add('z-10 min-h-14')
    ->add($container ? '' : 'flex items-center px-4')
    ;

if ($sticky) {
    $attributes = $attributes->merge([
        'x-data' => '',
        'x-bind:style' => '{ position: \'sticky\', top: $el.offsetTop + \'px\', \'max-height\': \'calc(100vh - \' + $el.offsetTop + \'px)\' }',
    ]);
}

if ($container) {
    $navMenus = \App\Models\Menu::with(['children.permissions', 'permissions'])
        ->roots()
        ->where('layout', 'sidebar')
        ->where('is_active', true)
        ->get();
}
@endphp

<header {{ $attributes->class($classes) }} data-flux-header>
    @if ($container)
        <div class="w-full flex flex-col">
            {{-- Main header row --}}
            <div class="mx-auto w-full px-4 flex items-center min-h-14">
                {{ $slot }}
            </div>

            {{-- Desktop nav row --}}
                <div class="mx-auto w-full px-4 border-t border-zinc-200 dark:border-zinc-700 text-sm [&_[data-flux-navbar-item]]:py-1.5 [&_[data-flux-navbar-item]]:px-2.5">
                    <flux:navbar compact>
                        @foreach ($navMenus as $menu)
                            @php
                                $viewPerm = $menu->permissions->first();
                                $href = $menu->route_name && \Illuminate\Support\Facades\Route::has($menu->route_name)
                                    ? route($menu->route_name)
                                    : '#';
                                $current = $menu->route_pattern ? request()->routeIs($menu->route_pattern) : false;

                                if ($menu->children->isNotEmpty()) {
                                    $current = $menu->children->contains(
                                        fn($c) => $c->is_active && $c->route_pattern && request()->routeIs($c->route_pattern)
                                    );
                                }
                            @endphp

                            @if ($menu->children->isNotEmpty())
                                @php
                                    $hasVisibleChild = $menu->children->where('is_active', true)->contains(function ($child) {
                                        $perm = $child->permissions->first();
                                        return !$perm || auth()->user()?->can($perm->name);
                                    });
                                @endphp

                                @if ($hasVisibleChild)
                                    <flux:dropdown>
                                        <flux:navbar.item :icon="$menu->icon ?? null" :current="$current">
                                            {{ $menu->name }}
                                        </flux:navbar.item>
                                        <flux:menu>
                                            @foreach ($menu->children->where('is_active', true) as $child)
                                                @php
                                                    $childPerm = $child->permissions->first();
                                                    $childHref = $child->route_name && \Illuminate\Support\Facades\Route::has($child->route_name)
                                                        ? route($child->route_name)
                                                        : '#';
                                                @endphp
                                                @if ($childPerm)
                                                    @can($childPerm->name)
                                                        <flux:menu.item :href="$childHref" wire:navigate>
                                                            {{ $child->name }}
                                                        </flux:menu.item>
                                                    @endcan
                                                @else
                                                    <flux:menu.item :href="$childHref" wire:navigate>
                                                        {{ $child->name }}
                                                    </flux:menu.item>
                                                @endif
                                            @endforeach
                                        </flux:menu>
                                    </flux:dropdown>
                                @endif
                            @else
                                @if ($viewPerm)
                                    @can($viewPerm->name)
                                        <flux:navbar.item :icon="$menu->icon ?? null" :href="$href" :current="$current" wire:navigate>
                                            {{ $menu->name }}
                                        </flux:navbar.item>
                                    @endcan
                                @else
                                    <flux:navbar.item :icon="$menu->icon ?? null" :href="$href" :current="$current" wire:navigate>
                                        {{ $menu->name }}
                                    </flux:navbar.item>
                                @endif
                            @endif
                        @endforeach
                    </flux:navbar>
                </div>
        </div>
    @else
        {{ $slot }}
    @endif
</header>
