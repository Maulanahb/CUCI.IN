@props([
    'id' => null,
    'title' => '',
])

@php
    $itemId = $id ?? 'accordion_' . uniqid();
@endphp

<div
    x-data="{ id: '{{ $itemId }}' }"
    {{ $attributes->merge(['class' => 'p-4 sm:p-5']) }}
>
    <button
        type="button"
        @click="activeAccordion = (activeAccordion === id ? null : id)"
        class="flex w-full items-center justify-between text-left text-sm font-semibold text-slate-800 transition hover:text-slate-900 cursor-pointer"
        :aria-expanded="activeAccordion === id"
    >
        <span>{{ $title }}</span>
        <x-lucide-chevron-down
            class="size-4 shrink-0 text-slate-400 transition-transform duration-200"
            :class="{ 'rotate-180': activeAccordion === id }"
        />
    </button>

    <div
        x-cloak
        x-show="activeAccordion === id"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="pt-3 text-xs sm:text-sm text-slate-600 leading-relaxed"
    >
        {{ $slot }}
    </div>
</div>
