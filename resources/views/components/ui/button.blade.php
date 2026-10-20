@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
    ];

    $sizes = [
        'sm' => 'h-9 gap-1.5 rounded-lg px-3 text-xs',
        'md' => 'h-10 gap-2 rounded-xl px-4 text-sm',
    ];

    $classes = 'inline-flex items-center justify-center font-semibold transition-colors disabled:pointer-events-none disabled:opacity-60 '
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
