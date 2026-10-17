{{-- ============================================================
    ADMINISTRATOR OPERATIONAL HEADER (ANTI-SLOP & BESPOKE CRAFT)
    Tanpa kicker/eyebrow terlarang, tanpa pulse dot palsu, hemat vertikal
============================================================= --}}
<div class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Panel Administrator
            </h1>
            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                Workshop Pusat
            </span>
        </div>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
            Kendali operasional laundry, pemantauan transaksi real-time, dan manajemen katalog layanan.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <a
            href="{{ route('admin.transactions.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98 cursor-pointer"
        >
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Transaksi Baru POS</span>
        </a>
        <a
            href="{{ route('admin.services.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98 cursor-pointer"
        >
            <svg class="size-4 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
            </svg>
            <span>Kelola Layanan</span>
        </a>
    </div>
</div>
