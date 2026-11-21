@props([
    'padding' => 'p-5',
])

<div {{ $attributes->class(['rounded-2xl border border-slate-200/80 bg-white shadow-xs', $padding]) }}>
    {{ $slot }}
</div>
