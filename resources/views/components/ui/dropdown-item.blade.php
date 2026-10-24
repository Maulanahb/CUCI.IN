@props([
    'href' => null,
    'destructive' => false,
])

@php
    $classes = 'flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium transition cursor-pointer text-left '
        . ($destructive
            ? 'text-rose-600 hover:bg-rose-50 hover:text-rose-700'
            : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
