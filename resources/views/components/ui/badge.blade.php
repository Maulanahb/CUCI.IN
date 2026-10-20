@props([
    'tone' => 'neutral',
    'size' => 'md',
])

@php
    $tones = [
        'neutral' => 'border-slate-200 bg-slate-50 text-slate-700',
        'info' => 'border-sky-200 bg-sky-50 text-sky-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
        'brand' => 'border-brand-200 bg-brand-50 text-brand-700',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
    ];

    $classes = 'inline-flex items-center rounded-full border font-semibold '
        .($tones[$tone] ?? $tones['neutral']).' '
        .($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->class($classes) }}>
    {{ $slot }}
</span>
