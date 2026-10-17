{{-- ============================================================
    CUSTOMER DASHBOARD HEADER (ANTI-SLOP & BESPOKE CRAFT)
    Tanpa kicker/eyebrow terlarang, fokus langsung pada status cucian pelanggan
============================================================= --}}
<div class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Status Cucian Saya
            </h1>
            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                Pelanggan CUCI.IN
            </span>
            @if (($stats['active_count'] ?? 0) > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 border border-amber-200 tabular-nums">
                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                    {{ $stats['active_count'] }} Cucian Sedang Diproses
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                    Tidak ada antrean cucian aktif
                </span>
            @endif
        </div>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
            Halo, <span class="font-semibold text-slate-700">{{ auth()->user()->name }}</span>. Pantau tahapan cuci pakaian Anda dan cek rincian nota kapan saja.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <a
            href="{{ route('customer.transactions.index') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98 cursor-pointer"
        >
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Semua Riwayat Transaksi</span>
        </a>
        <a
            href="{{ route('transactions.lookup') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98 cursor-pointer"
        >
            <svg class="size-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Lacak Nota Fisik</span>
        </a>
    </div>
</div>
