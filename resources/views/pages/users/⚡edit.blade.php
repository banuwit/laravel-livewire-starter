<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

new class extends Component {
    public User $user;

    public string $activeTab = 'profile';

    // Account
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

    // Profile
    public string $name = '';
    public ?string $phonenumber = null;
    public ?string $gender = null;

    // Organization
    public ?int $company_id = null;
    public ?int $branch_id = null;

    // Profile data record
    public ?string $identity_number = null;
    public ?string $religion = null;
    public ?string $birth_date = null;
    public ?string $marital_status = null;
    public ?string $address = null;
    public ?int $country_id = null;
    public ?int $province_id = null;
    public ?int $city_id = null;

    public ?int $selectedRole = null;

    public array $companies = [];
    public array $branches = [];
    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];
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
        $this->religion = $profile?->religion;
        $this->birth_date = $profile?->birth_date?->format('Y-m-d');
        $this->marital_status = $profile?->marital_status;
        $this->address = $profile?->address;
        $this->country_id = $profile?->country_id;
        $this->province_id = $profile?->province_id;
        $this->city_id = $profile?->city_id;

        $this->companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
        $this->branches = $this->company_id ? Branch::where('company_id', $this->company_id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->provinces = $this->country_id ? Province::where('country_id', $this->country_id)->orderBy('name')->get()->toArray() : [];
        $this->cities = $this->province_id ? City::where('province_id', $this->province_id)->orderBy('name')->get()->toArray() : [];
        $this->allRoles = Role::whereNotIn('name', ['superadmin'])->select('id', 'name')->get()->toArray();
        $this->selectedRole = $user->roles->first()?->id;

        // Auto-open HR tab if profile data exists
        if ($user->profile) {
            $this->activeTab = 'hr';
        }
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
            'religion' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'address' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
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

            $hasHrData = $this->identity_number || $this->birth_date || $this->marital_status
                || $this->religion || $this->address;

            if ($hasHrData) {
                $this->user->profile()->updateOrCreate(
                    ['user_id' => $this->user->id],
                    [
                        'identity_number' => $this->identity_number,
                        'religion' => $this->religion,
                        'birth_date' => $this->birth_date,
                        'marital_status' => $this->marital_status,
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

    private function profileFields(): array
    {
        return ['email', 'password', 'name', 'phonenumber', 'gender'];
    }

    private function hrFields(): array
    {
        return ['identity_number', 'religion', 'birth_date', 'marital_status', 'address', 'country_id', 'province_id', 'city_id'];
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
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Save User</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- Left: Tabs --}}
        <div class="lg:col-span-2">
            <flux:tab.group default="{{ $activeTab }}">
                <flux:tabs>
                    <flux:tab name="profile">
                        Profile
                        @if ($errors->hasAny($this->profileFields()))
                            <span class="inline-flex size-2 rounded-full bg-red-500 ml-1 mb-1"></span>
                        @endif
                    </flux:tab>
                    <flux:tab name="hr">
                        Profile Data
                        @if ($user->profile)
                            <flux:badge color="emerald" size="sm" class="ml-1">Linked</flux:badge>
                        @endif
                        @if ($errors->hasAny($this->hrFields()))
                            <span class="inline-flex size-2 rounded-full bg-red-500 ml-1 mb-1"></span>
                        @endif
                    </flux:tab>
                </flux:tabs>

                {{-- Tab: Profile --}}
                <flux:tab.panel name="profile" class="flex flex-col gap-6 pt-4">
                    {{-- Account --}}
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

                    {{-- Profile --}}
                    <flux:card class="space-y-5">
                        <div>
                            <flux:heading size="lg">Profile</flux:heading>
                            <flux:text variant="muted" size="sm">Personal information.</flux:text>
                        </div>
                        <flux:separator />
                        <flux:input wire:model="name" label="Full Name" placeholder="Enter full name" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <flux:input wire:model="phonenumber" type="tel" label="Phone Number" placeholder="+62 ..." icon="phone" />
                            <flux:select wire:model="gender" variant="listbox" label="Gender" clearable placeholder="Choose gender">
                                <flux:select.option value="male">Male</flux:select.option>
                                <flux:select.option value="female">Female</flux:select.option>
                            </flux:select>
                        </div>
                    </flux:card>
                </flux:tab.panel>

                {{-- Tab: Profile Data --}}
                <flux:tab.panel name="hr" class="flex flex-col gap-6 pt-4">
                    <flux:card class="space-y-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <flux:heading size="lg">Profile Data</flux:heading>
                                <flux:text variant="muted" size="sm">
                                    @if ($user->profile)
                                        Profile record linked. Update details below.
                                    @else
                                        No profile record yet. Fill in any field to create one automatically.
                                    @endif
                                </flux:text>
                            </div>
                            @if (!$user->profile)
                                <flux:badge color="zinc" size="sm" class="shrink-0">Optional</flux:badge>
                            @endif
                        </div>
                        <flux:separator />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <flux:input wire:model="identity_number" label="Identity Number" placeholder="e.g. KTP / ID number" />
                            <flux:select wire:model="religion" variant="listbox" label="Religion" searchable clearable placeholder="Choose religion">
                                <flux:select.option value="islam">Islam</flux:select.option>
                                <flux:select.option value="kristen">Christian</flux:select.option>
                                <flux:select.option value="hindu">Hindu</flux:select.option>
                                <flux:select.option value="buddhist">Buddhist</flux:select.option>
                                <flux:select.option value="other">Other</flux:select.option>
                            </flux:select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <flux:select wire:model="marital_status" variant="listbox" label="Marital Status" clearable placeholder="Choose status">
                                <flux:select.option value="single">Single</flux:select.option>
                                <flux:select.option value="married">Married</flux:select.option>
                                <flux:select.option value="divorced">Divorced</flux:select.option>
                                <flux:select.option value="widowed">Widowed</flux:select.option>
                            </flux:select>
                            <flux:date-picker label="Birth Date" :value="$birth_date" x-on:input="$wire.birth_date = $event.detail" />
                        </div>

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
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- Right sidebar --}}
        <div class="flex flex-col gap-6">
            {{-- Organization --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Organization</flux:heading>
                    <flux:text variant="muted" size="sm">Company & branch.</flux:text>
                </div>
                <flux:separator />
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
            </flux:card>

            {{-- Status --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Status</flux:heading>
                    <flux:text variant="muted" size="sm">Account state.</flux:text>
                </div>
                <flux:separator />
                <flux:checkbox wire:model="is_active" label="Account Active" description="Allow user to login" />
            </flux:card>

            {{-- Role --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Role</flux:heading>
                    <flux:text variant="muted" size="sm">Assign one role per user.</flux:text>
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
