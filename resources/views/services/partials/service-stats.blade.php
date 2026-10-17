{{-- ============================================================
    KPI STATISTIC CARDS (Aqua Teal + Semantic)
============================================================= --}}
<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
    {{-- Card 1: Total Layanan --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs transition hover:border-slate-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500">Total Paket</span>
            <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl tabular-nums">{{ $stats['total'] }}</p>
        <p class="mt-0.5 text-[11px] text-slate-500">Seluruh jenis layanan</p>
    </div>

    {{-- Card 2: Layanan Aktif --}}
    <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-xs transition hover:border-emerald-200">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500">Layanan Aktif</span>
            <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <span class="relative flex size-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                </span>
            </div>
        </div>
        <p class="mt-2 text-xl font-bold tracking-tight text-emerald-600 sm:text-2xl tabular-nums">{{ $stats['active'] }}</p>
        <p class="mt-0.5 text-[11px] text-slate-500">Siap dipilih di kasir</p>
    </div>

    {{-- Card 3: Layanan Nonaktif --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs transition hover:border-slate-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500">Nonaktif / Tutup</span>
            <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-xl font-bold tracking-tight text-slate-600 sm:text-2xl tabular-nums">{{ $stats['inactive'] }}</p>
        <p class="mt-0.5 text-[11px] text-slate-500">Layanan diarsipkan</p>
    </div>

    {{-- Card 4: Rata-rata Tarif --}}
    <div class="rounded-2xl border border-brand-100 bg-brand-50/40 p-4 shadow-xs transition hover:border-brand-200">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-brand-900">Rata-rata Tarif</span>
            <div class="flex size-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-lg font-bold tracking-tight text-brand-700 sm:text-xl tabular-nums">
            Rp {{ number_format($stats['avg_price'], 0, ',', '.') }}
        </p>
        <p class="mt-0.5 text-[11px] text-brand-700 font-medium">
            Durasi tercepat: {{ $stats['min_duration'] > 0 ? $stats['min_duration'] . ' jam' : '-' }}
        </p>
    </div>
</div>
