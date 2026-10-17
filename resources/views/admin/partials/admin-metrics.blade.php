{{-- ============================================================
    ADMIN METRICS (HIERARCHICAL & BESPOKE CRAFT)
    Tanpa icon-box bingo, hierarki visual kuat, tipografi presisi
============================================================= --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Total Transaksi (Metric Utama) --}}
    <a
        href="{{ route('admin.transactions.index') }}"
        class="group relative overflow-hidden rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-sm cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="group-hover:text-brand-600 transition">Volume Transaksi</span>
            <span class="font-mono text-[11px] text-slate-400 group-hover:text-brand-600 transition">&rarr;</span>
        </div>
        <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
            {{ number_format($stats['total_transactions'] ?? 0, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
            <span class="size-1.5 rounded-full bg-brand-500"></span>
            <span>Total nota masuk tersimpan</span>
        </div>
    </a>

    {{-- Total Pelanggan --}}
    <a
        href="{{ route('admin.customers.index') }}"
        class="group relative overflow-hidden rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-emerald-300 hover:shadow-sm cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="group-hover:text-emerald-700 transition">Database Pelanggan</span>
            <span class="font-mono text-[11px] text-slate-400 group-hover:text-emerald-700 transition">&rarr;</span>
        </div>
        <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
            {{ number_format($stats['total_customers'] ?? 0, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>
            <span>Customer terdaftar di sistem</span>
        </div>
    </a>

    {{-- Layanan Aktif --}}
    <a
        href="{{ route('admin.services.index') }}"
        class="group relative overflow-hidden rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-cyan-300 hover:shadow-sm cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="group-hover:text-cyan-700 transition">Katalog Layanan</span>
            <span class="font-mono text-[11px] text-slate-400 group-hover:text-cyan-700 transition">&rarr;</span>
        </div>
        <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
            {{ number_format($stats['total_services'] ?? 0, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
            <span class="size-1.5 rounded-full bg-cyan-500"></span>
            <span>Paket aktif siap dipilih kasir</span>
        </div>
    </a>

    {{-- Total Akun Pengguna (Restrained Secondary Card) --}}
    <div class="relative overflow-hidden rounded-xl border border-slate-200/70 bg-slate-50/70 p-5">
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span>Akun Pengguna</span>
            <span class="text-[10px] uppercase font-bold text-slate-400">Master</span>
        </div>
        <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-800 tabular-nums">
            {{ number_format($stats['total_users'] ?? 0, 0, ',', '.') }}
        </p>
        <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
            <span class="size-1.5 rounded-full bg-slate-400"></span>
            <span>Admin, Staff & Customer</span>
        </div>
    </div>
</div>
