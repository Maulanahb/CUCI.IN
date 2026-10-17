@props(['status'])

@php
    $visual = \App\Enums\TransactionStatus::visualStyle($status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold ' . $visual['badge']]) }}>
    <span class="size-1.5 shrink-0 rounded-full {{ $visual['dot'] }}" aria-hidden="true"></span>
    <span>{{ $visual['label'] }}</span>
</span>
