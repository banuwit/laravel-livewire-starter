<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Organization;
use App\Models\Country;
use App\Models\Province;
use App\Models\Parameter;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    // Account
    #[Validate('required|string|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    #[Validate('boolean')]
    public bool $is_active = true;

    // Personal Information
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:50')]
    public ?string $phonenumber = null;

    #[Validate('nullable|in:male,female')]
    public ?string $gender = null;

    #[Validate('nullable|date')]
    public ?string $birth_date = null;

    // Contact & Address
    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('nullable|exists:countries,id')]
    public ?int $country_id = null;

    #[Validate('nullable|exists:provinces,id')]
    public ?int $province_id = null;

    #[Validate('nullable|exists:cities,id')]
    public ?int $city_id = null;

    // Identity & Legal
    #[Validate('nullable|string|max:50|unique:profiles,identity_number')]
    public ?string $identity_number = null;

    #[Validate('nullable|exists:parameters,id')]
    public ?int $religion_id = null;

    #[Validate('nullable|exists:parameters,id')]
    public ?int $marital_status_id = null;

    // Organization
    #[Validate('nullable|exists:organizations,id')]
    public ?int $organization_id = null;

    #[Validate('nullable|exists:branches,id')]
    public ?int $branch_id = null;

    // Role
    #[Validate('nullable|exists:roles,id')]
    public ?int $selectedRole = null;

    public array $organizations = [];
    public array $branches = [];
    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];
    public array $religions = [];
    public array $maritalStatuses = [];
    public array $allRoles = [];

    public function mount()
    {
        $this->organizations = Organization::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->religions = Parameter::group('religion')->active()->orderBy('sort_order')->get(['id', 'value as name'])->toArray();
        $this->maritalStatuses = Parameter::group('marital_status')->active()->orderBy('sort_order')->get(['id', 'value as name'])->toArray();
        $this->allRoles = Role::whereNotIn('name', ['superadmin'])->select('id', 'name')->get()->toArray();
    }

    public function updatedOrganizationId($value): void
    {
        $this->branch_id = null;
        $this->branches = $value ? Branch::where('organization_id', $value)->where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray() : [];
    }

    public function updatedCountryId($value): void
    {
        $this->province_id = null;
        $this->city_id = null;
        $this->provinces = $value ? Province::where('country_id', $value)->orderBy('name')->get()->toArray() : [];
        $this->cities = [];
    }

    public function updatedProvinceId($value): void
    {
        $this->city_id = null;
        $this->cities = $value ? City::where('province_id', $value)->orderBy('name')->get()->toArray() : [];
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $newUser = User::create([
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'is_active' => $this->is_active,
                'name' => $this->name,
                'phonenumber' => $this->phonenumber,
                'gender' => $this->gender,
                'organization_id' => $this->organization_id,
                'branch_id' => $this->branch_id,
            ]);

            $hasProfileData = $this->identity_number || $this->birth_date || $this->marital_status_id
                || $this->religion_id || $this->address;

            if ($hasProfileData) {
                $newUser->profile()->create([
                    'identity_number' => $this->identity_number,
                    'religion_id' => $this->religion_id,
                    'birth_date' => $this->birth_date,
                    'marital_status_id' => $this->marital_status_id,
                    'address' => $this->address,
                    'country_id' => $this->country_id,
                    'province_id' => $this->province_id,
                    'city_id' => $this->city_id,
                ]);
            }

            if ($this->selectedRole) {
                $role = Role::find($this->selectedRole);
                if ($role) {
                    $newUser->assignRole($role);
                }
            }
        });

        Flux::toast(variant: 'success', text: 'User created successfully.');
        $this->redirectRoute('users.index', navigate: true);
    }
};
?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('users.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Create User</flux:heading>
                <flux:text variant="muted">Add a new user to the system.</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('users.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Create User</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="save" class="space-y-6">
        {{-- Account Section --}}
        <flux:card class="space-y-5">
            <div>
                <flux:heading size="lg">Account</flux:heading>
                <flux:text variant="muted" size="sm">Login credentials.</flux:text>
            </div>
            <flux:separator />
            <flux:input wire:model="email" type="email" label="Email Address" placeholder="name@example.com" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:input wire:model="password" type="password" label="Password" viewable />
                <flux:input wire:model="password_confirmation" type="password" label="Confirm Password" viewable />
            </div>
        </flux:card>

        {{-- Personal Information Section --}}
        <flux:card class="space-y-5">
            <div>
                <flux:heading size="lg">Personal Information</flux:heading>
                <flux:text variant="muted" size="sm">Basic profile details.</flux:text>
            </div>
            <flux:separator />
            <flux:input wire:model="name" label="Full Name" placeholder="Enter full name" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <flux:input wire:model="phonenumber" type="tel" label="Phone Number" placeholder="+62 ..." />
                <flux:select wire:model="gender" variant="listbox" label="Gender" clearable placeholder="Select gender">
                    <flux:select.option value="male">Male</flux:select.option>
                    <flux:select.option value="female">Female</flux:select.option>
                </flux:select>
                <flux:date-picker label="Birth Date" :value="$birth_date" x-on:input="$wire.birth_date = $event.detail" />
            </div>
        </flux:card>

        {{-- Contact & Address Section --}}
        <flux:card class="space-y-5">
            <div>
                <flux:heading size="lg">Contact & Address</flux:heading>
                <flux:text variant="muted" size="sm">Location information.</flux:text>
            </div>
            <flux:separator />
            <flux:textarea wire:model="address" label="Address" placeholder="Street, building, etc." rows="2" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <flux:select wire:model.live="country_id" variant="listbox" label="Country" searchable clearable placeholder="Choose country">
                    @foreach ($countries as $country)
                        <flux:select.option value="{{ $country['id'] }}">{{ $country['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="province_id" variant="listbox" label="Province" searchable clearable :disabled="!$country_id" placeholder="Choose province">
                    @foreach ($provinces as $province)
                        <flux:select.option value="{{ $province['id'] }}">{{ $province['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="city_id" variant="listbox" label="City" searchable clearable :disabled="!$province_id" placeholder="Choose city">
                    @foreach ($cities as $city)
                        <flux:select.option value="{{ $city['id'] }}">{{ $city['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </flux:card>

        {{-- Identity & Legal Section --}}
        <flux:card class="space-y-5">
            <div class="flex items-center gap-3">
                <div>
                    <flux:heading size="lg">Identity & Legal</flux:heading>
                    <flux:text variant="muted" size="sm">Additional identity information.</flux:text>
                </div>
                <flux:badge color="zinc" size="sm" class="shrink-0">Optional</flux:badge>
            </div>
            <flux:separator />
            <flux:input wire:model="identity_number" label="Identity Number" placeholder="e.g. KTP / Passport number" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:select wire:model="religion_id" variant="listbox" label="Religion" searchable clearable placeholder="Select religion">
                    @foreach ($religions as $religion)
                        <flux:select.option value="{{ $religion['id'] }}">{{ $religion['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="marital_status_id" variant="listbox" label="Marital Status" clearable placeholder="Select status">
                    @foreach ($maritalStatuses as $status)
                        <flux:select.option value="{{ $status['id'] }}">{{ $status['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </flux:card>

        {{-- Organization Section --}}
        <flux:card class="space-y-5">
            <div>
                <flux:heading size="lg">Organization</flux:heading>
                <flux:text variant="muted" size="sm">Organization & branch assignment.</flux:text>
            </div>
            <flux:separator />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:select wire:model.live="organization_id" variant="listbox" label="Organization" searchable clearable placeholder="Choose organization">
                    @foreach ($organizations as $organization)
                        <flux:select.option value="{{ $organization['id'] }}">{{ $organization['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="branch_id" variant="listbox" label="Branch" searchable clearable :disabled="!$organization_id" :placeholder="!$organization_id ? 'Select organization first' : 'Choose branch'">
                    @foreach ($branches as $branch)
                        <flux:select.option value="{{ $branch['id'] }}">{{ $branch['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </flux:card>

        {{-- Status & Role Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Status --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Status</flux:heading>
                    <flux:text variant="muted" size="sm">Account state.</flux:text>
                </div>
                <flux:separator />
                <flux:checkbox wire:model="is_active" label="Account Active" description="User can login" />
            </flux:card>

            {{-- Role --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Role</flux:heading>
                    <flux:text variant="muted" size="sm">Assign one role.</flux:text>
                </div>
                <flux:separator />
                <flux:radio.group wire:model="selectedRole" variant="cards" class="!grid !grid-cols-1 gap-2">
                    @foreach ($allRoles as $role)
                        <flux:radio value="{{ $role['id'] }}" :label="ucfirst($role['name'])" />
                    @endforeach
                </flux:radio.group>
                @error('selectedRole')
                    <flux:text size="sm" class="!text-red-500">{{ $message }}</flux:text>
                @enderror
            </flux:card>
        </div>
    </form>
</div>
