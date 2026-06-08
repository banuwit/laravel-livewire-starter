@props(['data' => '[]', 'labels' => '[]', 'height' => '300px', 'color' => 'var(--color-accent,#6366f1)'])

<flux:chart type="bar" :data="'[{ data:' . $data . ', backgroundColor: \'' . $color . '33\', borderColor: \'' . $color . '\', borderWidth: 2, borderRadius: 4 }]'" :labels="$labels" :height="$height" {{ $attributes }} />
