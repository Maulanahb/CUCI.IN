@props([
    'disabled' => false,
    'error' => false,
    'size' => 'default',
    'prefix' => null,
    'suffix' => null,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'py-1.5 text-xs',
        'lg' => 'py-2.5 text-sm',
        default => 'py-2 text-xs sm:text-sm',
    };

    $paddingLeft = $prefix ? 'pl-9' : 'pl-3.5';
    $paddingRight = $suffix ? 'pr-12' : 'pr-3.5';
@endphp

@if ($prefix || $suffix)
    <div class="relative w-full">
        @if ($prefix)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-xs font-semibold text-slate-400 select-none">{{ $prefix }}</span>
            </div>
        @endif

        <input
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full rounded-xl border bg-white ' . $sizeClasses . ' ' . $paddingLeft . ' ' . $paddingRight . ' text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 ' . (
                    $error
                        ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/15'
                        : 'border-slate-200 focus:border-brand-500 focus:ring-brand-500/15'
                )
            ]) }}
        />

        @if ($suffix)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <span class="text-xs font-semibold text-slate-400 select-none">{{ $suffix }}</span>
            </div>
        @endif
    </div>
@else
    <input
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl border bg-white px-3.5 ' . $sizeClasses . ' text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 ' . (
                $error
                    ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/15'
                    : 'border-slate-200 focus:border-brand-500 focus:ring-brand-500/15'
            )
        ]) }}
    />
@endif
