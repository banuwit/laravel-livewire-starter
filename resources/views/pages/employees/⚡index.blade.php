<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Province;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public ?string $gender = null;
    public array $religion = [];
    public ?int $company_id = null;
    public ?int $branch_id = null;
    public array $country_id = [];
    public array $province_id = [];
    public array $city_id = [];
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $companies = [];
    public array $branches = [];
    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];

    public function mount()
    {
        $this->companies = Company::orderBy('name')->get(['id', 'name'])->toArray();
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->provinces = Province::orderBy('name')->get()->toArray();
        $this->cities = City::orderBy('name')->get()->toArray();
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
        if (in_array($property, ['search', 'gender', 'religion', 'branch_id', 'country_id', 'province_id', 'city_id'])) {
            $this->resetPage();
        }
    }

    public function deleteEmployee(Employee $employee): void
    {
        $employee->delete();
    }

    public function render()
    {
        $employees = Employee::query()
            ->with(['user', 'company', 'branch', 'country', 'province', 'city'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->gender, fn ($q) => $q->where('gender', $this->gender))
            ->when($this->religion, fn ($q) => $q->whereIn('religion', $this->religion))
            ->when($this->company_id, fn ($q) => $q->where('company_id', $this->company_id))
            ->when($this->branch_id, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->country_id, fn ($q) => $q->whereIn('country_id', $this->country_id))
            ->when($this->province_id, fn ($q) => $q->whereIn('province_id', $this->province_id))
            ->when($this->city_id, fn ($q) => $q->whereIn('city_id', $this->city_id))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return $this->view(['employees' => $employees]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Employees</flux:heading>
        @can('employees.create')
            <flux:button wire:navigate href="{{ route('employees.create') }}" variant="primary" icon="plus">Add New</flux:button>
        @endcan
    </div>

    <flux:card class="space-y-4" size="sm">
        <flux:accordion>
            <div class="flex items-center gap-4">
                <flux:accordion.trigger>
                    <flux:button square icon="funnel" icon:variant="outline" :variant="$company_id || $branch_id || $gender || $religion || $country_id || $province_id || $city_id ? 'primary' : 'outline'" />
                </flux:accordion.trigger>
                <div class="w-72">
                    <flux:input icon="magnifying-glass" placeholder="Search name..." wire:model.live.debounce.300ms="search" clearable />
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
                    <div class="w-48">
                        <flux:select wire:model="religion" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="Religion">
                            <flux:select.option value="islam">Islam</flux:select.option>
                            <flux:select.option value="kristen">Christian</flux:select.option>
                            <flux:select.option value="hindu">Hindu</flux:select.option>
                            <flux:select.option value="buddhist">Buddhist</flux:select.option>
                            <flux:select.option value="other">Other</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model="country_id" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="Country">
                            @foreach ($countries as $country)
                                <flux:select.option value="{{ $country['id'] }}">{{ $country['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model="province_id" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="Province" dropdownWidth="200px">
                            @foreach ($provinces as $province)
                                <flux:select.option value="{{ $province['id'] }}">{{ $province['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model="city_id" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="City" dropdownWidth="200px">
                            @foreach ($cities as $city)
                                <flux:select.option value="{{ $city['id'] }}">{{ $city['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </flux:accordion.content>
        </flux:accordion>

        <flux:table :paginate="$employees" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>
                    <flux:table.sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">
                        Name
                    </flux:table.sortable>
                </flux:table.column>
                <flux:table.column>Company</flux:table.column>
                <flux:table.column>Branch</flux:table.column>
                <flux:table.column>Linked Account</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Gender</flux:table.column>
                <flux:table.column>Religion</flux:table.column>
                <flux:table.column>Country</flux:table.column>
                <flux:table.column>Province</flux:table.column>
                <flux:table.column>City</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($employees as $employee)
                    <flux:table.row>
                        <flux:table.cell>{{ $employees->firstItem() + $loop->index }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$employee->name" />
                                <flux:text variant="strong" class="font-semibold">{{ $employee->name }}</flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm">{{ $employee->company?->name ?? '—' }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm">{{ $employee->branch?->name ?? '—' }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($employee->user)
                                <flux:badge color="zinc">{{ $employee->user->username }}</flux:badge>
                            @else
                                <flux:text variant="muted">—</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $employee->phonenumber ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $employee->gender ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $employee->religion ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $employee->country?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $employee->province?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $employee->city?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($employee->is_active)
                                <flux:badge color="emerald">Active</flux:badge>
                            @else
                                <flux:badge color="red">Inactive</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @canany(['employees.edit', 'employees.delete'])
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                <flux:menu>
                                    @can('employees.edit')
                                    <flux:menu.item icon="pencil" wire:navigate href="{{ route('employees.edit', $employee->id) }}">Edit</flux:menu.item>
                                    @endcan
                                    @can('employees.delete')
                                    <flux:menu.item icon="trash" variant="danger" wire:click="deleteEmployee({{ $employee->id }})" wire:confirm="Are you sure you want to delete this employee?">Delete</flux:menu.item>
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
