<?php

use Livewire\Component;
use App\Models\Menu;
use App\Models\Permission;

new class extends Component
{
    public Menu $menu;

    public string $name = '';
    public string $slug = '';
    public string $icon = '';
    public string $route_name = '';
    public string $route_pattern = '';
    public ?int $parent_id = null;
    public int $level = 0;
    public int $sort_order = 0;
    public string $layout = 'sidebar';
    public bool $is_active = true;

    /** @var array<int, array{id: ?int, name: string}> */
    public array $permissions = [];

    public array $parentMenus = [];

    protected function rules(): array
    {
        $permissionRules = [];
        foreach ($this->permissions as $i => $p) {
            $ignoreId = $p['id'] ?? 'NULL';
            $permissionRules["permissions.$i.name"] = "required|string|max:255|distinct|unique:permissions,name,{$ignoreId}";
        }

        return array_merge([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|max:255|unique:menus,slug,' . $this->menu->id,
            'icon'          => 'nullable|string|max:100',
            'route_name'    => 'nullable|string|max:255',
            'route_pattern' => 'nullable|string|max:255',
            'parent_id'     => 'nullable|integer|exists:menus,id',
            'level'         => 'required|integer|min:0|max:1',
            'sort_order'    => 'required|integer|min:0',
            'layout'        => 'required|string|in:sidebar,header,nav_user',
            'is_active'     => 'boolean',
            'permissions'   => 'array',
        ], $permissionRules);
    }

    protected function messages(): array
    {
        return [
            'permissions.*.name.required' => 'Permission name is required.',
            'permissions.*.name.distinct' => 'Duplicate permission name in the list.',
            'permissions.*.name.unique'   => 'Permission name already exists in the system.',
        ];
    }

    public function mount(Menu $menu): void
    {
        $this->menu = $menu;
        $this->name          = $menu->name;
        $this->slug          = $menu->slug;
        $this->icon          = $menu->icon ?? '';
        $this->route_name    = $menu->route_name ?? '';
        $this->route_pattern = $menu->route_pattern ?? '';
        $this->parent_id     = $menu->parent_id;
        $this->level         = $menu->level;
        $this->sort_order    = $menu->sort_order;
        $this->layout        = $menu->layout;
        $this->is_active     = $menu->is_active;

        $this->permissions = $menu->permissions()
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
            ->toArray();

        $this->parentMenus = Menu::where('level', 0)
            ->where('id', '!=', $menu->id)
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function updatedName(string $value): void
    {
        $this->slug = str($value)->slug()->toString();
    }

    public function updatedParentId(?int $value): void
    {
        $this->level = $value ? 1 : 0;
    }

    public function addPermission(string $action = ''): void
    {
        $prefix = $this->slug ? $this->slug . '.' : '';
        $this->permissions[] = ['id' => null, 'name' => $prefix . $action];
    }

    public function removePermission(int $index): void
    {
        unset($this->permissions[$index]);
        $this->permissions = array_values($this->permissions);
        $this->resetValidation('permissions');
    }

    public function updatedPermissions($value, $key): void
    {
        // Real-time validation per row (e.g. key = "0.name")
        if (str_ends_with($key, '.name')) {
            $this->validateOnly("permissions.$key");
        }
    }

    public function updateMenu(): void
    {
        $this->validate();

        // Delete permissions removed from the list
        $keepIds = collect($this->permissions)->pluck('id')->filter()->all();
        Permission::where('menu_id', $this->menu->id)
            ->when(!empty($keepIds), fn($q) => $q->whereNotIn('id', $keepIds))
            ->delete();

        // Upsert permissions
        foreach ($this->permissions as $i => $p) {
            if ($p['id']) {
                Permission::where('id', $p['id'])->update([
                    'name'       => $p['name'],
                    'sort_order' => $i + 1,
                ]);
            } else {
                Permission::create([
                    'name'       => $p['name'],
                    'guard_name' => 'web',
                    'menu_id'    => $this->menu->id,
                    'sort_order' => $i + 1,
                ]);
            }
        }

        $this->menu->update([
            'name'          => $this->name,
            'slug'          => $this->slug,
            'icon'          => $this->icon ?: null,
            'route_name'    => $this->route_name ?: null,
            'route_pattern' => $this->route_pattern ?: null,
            'parent_id'     => $this->parent_id,
            'level'         => $this->level,
            'sort_order'    => $this->sort_order,
            'layout'        => $this->layout,
            'is_active'     => $this->is_active,
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        session()->flash('success', 'Menu updated successfully.');
        $this->redirectRoute('menus.index', navigate: true);
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
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('menus.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Edit Menu: {{ $menu->name }}</flux:heading>
                <flux:text variant="muted">Update menu details and manage its permissions.</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('menus.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Update Menu</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="updateMenu" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Right wide: Tabs --}}
        <flux:card class="lg:col-span-2 lg:order-2">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Configuration</flux:heading>
                    <flux:text variant="muted" size="sm">Routing, hierarchy, and permissions managed by this menu.</flux:text>
                </div>
                <flux:separator />

                <flux:tab.group variant="segmented">
                    <flux:tab.list variant="segmented">
                        <flux:tab name="routing" icon="link">Routing & Hierarchy</flux:tab>
                        <flux:tab name="permissions" icon="key">
                            Permissions
                        </flux:tab>
                    </flux:tab.list>

                    {{-- Routing tab --}}
                    <flux:tab.panel name="routing" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:input wire:model="route_name" label="Route Name" placeholder="e.g. users.index" />
                            <flux:input wire:model="route_pattern" label="Route Pattern" placeholder="e.g. users.*"/>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <flux:select wire:model.live="parent_id" variant="listbox" label="Parent Menu" searchable clearable placeholder="None (Root)">
                                @foreach ($parentMenus as $parent)
                                    <flux:select.option value="{{ $parent['id'] }}">{{ $parent['name'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:input wire:model="level" label="Level" type="number" min="0" max="1" readonly />
                            <flux:input wire:model="sort_order" label="Sort Order" type="number" min="0" />
                        </div>
                    </flux:tab.panel>

                    {{-- Permissions tab --}}
                    <flux:tab.panel name="permissions" class="space-y-3">
                        <flux:text size="sm" class="!text-zinc-500">
                            <flux:icon.information-circle variant="mini" class="inline -mt-0.5" />
                            Each permission belongs strictly to this menu. Use the convention <code class="text-xs px-1 rounded bg-zinc-100 dark:bg-white/10">{{ $slug ?: 'slug' }}.action</code> (e.g. <code class="text-xs px-1 rounded bg-zinc-100 dark:bg-white/10">{{ $slug ?: 'users' }}.view</code>). Removing a row deletes the permission on save.
                        </flux:text>

                        @if (!empty($permissions))
                            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($permissions as $i => $perm)
                                    <div class="flex items-start gap-2 p-3" wire:key="perm-{{ $i }}-{{ $perm['id'] ?? 'new' }}">
                                        <div class="flex-1">
                                            <flux:input
                                                wire:model.blur="permissions.{{ $i }}.name"
                                                placeholder="{{ $slug ?: 'slug' }}.action"
                                                size="sm"
                                                :badge="$perm['id'] ? null : 'New'"
                                                :invalid="$errors->has('permissions.'.$i.'.name')"
                                            />
                                            @error('permissions.'.$i.'.name')
                                                <flux:text size="sm" class="mt-1 !text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
                                            @enderror
                                        </div>
                                        <flux:button type="button" variant="ghost" size="sm" icon="trash" square
                                            wire:click="removePermission({{ $i }})"
                                            wire:confirm="{{ $perm['id'] ? 'This will delete the permission permanently on save. Continue?' : '' }}"
                                            class="!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-500/10" />
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 p-6 text-center">
                                <flux:icon.key class="size-8 text-zinc-400 mx-auto" />
                                <flux:text class="mt-2 !text-zinc-500">No permissions yet.</flux:text>
                                <flux:text size="sm" class="!text-zinc-400">Add permissions to control access to this menu.</flux:text>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button type="button" size="sm" variant="primary" icon="plus" wire:click="addPermission">Add Permission</flux:button>
                            <flux:dropdown>
                                <flux:button type="button" size="sm" variant="ghost" icon="bolt" icon-trailing="chevron-down">Quick Add</flux:button>
                                <flux:menu>
                                    <flux:menu.item wire:click="addPermission('view')">view</flux:menu.item>
                                    <flux:menu.item wire:click="addPermission('create')">create</flux:menu.item>
                                    <flux:menu.item wire:click="addPermission('edit')">edit</flux:menu.item>
                                    <flux:menu.item wire:click="addPermission('delete')">delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </flux:tab.panel>
                </flux:tab.group>
            </div>
        </flux:card>

        {{-- Left narrow: Basic info + actions --}}
        <div class="flex flex-col gap-6 lg:order-1">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Menu Information</flux:heading>
                    <flux:text variant="muted" size="sm">Identity and visibility.</flux:text>
                </div>
                <flux:separator />

                <flux:input wire:model.live="name" label="Name" placeholder="e.g. Users" required />
                <flux:input wire:model="slug" label="Slug" placeholder="auto from name" />
                <flux:input wire:model="icon" label="Icon" placeholder="e.g. user, cog-6-tooth" />

                <flux:select wire:model.live="layout" variant="listbox" label="Layout" searchable required>
                    <flux:select.option value="sidebar">Sidebar</flux:select.option>
                    <flux:select.option value="header">Header</flux:select.option>
                    <flux:select.option value="nav_user">Nav User</flux:select.option>
                </flux:select>

                <flux:separator />

                <flux:switch wire:model="is_active" label="Active" description="Show this menu in navigation" />
            </flux:card>

        </div>
    </form>
</div>
