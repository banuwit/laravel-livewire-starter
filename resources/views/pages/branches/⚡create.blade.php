<?php

use App\Models\Branch;
use App\Models\Company;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|exists:companies,id')]
    public ?int $company_id = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:headquarter,branch')]
    public string $type = 'branch';

    #[Validate('nullable|string|max:50')]
    public ?string $code = null;

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    public array $companies = [];

    public function mount(): void
    {
        $this->companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function save(): void
    {
        $this->validate();

        Branch::create([
            'company_id' => $this->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'code' => $this->code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        Flux::toast(variant: 'success', text: 'Branch created successfully.');
        $this->redirectRoute('branches.index', navigate: true);
    }
};
?>
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('branches.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Create Branch</flux:heading>
                <flux:text variant="muted">Add a new branch under a company.</flux:text>
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
