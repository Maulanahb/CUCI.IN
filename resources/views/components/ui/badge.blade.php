@props([
    'tone' => 'neutral',
    'size' => 'md',
    'dot' => false,
    'pulse' => false,
])

@php
    $tones = [
        'neutral' => 'bg-slate-50 text-slate-700 ring-slate-600/15',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-700/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/25',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-700/20',
    ];

    $dotFills = [
        'neutral' => 'fill-slate-400',
        'info' => 'fill-sky-500',
        'warning' => 'fill-amber-500',
        'success' => 'fill-emerald-500',
        'danger' => 'fill-rose-500',
        'brand' => 'fill-brand-500',
    ];

    $sizes = [
        'xs' => 'px-1.5 py-0.5 text-2xs gap-1 leading-4',
        'sm' => 'px-2 py-0.5 text-xs gap-1.5 leading-4',
        'md' => 'px-2.5 py-1 text-xs gap-1.5 leading-4',
    ];

    $classes = 'inline-flex items-center rounded-full font-medium ring-1 ring-inset select-none whitespace-nowrap '
        .($tones[$tone] ?? $tones['neutral']).' '
        .($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->class($classes) }}>
    @if ($dot)
        <svg class="size-1.5 {{ $dotFills[$tone] ?? $dotFills['neutral'] }} shrink-0 {{ $pulse ? 'animate-pulse' : '' }}" viewBox="0 0 6 6" aria-hidden="true">
            <circle cx="3" cy="3" r="3" />
        </svg>
    @endif
    {{ $slot }}
</span>
