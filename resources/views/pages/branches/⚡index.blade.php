<?php

use App\Models\Branch;
use App\Models\Company;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public ?int $company_id = null;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public ?int $deletingId = null;
    public string $deletingLabel = '';

    public array $companies = [];

    public function mount(): void
    {
        $this->companies = Company::orderBy('name')->get(['id', 'name'])->toArray();
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
        if (in_array($property, ['search', 'company_id'])) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $branch = Branch::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingLabel = $branch->name;
        Flux::modal('delete-branch')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Branch::find($this->deletingId)?->delete();
            Flux::toast(variant: 'success', text: 'Branch deleted.');
        }
        $this->reset('deletingId', 'deletingLabel');
        Flux::modal('delete-branch')->close();
        $this->resetPage();
    }

    public function render()
    {
        $branches = Branch::query()
            ->with('company')
            ->withCount('users')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->when($this->company_id, fn ($q) => $q->where('company_id', $this->company_id))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return $this->view(['branches' => $branches]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Branches</flux:heading>
        @can('branches.create')
            <flux:button wire:navigate href="{{ route('branches.create') }}" variant="primary" icon="plus">Add New</flux:button>
        @endcan
    </div>

    <flux:card class="space-y-4" size="sm">
        <flux:accordion>
            <div class="flex items-center gap-4">
                <flux:accordion.trigger>
                    <flux:button square icon="funnel" icon:variant="outline" :variant="$company_id ? 'primary' : 'outline'" />
                </flux:accordion.trigger>
                <div class="w-72">
                    <flux:input icon="magnifying-glass" placeholder="Search name or code..." wire:model.live.debounce.300ms="search" clearable />
                </div>
            </div>

            <flux:accordion.content class="mt-4">
                <div class="flex gap-3 flex-wrap">
                    <div class="w-56">
                        <flux:select wire:model.live="company_id" variant="listbox" searchable clearable placeholder="All Companies">
                            @foreach ($companies as $company)
                                <flux:select.option value="{{ $company['id'] }}">{{ $company['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </flux:accordion.content>
        </flux:accordion>

        <flux:table :paginate="$branches" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Company</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Branch Name</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Users</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($branches as $branch)
                    <flux:table.row wire:key="branch-{{ $branch->id }}">
                        <flux:table.cell class="text-zinc-400 text-xs">{{ $branches->firstItem() + $loop->index }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm">{{ $branch->company->name }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell variant="strong">{{ $branch->name }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($branch->type === 'headquarter')
                                <flux:badge color="amber" size="sm">Headquarter</flux:badge>
                            @else
                                <flux:badge color="blue" size="sm">Branch</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $branch->code ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $branch->phone ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $branch->email ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="violet" size="sm">{{ $branch->users_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($branch->is_active)
                                <flux:badge color="emerald" size="sm">Active</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">Inactive</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @canany(['branches.edit', 'branches.delete'])
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                <flux:menu>
                                    @can('branches.edit')
                                    <flux:menu.item icon="pencil" wire:navigate href="{{ route('branches.edit', $branch->id) }}">Edit</flux:menu.item>
                                    @endcan
                                    @can('branches.delete')
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $branch->id }})">Delete</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-1 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.building-storefront class="size-8 opacity-40" />
                                <flux:text>No branches found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="delete-branch" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete branch?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete <strong>{{ $deletingLabel }}</strong>. This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="trash" wire:click="delete">Delete branch</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
