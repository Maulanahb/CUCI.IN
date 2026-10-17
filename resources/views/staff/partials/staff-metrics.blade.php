{{-- ============================================================
    METRIK OPERASIONAL HARI INI (4 CARDS)
============================================================= --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Transaksi Hari Ini --}}
    <a
        href="{{ route('staff.transactions.index') }}"
        class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md cursor-pointer"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Transaksi Hari Ini</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:scale-105">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold text-slate-900 tabular-nums">{{ $stats['transactions_today'] }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-brand-600">
            <span>Dibuat hari ini</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>

    {{-- Pelanggan Terdaftar --}}
    <a
        href="{{ route('staff.customers.index') }}"
        class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md cursor-pointer"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Pelanggan Terdaftar</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:scale-105">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold text-slate-900 tabular-nums">{{ $stats['total_customers'] }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600">
            <span>Data customer laundry</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>

    {{-- Layanan Aktif --}}
    <a
        href="{{ route('staff.services.index') }}"
        class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md cursor-pointer"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Layanan Tersedia</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:scale-105">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold text-slate-900 tabular-nums">{{ $stats['active_services'] }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-indigo-600">
            <span>Katalog layanan</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>

    {{-- Siap Diambil Pelanggan --}}
    <a
        href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
        class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md cursor-pointer"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Siap Diambil</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600 transition group-hover:scale-105">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold text-slate-900 tabular-nums">{{ $statusCounts['selesai'] ?? 0 }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-teal-600">
            <span>Cucian selesai</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>
</div>
