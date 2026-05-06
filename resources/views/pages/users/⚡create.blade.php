<?php

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    #[Validate('nullable|string')]
    public ?string $gender = null;

    #[Validate('nullable|string')]
    public ?string $phonenumber = null;

    #[Validate('nullable|string')]
    public ?string $religion = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    #[Validate('nullable|exists:countries,id')]
    public ?int $country_id = null;

    #[Validate('nullable|exists:provinces,id')]
    public ?int $province_id = null;

    #[Validate('nullable|exists:cities,id')]
    public ?int $city_id = null;

    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('nullable|exists:roles,id')]
    public ?int $selectedRole = null;

    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];
    public array $allRoles = [];

    public function mount()
    {
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->allRoles = Role::whereNotIn('name', ['superadmin'])->select('id', 'name')->get()->toArray();
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

        $newUser = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'gender' => $this->gender,
            'phonenumber' => $this->phonenumber,
            'religion' => $this->religion,
            'is_active' => $this->is_active,
            'address' => $this->address,
            'country_id' => $this->country_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
        ]);

        if ($this->selectedRole) {
            $role = Role::find($this->selectedRole);
            if ($role) {
                $newUser->assignRole($role);
            }
        }

        session()->flash('success', 'User created successfully.');

        $this->redirectRoute('users.index', navigate: true);
    }
};
?>

<div class="flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('users.index') }}" />
        <div class="flex flex-col">
            <flux:heading size="xl">Create User</flux:heading>
            <flux:text variant="muted">Add a new user account with profile and role.</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            {{-- Account --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Account</flux:heading>
                    <flux:text variant="muted" size="sm">Login credentials and identity.</flux:text>
                </div>
                <flux:separator />

                <flux:input wire:model="name" label="Full Name" placeholder="Enter full name" />
                <flux:input wire:model="email" type="email" label="Email Address" placeholder="name@example.com" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="password" type="password" label="Password" viewable />
                    <flux:input wire:model="password_confirmation" type="password" label="Confirm Password" viewable />
                </div>
            </flux:card>

            {{-- Personal Info --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Personal Information</flux:heading>
                    <flux:text variant="muted" size="sm">Optional profile details.</flux:text>
                </div>
                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:select wire:model="gender" variant="listbox" label="Gender" clearable placeholder="Choose gender">
                        <flux:select.option value="male">Male</flux:select.option>
                        <flux:select.option value="female">Female</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="religion" variant="listbox" label="Religion" searchable clearable placeholder="Choose religion">
                        <flux:select.option value="islam">Islam</flux:select.option>
                        <flux:select.option value="kristen">Christian</flux:select.option>
                        <flux:select.option value="hindu">Hindu</flux:select.option>
                        <flux:select.option value="buddhist">Buddhist</flux:select.option>
                        <flux:select.option value="other">Other</flux:select.option>
                    </flux:select>
                </div>

                <flux:input wire:model="phonenumber" type="tel" label="Phone Number" placeholder="+62 ..." icon="phone" />
            </flux:card>

            {{-- Address --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Address</flux:heading>
                    <flux:text variant="muted" size="sm">Country, region and street.</flux:text>
                </div>
                <flux:separator />

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

                <flux:textarea wire:model="address" label="Street Address" placeholder="Street, building, etc." rows="2" />
            </flux:card>
        </div>

        {{-- Right column: Status + Role --}}
        <div class="flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Status</flux:heading>
                    <flux:text variant="muted" size="sm">Account access state.</flux:text>
                </div>
                <flux:separator />

                <flux:checkbox wire:model="is_active" label="Active" description="Allow user to login and access the system" />
            </flux:card>

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

            <flux:card class="space-y-3">
                <flux:button variant="primary" type="submit" class="w-full" icon="check">Save User</flux:button>
                <flux:button wire:navigate href="{{ route('users.index') }}" variant="ghost" class="w-full">Cancel</flux:button>
            </flux:card>
        </div>
    </form>
</div>
