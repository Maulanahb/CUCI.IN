@props([
    'steps' => ['Diterima', 'Diproses', 'Selesai', 'Diambil'],
    'current' => 1,
    'isCanceled' => false,
])

@php
    $totalSteps = count($steps);
    $progressPercent = $totalSteps > 1
        ? max(0, min(100, (($current - 1) / ($totalSteps - 1)) * 100))
        : 0;
@endphp

@if (! $isCanceled)
    <div {{ $attributes->class(['w-full py-2']) }}>
        <div class="relative">
            {{-- Connecting Progress Line --}}
            <div class="absolute top-4 left-[12.5%] right-[12.5%] h-1 rounded-full bg-slate-200 -z-0">
                <div
                    class="h-full rounded-full bg-brand-700 transition-all duration-500 ease-out"
                    style="width: {{ $progressPercent }}%;"
                ></div>
            </div>

            {{-- Steps Grid --}}
            <div class="grid relative z-10" style="grid-template-columns: repeat({{ $totalSteps }}, minmax(0, 1fr));">
                @foreach ($steps as $index => $step)
                    @php
                        $stepNumber = $index + 1;
                        $isCompleted = $stepNumber < $current;
                        $isCurrent = $stepNumber === (int) $current;
                        $isUpcoming = $stepNumber > $current;
                    @endphp

                    <div class="flex flex-col items-center text-center px-1">
                        {{-- Step Circle Indicator --}}
                        <div
                            @class([
                                'size-8 sm:size-9 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold transition-all duration-300',
                                'bg-brand-700 text-white shadow-xs' => $isCompleted,
                                'bg-brand-700 text-white ring-4 ring-brand-100/90 font-extrabold shadow-sm scale-105' => $isCurrent,
                                'bg-white text-slate-500 border-2 border-slate-200' => $isUpcoming,
                            ])
                        >
                            @if ($isCompleted)
                                <x-lucide-check class="size-4 stroke-[2.5]" />
                            @else
                                <span>{{ $stepNumber }}</span>
                            @endif
                        </div>

                        {{-- Step Label --}}
                        <span
                            @class([
                                'mt-2 text-xs font-semibold leading-tight text-center transition-colors',
                                'text-brand-900 font-extrabold' => $isCurrent,
                                'text-slate-800 font-bold' => $isCompleted,
                                'text-slate-500 font-medium' => $isUpcoming,
                            ])
                        >
                            {{ $step }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->class(['w-full rounded-xl bg-rose-50 border border-rose-200/80 p-3.5 text-center text-xs font-semibold text-rose-700 flex items-center justify-center gap-2']) }}>
        <x-lucide-alert-circle class="size-4 shrink-0 text-rose-600" />
        <span>Pesanan cucian ini telah berstatus dibatalkan.</span>
    </div>
@endif
