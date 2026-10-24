@props([
    'status',
    'size' => 'md',
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
@endphp

<x-ui.badge :tone="$tone" :size="$size" {{ $attributes }}>
    {{ $value }}
</x-ui.badge>
