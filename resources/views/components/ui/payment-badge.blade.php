@props([
    'status',
    'size' => 'md',
    'dot' => false,
])

@php
    $tone = match ($status) {
        'Lunas' => 'success',
        'DP' => 'warning',
        default => 'danger',
    };
@endphp

<x-ui.badge :tone="$tone" :size="$size" :dot="$dot" {{ $attributes }}>
    {{ $status }}
</x-ui.badge>
