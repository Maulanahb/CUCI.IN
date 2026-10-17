{{-- ============================================================
    STAFF / KASIR METRICS (HIERARCHICAL & BESPOKE CRAFT)
    Tanpa icon-box template, fokus pada beban kerja kasir hari ini
============================================================= --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Transaksi Hari Ini (Hero Metric Kasir) --}}
    <a
        href="{{ route('staff.transactions.index') }}"
        class="group relative overflow-hidden rounded-xl border border-brand-200/90 bg-brand-50/30 p-5 shadow-xs transition hover:border-brand-400 hover:bg-brand-50/60 cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-brand-900">
            <span>Transaksi Hari Ini</span>
            <span class="font-mono text-[11px] text-brand-600 transition group-hover:translate-x-0.5">&rarr;</span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-brand-950 tabular-nums">
                {{ number_format($stats['transactions_today'] ?? 0, 0, ',', '.') }}
            </p>
            <span class="text-xs font-semibold text-brand-700">Nota Masuk</span>
        </div>
        <p class="mt-2 text-xs text-brand-800/80">Aktivitas penerimaan shift ini</p>
    </a>

    {{-- Siap Diambil (Actionable Alert) --}}
    <a
        href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
        class="group relative overflow-hidden rounded-xl border border-emerald-200/90 bg-emerald-50/30 p-5 shadow-xs transition hover:border-emerald-400 hover:bg-emerald-50/60 cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-emerald-900">
            <span>Siap Diambil di Rak</span>
            <span class="font-mono text-[11px] text-emerald-600 transition group-hover:translate-x-0.5">&rarr;</span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-emerald-950 tabular-nums">
                {{ number_format($statusCounts['selesai'] ?? 0, 0, ',', '.') }}
            </p>
            <span class="text-xs font-semibold text-emerald-700">Cucian Siap</span>
        </div>
        <p class="mt-2 text-xs text-emerald-800/80">Menunggu serah terima / pelunasan</p>
    </a>

    {{-- Pelanggan Terdaftar --}}
    <a
        href="{{ route('staff.customers.index') }}"
        class="group relative overflow-hidden rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-slate-300 hover:shadow-sm cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="group-hover:text-slate-900 transition">Pelanggan Terdaftar</span>
            <span class="font-mono text-[11px] text-slate-400 transition group-hover:translate-x-0.5">&rarr;</span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                {{ number_format($stats['total_customers'] ?? 0, 0, ',', '.') }}
            </p>
            <span class="text-xs font-medium text-slate-500">Member</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Buku kontak & riwayat pelanggan</p>
    </a>

    {{-- Layanan Aktif --}}
    <a
        href="{{ route('staff.services.index') }}"
        class="group relative overflow-hidden rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-slate-300 hover:shadow-sm cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="group-hover:text-slate-900 transition">Katalog Layanan</span>
            <span class="font-mono text-[11px] text-slate-400 transition group-hover:translate-x-0.5">&rarr;</span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                {{ number_format($stats['active_services'] ?? 0, 0, ',', '.') }}
            </p>
            <span class="text-xs font-medium text-slate-500">Paket Aktif</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Daftar tarif cuci kiloan & satuan</p>
    </a>
</div>
