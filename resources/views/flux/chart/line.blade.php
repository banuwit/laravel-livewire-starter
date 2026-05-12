@props(['data' => '[]', 'labels' => '[]', 'height' => '300px', 'color' => 'var(--color-accent,#6366f1)'])

<x-flux.chart type="line" :data="'[{ data:' . $data . ', borderColor: \'' . $color . '\', tension: 0.4, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5 }]'" :labels="$labels" :height="$height" {{ $attributes }} />
