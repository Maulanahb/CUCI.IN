@props([
    'padding' => 'p-5',
])

<div {{ $attributes->class(['rounded-2xl border border-slate-200 bg-white', $padding]) }}>
    {{ $slot }}
</div>
