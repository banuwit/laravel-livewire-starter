<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Province;
use App\Models\User;
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

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function render()
    {
        $query = User::query()
            ->with(['employee.company', 'employee.branch', 'employee.country', 'employee.province', 'employee.city'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhereHas('employee', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->when($this->gender, fn ($q) => $q->whereHas('employee', fn ($q) => $q->where('gender', $this->gender)))
            ->when($this->religion, fn ($q) => $q->whereHas('employee', fn ($q) => $q->whereIn('religion', $this->religion)))
            ->when($this->company_id, fn ($q) => $q->whereHas('employee', fn ($q) => $q->where('company_id', $this->company_id)))
            ->when($this->branch_id, fn ($q) => $q->whereHas('employee', fn ($q) => $q->where('branch_id', $this->branch_id)))
            ->when($this->country_id, fn ($q) => $q->whereHas('employee', fn ($q) => $q->whereIn('country_id', $this->country_id)))
            ->when($this->province_id, fn ($q) => $q->whereHas('employee', fn ($q) => $q->whereIn('province_id', $this->province_id)))
            ->when($this->city_id, fn ($q) => $q->whereHas('employee', fn ($q) => $q->whereIn('city_id', $this->city_id)));

        if ($this->sortField === 'name') {
            $query->orderBy(Employee::select('name')->whereColumn('user_id', 'users.id'), $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $users = $query->paginate(10);
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
    <div class="flex flex-col">
        <flux:card class="space-y-4" size="sm">
            <flux:accordion>
                <div class="flex items-center gap-4">
                    <flux:accordion.trigger>
                        <flux:button square icon="funnel" icon:variant="outline" :variant="$company_id || $branch_id || $gender || $religion || $country_id || $province_id || $city_id ? 'primary' : 'outline'" />
                    </flux:accordion.trigger>
                    <div class="w-72">
                        <flux:input icon="magnifying-glass" placeholder="Search username or name..." wire:model.live.debounce.300ms="search" clearable />
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
                    <flux:table.column>Religion</flux:table.column>
                    <flux:table.column>Country</flux:table.column>
                    <flux:table.column>Province</flux:table.column>
                    <flux:table.column>City</flux:table.column>
                    <flux:table.column>
                        <flux:table.sortable :sorted="$sortField === 'is_active'" :direction="$sortField === 'is_active' ? $sortDirection : null" wire:click="sortBy('is_active')">
                            Status
                        </flux:table.sortable>
                    </flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows wire:loading.class.remove="hidden" class="hidden">
                    @for ($i = 0; $i < 10; $i++)
                        <flux:table.row>
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900">
                                <flux:skeleton class="w-4 h-4" animate="pulse" />
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:skeleton class="size-9 rounded-full" animate="pulse" />
                                    <div class="flex flex-col gap-1.5">
                                        <flux:skeleton class="w-28 h-3.5" animate="pulse" />
                                        <flux:skeleton class="w-36 h-3" animate="pulse" />
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-28 h-3.5" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-3.5" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-16 h-3.5" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-14 h-5 rounded-full" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-16 h-5 rounded-full" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-3.5" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-3.5" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-16 h-3.5" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-14 h-5 rounded-full" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="size-7 rounded-md" animate="pulse" /></flux:table.cell>
                        </flux:table.row>
                    @endfor
                </flux:table.rows>

                <flux:table.rows wire:loading.remove>
                    @foreach ($users as $user)
                        <flux:table.row>
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900 text-zinc-400 text-xs">
                                {{ $users->firstItem() + $loop->index }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar circle :name="$user->employee?->name ?? $user->username" />
                                    <div class="flex flex-col">
                                        <flux:text variant="strong" class="font-semibold text-sm">{{ $user->employee?->name ?? '—' }}</flux:text>
                                        <flux:text size="xs" variant="muted">{{ $user->email }}</flux:text>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="{{ $user->employee?->phonenumber ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                    {{ $user->employee?->phonenumber ?? '—' }}
                                </flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="{{ $user->employee?->company ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                    {{ $user->employee?->company?->name ?? '—' }}
                                </flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="{{ $user->employee?->branch ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                    {{ $user->employee?->branch?->name ?? '—' }}
                                </flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($user->employee?->gender === 'male')
                                    <flux:badge color="sky" size="sm">Male</flux:badge>
                                @elseif ($user->employee?->gender === 'female')
                                    <flux:badge color="pink" size="sm">Female</flux:badge>
                                @else
                                    <flux:text size="sm" class="text-zinc-300 dark:text-zinc-600">—</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $religionColor = match($user->employee?->religion) {
                                        'islam'    => 'lime',
                                        'kristen'  => 'blue',
                                        'hindu'    => 'orange',
                                        'buddhist' => 'yellow',
                                        'other'    => 'zinc',
                                        default    => null,
                                    };
                                    $religionLabel = match($user->employee?->religion) {
                                        'islam'    => 'Islam',
                                        'kristen'  => 'Christian',
                                        'hindu'    => 'Hindu',
                                        'buddhist' => 'Buddhist',
                                        'other'    => 'Other',
                                        default    => null,
                                    };
                                @endphp
                                @if ($religionColor)
                                    <flux:badge :color="$religionColor" size="sm">{{ $religionLabel }}</flux:badge>
                                @else
                                    <flux:text size="sm" class="text-zinc-300 dark:text-zinc-600">—</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="{{ $user->employee?->country ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                    {{ $user->employee?->country?->name ?? '—' }}
                                </flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="{{ $user->employee?->province ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                    {{ $user->employee?->province?->name ?? '—' }}
                                </flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="{{ $user->employee?->city ? '' : 'text-zinc-300 dark:text-zinc-600' }}">
                                    {{ $user->employee?->city?->name ?? '—' }}
                                </flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($user->is_active)
                                    <flux:badge color="emerald" size="sm">Active</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Inactive</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @canany(['users.edit', 'users.assign_roles', 'users.delete'])
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
</div>
