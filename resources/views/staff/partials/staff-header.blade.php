{{-- ============================================================
    STAFF / KASIR OPERATIONAL ACTION BAR (ANTI-SLOP & BESPOKE CRAFT)
    Ringkas, berorientasi tugas kasir langsung tanpa dekorasi berlebih
============================================================= --}}
<div class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Kasir & Penerimaan POS
            </h1>
            <span class="rounded-md bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-700 border border-brand-200/60">
                Shift: {{ auth()->user()->name }}
            </span>
        </div>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
            Input penerimaan laundry baru, cetak struk kasir, dan update status cucian pelanggan.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <a
            href="{{ route('staff.transactions.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98 cursor-pointer"
        >
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Transaksi Baru POS</span>
        </a>
        <a
            href="{{ route('staff.customers.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98 cursor-pointer"
        >
            <svg class="size-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
            <span>Data Pelanggan</span>
        </a>
    </div>
</div>
