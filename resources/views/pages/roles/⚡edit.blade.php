<?php

use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public Role $role;
    public string $name;
    public string $guard_name;
    public array $selectedPermission = [];
    public array $menuGroups = [];

    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->selectedPermission = $role->permissions->pluck('id')->toArray();
        $this->menuGroups = \App\Models\Menu::with([
                'permissions' => fn($q) => $q->orderBy('sort_order'),
                'children' => fn($q) => $q->with(['permissions' => fn($q) => $q->orderBy('sort_order')])->orderBy('sort_order')
            ])
            ->roots()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('layout')
            ->map(fn($items) => $items->toArray())
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'guard_name' => 'required|string|max:255',
            'selectedPermission' => 'array',
            'selectedPermission.*' => 'integer|exists:permissions,id',
        ];
    }

    public function getTotalPermissionsProperty(): int
    {
        $total = 0;
        foreach ($this->menuGroups as $layoutMenus) {
            foreach ($layoutMenus as $menu) {
                $total += count($menu['permissions'] ?? []);
                foreach ($menu['children'] ?? [] as $child) {
                    $total += count($child['permissions'] ?? []);
                }
            }
        }
        return $total;
    }

    public function updateRole(): void
    {
        $this->validate();
        $this->role->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);
        $this->role->syncPermissions($this->selectedPermission);
        session()->flash('success', 'Role updated successfully.');
        $this->redirectRoute('roles.index', navigate: true);
    }

    public function render()
    {
        return $this->view();
    }
};
?>
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('roles.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Edit Role</flux:heading>
                <flux:text variant="muted">Update role details and permissions.</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('roles.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Update Role</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="updateRole" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Right wide: permissions --}}
        <flux:card class="lg:col-span-2 lg:order-2">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Permissions</flux:heading>
                    <flux:text variant="muted" size="sm">Pick what this role is allowed to do.</flux:text>
                </div>
                <flux:separator />

                @php
                    $layoutLabels = ['sidebar' => 'Sidebar Menu', 'nav_user' => 'User Nav Menu'];
                @endphp

                <flux:tab.group>
                    <flux:tab.list>
                        @foreach ($menuGroups as $layout => $menus)
                            <flux:tab name="{{ $layout }}">
                                {{ $layoutLabels[$layout] ?? \Illuminate\Support\Str::headline($layout) }}
                            </flux:tab>
                        @endforeach
                    </flux:tab.list>

                    @foreach ($menuGroups as $layout => $menus)
                        <flux:tab.panel name="{{ $layout }}" class="space-y-2">
                            @foreach ($menus as $menu)
                                @php
                                    $hasChildrenWithPerms = !empty($menu['children'])
                                        && collect($menu['children'])->some(fn($c) => !empty($c['permissions']));
                                @endphp

                                @if ($hasChildrenWithPerms)
                                    <div x-data="{ openParent: true }" class="space-y-2 rounded-lg bg-white dark:bg-zinc-900/40">
                                        <button type="button" @click="openParent = !openParent"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/60 dark:bg-white/5 rounded-lg hover:bg-zinc-100 dark:hover:bg-white/10 transition">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <flux:icon.chevron-down variant="mini" class="text-zinc-500 transition-transform shrink-0" x-bind:class="openParent ? '' : '-rotate-90'" />
                                                <flux:heading size="sm" class="font-semibold capitalize truncate">{{ $menu['name'] }}</flux:heading>
                                            </div>
                                        </button>
                                        <div x-show="openParent" x-collapse class="space-y-2 pl-8">
                                            @include('pages.roles._permission-group', ['group' => $menu])
                                            @foreach ($menu['children'] as $child)
                                                @include('pages.roles._permission-group', ['group' => $child, 'isChild' => true])
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @include('pages.roles._permission-group', ['group' => $menu])
                                @endif
                            @endforeach
                        </flux:tab.panel>
                    @endforeach
                </flux:tab.group>
            </div>
        </flux:card>

        {{-- Left narrow: info + actions --}}
        <div class="flex flex-col gap-6 lg:order-1">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Role Information</flux:heading>
                    <flux:text variant="muted" size="sm">Basic details for this role.</flux:text>
                </div>
                <flux:separator />

                <flux:input wire:model="name" label="Name" placeholder="e.g. Editor, Manager" required />
                <flux:input wire:model="guard_name" label="Guard Name" placeholder="web" required description="Default: web" />
            </flux:card>

        </div>
    </form>
</div>
