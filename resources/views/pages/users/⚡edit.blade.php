<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Province;
use App\Models\Parameter;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

new class extends Component {
    public User $user;

    // Account
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

    // Personal Information
    public string $name = '';
    public ?string $phonenumber = null;
    public ?string $gender = null;
    public ?string $birth_date = null;

    // Contact & Address
    public ?string $address = null;
    public ?int $country_id = null;
    public ?int $province_id = null;
    public ?int $city_id = null;

    // Identity & Legal
    public ?string $identity_number = null;
    public ?int $religion_id = null;
    public ?int $marital_status_id = null;

    // Organization
    public ?int $company_id = null;
    public ?int $branch_id = null;

    // Role
    public ?int $selectedRole = null;

    public array $companies = [];
    public array $branches = [];
    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];
    public array $religions = [];
    public array $maritalStatuses = [];
    public array $allRoles = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->email = $user->email;
        $this->is_active = (bool) $user->is_active;
        $this->name = $user->name ?? '';
        $this->phonenumber = $user->phonenumber;
        $this->gender = $user->gender;
        $this->company_id = $user->company_id;
        $this->branch_id = $user->branch_id;

        $profile = $user->profile;
        $this->identity_number = $profile?->identity_number;
        $this->religion_id = $profile?->religion_id;
        $this->birth_date = $profile?->birth_date?->format('Y-m-d');
        $this->marital_status_id = $profile?->marital_status_id;
        $this->address = $profile?->address;
        $this->country_id = $profile?->country_id;
        $this->province_id = $profile?->province_id;
        $this->city_id = $profile?->city_id;

        $this->companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
        $this->branches = $this->company_id ? Branch::where('company_id', $this->company_id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->provinces = $this->country_id ? Province::where('country_id', $this->country_id)->orderBy('name')->get()->toArray() : [];
        $this->cities = $this->province_id ? City::where('province_id', $this->province_id)->orderBy('name')->get()->toArray() : [];
        $this->religions = Parameter::group('religion')->active()->orderBy('sort_order')->get(['id', 'value as name'])->toArray();
        $this->maritalStatuses = Parameter::group('marital_status')->active()->orderBy('sort_order')->get(['id', 'value as name'])->toArray();
        $this->allRoles = Role::whereNotIn('name', ['superadmin'])->select('id', 'name')->get()->toArray();
        $this->selectedRole = $user->roles->first()?->id;
    }

    public function updatedCompanyId($value): void
    {
        $this->branch_id = null;
        $this->branches = $value ? Branch::where('company_id', $value)->where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray() : [];
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

    public function rules()
    {
        $profileId = $this->user->profile?->id;
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'name' => ['required', 'string', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'identity_number' => ['nullable', 'string', 'max:50', 'unique:profiles,identity_number,' . $profileId],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'religion_id' => ['nullable', 'exists:parameters,id'],
            'marital_status_id' => ['nullable', 'exists:parameters,id'],
            'selectedRole' => ['nullable', 'exists:roles,id'],
        ];
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $userData = [
                'email' => $this->email,
                'is_active' => $this->is_active,
                'name' => $this->name,
                'phonenumber' => $this->phonenumber,
                'gender' => $this->gender,
                'company_id' => $this->company_id,
                'branch_id' => $this->branch_id,
            ];
            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }
            $this->user->update($userData);

            $hasProfileData = $this->identity_number || $this->birth_date || $this->marital_status_id
                || $this->religion_id || $this->address;

            if ($hasProfileData) {
                $this->user->profile()->updateOrCreate(
                    ['user_id' => $this->user->id],
                    [
                        'identity_number' => $this->identity_number,
                        'religion_id' => $this->religion_id,
                        'birth_date' => $this->birth_date,
                        'marital_status_id' => $this->marital_status_id,
                        'address' => $this->address,
                        'country_id' => $this->country_id,
                        'province_id' => $this->province_id,
                        'city_id' => $this->city_id,
                    ]
                );
            }

            if ($this->selectedRole) {
                $role = Role::find($this->selectedRole);
                if ($role) {
                    $this->user->syncRoles([$role->name]);
                }
            } else {
                $this->user->syncRoles([]);
            }
        });

        Flux::toast(variant: 'success', text: 'User updated successfully.');
        $this->redirectRoute('users.index', navigate: true);
    }
};
?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('users.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Edit User</flux:heading>
                <flux:text variant="muted">{{ $user->displayName() }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('users.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Save Changes</flux:button>
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
                <flux:input wire:model="password" type="password" label="New Password" description="Leave blank to keep current" viewable />
                <flux:input wire:model="password_confirmation" type="password" label="Confirm New Password" viewable />
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
                @if ($user->profile)
                    <flux:badge color="emerald" size="sm" class="shrink-0">Linked</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm" class="shrink-0">Optional</flux:badge>
                @endif
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
                <flux:text variant="muted" size="sm">Company & branch assignment.</flux:text>
            </div>
            <flux:separator />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:select wire:model.live="company_id" variant="listbox" label="Company" searchable clearable placeholder="Choose company">
                    @foreach ($companies as $company)
                        <flux:select.option value="{{ $company['id'] }}">{{ $company['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="branch_id" variant="listbox" label="Branch" searchable clearable :disabled="!$company_id" :placeholder="!$company_id ? 'Select company first' : 'Choose branch'">
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
