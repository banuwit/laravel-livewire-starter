<?php

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
    public ?int $roleToDelete = null;

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

    public function deleteRole(): void
    {
        if ($this->roleToDelete) {
            Role::findById($this->roleToDelete)?->delete();
            $this->dispatch('toast', message: 'Role deleted successfully');
            $this->resetPage();
        }
        $this->roleToDelete = null;
    }

    public function setRoleToDelete(?int $id): void
    {
        $this->roleToDelete = $id;
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
        <div class="flex items-center justify-between gap-4">
            <div class="w-72">
                <flux:input icon="magnifying-glass" placeholder="Search name..." wire:model.live.debounce.300ms="search" clearable />
            </div>
        </div>
        <flux:table :paginate="$roles" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sticky>#</flux:table.column>
                <flux:table.column sortable="name" :sort="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Name</flux:table.column>
                <flux:table.column sortable="guard_name" :sort="$sortField === 'guard_name' ? $sortDirection : null" wire:click="sortBy('guard_name')">Guard Name</flux:table.column>
                <flux:table.column sortable="created_at" :sort="$sortField === 'created_at' ? $sortDirection : null" wire:click="sortBy('created_at')">Created At</flux:table.column>
                <flux:table.column sticky class="text-right">Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($roles as $role)
                    <flux:table.row>
                        <flux:table.cell>{{ $loop->iteration }}</flux:table.cell>
                        <flux:table.cell>{{ $role->name }}</flux:table.cell>
                        <flux:table.cell>{{ $role->guard_name }}</flux:table.cell>
                        <flux:table.cell>{{ $role->created_at->format('Y-m-d') }}</flux:table.cell>
                        <flux:table.cell class="text-right">
                            @if($role->name != 'superadmin')
                                <flux:button wire:navigate href="{{ route('roles.edit', $role) }}" variant="ghost" icon="pencil" size="sm" />
                                <flux:modal.trigger name="delete-role">
                                    <flux:button variant="ghost" icon="trash" size="sm" wire:click="setRoleToDelete({{ $role->id }})" />
                                </flux:modal.trigger>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
    <flux:modal name="delete-role">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Role</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to delete this role?
                </flux:text>
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button wire:click="deleteRole()" wire:navigate variant="danger">Delete</flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>