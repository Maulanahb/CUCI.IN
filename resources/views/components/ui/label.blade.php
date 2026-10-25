@props([
    'required' => false,
    'optional' => false,
])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold text-slate-700']) }}>
    {{ $slot }}
    @if ($required)
        <span class="text-rose-500">*</span>
    @elseif ($optional)
        <span class="text-2xs font-normal text-slate-400">(Opsional)</span>
    @endif
</label>
