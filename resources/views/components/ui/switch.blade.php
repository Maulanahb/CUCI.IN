@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
    'description' => null,
    'disabled' => false,
])

@php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'switch_' . uniqid());
@endphp

<div class="flex items-start gap-3">
    <label for="{{ $inputId }}" class="relative inline-flex shrink-0 cursor-pointer items-center select-none pt-0.5">
        <input
            type="checkbox"
            id="{{ $inputId }}"
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        />
        {{-- Track --}}
        <div class="h-5 w-9 rounded-full bg-slate-200 transition-colors duration-200 ease-in-out peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500/20 peer-focus:ring-offset-1 peer-checked:bg-brand-600 peer-disabled:cursor-not-allowed peer-disabled:opacity-50"></div>
        {{-- Thumb --}}
        <div class="absolute left-0.5 top-0.5 size-4 rounded-full bg-white shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-4"></div>
    </label>

    @if ($label || $description)
        <div class="flex flex-col">
            @if ($label)
                <label for="{{ $inputId }}" class="text-xs font-medium text-slate-800 sm:text-sm cursor-pointer select-none">
                    {{ $label }}
                </label>
            @endif
            @if ($description)
                <p class="text-2xs text-slate-500 select-none">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endif
</div>
