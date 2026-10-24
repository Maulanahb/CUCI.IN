@props([
    'variant' => 'segmented',
    'as' => 'nav',
])

@php
    $baseClasses = match ($variant) {
        'segmented' => 'inline-flex items-center gap-1 rounded-xl bg-slate-100/90 p-1 text-xs border border-slate-200/80 select-none overflow-x-auto no-scrollbar',
        'pills' => 'inline-flex items-center gap-1.5 text-xs select-none overflow-x-auto no-scrollbar',
        'underline' => 'flex items-center gap-4 border-b border-slate-200 text-xs font-medium select-none overflow-x-auto no-scrollbar',
        default => 'inline-flex items-center gap-1 rounded-xl bg-slate-100/90 p-1 text-xs border border-slate-200/80 select-none overflow-x-auto no-scrollbar',
    };
@endphp

@if ($as === 'div')
    <div role="tablist" {{ $attributes->class([$baseClasses]) }}>
        {{ $slot }}
    </div>
@else
    <nav role="tablist" {{ $attributes->class([$baseClasses]) }}>
        {{ $slot }}
    </nav>
@endif
