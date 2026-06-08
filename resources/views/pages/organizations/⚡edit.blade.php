<?php

use App\Models\Organization;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public Organization $organization;

    public string $name = '';
    public ?string $code = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $address = null;
    public bool $is_active = true;

    public function mount(Organization $organization): void
    {
        $this->organization = $organization;
        $this->name = $organization->name;
        $this->code = $organization->code;
        $this->phone = $organization->phone;
        $this->email = $organization->email;
        $this->address = $organization->address;
        $this->is_active = (bool) $organization->is_active;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:organizations,code,' . $this->organization->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->organization->update([
            'name' => $this->name,
            'code' => $this->code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        Flux::toast(variant: 'success', text: 'Organization updated successfully.');
        $this->redirectRoute('organizations.index', navigate: true);
    }
};
?>
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('organizations.index') }}" />
            <div class="flex flex-col">
                <flux:heading size="xl">Edit Organization</flux:heading>
                <flux:text variant="muted">{{ $organization->name }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:navigate href="{{ route('organizations.index') }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="save-form" variant="primary" icon="check">Save Organization</flux:button>
        </div>
    </div>

    <form id="save-form" wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Organization Info</flux:heading>
                    <flux:text variant="muted" size="sm">Basic organization details.</flux:text>
                </div>
                <flux:separator />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="name" label="Organization Name" placeholder="PT Example Indonesia" />
                    <flux:input wire:model="code" label="Organization Code" placeholder="e.g. EXM" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="phone" type="tel" label="Phone" placeholder="(021) 000-0000" icon="phone" />
                    <flux:input wire:model="email" type="email" label="Email" placeholder="info@organization.com" />
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
                <flux:checkbox wire:model="is_active" label="Active" description="Organization is operational" />
            </flux:card>

        </div>
    </form>
</div>
