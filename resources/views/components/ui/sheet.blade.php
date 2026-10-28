@props([
    'name' => null,
    'show' => null,
    'title' => null,
    'description' => null,
    'side' => 'right',
    'maxWidth' => 'xl',
    'closable' => true,
    'closeTitle' => 'Tutup panel',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        'full' => 'sm:max-w-full',
        default => 'sm:max-w-xl',
    };

    $showCondition = $show ?? ($name ? "openSheet === '{$name}'" : 'open');
    $closeAction = $show
        ? "{$show} = false" . ($name ? "; openSheet = null" : '')
        : ($name ? "openSheet = null" : 'open = false');
@endphp

<div
    x-cloak
    x-show="{{ $showCondition }}"
    x-init="$watch('{{ $showCondition }}', value => document.body.classList.toggle('overflow-hidden', Boolean(value)))"
    @keydown.escape.window="@if($closable) {{ $closeAction }} @endif"
    class="fixed inset-0 z-50 overflow-hidden"
    role="dialog"
    aria-modal="true"
    @if($name) aria-labelledby="{{ $name }}-title" @endif
>
    {{-- Backdrop with soft blur --}}
    <div
        x-show="{{ $showCondition }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @if($closable) @click="{{ $closeAction }}" @endif
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity cursor-pointer"
        aria-hidden="true"
    ></div>

    {{-- Slide-over Panel Container --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
        <div
            x-show="{{ $showCondition }}"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="pointer-events-auto w-screen {{ $maxWidthClass }}"
        >
            <div {{ $attributes->class([
                'flex h-full flex-col overflow-hidden bg-white shadow-2xl border-l border-slate-200/90 text-left transition-all',
            ]) }}>
                {{-- Header --}}
                @if ($title || $closable || isset($header))
                    <div class="shrink-0 flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/80 backdrop-blur-xs">
                        @if (isset($header))
                            {{ $header }}
                        @else
                            <div>
                                <h2 id="{{ $name }}-title" class="text-base font-bold text-slate-900 tracking-tight">
                                    {{ $title }}
                                </h2>
                                @if ($description)
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $description }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if ($closable)
                            <button
                                type="button"
                                @click="{{ $closeAction }}"
                                class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer"
                                aria-label="{{ $closeTitle }}"
                                title="{{ $closeTitle }}"
                            >
                                <x-lucide-x class="size-5" />
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Body Content --}}
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                    {{ $slot }}
                </div>

                {{-- Footer / Actions --}}
                @if (isset($footer))
                    <div class="shrink-0 border-t border-slate-100 bg-slate-50/90 backdrop-blur-xs px-6 py-3.5 flex items-center justify-between gap-3">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
