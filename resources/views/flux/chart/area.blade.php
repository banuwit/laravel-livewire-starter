@props(['data' => '[]', 'labels' => '[]', 'height' => '300px', 'color' => 'var(--color-accent,#6366f1)'])

<flux:chart type="area" :data="'[{ data:' . $data . ', borderColor: \'' . $color . '\', backgroundColor: \'' . $color . '20\', tension: 0.4, fill: true, borderWidth: 2 }]'" :labels="$labels" :height="$height" {{ $attributes }} />
