<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Province;
use App\Models\User;
use Livewire\Component;

new class extends Component {
    public Employee $employee;

    // Identity
    public string $name = '';
    public ?string $employee_number = null;
    public ?int $user_id = null;
    public ?string $gender = null;
    public ?string $phonenumber = null;
    public ?string $religion = null;
    public ?string $birth_place = null;
    public ?string $birth_date = null;
    public ?string $marital_status = null;
    public ?string $address = null;

    // Location
    public ?int $country_id = null;
    public ?int $province_id = null;
    public ?int $city_id = null;

    // Employment
    public ?int $company_id = null;
    public ?int $branch_id = null;
    public bool $is_active = true;
    public ?string $employee_type = null;
    public ?string $join_date = null;
    public ?string $end_date = null;

    public array $countries = [];
    public array $provinces = [];
    public array $cities = [];
    public array $availableUsers = [];
    public array $companies = [];
    public array $branches = [];

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
        $this->name = $employee->name;
        $this->employee_number = $employee->employee_number;
        $this->user_id = $employee->user_id;
        $this->gender = $employee->gender;
        $this->phonenumber = $employee->phonenumber;
        $this->religion = $employee->religion;
        $this->birth_place = $employee->birth_place;
        $this->birth_date = $employee->birth_date?->format('Y-m-d');
        $this->marital_status = $employee->marital_status;
        $this->address = $employee->address;
        $this->company_id = $employee->company_id;
        $this->branch_id = $employee->branch_id;
        $this->country_id = $employee->country_id;
        $this->province_id = $employee->province_id;
        $this->city_id = $employee->city_id;
        $this->is_active = (bool) $employee->is_active;
        $this->employee_type = $employee->employee_type;
        $this->join_date = $employee->join_date?->format('Y-m-d');
        $this->end_date = $employee->end_date?->format('Y-m-d');

        $this->companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
        $this->branches = $this->company_id ? Branch::where('company_id', $this->company_id)->where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->countries = Country::orderBy('name')->get()->toArray();
        $this->provinces = $this->country_id ? Province::where('country_id', $this->country_id)->orderBy('name')->get()->toArray() : [];
        $this->cities = $this->province_id ? City::where('province_id', $this->province_id)->orderBy('name')->get()->toArray() : [];
        $this->availableUsers = User::query()
            ->where(function ($q) {
                $q->whereDoesntHave('employee')
                  ->orWhere('id', $this->employee->user_id);
            })
            ->orderBy('username')
            ->get(['id', 'username', 'email'])
            ->toArray();
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:employees,employee_number,' . $this->employee->id],
            'user_id' => ['nullable', 'exists:users,id', 'unique:employees,user_id,' . $this->employee->id],
            'gender' => ['nullable', 'in:male,female'],
            'phonenumber' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'address' => ['nullable', 'string', 'max:500'],
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'is_active' => ['boolean'],
            'employee_type' => ['nullable', 'in:permanent,contract,intern,parttime'],
            'join_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:join_date'],
        ];
    }

    public function save()
    {
        $this->validate();

        $this->employee->update([
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'employee_number' => $this->employee_number,
            'name' => $this->name,
            'gender' => $this->gender,
            'phonenumber' => $this->phonenumber,
            'religion' => $this->religion,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'marital_status' => $this->marital_status,
            'address' => $this->address,
            'country_id' => $this->country_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'is_active' => $this->is_active,
            'employee_type' => $this->employee_type,
            'join_date' => $this->join_date,
            'end_date' => $this->end_date,
        ]);

        session()->flash('success', 'Employee updated successfully.');
        $this->redirectRoute('employees.index', navigate: true);
    }
};
?>

