<?php

use App\Models\ActivityLog;
use App\Models\User;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search      = '';
    public string $logName     = '';
    public string $subjectType = '';
    public ?int   $causerId    = null;
    public string $dateFrom    = '';
    public string $dateTo      = '';
    public string $sortField     = 'created_at';
    public string $sortDirection = 'desc';

    public ?int    $viewingId = null;
    public array   $viewingChanges = [];
    public ?string $viewingDescription = null;
    public ?string $viewingEvent       = null;
    public ?string $viewingSubject     = null;
    public ?string $viewingCauser      = null;
    public ?string $viewingDate        = null;
    public array   $viewingMeta        = [];

    public array $users = [];

    public array $subjectTypes = [
        'User',
        'Organization',
        'Branch',
        'Role',
        'Permission',
    ];

    public function mount(): void
    {
        $this->users = User::orderBy('name')
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, [
            'search', 'logName', 'subjectType', 'causerId', 'dateFrom', 'dateTo',
        ])) {
            $this->resetPage();
        }
    }

    public function viewDetail(int $id): void
    {
        $log = ActivityLog::find($id);
        if (! $log) return;

        $this->viewingId          = $id;
        $this->viewingDescription = $log->description;
        $this->viewingEvent       = $log->event;
        $this->viewingSubject     = $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : '—';
        $this->viewingCauser      = $log->causer?->name ?? $log->causer?->email ?? '—';
        $this->viewingDate        = $log->created_at->format('Y-m-d H:i:s');
        $this->viewingChanges     = $log->changes;

        $props = $log->properties->toArray();
        unset($props['attributes'], $props['old']);
        $this->viewingMeta = $props;

        Flux::modal('activity-log-detail')->show();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'logName', 'subjectType', 'causerId', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::query()
            ->with(['causer'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('event', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->logName, fn ($q) => $q->where('log_name', $this->logName))
            ->when($this->subjectType, function ($q) {
                $map = [
                    'User'       => \App\Models\User::class,
                    'Organization' => \App\Models\Organization::class,
                    'Branch'     => \App\Models\Branch::class,
                    'Role'       => \App\Models\Role::class,
                    'Permission' => \App\Models\Permission::class,
                ];
                $fqn = $map[$this->subjectType] ?? $this->subjectType;
                $q->where('subject_type', $fqn);
            })
            ->when($this->causerId, fn ($q) => $q->where('causer_id', $this->causerId)
                ->where('causer_type', \App\Models\User::class))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return $this->view(['logs' => $logs]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Activity Logs</flux:heading>
        @can('activity_logs.export')
            <flux:button variant="ghost" icon="arrow-down-tray">Export</flux:button>
        @endcan
    </div>

    <flux:card class="space-y-4" size="sm">

        <div class="flex flex-wrap items-end gap-3">
            <div class="w-72">
                <flux:input
                    icon="magnifying-glass"
                    placeholder="Search description or event..."
                    wire:model.live.debounce.300ms="search"
                    clearable
                />
            </div>

            <div class="w-36">
                <flux:select
                    wire:model.live="logName"
                    variant="listbox"
                    clearable
                    placeholder="Log type"
                >
                    <flux:select.option value="auth">Auth</flux:select.option>
                    <flux:select.option value="model">Model</flux:select.option>
                </flux:select>
            </div>

            <div class="w-40">
                <flux:select
                    wire:model.live="subjectType"
                    variant="listbox"
                    clearable
                    placeholder="Subject"
                >
                    @foreach ($subjectTypes as $type)
                        <flux:select.option value="{{ $type }}">{{ $type }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-48">
                <flux:select
                    wire:model.live="causerId"
                    variant="listbox"
                    searchable
                    clearable
                    placeholder="User (causer)"
                >
                    @foreach ($users as $user)
                        <flux:select.option value="{{ $user['id'] }}">
                            {{ $user['name'] ?? $user['email'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-38">
                <flux:input
                    wire:model.live="dateFrom"
                    type="date"
                    label="From"
                />
            </div>

            <div class="w-38">
                <flux:input
                    wire:model.live="dateTo"
                    type="date"
                    label="To"
                />
            </div>

            @if ($search || $logName || $subjectType || $causerId || $dateFrom || $dateTo)
                <flux:button
                    wire:click="resetFilters"
                    variant="ghost"
                    icon="x-mark"
                    size="sm"
                >
                    Clear filters
                </flux:button>
            @endif
        </div>

        <flux:table :paginate="$logs" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'created_at'"
                    :direction="$sortField === 'created_at' ? $sortDirection : null"
                    wire:click="sortBy('created_at')"
                >
                    Date
                </flux:table.column>
                <flux:table.column>Causer</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'event'"
                    :direction="$sortField === 'event' ? $sortDirection : null"
                    wire:click="sortBy('event')"
                >
                    Event
                </flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'log_name'"
                    :direction="$sortField === 'log_name' ? $sortDirection : null"
                    wire:click="sortBy('log_name')"
                >
                    Log Type
                </flux:table.column>
                <flux:table.column>Subject</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($logs as $log)
                    <flux:table.row wire:key="log-{{ $log->id }}">

                        <flux:table.cell class="text-zinc-400 text-xs">
                            {{ $logs->firstItem() + $loop->index }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:text size="sm">{{ $log->created_at->format('Y-m-d') }}</flux:text>
                            <flux:text size="xs" variant="muted">{{ $log->created_at->format('H:i:s') }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($log->causer)
                                <flux:text size="sm" variant="strong">{{ $log->causer->name ?? '—' }}</flux:text>
                                <flux:text size="xs" variant="muted">{{ $log->causer->email }}</flux:text>
                            @else
                                <flux:text size="sm" class="text-zinc-300 dark:text-zinc-600">System</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $eventColors = [
                                    'created'      => 'emerald',
                                    'updated'      => 'sky',
                                    'deleted'      => 'red',
                                    'login'        => 'violet',
                                    'logout'       => 'zinc',
                                    'failed_login' => 'amber',
                                ];
                                $color = $eventColors[$log->event] ?? 'zinc';
                            @endphp
                            <flux:badge :color="$color" size="sm">
                                {{ ucfirst(str_replace('_', ' ', $log->event ?? $log->description)) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge
                                color="{{ $log->log_name === 'auth' ? 'violet' : 'blue' }}"
                                size="sm"
                                variant="pill"
                            >
                                {{ ucfirst($log->log_name) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($log->subject_type)
                                <flux:text size="sm" variant="strong">{{ $log->subjectLabel() }}</flux:text>
                                <flux:text size="xs" variant="muted">#{{ $log->subject_id }}</flux:text>
                            @else
                                <flux:text size="sm" class="text-zinc-300 dark:text-zinc-600">—</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:text size="sm">{{ Str::limit($log->description, 60) }}</flux:text>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:button
                                icon="eye"
                                variant="ghost"
                                size="sm"
                                square
                                wire:click="viewDetail({{ $log->id }})"
                            />
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-1 text-zinc-400 dark:text-zinc-500">
                                <flux:icon.clock class="size-8 opacity-40" />
                                <flux:text>No activity logs found.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="activity-log-detail" class="min-w-2xl max-w-3xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Activity Detail</flux:heading>
                <flux:text variant="muted" size="sm">{{ $viewingDate }}</flux:text>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <flux:text variant="muted" size="xs" class="uppercase tracking-wide">Causer</flux:text>
                    <flux:text variant="strong">{{ $viewingCauser }}</flux:text>
                </div>
                <div>
                    <flux:text variant="muted" size="xs" class="uppercase tracking-wide">Event</flux:text>
                    <flux:text variant="strong">{{ ucfirst(str_replace('_', ' ', $viewingEvent ?? '')) }}</flux:text>
                </div>
                <div>
                    <flux:text variant="muted" size="xs" class="uppercase tracking-wide">Subject</flux:text>
                    <flux:text variant="strong">{{ $viewingSubject }}</flux:text>
                </div>
                <div>
                    <flux:text variant="muted" size="xs" class="uppercase tracking-wide">Description</flux:text>
                    <flux:text>{{ $viewingDescription }}</flux:text>
                </div>
            </div>

            <flux:separator />

            @if (!empty($viewingMeta))
                <div>
                    <flux:heading size="sm" class="mb-2">Details</flux:heading>
                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($viewingMeta as $key => $value)
                            <div class="flex items-center gap-4 px-4 py-2">
                                <flux:text size="xs" variant="muted" class="w-28 shrink-0">{{ ucfirst(str_replace('_', ' ', $key)) }}</flux:text>
                                <flux:text size="sm" class="font-mono break-all">{{ $value ?? '—' }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($viewingChanges))
                <div>
                    <flux:heading size="sm" class="mb-2">Changes</flux:heading>
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide w-1/4">Field</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-red-500 uppercase tracking-wide w-[37.5%]">Before</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-emerald-500 uppercase tracking-wide w-[37.5%]">After</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($viewingChanges as $change)
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs text-zinc-500">{{ $change['field'] }}</td>
                                        <td class="px-4 py-2">
                                            @if ($change['before'] !== null)
                                                <span class="inline-block px-2 py-0.5 rounded bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 font-mono text-xs break-all">
                                                    {{ is_bool($change['before']) ? ($change['before'] ? 'true' : 'false') : $change['before'] }}
                                                </span>
                                            @else
                                                <span class="text-zinc-300 dark:text-zinc-600 text-xs">null</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if ($change['after'] !== null)
                                                <span class="inline-block px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 font-mono text-xs break-all">
                                                    {{ is_bool($change['after']) ? ($change['after'] ? 'true' : 'false') : $change['after'] }}
                                                </span>
                                            @else
                                                <span class="text-zinc-300 dark:text-zinc-600 text-xs">null</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif (empty($viewingMeta))
                <flux:text variant="muted" size="sm">No property changes recorded for this event.</flux:text>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
