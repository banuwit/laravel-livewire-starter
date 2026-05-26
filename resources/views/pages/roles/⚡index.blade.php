<?php

use Flux\Flux;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

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

    public function updated($property)
    {
        if (in_array($property, ['search'])) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $role = Role::findById($id);
        $this->deletingId = $id;
        $this->deletingLabel = $role->name;
        Flux::modal('delete-role')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Role::findById($this->deletingId)?->delete();
            Flux::toast(variant: 'success', text: 'Role deleted.');
        }
        $this->reset('deletingId', 'deletingLabel');
        Flux::modal('delete-role')->close();
        $this->resetPage();
    }

    public function render()
    {
        $roles = Role::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
        return $this->view(['roles' => $roles]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Roles</flux:heading>
        <flux:button wire:navigate href="{{ route('roles.create') }}" variant="primary" icon="plus">Add New</flux:button>
    </div>
    <flux:card class="space-y-4" size="sm">
        <div class="w-full sm:w-72">
            <flux:input icon="magnifying-glass" placeholder="Search name..." wire:model.live.debounce.300ms="search" clearable />
        </div>
        <flux:table :paginate="$roles" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Name</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'guard_name'" :direction="$sortField === 'guard_name' ? $sortDirection : null" wire:click="sortBy('guard_name')">Guard Name</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortField === 'created_at' ? $sortDirection : null" wire:click="sortBy('created_at')">Created At</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($roles as $role)
                    <flux:table.row wire:key="role-{{ $role->id }}">
                        <flux:table.cell class="text-zinc-400 text-xs">{{ $roles->firstItem() + $loop->index }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ ucfirst($role->name) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm">{{ $role->guard_name }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $role->created_at->format('Y-m-d') }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($role->name !== 'superadmin')
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:navigate href="{{ route('roles.edit', $role) }}">Edit</flux:menu.item>
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $role->id }})">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <flux:badge color="amber" size="sm" icon="lock-closed">Protected</flux:badge>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-1 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.shield-check class="size-8 opacity-40" />
                                <flux:text>No roles found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="delete-role" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete role?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete <strong>{{ ucfirst($deletingLabel) }}</strong>. This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="trash" wire:click="delete">Delete role</flux:button>
            </div>
        </div>
    </flux:modal>
</div>