<div class="flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('employees.index') }}" />
        <div class="flex flex-col">
            <flux:heading size="xl">Edit Employee</flux:heading>
            <flux:text variant="muted">Update employee profile and linked account.</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Identity --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Identity</flux:heading>
                    <flux:text variant="muted" size="sm">Personal information.</flux:text>
                </div>
                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="name" label="Full Name" placeholder="Enter full name" />
                    <flux:input wire:model="employee_number" label="Employee Number" placeholder="e.g. EMP-001" />
                </div>

                <flux:select wire:model="user_id" variant="listbox" label="Linked User Account" searchable clearable placeholder="Choose user (optional)">
                    @foreach ($availableUsers as $u)
                        <flux:select.option value="{{ $u['id'] }}">{{ $u['username'] }} — {{ $u['email'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="phonenumber" type="tel" label="Phone Number" placeholder="+62 ..." icon="phone" />
                    <flux:select wire:model="gender" variant="listbox" label="Gender" clearable placeholder="Choose gender">
                        <flux:select.option value="male">Male</flux:select.option>
                        <flux:select.option value="female">Female</flux:select.option>
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:select wire:model="religion" variant="listbox" label="Religion" searchable clearable placeholder="Choose religion">
                        <flux:select.option value="islam">Islam</flux:select.option>
                        <flux:select.option value="kristen">Christian</flux:select.option>
                        <flux:select.option value="hindu">Hindu</flux:select.option>
                        <flux:select.option value="buddhist">Buddhist</flux:select.option>
                        <flux:select.option value="other">Other</flux:select.option>
                    </flux:select>
                    <flux:select wire:model="marital_status" variant="listbox" label="Marital Status" clearable placeholder="Choose status">
                        <flux:select.option value="single">Single</flux:select.option>
                        <flux:select.option value="married">Married</flux:select.option>
                        <flux:select.option value="divorced">Divorced</flux:select.option>
                        <flux:select.option value="widowed">Widowed</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="birth_place" label="Birth Place" placeholder="City of birth" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="birth_date" type="date" label="Birth Date" />
                    <div></div>
                </div>

                <flux:textarea wire:model="address" label="Address" placeholder="Street, building, etc." rows="2" />
            </flux:card>

            {{-- Location --}}
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Location</flux:heading>
                    <flux:text variant="muted" size="sm">Domicile region.</flux:text>
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
            </flux:card>

        </div>

        {{-- Right column --}}
        <div class="flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Employment</flux:heading>
                    <flux:text variant="muted" size="sm">Company, contract & status.</flux:text>
                </div>
                <flux:separator />

                <flux:select wire:model.live="company_id" variant="listbox" label="Company" searchable :placeholder="__('Choose company')" required>
                    @foreach ($companies as $company)
                        <flux:select.option value="{{ $company['id'] }}">{{ $company['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="branch_id" variant="listbox" label="Branch" searchable clearable :disabled="!$company_id" :placeholder="!$company_id ? 'Select company first' : 'Choose branch (optional)'">
                    @foreach ($branches as $branch)
                        <flux:select.option value="{{ $branch['id'] }}">{{ $branch['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:separator />

                <flux:select wire:model="employee_type" variant="listbox" label="Employment Type" clearable placeholder="Choose type">
                    <flux:select.option value="permanent">Permanent</flux:select.option>
                    <flux:select.option value="contract">Contract</flux:select.option>
                    <flux:select.option value="intern">Intern</flux:select.option>
                    <flux:select.option value="parttime">Part-time</flux:select.option>
                </flux:select>

                <flux:input wire:model="join_date" type="date" label="Join Date" />
                <flux:input wire:model="end_date" type="date" label="End Date" description="Leave blank for permanent" />

                <flux:separator />
                <flux:checkbox wire:model="is_active" label="Active" description="Active employment status" />
            </flux:card>

            <flux:card class="space-y-3">
                <flux:button variant="primary" type="submit" class="w-full" icon="check">Update Employee</flux:button>
                <flux:button wire:navigate href="{{ route('employees.index') }}" variant="ghost" class="w-full">Cancel</flux:button>
            </flux:card>
        </div>
    </form>
</div>
