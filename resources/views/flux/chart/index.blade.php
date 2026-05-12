@props([
    'type'   => 'bar',
    'data'   => '[]',
    'labels' => '[]',
    'height' => '300px',
    'options' => '{}',
])

{{--
    Requires Chart.js: <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    Usage: <x-flux.chart type="bar" :labels="['Jan','Feb']" :data="[10,20]" />
--}}

<div {{ $attributes->class('relative w-full') }} style="height: {{ $height }}">
    <canvas
        x-data="{
            chart: null,
            init() {
                if (typeof Chart === 'undefined') {
                    console.error('[x-flux.chart] Chart.js not loaded.');
                    return;
                }
                const isArea = '{{ $type }}' === 'area';
                const ctx = this.$el.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: isArea ? 'line' : '{{ $type }}',
                    data: {
                        labels: {{ $labels }},
                        datasets: Array.isArray({{ $data }})
                            && typeof {{ $data }}[0] === 'object'
                            ? {{ $data }}
                            : [{ data: {{ $data }}, label: 'Value', backgroundColor: 'var(--color-accent, #6366f1)33', borderColor: 'var(--color-accent, #6366f1)', borderWidth: 2, fill: isArea, tension: 0.4 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: ['pie','doughnut','polarArea'].includes('{{ $type }}') ? {} : {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                        },
                        ...{{ $options }}
                    }
                });
            }
        }"
        x-init="init()"
    ></canvas>
</div>
