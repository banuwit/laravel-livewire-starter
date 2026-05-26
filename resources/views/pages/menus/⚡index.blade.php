<?php

use Flux\Flux;
use Livewire\Component;
use App\Models\Menu;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $sortField = 'sort_order';
    public string $sortDirection = 'asc';
    public array $layoutFilter = [];

    public ?int $deletingId = null;
    public string $deletingLabel = '';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'layoutFilter'])) {
            $this->resetPage();
        }
    }

    public function toggleActive(Menu $menu): void
    {
        $menu->update(['is_active' => !$menu->is_active]);
        Flux::toast(variant: 'success', text: 'Menu status updated.');
    }

    public function confirmDelete(int $id): void
    {
        $menu = Menu::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingLabel = $menu->name;
        Flux::modal('delete-menu')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Menu::find($this->deletingId)?->delete();
            Flux::toast(variant: 'success', text: 'Menu deleted.');
        }
        $this->reset('deletingId', 'deletingLabel');
        Flux::modal('delete-menu')->close();
        $this->resetPage();
    }

    public function render()
    {
        $menus = Menu::with('parent')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->layoutFilter, fn($q) => $q->whereIn('layout', $this->layoutFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return $this->view(['menus' => $menus]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Menus</flux:heading>
        @can('menus.create')
            <flux:button wire:navigate href="{{ route('menus.create') }}" variant="primary" icon="plus">Add Menu</flux:button>
        @endcan
    </div>

    <flux:card class="space-y-4" size="sm">
        <div class="flex gap-4">
            <div class="w-64">
                <flux:input icon="magnifying-glass" placeholder="Search name..." wire:model.live.debounce.300ms="search" clearable />
            </div>
            <div class="w-48">
                <flux:select wire:model.live="layoutFilter" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable placeholder="Layouts">
                    <flux:select.option value="sidebar">Sidebar</flux:select.option>
                    <flux:select.option value="header">Header</flux:select.option>
                    <flux:select.option value="nav_user">Nav User</flux:select.option>
                </flux:select>
            </div>
        </div>

        <flux:table :paginate="$menus" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Name</flux:table.column>
                <flux:table.column>Icon</flux:table.column>
                <flux:table.column>Layout</flux:table.column>
                <flux:table.column>Level</flux:table.column>
                <flux:table.column>Parent</flux:table.column>
                <flux:table.column>Route</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'sort_order'" :direction="$sortField === 'sort_order' ? $sortDirection : null" wire:click="sortBy('sort_order')">Order</flux:table.column>
                <flux:table.column>Active</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($menus as $menu)
                    <flux:table.row wire:key="menu-{{ $menu->id }}">
                        <flux:table.cell class="text-zinc-400 text-xs">{{ $menus->firstItem() + $loop->index }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ $menu->name }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($menu->icon)
                                <flux:icon :icon="$menu->icon" size="sm" class="text-zinc-500" />
                            @else
                                <span class="text-zinc-400 text-xs">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $layoutColor = match($menu->layout) {
                                    'sidebar' => 'blue',
                                    'header' => 'purple',
                                    'nav_user' => 'green',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$layoutColor" size="sm">{{ ucfirst(str_replace('_', ' ', $menu->layout)) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $menu->level === 0 ? 'zinc' : 'amber' }}" size="sm">
                                {{ $menu->level === 0 ? 'Root' : 'Sub-menu' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500 text-sm">
                            {{ $menu->parent?->name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500 text-xs font-mono">
                            {{ $menu->route_name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell class="text-center">{{ $menu->sort_order }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:switch wire:click="toggleActive({{ $menu->id }})" :checked="$menu->is_active" />
                        </flux:table.cell>
                        <flux:table.cell>
                            @canany(['menus.edit', 'menus.delete'])
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                    <flux:menu>
                                        @can('menus.edit')
                                            <flux:menu.item icon="pencil" wire:navigate href="{{ route('menus.edit', $menu) }}">Edit</flux:menu.item>
                                        @endcan
                                        @can('menus.delete')
                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $menu->id }})">Delete</flux:menu.item>
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            @endcanany
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-1 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.bars-3 class="size-8 opacity-40" />
                                <flux:text>No menus found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="delete-menu" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete menu?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete <strong>{{ $deletingLabel }}</strong>. Its permissions and sub-menus may also be affected. This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="trash" wire:click="delete">Delete menu</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
