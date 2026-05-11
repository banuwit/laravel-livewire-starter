<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100 dark:bg-zinc-800">
        <flux:header sticky class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.collapse class="data-flux-sidebar-collapsed-desktop:flex" />
            @php
                $activeMenu = \App\Models\Menu::with('parent')
                    ->where('is_active', true)
                    ->whereNotNull('route_pattern')
                    ->get()
                    ->first(fn($m) => request()->routeIs($m->route_pattern));
            @endphp
            <flux:breadcrumbs>
                @if ($activeMenu)
                    @if ($activeMenu->parent)
                        <flux:breadcrumbs.item>{{ $activeMenu->parent->name }}</flux:breadcrumbs.item>
                    @endif
                    <flux:breadcrumbs.item>{{ $activeMenu->name }}</flux:breadcrumbs.item>
                @endif
            </flux:breadcrumbs>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" />
        </flux:header>

        <!-- Desktop Sidebar -->
        <flux:sidebar sticky collapsible collapsed class="bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 dark:bg-zinc-950 dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-950 border-e dark:border-zinc-700">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <!-- <flux:sidebar.item icon="document-text" href="#">Kanban</flux:sidebar.item>
                <flux:sidebar.item icon="calendar" href="#">Calendar</flux:sidebar.item>
                <flux:sidebar.group expandable heading="Favorites" icon="star" class="grid">
                    <flux:sidebar.item href="#">Marketing site</flux:sidebar.item>
                    <flux:sidebar.item href="#">Android app</flux:sidebar.item>
                    <flux:sidebar.item href="#">Brand guidelines</flux:sidebar.item>
                </flux:sidebar.group> -->
                @php
                    $sidebarMenus = \App\Models\Menu::with(['children.permissions', 'permissions'])
                        ->roots()
                        ->where('layout', 'sidebar')
                        ->where('is_active', true)
                        ->get();
                @endphp

                @foreach ($sidebarMenus as $menu)
                    @if ($menu->children->isNotEmpty())
                        @php
                            $isChildActive = false;
                            foreach ($menu->children as $child) {
                                if ($child->is_active && $child->route_pattern && request()->routeIs($child->route_pattern)) {
                                    $isChildActive = true;
                                    break;
                                }
                            }
                        @endphp
                        {{-- Group with children --}}
                        <flux:sidebar.group 
                            expandable 
                            :heading="$menu->name" 
                            :icon="$menu->icon ?? 'folder'" 
                            :expanded="$isChildActive"
                        >
                            @foreach ($menu->children->where('is_active', true) as $child)
                                @php
                                    $viewPerm = $child->permissions->first();
                                    $childHref = $child->route_name && \Illuminate\Support\Facades\Route::has($child->route_name)
                                        ? route($child->route_name)
                                        : '#';
                                    $childCurrent = $child->route_pattern ? request()->routeIs($child->route_pattern) : false;
                                @endphp
                                @if ($viewPerm)
                                    @can($viewPerm->name)
                                        <flux:sidebar.item
                                            :href="$childHref"
                                            :current="$childCurrent"
                                            wire:navigate>
                                            {{ $child->name }}
                                        </flux:sidebar.item>
                                    @endcan
                                @else
                                    <flux:sidebar.item
                                        :href="$childHref"
                                        :current="$childCurrent"
                                        wire:navigate>
                                        {{ $child->name }}
                                    </flux:sidebar.item>
                                @endif
                            @endforeach
                        </flux:sidebar.group>
                    @else
                        {{-- Root single item --}}
                        @php
                            $viewPerm = $menu->permissions->first();
                            $rootHref = $menu->route_name && \Illuminate\Support\Facades\Route::has($menu->route_name)
                                ? route($menu->route_name)
                                : '#';
                            $rootCurrent = $menu->route_pattern ? request()->routeIs($menu->route_pattern) : false;
                        @endphp
                        @if ($viewPerm)
                            @can($viewPerm->name)
                                <flux:sidebar.item
                                    :icon="$menu->icon"
                                    :href="$rootHref"
                                    :current="$rootCurrent"
                                    wire:navigate>
                                    {{ $menu->name }}
                                </flux:sidebar.item>
                            @endcan
                        @else
                            <flux:sidebar.item
                                :icon="$menu->icon"
                                :href="$rootHref"
                                :current="$rootCurrent"
                                wire:navigate>
                                {{ $menu->name }}
                            </flux:sidebar.item>
                        @endif
                    @endif
                @endforeach
            </flux:sidebar.nav>

            <flux:spacer />
            

        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.collapse />

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
