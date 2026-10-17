{{-- ============================================================
    BANNER SAPAAN STAFF & QUICK ACTION KASIR
============================================================= --}}
<div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                <span class="size-2 rounded-full bg-brand-600 animate-pulse"></span>
                Staff Operasional & Kasir
            </span>
            <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Selamat Datang, {{ auth()->user()->name }}!
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Kelola penerimaan laundry, timbangan cucian, pencatatan transaksi, dan pembaruan status pengerjaan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('staff.transactions.create') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98 cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Transaksi Baru</span>
            </a>
            <a
                href="{{ route('staff.customers.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98 cursor-pointer"
            >
                <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                </svg>
                <span>Data Pelanggan</span>
            </a>
        </div>
    </div>
</div>
