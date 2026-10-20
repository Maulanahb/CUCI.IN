@props([
    'value',
    'decimals' => 0,
])

<span {{ $attributes->class('whitespace-nowrap tabular-nums') }}>
    Rp {{ number_format((float) $value, $decimals, ',', '.') }}
</span>
