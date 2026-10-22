@props([
    'status',
    'size' => 'md',
])

@php
    $tone = match ($status) {
        'Lunas' => 'success',
        'DP' => 'warning',
        default => 'danger',
    };
@endphp

<x-ui.badge :tone="$tone" :size="$size" {{ $attributes }}>
    {{ $status }}
</x-ui.badge>
