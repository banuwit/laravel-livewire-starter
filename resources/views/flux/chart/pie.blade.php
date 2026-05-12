@props([
    'data' => '[]',
    'labels' => '[]',
    'height' => '300px',
    'colors' => "['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444']",
])

<x-flux.chart type="pie" :data="'[{ data:' . $data . ', backgroundColor: ' . $colors . ', borderWidth: 2, borderColor: \'#fff\' }]'" :labels="$labels" :height="$height" {{ $attributes }} />
