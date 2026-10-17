{{-- ============================================================
    STATISTIK RINGKASAN UTAMA (4 METRIC CARDS)
============================================================= --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Total Pengguna --}}
    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500">Total Pengguna</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums">{{ number_format($stats['total_users'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-500">Admin, Staff & Customer</p>
    </div>

    {{-- Total Pelanggan --}}
    <a href="{{ route('admin.customers.index') }}" class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:border-brand-300 hover:shadow-md cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 transition group-hover:text-brand-600">Total Pelanggan</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:scale-110">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums">{{ number_format($stats['total_customers'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600">
            <span>Data customer laundry</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>

    {{-- Layanan Aktif --}}
    <a href="{{ route('admin.services.index') }}" class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:border-brand-300 hover:shadow-md cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 transition group-hover:text-brand-600">Layanan Aktif</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:scale-110">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums">{{ number_format($stats['total_services'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-brand-600">
            <span>Kelola master layanan</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>

    {{-- Total Transaksi --}}
    <a href="{{ route('admin.transactions.index') }}" class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:border-brand-300 hover:shadow-md cursor-pointer">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 transition group-hover:text-brand-600">Total Transaksi</span>
            <div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:scale-110">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums">{{ number_format($stats['total_transactions'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500 transition group-hover:text-brand-600">
            <span>Riwayat transaksi</span>
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </p>
    </a>
</div>
