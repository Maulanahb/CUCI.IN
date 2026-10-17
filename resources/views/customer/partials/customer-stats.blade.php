{{-- ============================================================
    KARTU RINGKASAN STATISTIK PELANGGAN
============================================================= --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    {{-- Kartu 1: Cucian Berjalan --}}
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cucian Berjalan</p>
            <div class="flex size-9 items-center justify-center rounded-lg bg-sky-50 text-sky-700 shrink-0">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-2xl font-extrabold text-brand-700 tracking-tight tabular-nums">{{ $stats['active_count'] }}</p>
            <span class="text-xs text-slate-600 font-medium">Nota Aktif</span>
        </div>
        <p class="mt-1 text-xs text-slate-500">Sedang diproses di workshop</p>
    </div>

    {{-- Kartu 2: Menunggu Pelunasan --}}
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Menunggu Pelunasan</p>
            <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700 shrink-0">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <p class="text-2xl font-extrabold {{ $stats['unpaid_amount'] > 0 ? 'text-amber-800' : 'text-slate-800' }} tracking-tight tabular-nums">
                Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}
            </p>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            {{ $stats['unpaid_amount'] > 0 ? 'Dapat dilunasi saat pengambilan' : 'Semua tagihan lunas' }}
        </p>
    </div>

    {{-- Kartu 3: Total Transaksi --}}
    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Riwayat Transaksi</p>
            <div class="flex size-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 shrink-0">
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-2xl font-extrabold text-slate-900 tracking-tight tabular-nums">{{ $stats['total_count'] }}</p>
            <span class="text-xs text-slate-600 font-medium">Nota Laundry</span>
        </div>
        <p class="mt-1 text-xs text-slate-500">Riwayat sejak akun terdaftar</p>
    </div>
</div>
