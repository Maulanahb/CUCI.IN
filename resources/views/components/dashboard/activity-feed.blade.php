@props([
    'activities',
    'prefix' => null,
])

@php
    $prefix = $prefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $transactionsRoute = "{$prefix}.transactions.index";
@endphp

<x-ui.card {{ $attributes->class(['flex flex-col']) }}>
    <div class="mb-4 flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2">
            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <h3 class="text-sm font-bold text-slate-900">Aktivitas Cucian</h3>
                    @if ($activities->isNotEmpty())
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700">
                            {{ $activities->count() }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500">Pembaruan status terbaru oleh tim.</p>
            </div>
        </div>
        <a href="{{ route($transactionsRoute) }}" class="shrink-0 text-xs font-semibold text-brand-600 hover:text-brand-700">
            Transaksi &rarr;
        </a>
    </div>

    @if ($activities->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
            <div class="flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-3 text-xs font-semibold text-slate-700">Belum Ada Aktivitas Status</p>
            <p class="mt-1 max-w-xs text-xs text-slate-500">Pembaruan pengerjaan cucian oleh staf akan tercatat di sini.</p>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($activities as $activity)
                @php
                    $actor = $activity->changedBy?->name;
                    $initials = collect(preg_split('/\s+/u', trim((string) $actor)))
                        ->filter()
                        ->take(2)
                        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('');
                    $transaction = $activity->transaction;
                @endphp

                <li class="flex gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/40 p-3 transition-colors hover:border-brand-200">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700">
                        @if ($actor)
                            <span class="text-xs font-bold">{{ $initials }}</span>
                        @else
                            <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="min-w-0 text-xs text-slate-600">
                                <span class="font-bold text-slate-900">{{ $actor ?? 'Sistem' }}</span>
                                <span>mengubah status</span>
                            </p>
                            <time
                                class="shrink-0 text-xs text-slate-500"
                                datetime="{{ $activity->changed_at?->toIso8601String() }}"
                                title="{{ $activity->changed_at?->locale('id')->translatedFormat('d F Y, H:i') }}"
                            >
                                {{ $activity->changed_at?->locale('id')->diffForHumans() ?? '-' }}
                            </time>
                        </div>

                        <p class="mt-0.5 flex min-w-0 flex-wrap items-center gap-1 text-xs text-slate-500">
                            @if ($transaction)
                                <a href="{{ route("{$prefix}.transactions.show", $transaction) }}" class="font-mono font-bold text-brand-700 hover:underline">
                                    {{ $transaction->transaction_code }}
                                </a>
                            @else
                                <span class="font-mono font-bold text-slate-700">-</span>
                            @endif

                            @if ($transaction?->customer?->name)
                                <span class="text-slate-300">&bull;</span>
                                <span class="truncate">{{ $transaction->customer->name }}</span>
                            @endif
                        </p>

                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            @if ($activity->previous_status)
                                <x-ui.status-badge :status="$activity->previous_status" size="sm" />
                                <svg class="size-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            @else
                                <span class="text-xs font-medium text-slate-500">Status awal</span>
                            @endif

                            <x-ui.status-badge :status="$activity->status" size="sm" />
                        </div>

                        @if ($activity->notes)
                            <p class="mt-1 line-clamp-2 text-xs italic text-slate-500" title="{{ $activity->notes }}">
                                "{{ $activity->notes }}"
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
