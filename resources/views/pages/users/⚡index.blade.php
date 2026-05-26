<?php

use App\Models\Branch;
use App\Models\User;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public ?string $gender = null;
    public array $branchFilter = [];
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public ?int $deletingId = null;
    public string $deletingLabel = '';

    public array $branches = [];

    public function mount()
    {
        $this->branches = Branch::orderBy('name')->get(['id', 'name'])->toArray();
    }

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
        if (in_array($property, ['search', 'gender', 'branchFilter'])) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $user = User::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingLabel = $user->name ?? $user->email;
        Flux::modal('delete-user')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            User::find($this->deletingId)?->delete();
            Flux::toast(variant: 'success', text: 'User deleted.');
        }
        $this->reset('deletingId', 'deletingLabel');
        Flux::modal('delete-user')->close();
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->with(['company', 'branch', 'roles'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->when($this->gender, fn ($q) => $q->where('gender', $this->gender))
            ->when($this->branchFilter, fn ($q) => $q->whereIn('branch_id', $this->branchFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return $this->view(['users' => $users]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Users</flux:heading>
        @can('users.create')
            <flux:button wire:navigate href="{{ route('users.create') }}" variant="primary" icon="plus">Add New</flux:button>
        @endcan
    </div>

    <flux:card class="space-y-4" size="sm">
        <div class="flex items-center gap-4">
            <div class="w-72">
                <flux:input icon="magnifying-glass" placeholder="Search name or email..." wire:model.live.debounce.300ms="search" clearable />
            </div>
            <div class="w-48">
                <flux:select wire:model.live="branchFilter" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable placeholder="Branch">
                    @foreach ($branches as $branch)
                        <flux:select.option value="{{ $branch['id'] }}">{{ $branch['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-48">
                <flux:select wire:model.live="gender" variant="listbox" indicator="radio" searchable clearable placeholder="Gender">
                    <flux:select.option value="male">Male</flux:select.option>
                    <flux:select.option value="female">Female</flux:select.option>
                </flux:select>
            </div>
        </div>

        <flux:table :paginate="$users" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Name</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Company</flux:table.column>
                <flux:table.column>Branch</flux:table.column>
                <flux:table.column>Gender</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'is_active'" :direction="$sortField === 'is_active' ? $sortDirection : null" wire:click="sortBy('is_active')">Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell class="text-zinc-400 text-xs">
                            {{ $users->firstItem() + $loop->index }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar circle :name="$user->displayName()" />
                                <div class="flex flex-col">
                                    <flux:text variant="strong" class="font-semibold text-sm">{{ $user->name ?? '—' }}</flux:text>
                                    <flux:text size="xs" variant="muted">{{ $user->email }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm" class="{{ $user->phonenumber ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                {{ $user->phonenumber ?? '—' }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm" class="{{ $user->company ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                {{ $user->company?->name ?? '—' }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm" class="{{ $user->branch ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                {{ $user->branch?->name ?? '—' }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->gender === 'male')
                                <flux:badge color="sky" size="sm">Male</flux:badge>
                            @elseif ($user->gender === 'female')
                                <flux:badge color="pink" size="sm">Female</flux:badge>
                            @else
                                <flux:text size="sm" class="text-zinc-300 dark:text-zinc-600">—</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @foreach ($user->roles as $role)
                                <flux:badge color="violet" size="sm">{{ ucfirst($role->name) }}</flux:badge>
                            @endforeach
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->is_active)
                                <flux:badge color="emerald" size="sm">Active</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">Inactive</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @canany(['users.edit', 'users.delete'])
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                <flux:menu>
                                    @can('users.edit')
                                    <flux:menu.item icon="pencil" wire:navigate href="{{ route('users.edit', $user->id) }}">Edit</flux:menu.item>
                                    @endcan
                                    @can('users.delete')
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $user->id }})">Delete</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-1 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.users class="size-8 opacity-40" />
                                <flux:text>No users found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="delete-user" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete user?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete <strong>{{ $deletingLabel }}</strong>. This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="trash" wire:click="delete">Delete user</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
