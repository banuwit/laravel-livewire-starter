<?php

use App\Models\Company;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:50|unique:companies,code')]
    public ?string $code = null;

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    #[Validate('nullable|email|max:255')]
    public ?string $email = null;

    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    public function save(): void
    {
        $this->validate();

        Company::create([
            'name' => $this->name,
            'code' => $this->code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        Flux::toast(variant: 'success', text: 'Company created successfully.');
        $this->redirectRoute('companies.index', navigate: true);
    }
};
?>
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('companies.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Create Company</flux:heading>
                <flux:text variant="muted">Add a new company.</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('companies.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Save Company</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Company Info</flux:heading>
                    <flux:text variant="muted" size="sm">Basic company details.</flux:text>
                </div>
                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="name" label="Company Name" placeholder="PT Example Indonesia" />
                    <flux:input wire:model="code" label="Company Code" placeholder="e.g. EXM" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="phone" type="tel" label="Phone" placeholder="(021) 000-0000" icon="phone" />
                    <flux:input wire:model="email" type="email" label="Email" placeholder="info@company.com" />
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
                <flux:checkbox wire:model="is_active" label="Active" description="Company is operational" />
            </flux:card>

        </div>
    </form>
</div>
