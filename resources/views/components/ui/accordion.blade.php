@props([
    'default' => null,
])

<div
    x-data="{ activeAccordion: @js($default) }"
    {{ $attributes->merge(['class' => 'divide-y divide-slate-200/90 rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden']) }}
>
    {{ $slot }}
</div>
