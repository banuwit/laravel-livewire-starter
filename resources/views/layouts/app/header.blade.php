<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:spacer />

            <x-theme-toggle class="mr-2" />

            <x-desktop-user-menu />
        </flux:header>

        @php
            $navMenus = \App\Models\Menu::with(['children.permissions', 'permissions'])
                ->roots()
                ->where('layout', 'sidebar')
                ->where('is_active', true)
                ->get();
        @endphp

        <!-- Mobile Sidebar -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @foreach ($navMenus as $menu)
                    @if ($menu->children->isNotEmpty())
                        @php
                            $isChildActive = $menu->children->contains(fn($c) =>
                                $c->is_active && $c->route_pattern && request()->routeIs($c->route_pattern)
                            );
                        @endphp
                        <flux:sidebar.group
                            expandable
                            :heading="$menu->name"
                            :icon="$menu->icon ?? 'folder'"
                            :expanded="$isChildActive"
                        >
                            @foreach ($menu->children->where('is_active', true) as $child)
                                @php
                                    $childPerm = $child->permissions->first();
                                    $childHref = $child->route_name && \Illuminate\Support\Facades\Route::has($child->route_name)
                                        ? route($child->route_name)
                                        : '#';
                                    $childCurrent = $child->route_pattern ? request()->routeIs($child->route_pattern) : false;
                                @endphp
                                @if ($childPerm)
                                    @can($childPerm->name)
                                        <flux:sidebar.item :href="$childHref" :current="$childCurrent" wire:navigate>
                                            {{ $child->name }}
                                        </flux:sidebar.item>
                                    @endcan
                                @else
                                    <flux:sidebar.item :href="$childHref" :current="$childCurrent" wire:navigate>
                                        {{ $child->name }}
                                    </flux:sidebar.item>
                                @endif
                            @endforeach
                        </flux:sidebar.group>
                    @else
                        @php
                            $viewPerm = $menu->permissions->first();
                            $rootHref = $menu->route_name && \Illuminate\Support\Facades\Route::has($menu->route_name)
                                ? route($menu->route_name)
                                : '#';
                            $rootCurrent = $menu->route_pattern ? request()->routeIs($menu->route_pattern) : false;
                        @endphp
                        @if ($viewPerm)
                            @can($viewPerm->name)
                                <flux:sidebar.item :icon="$menu->icon" :href="$rootHref" :current="$rootCurrent" wire:navigate>
                                    {{ $menu->name }}
                                </flux:sidebar.item>
                            @endcan
                        @else
                            <flux:sidebar.item :icon="$menu->icon" :href="$rootHref" :current="$rootCurrent" wire:navigate>
                                {{ $menu->name }}
                            </flux:sidebar.item>
                        @endif
                    @endif
                @endforeach
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
