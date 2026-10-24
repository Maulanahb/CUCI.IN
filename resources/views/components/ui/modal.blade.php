@props([
    'name' => null,
    'show' => null,
    'title' => null,
    'description' => null,
    'maxWidth' => 'lg',
    'closable' => true,
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        default => 'sm:max-w-lg',
    };

    $showCondition = $show ?? ($name ? "openModal === '{$name}'" : 'open');
    $closeAction = $name ? "openModal = null" : ($show ? "{$show} = false" : 'open = false');
@endphp

<div
    x-cloak
    x-show="{{ $showCondition }}"
    @keydown.escape.window="@if($closable) {{ $closeAction }} @endif"
    class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop with subtle blur --}}
    <div
        x-show="{{ $showCondition }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @if($closable) @click="{{ $closeAction }}" @endif
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- Dialog Card --}}
    <div
        x-show="{{ $showCondition }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        {{ $attributes->class([
            'relative w-full rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 text-left shadow-lg transition-all',
            $maxWidthClass,
        ]) }}
    >
        {{-- Header: Title, Description, and Close Button --}}
        @if ($title || $closable)
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="space-y-1">
                    @if ($title)
                        <h3 class="text-base sm:text-lg font-bold tracking-tight text-slate-900">
                            {{ $title }}
                        </h3>
                    @endif
                    @if ($description)
                        <p class="text-xs text-slate-500 leading-relaxed">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                @if ($closable)
                    <button
                        type="button"
                        @click="{{ $closeAction }}"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition min-h-[36px] min-w-[36px] flex items-center justify-center cursor-pointer"
                        aria-label="Tutup dialog"
                    >
                        <x-lucide-x class="size-4" />
                    </button>
                @endif
            </div>
        @endif

        {{-- Dialog Body --}}
        <div class="{{ $title ? 'pt-4' : '' }}">
            {{ $slot }}
        </div>

        {{-- Dialog Footer (Optional Slot) --}}
        @isset($footer)
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5 pt-4 border-t border-slate-100">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
