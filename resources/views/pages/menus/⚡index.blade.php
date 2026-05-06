<?php

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
        $this->dispatch('toast', message: 'Menu status updated.');
    }

    public function deleteMenu(Menu $menu): void
    {
        $menu->delete();
        $this->dispatch('toast', message: 'Menu deleted successfully.');
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

    <flux:card class="space-y-4 mt-4" size="sm">
        <div class="flex items-center gap-3 flex-wrap">
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
                <flux:table.column sticky>#</flux:table.column>
                <flux:table.column sortable="name" :sort="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Name</flux:table.column>
                <flux:table.column>Icon</flux:table.column>
                <flux:table.column>Layout</flux:table.column>
                <flux:table.column>Level</flux:table.column>
                <flux:table.column>Parent</flux:table.column>
                <flux:table.column>Route</flux:table.column>
                <flux:table.column sortable="sort_order" :sort="$sortField === 'sort_order' ? $sortDirection : null" wire:click="sortBy('sort_order')">Order</flux:table.column>
                <flux:table.column>Active</flux:table.column>
                <flux:table.column sticky class="text-right">Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($menus as $menu)
                    <flux:table.row>
                        <flux:table.cell>{{ $loop->iteration }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $menu->name }}</flux:table.cell>
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
                        <flux:table.cell class="text-right">
                            @can('menus.edit')
                                <flux:button wire:navigate href="{{ route('menus.edit', $menu) }}" variant="ghost" icon="pencil" size="sm" />
                            @endcan
                            @can('menus.delete')
                                <flux:button wire:click="deleteMenu({{ $menu->id }})" wire:confirm="Are you sure you want to delete this menu?" variant="ghost" icon="trash" size="sm" />
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10" class="text-center text-zinc-500 py-8">No menus found.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
