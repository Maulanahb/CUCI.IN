@props([
    'status',
    'size' => 'md',
    'dot' => false,
])

@php
    [$tone, $label] = match ($status) {
        'Menunggu Verifikasi' => ['warning', 'Menunggu Verifikasi'],
        'Terverifikasi' => ['success', 'Terverifikasi'],
        'Ditolak' => ['danger', 'Ditolak'],
        default => ['neutral', 'Tanpa Verifikasi'],
    };
@endphp

<x-ui.badge :tone="$tone" :size="$size" :dot="$dot" {{ $attributes }}>
    {{ $label }}
</x-ui.badge>
