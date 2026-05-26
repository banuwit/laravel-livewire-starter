<?php

use App\Models\Parameter;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $activeGroup = 'gender';
    public string $search = '';
    public string $sortField = 'sort_order';
    public string $sortDirection = 'asc';

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public string $deletingLabel = '';

    // Form fields
    public string $code = '';
    public string $value = '';
    public ?string $description = null;
    public ?string $text_color = null;
    public ?string $bg_color = null;
    public bool $is_system = false;
    public bool $is_active = true;
    public int $sort_order = 0;

    public array $groups = [
        'gender'         => 'Gender',
        'religion'       => 'Religion',
        'marital_status' => 'Marital Status',
    ];

    public function updatedActiveGroup(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sortField === $field
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : [$this->sortField = $field, $this->sortDirection = 'asc'];
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEdit(string $id): void
    {
        $p = Parameter::findOrFail($id);
        $this->editingId   = $id;
        $this->code        = $p->code;
        $this->value       = $p->value;
        $this->description = $p->description;
        $this->text_color  = $p->text_color;
        $this->bg_color    = $p->bg_color;
        $this->is_system   = $p->is_system;
        $this->is_active   = $p->is_active;
        $this->sort_order  = $p->sort_order;
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $uniqueRule = $this->editingId
            ? 'unique:parameters,code,' . $this->editingId
            : 'unique:parameters,code';

        $this->validate([
            'code'        => ['required', 'string', 'max:100', 'alpha_dash', $uniqueRule],
            'value'       => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'text_color'  => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bg_color'    => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order'  => ['required', 'integer', 'min:0'],
        ]);

        $data = [
            'group'       => $this->activeGroup,
            'code'        => $this->code,
            'value'       => $this->value,
            'description' => $this->description ?: null,
            'text_color'  => $this->text_color ?: null,
            'bg_color'    => $this->bg_color ?: null,
            'is_active'   => $this->is_active,
            'sort_order'  => $this->sort_order,
        ];

        if ($this->editingId) {
            Parameter::findOrFail($this->editingId)->update($data);
            Flux::toast(variant: 'success', text: 'Parameter updated.');
        } else {
            Parameter::create(array_merge($data, ['id' => (string) Str::uuid()]));
            Flux::toast(variant: 'success', text: 'Parameter created.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function confirmDelete(string $id): void
    {
        $p = Parameter::findOrFail($id);
        $this->deletingId    = $id;
        $this->deletingLabel = $p->value;
        $this->showDeleteModal = true;
    }

    public function deleteParameter(): void
    {
        if ($this->deletingId) {
            $parameter = Parameter::findOrFail($this->deletingId);
            if ($parameter->is_system) {
                Flux::toast(variant: 'danger', text: 'System parameters cannot be deleted.');
            } else {
                $parameter->delete();
                Flux::toast(variant: 'success', text: 'Parameter deleted.');
            }
        }
        $this->deletingId    = null;
        $this->deletingLabel = '';
        $this->showDeleteModal = false;
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->code        = '';
        $this->value       = '';
        $this->description = null;
        $this->text_color  = null;
        $this->bg_color    = null;
        $this->is_system   = false;
        $this->is_active   = true;
        $this->sort_order  = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $parameters = Parameter::query()
            ->where('group', $this->activeGroup)
            ->when($this->search, fn ($q) => $q
                ->where('value', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return $this->view(['parameters' => $parameters]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Parameters</flux:heading>
        @can('parameters.create')
            <flux:button wire:click="openCreate" variant="primary" icon="plus">Add New</flux:button>
        @endcan
    </div>

    <flux:card size="sm" class="space-y-4">

        {{-- Group Tabs --}}
        <flux:tabs wire:model.live="activeGroup">
            @foreach ($groups as $key => $label)
                <flux:tab name="{{ $key }}">{{ $label }}</flux:tab>
            @endforeach
        </flux:tabs>

        {{-- Search --}}
        <div class="w-72">
            <flux:input
                icon="magnifying-glass"
                placeholder="Search code or value..."
                wire:model.live.debounce.300ms="search"
                clearable
            />
        </div>

        {{-- Table --}}
        <flux:table :paginate="$parameters" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'code'" :direction="$sortField === 'code' ? $sortDirection : null" wire:click="sortBy('code')">Code</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'value'" :direction="$sortField === 'value' ? $sortDirection : null" wire:click="sortBy('value')">Value</flux:table.column>
                <flux:table.column>Badge</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'sort_order'" :direction="$sortField === 'sort_order' ? $sortDirection : null" wire:click="sortBy('sort_order')">Order</flux:table.column>
                <flux:table.column>System</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($parameters as $param)
                    <flux:table.row wire:key="param-{{ $param->id }}">
                        <flux:table.cell class="text-zinc-400 text-xs">{{ $parameters->firstItem() + $loop->index }}</flux:table.cell>

                        <flux:table.cell>
                            <code class="text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded font-mono">{{ $param->code }}</code>
                        </flux:table.cell>

                        <flux:table.cell variant="strong">{{ $param->value }}</flux:table.cell>

                        <flux:table.cell>
                            @if ($param->bg_color && $param->text_color)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    style="background-color:{{ $param->bg_color }};color:{{ $param->text_color }}"
                                >{{ $param->value }}</span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500 text-xs">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-center">{{ $param->sort_order }}</flux:table.cell>

                        <flux:table.cell>
                            @if ($param->is_system)
                                <flux:badge color="amber" size="sm">System</flux:badge>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500 text-xs">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($param->is_active)
                                <flux:badge color="emerald" size="sm">Active</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">Inactive</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @canany(['parameters.edit', 'parameters.delete'])
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                    <flux:menu>
                                        @can('parameters.edit')
                                            <flux:menu.item icon="pencil" wire:click="openEdit('{{ $param->id }}')">Edit</flux:menu.item>
                                        @endcan
                                        @can('parameters.delete')
                                            @unless ($param->is_system)
                                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete('{{ $param->id }}')">Delete</flux:menu.item>
                                            @endunless
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            @endcanany
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-1 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.adjustments-horizontal class="size-8 opacity-40" />
                                <flux:text>No parameters found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- ─── Form Modal (Create / Edit) ─────────────────────────────── --}}
    <flux:modal wire:model="showFormModal" flyout variant="floating">
        <form wire:submit="save" class="flex flex-col h-full gap-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit Parameter' : 'New Parameter' }}</flux:heading>
                <flux:text variant="muted" class="mt-1">
                    Group: <strong>{{ $groups[$activeGroup] ?? $activeGroup }}</strong>
                </flux:text>
            </div>

            <div class="flex flex-col gap-4 flex-1">
                <flux:input
                    wire:model="code"
                    label="Code"
                    placeholder="e.g. male, islam, single"
                    description="Unique identifier. Use lowercase with underscores."
                    :disabled="(bool) $is_system"
                    required
                />

                <flux:input
                    wire:model="value"
                    label="Value"
                    placeholder="e.g. Male, Islam, Single"
                    required
                />

                <flux:textarea
                    wire:model="description"
                    label="Description"
                    placeholder="Optional description..."
                    rows="2"
                />

                <div>
                    <flux:label>Badge Colors <span class="text-zinc-400 font-normal">(optional)</span></flux:label>
                    <flux:text variant="muted" size="sm" class="mb-2">Fill both or leave both blank.</flux:text>
                    <div class="grid grid-cols-2 gap-3">
                        <flux:input wire:model.live="text_color" label="Text Color" placeholder="#ffffff" />
                        <flux:input wire:model.live="bg_color" label="Background Color" placeholder="#16a34a" />
                    </div>
                    @if ($text_color && $bg_color)
                        <div class="mt-2 flex items-center gap-2">
                            <flux:text variant="muted" size="sm">Preview:</flux:text>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium"
                                style="background-color:{{ $bg_color }};color:{{ $text_color }}"
                            >{{ $value ?: 'Preview' }}</span>
                        </div>
                    @endif
                </div>

                <flux:input
                    type="number"
                    wire:model="sort_order"
                    label="Sort Order"
                    min="0"
                />

                <flux:checkbox
                    wire:model="is_active"
                    label="Active"
                    description="Parameter is available for selection"
                />
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ $editingId ? 'Update' : 'Create' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ─── Delete Confirmation Modal ────────────────────────────────── --}}
    <flux:modal wire:model="showDeleteModal" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete parameter?</flux:heading>
                <flux:text class="mt-2">
                    You're about to delete <strong>{{ $deletingLabel }}</strong>. This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteParameter" variant="danger" icon="trash">Delete parameter</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
