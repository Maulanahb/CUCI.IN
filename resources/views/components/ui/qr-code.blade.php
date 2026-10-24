@props([
    'text',
    'size' => 100,
    'label' => null,
])

@php
    $svg = app(\App\Services\BarcodeService::class)->generateQrCodeSvg((string) $text, (int) $size);
@endphp

<div {{ $attributes->merge(['class' => 'text-center inline-block']) }}>
    <div class="inline-flex justify-center p-0.5 bg-white">
        {!! $svg !!}
    </div>
    @if ($label)
        <p class="font-mono text-[9.5px] text-black mt-1 text-center font-medium">
            {{ $label }}
        </p>
    @endif
</div>
