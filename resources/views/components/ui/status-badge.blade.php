@props([
    'status',
    'size' => 'md',
    'dot' => true,
])

@php
    $value = $status instanceof \App\Enums\TransactionStatus ? $status->value : (string) $status;

    $tone = match ($value) {
        'Diterima' => 'info',
        'Diproses' => 'warning',
        'Selesai' => 'success',
        'Diambil' => 'neutral',
        'Dibatalkan' => 'danger',
        default => 'neutral',
    };

    $pulse = $value === 'Diproses';
@endphp

<x-ui.badge :tone="$tone" :size="$size" :dot="$dot" :pulse="$pulse" {{ $attributes }}>
    {{ $value }}
</x-ui.badge>
