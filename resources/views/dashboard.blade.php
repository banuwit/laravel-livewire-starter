<x-layouts::app :title="__('Dashboard')">
    <div class="section w-full h-full flex flex-col gap-4">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Dashboard Overview') }}</flux:heading>
                <flux:subheading>{{ __('Sales, transactions, and CRM insights at a glance.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:select size="sm" class="w-40">
                    <option>Last 7 days</option>
                    <option selected>Last 30 days</option>
                    <option>Last 90 days</option>
                    <option>This year</option>
                </flux:select>
                <flux:button size="sm" icon="plus" variant="primary">{{ __('New Order') }}</flux:button>
            </div>
        </div>

        {{-- KPI Stat Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Revenue --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text class="!text-zinc-500">Total Revenue</flux:text>
                    <flux:icon.banknotes variant="mini" class="text-emerald-500" />
                </div>
                <div class="mt-2 flex items-end justify-between">
                    <flux:heading size="xl" level="2">$248,920</flux:heading>
                    <flux:badge color="emerald" size="sm" icon="arrow-trending-up">+12.4%</flux:badge>
                </div>
                <flux:text size="sm" class="mt-1 !text-zinc-500">vs $221,540 last period</flux:text>
            </div>

            {{-- Orders --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text class="!text-zinc-500">Orders</flux:text>
                    <flux:icon.shopping-bag variant="mini" class="text-blue-500" />
                </div>
                <div class="mt-2 flex items-end justify-between">
                    <flux:heading size="xl" level="2">3,841</flux:heading>
                    <flux:badge color="emerald" size="sm" icon="arrow-trending-up">+8.1%</flux:badge>
                </div>
                <flux:text size="sm" class="mt-1 !text-zinc-500">312 pending fulfillment</flux:text>
            </div>

            {{-- Customers --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text class="!text-zinc-500">New Customers</flux:text>
                    <flux:icon.user-group variant="mini" class="text-violet-500" />
                </div>
                <div class="mt-2 flex items-end justify-between">
                    <flux:heading size="xl" level="2">1,204</flux:heading>
                    <flux:badge color="rose" size="sm" icon="arrow-trending-down">-2.3%</flux:badge>
                </div>
                <flux:text size="sm" class="mt-1 !text-zinc-500">68% returning customers</flux:text>
            </div>

            {{-- Conversion Rate --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <flux:text class="!text-zinc-500">Conversion Rate</flux:text>
                    <flux:icon.chart-bar variant="mini" class="text-amber-500" />
                </div>
                <div class="mt-2 flex items-end justify-between">
                    <flux:heading size="xl" level="2">3.84%</flux:heading>
                    <flux:badge color="emerald" size="sm" icon="arrow-trending-up">+0.6%</flux:badge>
                </div>
                <flux:text size="sm" class="mt-1 !text-zinc-500">Avg. order value $64.79</flux:text>
            </div>
        </div>

        {{-- Mini-stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'truck',        'label' => 'In Transit',         'value' => '286 orders', 'hint' => 'Avg 2.4 days delivery'],
                ['icon' => 'archive-box',  'label' => 'Inventory Alerts',   'value' => '14 SKUs',    'hint' => 'Low stock threshold'],
                ['icon' => 'arrow-uturn-left', 'label' => 'Returns',        'value' => '32 items',   'hint' => '1.2% return rate'],
                ['icon' => 'ticket',       'label' => 'Open Support',       'value' => '47 tickets', 'hint' => 'SLA 92% on time'],
            ] as $m)
                <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon :name="$m['icon']" variant="mini" class="text-zinc-600 dark:text-zinc-300" />
                    </div>
                    <div class="flex-1">
                        <flux:text size="sm" class="!text-zinc-500">{{ $m['label'] }}</flux:text>
                        <flux:text class="!font-semibold">{{ $m['value'] }}</flux:text>
                    </div>
                    <flux:text size="sm" class="!text-zinc-500 hidden sm:block">{{ $m['hint'] }}</flux:text>
                </div>
            @endforeach
        </div>

        {{-- Sales Chart + Channel Mix --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Revenue chart placeholder --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">Revenue Trend</flux:heading>
                        <flux:subheading>Daily gross sales over the last 30 days</flux:subheading>
                    </div>
                    <flux:button.group size="sm">
                        <flux:button>Day</flux:button>
                        <flux:button variant="primary">Week</flux:button>
                        <flux:button>Month</flux:button>
                    </flux:button.group>
                </div>

                {{-- Faux line chart --}}
                <div class="relative mt-6 h-64 w-full">
                    <svg viewBox="0 0 600 220" preserveAspectRatio="none" class="h-full w-full">
                        <defs>
                            <linearGradient id="grad" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="rgb(16,185,129)" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="rgb(16,185,129)" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        {{-- gridlines --}}
                        <g stroke="currentColor" class="text-zinc-200 dark:text-zinc-700" stroke-width="1">
                            <line x1="0" y1="40"  x2="600" y2="40"  />
                            <line x1="0" y1="90"  x2="600" y2="90"  />
                            <line x1="0" y1="140" x2="600" y2="140" />
                            <line x1="0" y1="190" x2="600" y2="190" />
                        </g>
                        <path d="M0,160 L40,150 L80,135 L120,140 L160,120 L200,110 L240,118 L280,95 L320,100 L360,80 L400,85 L440,65 L480,72 L520,55 L560,48 L600,40 L600,220 L0,220 Z"
                              fill="url(#grad)" />
                        <path d="M0,160 L40,150 L80,135 L120,140 L160,120 L200,110 L240,118 L280,95 L320,100 L360,80 L400,85 L440,65 L480,72 L520,55 L560,48 L600,40"
                              fill="none" stroke="rgb(16,185,129)" stroke-width="2.5" />
                    </svg>
                </div>
                <div class="mt-4 flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2"><span class="size-2.5 rounded-full bg-emerald-500"></span><span class="text-zinc-500">This period</span></div>
                    <div class="flex items-center gap-2"><span class="size-2.5 rounded-full bg-zinc-400"></span><span class="text-zinc-500">Previous</span></div>
                </div>
            </div>

            {{-- Channel mix --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">Sales by Channel</flux:heading>
                <flux:subheading>Distribution this month</flux:subheading>

                <div class="mt-6 space-y-5">
                    @foreach ([
                        ['label' => 'Online Store', 'value' => 62, 'amount' => '$154,330', 'color' => 'bg-emerald-500'],
                        ['label' => 'Marketplace', 'value' => 21, 'amount' => '$52,270', 'color' => 'bg-blue-500'],
                        ['label' => 'Retail POS',  'value' => 11, 'amount' => '$27,380', 'color' => 'bg-violet-500'],
                        ['label' => 'Wholesale',   'value' => 6,  'amount' => '$14,940', 'color' => 'bg-amber-500'],
                    ] as $row)
                        <div>
                            <div class="flex items-center justify-between">
                                <flux:text class="!font-medium">{{ $row['label'] }}</flux:text>
                                <flux:text size="sm" class="!text-zinc-500">{{ $row['amount'] }} • {{ $row['value'] }}%</flux:text>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full {{ $row['color'] }}" style="width: {{ $row['value'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recent Transactions + Top Products --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Transactions table --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Recent Transactions</flux:heading>
                        <flux:subheading>Latest orders and payments</flux:subheading>
                    </div>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right">View all</flux:button>
                </div>

                <flux:table class="mt-4">
                    <flux:table.columns>
                        <flux:table.column>Order</flux:table.column>
                        <flux:table.column>Customer</flux:table.column>
                        <flux:table.column>Channel</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Amount</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ([
                            ['id' => '#ORD-10421', 'customer' => 'Amelia Hartwell', 'channel' => 'Online Store', 'status' => 'Paid',     'color' => 'emerald', 'amount' => '$1,249.00'],
                            ['id' => '#ORD-10420', 'customer' => 'Marcus Chen',     'channel' => 'Marketplace',  'status' => 'Pending',  'color' => 'amber',   'amount' => '$329.50'],
                            ['id' => '#ORD-10419', 'customer' => 'Sara Okafor',    'channel' => 'Retail POS',   'status' => 'Paid',     'color' => 'emerald', 'amount' => '$84.20'],
                            ['id' => '#ORD-10418', 'customer' => 'Liam Becker',    'channel' => 'Wholesale',    'status' => 'Refunded', 'color' => 'rose',    'amount' => '$2,450.00'],
                            ['id' => '#ORD-10417', 'customer' => 'Priya Natarajan','channel' => 'Online Store', 'status' => 'Paid',     'color' => 'emerald', 'amount' => '$612.75'],
                            ['id' => '#ORD-10416', 'customer' => 'Jonas Vega',     'channel' => 'Online Store', 'status' => 'Failed',   'color' => 'rose',    'amount' => '$148.00'],
                        ] as $tx)
                            <flux:table.row>
                                <flux:table.cell variant="strong">{{ $tx['id'] }}</flux:table.cell>
                                <flux:table.cell>{{ $tx['customer'] }}</flux:table.cell>
                                <flux:table.cell class="!text-zinc-500">{{ $tx['channel'] }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ $tx['color'] }}">{{ $tx['status'] }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end" variant="strong">{{ $tx['amount'] }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            {{-- Top Products --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Top Products</flux:heading>
                        <flux:subheading>Best sellers this month</flux:subheading>
                    </div>
                </div>

                <ul class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ([
                        ['name' => 'Aurora Wireless Headphones', 'sku' => 'SKU-AWH-22',  'sold' => 482, 'revenue' => '$57,840'],
                        ['name' => 'Nimbus Smart Watch S2',      'sku' => 'SKU-NSW-S2',  'sold' => 361, 'revenue' => '$43,320'],
                        ['name' => 'Ember Ceramic Mug 14oz',     'sku' => 'SKU-ECM-14',  'sold' => 298, 'revenue' => '$8,940'],
                        ['name' => 'Volta Power Bank 20k',       'sku' => 'SKU-VPB-20',  'sold' => 254, 'revenue' => '$12,700'],
                        ['name' => 'Lumen Desk Lamp Pro',        'sku' => 'SKU-LDL-PR',  'sold' => 211, 'revenue' => '$15,825'],
                    ] as $i => $p)
                        <li class="flex items-center gap-3 py-3">
                            <div class="flex size-10 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-100 to-zinc-200 text-sm font-semibold text-zinc-600 dark:from-zinc-800 dark:to-zinc-700 dark:text-zinc-300">
                                #{{ $i + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <flux:text class="!font-medium truncate">{{ $p['name'] }}</flux:text>
                                <flux:text size="sm" class="!text-zinc-500">{{ $p['sku'] }} • {{ $p['sold'] }} sold</flux:text>
                            </div>
                            <flux:text class="!font-semibold">{{ $p['revenue'] }}</flux:text>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- CRM section --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Pipeline --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">CRM Pipeline</flux:heading>
                        <flux:subheading>Deals across stages</flux:subheading>
                    </div>
                    <flux:button size="sm" variant="ghost" icon="adjustments-horizontal">Filters</flux:button>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['stage' => 'Leads',       'count' => 124, 'value' => '$182k', 'color' => 'border-blue-500',    'dot' => 'bg-blue-500'],
                        ['stage' => 'Qualified',   'count' => 68,  'value' => '$96k',  'color' => 'border-violet-500',  'dot' => 'bg-violet-500'],
                        ['stage' => 'Proposal',    'count' => 32,  'value' => '$54k',  'color' => 'border-amber-500',   'dot' => 'bg-amber-500'],
                        ['stage' => 'Won',         'count' => 19,  'value' => '$41k',  'color' => 'border-emerald-500', 'dot' => 'bg-emerald-500'],
                    ] as $s)
                        <div class="rounded-lg border-l-4 {{ $s['color'] }} bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full {{ $s['dot'] }}"></span>
                                <flux:text size="sm" class="!text-zinc-500 !font-medium">{{ $s['stage'] }}</flux:text>
                            </div>
                            <flux:heading size="lg" class="!mt-2">{{ $s['count'] }}</flux:heading>
                            <flux:text size="sm" class="!text-zinc-500">{{ $s['value'] }} potential</flux:text>
                        </div>
                    @endforeach
                </div>

                {{-- Recent activity --}}
                <div class="mt-6">
                    <flux:heading size="sm" class="!text-zinc-500 !uppercase !tracking-wide">Recent Activity</flux:heading>
                    <ul class="mt-3 space-y-4">
                        @foreach ([
                            ['icon' => 'phone',          'color' => 'text-blue-500',    'who' => 'Amelia Hartwell',  'what' => 'Discovery call scheduled with Acme Corp.', 'time' => '2m ago'],
                            ['icon' => 'envelope',       'color' => 'text-violet-500',  'who' => 'Marcus Chen',      'what' => 'Sent proposal to Globex Industries.',     'time' => '24m ago'],
                            ['icon' => 'check-circle',   'color' => 'text-emerald-500', 'who' => 'Sara Okafor',     'what' => 'Closed deal — Initech ($24,500).',         'time' => '1h ago'],
                            ['icon' => 'exclamation-circle', 'color' => 'text-amber-500', 'who' => 'Liam Becker',  'what' => 'Follow-up overdue: Stark Holdings.',       'time' => '3h ago'],
                        ] as $a)
                            <li class="flex items-start gap-3">
                                <div class="mt-0.5 flex size-8 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon :name="$a['icon']" variant="mini" class="{{ $a['color'] }}" />
                                </div>
                                <div class="flex-1">
                                    <flux:text><span class="font-medium">{{ $a['who'] }}</span> — {{ $a['what'] }}</flux:text>
                                    <flux:text size="sm" class="!text-zinc-500">{{ $a['time'] }}</flux:text>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Top customers --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Top Customers</flux:heading>
                        <flux:subheading>Highest lifetime value</flux:subheading>
                    </div>
                </div>

                <ul class="mt-4 space-y-4">
                    @foreach ([
                        ['name' => 'Acme Corporation',   'email' => 'billing@acme.io',     'ltv' => '$48,920', 'initials' => 'AC', 'tier' => 'Platinum', 'color' => 'zinc'],
                        ['name' => 'Globex Industries',  'email' => 'orders@globex.com',   'ltv' => '$36,410', 'initials' => 'GI', 'tier' => 'Gold',     'color' => 'amber'],
                        ['name' => 'Initech Ltd.',       'email' => 'ap@initech.co',       'ltv' => '$28,750', 'initials' => 'IN', 'tier' => 'Gold',     'color' => 'amber'],
                        ['name' => 'Stark Holdings',     'email' => 'hello@stark.io',      'ltv' => '$22,180', 'initials' => 'SH', 'tier' => 'Silver',   'color' => 'zinc'],
                        ['name' => 'Wayne Enterprises',  'email' => 'pay@wayne.com',       'ltv' => '$19,640', 'initials' => 'WE', 'tier' => 'Silver',   'color' => 'zinc'],
                    ] as $c)
                        <li class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-blue-500 text-sm font-semibold text-white">
                                {{ $c['initials'] }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <flux:text class="!font-medium truncate">{{ $c['name'] }}</flux:text>
                                    <flux:badge size="sm" color="{{ $c['color'] }}">{{ $c['tier'] }}</flux:badge>
                                </div>
                                <flux:text size="sm" class="!text-zinc-500 truncate">{{ $c['email'] }}</flux:text>
                            </div>
                            <flux:text class="!font-semibold">{{ $c['ltv'] }}</flux:text>
                        </li>
                    @endforeach
                </ul>

                <flux:separator class="my-5" />

                <flux:button class="w-full" variant="primary" icon="user-plus">Add new customer</flux:button>
            </div>
        </div>
    </div>
</x-layouts::app>
