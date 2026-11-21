@props([
    'variant' => 'default',
    'title' => null,
    'dismissible' => false,
])

@php
    $variants = [
        'default' => [
            'card' => 'border-slate-200/90 bg-slate-50/70 text-slate-800',
            'title' => 'text-slate-900',
            'icon' => 'text-slate-500',
            'icon_name' => 'info',
        ],
        'info' => [
            'card' => 'border-brand-200 bg-brand-50/70 text-brand-900',
            'title' => 'text-brand-950',
            'icon' => 'text-brand-600',
            'icon_name' => 'info',
        ],
        'success' => [
            'card' => 'border-emerald-200 bg-emerald-50/70 text-emerald-900',
            'title' => 'text-emerald-950',
            'icon' => 'text-emerald-600',
            'icon_name' => 'check-circle-2',
        ],
        'warning' => [
            'card' => 'border-amber-200 bg-amber-50/70 text-amber-900',
            'title' => 'text-amber-950',
            'icon' => 'text-amber-600',
            'icon_name' => 'alert-triangle',
        ],
        'destructive' => [
            'card' => 'border-rose-200 bg-rose-50/70 text-rose-900',
            'title' => 'text-rose-950',
            'icon' => 'text-rose-600',
            'icon_name' => 'alert-circle',
        ],
    ];

    $cfg = $variants[$variant] ?? $variants['default'];
@endphp

<div
    @if ($dismissible)
        x-data="{ visible: true }"
        x-show="visible"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    @endif
    {{ $attributes->class([
        'relative w-full rounded-2xl border p-4 text-xs sm:text-sm shadow-2xs transition-all',
        $cfg['card'],
    ]) }}
    role="alert"
>
    <div class="flex items-start gap-3">
        <div class="shrink-0 mt-0.5 {{ $cfg['icon'] }}">
            @switch($cfg['icon_name'])
                @case('check-circle-2')
                    <x-lucide-check-circle-2 class="size-4" />
                    @break
                @case('alert-triangle')
                    <x-lucide-alert-triangle class="size-4" />
                    @break
                @case('alert-circle')
                    <x-lucide-alert-circle class="size-4" />
                    @break
                @default
                    <x-lucide-info class="size-4" />
            @endswitch
        </div>

        <div class="flex-1 space-y-1">
            @if ($title)
                <h4 class="font-bold tracking-tight text-xs sm:text-sm {{ $cfg['title'] }}">
                    {{ $title }}
                </h4>
            @endif
            <div class="leading-relaxed opacity-95">
                {{ $slot }}
            </div>
        </div>

        @if ($dismissible)
            <button
                type="button"
                @click="visible = false"
                class="shrink-0 rounded-lg p-1 text-slate-400 hover:bg-black/5 hover:text-slate-600 transition cursor-pointer"
                aria-label="Tutup notifikasi"
            >
                <x-lucide-x class="size-3.5" />
            </button>
        @endif
    </div>
</div>
