<?php

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

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

    public function deleteCompany(Company $company): void
    {
        $company->delete();
    }

    public function render()
    {
        $companies = Company::query()
            ->withCount(['branches', 'employees'])
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
        <div class="w-72">
            <flux:input icon="magnifying-glass" placeholder="Search name or code..." wire:model.live.debounce.300ms="search" clearable />
        </div>

        <flux:table :paginate="$companies" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>
                    <flux:table.sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">
                        Name
                    </flux:table.sortable>
                </flux:table.column>
                <flux:table.column>Code</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Branches</flux:table.column>
                <flux:table.column>Employees</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($companies as $company)
                    <flux:table.row>
                        <flux:table.cell>{{ $companies->firstItem() + $loop->index }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ $company->name }}</flux:table.cell>
                        <flux:table.cell>{{ $company->code ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $company->phone ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $company->email ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="blue">{{ $company->branches_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="violet">{{ $company->employees_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($company->is_active)
                                <flux:badge color="emerald">Active</flux:badge>
                            @else
                                <flux:badge color="red">Inactive</flux:badge>
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
                                    <flux:menu.item icon="trash" variant="danger" wire:click="deleteCompany({{ $company->id }})" wire:confirm="Delete this company? All branches will also be deleted.">Delete</flux:menu.item>
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
