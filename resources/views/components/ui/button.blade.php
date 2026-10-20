@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand-700 text-white hover:bg-brand-800',
        'secondary' => 'border border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900',
        'soft' => 'border border-brand-200 bg-brand-50 text-brand-700 hover:border-brand-300 hover:bg-brand-100 hover:text-brand-800',
        'soft-warning' => 'border border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-300 hover:bg-amber-100 hover:text-amber-900',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
        'danger-outline' => 'border border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300 hover:bg-rose-100 hover:text-rose-800',
    ];

    $sizes = [
        'xs' => 'h-8 gap-1.5 rounded-lg px-2.5 text-xs',
        'sm' => 'h-9 gap-1.5 rounded-lg px-3 text-xs',
        'md' => 'h-11 gap-2 rounded-xl px-4 text-sm',
    ];

    $classes = 'inline-flex items-center justify-center font-semibold transition-colors disabled:pointer-events-none disabled:bg-slate-200 disabled:text-slate-500 disabled:opacity-60 '
        .($variants[$variant] ?? $variants['primary']).' '
        .($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
