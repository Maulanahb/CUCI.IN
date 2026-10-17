{{-- ============================================================
    BANNER SELAMAT DATANG & QUICK ACTIONS PELANGGAN
============================================================= --}}
<div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/40 p-6 shadow-xs">
    <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                    <span class="size-1.5 rounded-full bg-brand-600 animate-pulse"></span>
                    Pelanggan CUCI.IN
                </span>
                @if ($stats['active_count'] > 0)
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 border border-amber-200/60 tabular-nums">
                        {{ $stats['active_count'] }} Cucian Sedang Diproses
                    </span>
                @endif
            </div>
            <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Selamat Datang, {{ auth()->user()->name }}!
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm max-w-xl">
                Pantau status pengerjaan pakaian laundry Anda secara langsung serta cek riwayat transaksi dan rincian nota kapan saja.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a
                href="{{ route('customer.transactions.index') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 cursor-pointer"
            >
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Semua Riwayat Transaksi</span>
            </a>
            <a
                href="{{ route('transactions.lookup') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 cursor-pointer"
            >
                <svg class="size-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Cek Kode Nota Fisik</span>
            </a>
        </div>
    </div>
</div>
