<?php

use App\Models\City;
use App\Models\Country;
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
    public array $country_id = [];
    public array $province_id = [];
    public array $city_id = [];
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];

    public function mount()
    {
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->provinces = Province::orderBy('name')->get()->toArray();
        $this->cities = City::orderBy('name')->get()->toArray();
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
        if (in_array($property, ['search', 'gender', 'religion', 'country_id', 'province_id', 'city_id'])) {
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
            ->with(['country', 'province', 'city'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->gender, fn ($q) => $q->where('gender', $this->gender))
            ->when($this->religion, fn ($q) => $q->whereIn('religion', $this->religion))
            ->when($this->country_id, fn ($q) => $q->whereIn('country_id', $this->country_id))
            ->when($this->province_id, fn ($q) => $q->whereIn('province_id', $this->province_id))
            ->when($this->city_id, fn ($q) => $q->whereIn('city_id', $this->city_id))
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
    <div class="flex flex-col">
        <flux:card class="space-y-4" size="sm">
            <div class="flex items-center justify-between gap-4">
                <div class="flex gap-4">
                    <div class="w-72">
                        <flux:input icon="magnifying-glass" placeholder="Search name..." wire:model.live.debounce.300ms="search" clearable />
                    </div>
                    <div class="w-48">
                        <flux:select wire:model.live="gender" variant="listbox" indicator="radio" searchable clearable placeholder="Gender">
                            <flux:select.option value="male">Male</flux:select.option>
                            <flux:select.option value="female">Female</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model="religion" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="Religion" >
                            <flux:select.option value="islam">Islam</flux:select.option>
                            <flux:select.option value="kristen">Christian</flux:select.option>
                            <flux:select.option value="hindu">Hindu</flux:select.option>
                            <flux:select.option value="buddhist">Buddhist</flux:select.option>
                            <flux:select.option value="other">Other</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model.model="country_id" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="Country">
                            @foreach ($countries as $country)
                                <flux:select.option value="{{ $country['id'] }}">{{ $country['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model.model="province_id" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="Province" dropdownWidth="200px">
                            @foreach ($provinces as $province)
                                <flux:select.option value="{{ $province['id'] }}">{{ $province['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="w-48">
                        <flux:select wire:model.model="city_id" variant="listbox" multiple multiple-display="count" indicator="checkbox" searchable clearable apply placeholder="City" dropdownWidth="200px">
                            @foreach ($cities as $city)
                                <flux:select.option value="{{ $city['id'] }}">{{ $city['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>
            <flux:table :paginate="$users" pagination:scroll-to>
                <flux:table.columns>
                    <flux:table.column sticky>#</flux:table.column>
                    <flux:table.column>
                        <flux:table.sortable :sorted="$sortField === 'name'" :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">
                            Name
                        </flux:table.sortable>
                    </flux:table.column>
                    <flux:table.column>Gender</flux:table.column>
                    <flux:table.column>Phone</flux:table.column>
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
                                    <flux:skeleton class="size-10 rounded-full" animate="pulse" />
                                    <div class="flex flex-col gap-2">
                                        <flux:skeleton class="w-24 h-4" animate="pulse" />
                                        <flux:skeleton class="w-32 h-3" animate="pulse" />
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-12 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-24 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-16 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-16 h-6 rounded-full" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="size-8 rounded" animate="pulse" /></flux:table.cell>
                        </flux:table.row>
                    @endfor
                </flux:table.rows>

                <flux:table.rows wire:loading.remove>
                    @foreach ($users as $user)
                        <flux:table.row>
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900">{{ $users->firstItem() + $loop->index }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar :name="$user->name" />
                                    <div class="flex flex-col">
                                        <flux:text variant="strong" class="font-semibold">{{ $user->name }}</flux:text>
                                        <flux:text variant="muted">{{ $user->email }}</flux:text>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->gender ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $user->phonenumber ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $user->religion ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $user->country?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $user->province?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $user->city?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($user->is_active)
                                    <flux:badge color="emerald">Active</flux:badge>
                                @else
                                    <flux:badge color="red">Inactive</flux:badge>
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