@props([
    'href' => null,
    'active' => false,
    'name' => null,
    'model' => null,
    'badge' => null,
    'badgeTone' => 'neutral',
    'icon' => null,
    'variant' => 'segmented',
])

@php
    $badgeToneClasses = match ($badgeTone) {
        'brand' => 'bg-brand-50 text-brand-700',
        'emerald', 'success' => 'bg-emerald-50 text-emerald-700',
        'amber', 'warning' => 'bg-amber-50 text-amber-800',
        'rose', 'danger' => 'bg-rose-50 text-rose-700',
        default => 'bg-slate-200/70 text-slate-700',
    };

    $activeClasses = match ($variant) {
        'segmented' => 'bg-white text-slate-900 shadow-2xs font-bold',
        'pills' => 'bg-brand-50 text-brand-700 font-bold',
        'underline' => 'border-b-2 border-brand-600 text-brand-700 font-bold -mb-px pb-2',
        default => 'bg-white text-slate-900 shadow-2xs font-bold',
    };

    $inactiveClasses = match ($variant) {
        'segmented' => 'text-slate-600 hover:text-slate-900 font-medium',
        'pills' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium',
        'underline' => 'border-b-2 border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 font-medium pb-2',
        default => 'text-slate-600 hover:text-slate-900 font-medium',
    };

    $baseClasses = 'inline-flex items-center gap-1.5 shrink-0 rounded-lg px-3 py-1.5 transition-all duration-150 cursor-pointer focus:outline-none';
    if ($variant === 'underline') {
        $baseClasses = 'inline-flex items-center gap-1.5 shrink-0 transition-all duration-150 cursor-pointer focus:outline-none';
    }

    $isAlpineControlled = (bool) ($model && $name);
    $computedClasses = $baseClasses . ' ' . ($isAlpineControlled ? '' : ($active ? $activeClasses : $inactiveClasses));

    $buttonAttrs = [];
    if ($isAlpineControlled) {
        $buttonAttrs['@click'] = "{$model} = '{$name}'";
        $buttonAttrs[':class'] = "{$model} === '{$name}' ? '{$activeClasses}' : '{$inactiveClasses}'";
        $buttonAttrs[':aria-selected'] = "{$model} === '{$name}'";
    } else {
        $buttonAttrs['aria-selected'] = $active ? 'true' : 'false';
    }
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        role="tab"
        aria-selected="{{ $active ? 'true' : 'false' }}"
        {{ $attributes->class([$computedClasses]) }}
    >
        @if ($icon)
            <x-dynamic-component :component="'lucide-' . $icon" class="size-3.5 shrink-0" />
        @endif

        <span>{{ $slot }}</span>

        @if ($badge !== null)
            <span class="rounded-md px-1.5 py-0.5 text-2xs font-bold tabular-nums {{ $badgeToneClasses }}">
                {{ $badge }}
            </span>
        @endif
    </a>
@else
    <button
        type="button"
        role="tab"
        {{ $attributes->merge($buttonAttrs)->class([$computedClasses]) }}
    >
        @if ($icon)
            <x-dynamic-component :component="'lucide-' . $icon" class="size-3.5 shrink-0" />
        @endif

        <span>{{ $slot }}</span>

        @if ($badge !== null)
            <span class="rounded-md px-1.5 py-0.5 text-2xs font-bold tabular-nums {{ $badgeToneClasses }}">
                {{ $badge }}
            </span>
        @endif
    </button>
@endif
