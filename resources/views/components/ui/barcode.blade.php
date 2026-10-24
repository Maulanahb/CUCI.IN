@props([
    'code',
    'height' => 38,
    'showText' => true,
])

@php
    $svg = app(\App\Services\BarcodeService::class)->generateCode128Svg((string) $code, (int) $height);
@endphp

<div {{ $attributes->merge(['class' => 'text-center inline-block']) }}>
    <div class="overflow-hidden flex justify-center">
        {!! $svg !!}
    </div>
    @if ($showText)
        <p class="font-mono font-bold tracking-widest text-center mt-0.5 text-black text-[11px] uppercase select-all">
            {{ $code }}
        </p>
    @endif
</div>
