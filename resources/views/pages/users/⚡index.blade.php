<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public ?string $gender = null;
    public ?int $company_id = null;
    public ?int $branch_id = null;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $companies = [];
    public array $branches = [];

    public function mount()
    {
        $this->companies = Company::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatedCompanyId($value): void
    {
        $this->branch_id = null;
        $this->branches = $value ? Branch::where('company_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->resetPage();
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
        if (in_array($property, ['search', 'gender', 'branch_id', 'company_id'])) {
            $this->resetPage();
        }
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
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
            ->when($this->company_id, fn ($q) => $q->where('company_id', $this->company_id))
            ->when($this->branch_id, fn ($q) => $q->where('branch_id', $this->branch_id))
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
        <flux:accordion>
            <div class="flex items-center gap-4">
                <flux:accordion.trigger>
                    <flux:button square icon="funnel" icon:variant="outline" :variant="$company_id || $branch_id || $gender ? 'primary' : 'outline'" />
                </flux:accordion.trigger>
                <div class="w-72">
                    <flux:input icon="magnifying-glass" placeholder="Search name or email..." wire:model.live.debounce.300ms="search" clearable />
                </div>
            </div>

            <flux:accordion.content class="mt-4">
                <div class="flex gap-3 flex-wrap">
                    <div class="w-48">
                        <flux:select wire:model.live="company_id" variant="listbox" searchable clearable placeholder="Company">
                            @foreach ($companies as $company)
                                <flux:select.option value="{{ $company['id'] }}">{{ $company['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model.live="branch_id" variant="listbox" searchable clearable :disabled="!$company_id" :placeholder="!$company_id ? 'Select company first' : 'Branch'">
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
            </flux:accordion.content>
        </flux:accordion>

        <flux:table :paginate="$users" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>
                    <flux:table.sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">
                        Name
                    </flux:table.sortable>
                </flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Company</flux:table.column>
                <flux:table.column>Branch</flux:table.column>
                <flux:table.column>Gender</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>
                    <flux:table.sortable :sorted="$sortField === 'is_active'" :direction="$sortField === 'is_active' ? $sortDirection : null" wire:click="sortBy('is_active')">
                        Status
                    </flux:table.sortable>
                </flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row>
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
                                    <flux:menu.item icon="trash" variant="danger" wire:click="deleteUser({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?">Delete</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
