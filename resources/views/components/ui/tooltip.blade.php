@props([
    'text' => '',
    'position' => 'top',
])

@php
    $positionClasses = match ($position) {
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-1.5',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-1.5',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-1.5',
        default => 'bottom-full left-1/2 -translate-x-1/2 mb-1.5',
    };
@endphp

<div
    x-data="{ tooltip: false }"
    @keydown.escape.window="tooltip = false"
    class="relative inline-flex"
>
    <div
        @mouseenter="tooltip = true"
        @mouseleave="tooltip = false"
        @focusin="tooltip = true"
        @focusout="tooltip = false"
        class="inline-flex"
    >
        {{ $slot }}
    </div>

    <div
        x-cloak
        x-show="tooltip"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute pointer-events-none z-50 rounded-md bg-slate-900 px-2 py-1 text-2xs font-medium text-white shadow-md whitespace-nowrap {{ $positionClasses }}"
        role="tooltip"
    >
        {{ $text }}
    </div>
</div>
