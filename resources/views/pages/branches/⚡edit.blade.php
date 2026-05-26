<?php

use App\Models\Branch;
use App\Models\Company;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public Branch $branch;

    public ?int $company_id = null;
    public string $name = '';
    public string $type = 'branch';
    public ?string $code = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $address = null;
    public bool $is_active = true;

    public array $companies = [];

    public function mount(Branch $branch): void
    {
        $this->branch = $branch;
        $this->company_id = $branch->company_id;
        $this->name = $branch->name;
        $this->type = $branch->type;
        $this->code = $branch->code;
        $this->phone = $branch->phone;
        $this->email = $branch->email;
        $this->address = $branch->address;
        $this->is_active = (bool) $branch->is_active;

        $this->companies = Company::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:headquarter,branch'],
            'code' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->branch->update([
            'company_id' => $this->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'code' => $this->code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        Flux::toast(variant: 'success', text: 'Branch updated successfully.');
        $this->redirectRoute('branches.index', navigate: true);
    }
};
?>
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('branches.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Edit Branch</flux:heading>
                <flux:text variant="muted">{{ $branch->name }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('branches.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Save Branch</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Branch Info</flux:heading>
                    <flux:text variant="muted" size="sm">Branch identity and contact.</flux:text>
                </div>
                <flux:separator />

                <flux:select wire:model="company_id" variant="listbox" label="Company" searchable :placeholder="__('Choose company')" required>
                    @foreach ($companies as $company)
                        <flux:select.option value="{{ $company['id'] }}">{{ $company['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="type" variant="listbox" label="Type" required>
                    <flux:select.option value="headquarter">Headquarter</flux:select.option>
                    <flux:select.option value="branch">Branch</flux:select.option>
                </flux:select>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="name" label="Branch Name" placeholder="Head Office" />
                    <flux:input wire:model="code" label="Branch Code" placeholder="e.g. HO" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="phone" type="tel" label="Phone" placeholder="(021) 000-0000" icon="phone" />
                    <flux:input wire:model="email" type="email" label="Email" placeholder="branch@company.com" />
                </div>

                <flux:textarea wire:model="address" label="Address" placeholder="Full address..." rows="3" />
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Status</flux:heading>
                    <flux:text variant="muted" size="sm">Operational state.</flux:text>
                </div>
                <flux:separator />
                <flux:checkbox wire:model="is_active" label="Active" description="Branch is operational" />
            </flux:card>

        </div>
    </form>
</div>
