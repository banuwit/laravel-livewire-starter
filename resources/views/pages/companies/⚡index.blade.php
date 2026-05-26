<?php

use App\Models\Company;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

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
        if ($property === 'search') {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $company = Company::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingLabel = $company->name;
        Flux::modal('delete-company')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Company::find($this->deletingId)?->delete();
            Flux::toast(variant: 'success', text: 'Company deleted.');
        }
        $this->reset('deletingId', 'deletingLabel');
        Flux::modal('delete-company')->close();
        $this->resetPage();
    }

    public function render()
    {
        $companies = Company::query()
            ->withCount(['branches', 'users'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return $this->view(['companies' => $companies]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Companies</flux:heading>
        @can('companies.create')
            <flux:button wire:navigate href="{{ route('companies.create') }}" variant="primary" icon="plus">Add New</flux:button>
        @endcan
    </div>

    <flux:card class="space-y-4" size="sm">
        <div class="w-full sm:w-72">
            <flux:input icon="magnifying-glass" placeholder="Search name or code..." wire:model.live.debounce.300ms="search" clearable />
        </div>

        <flux:table :paginate="$companies" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">Name</flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Branches</flux:table.column>
                <flux:table.column>Users</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($companies as $company)
                    <flux:table.row wire:key="company-{{ $company->id }}">
                        <flux:table.cell class="text-zinc-400 text-xs">{{ $companies->firstItem() + $loop->index }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ $company->name }}</flux:table.cell>
                        <flux:table.cell>{{ $company->code ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $company->phone ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $company->email ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="blue" size="sm">{{ $company->branches_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="violet" size="sm">{{ $company->users_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($company->is_active)
                                <flux:badge color="emerald" size="sm">Active</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">Inactive</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @canany(['companies.edit', 'companies.delete'])
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                <flux:menu>
                                    @can('companies.edit')
                                    <flux:menu.item icon="pencil" wire:navigate href="{{ route('companies.edit', $company->id) }}">Edit</flux:menu.item>
                                    @endcan
                                    @can('companies.delete')
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $company->id }})">Delete</flux:menu.item>
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
                                <flux:icon.building-office-2 class="size-8 opacity-40" />
                                <flux:text>No companies found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="delete-company" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete company?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete <strong>{{ $deletingLabel }}</strong>. All its branches will also be deleted. This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="trash" wire:click="delete">Delete company</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
