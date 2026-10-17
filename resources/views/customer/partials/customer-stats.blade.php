{{-- ============================================================
    CUSTOMER METRICS SUMMARY (ANTI-SLOP & BESPOKE CRAFT)
    Tanpa icon-box template, fokus pada cucian aktif & tagihan riil
============================================================= --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    {{-- Kartu 1: Cucian Berjalan --}}
    <div @class([
        'relative overflow-hidden rounded-xl border p-5 transition shadow-xs',
        'border-brand-200/90 bg-brand-50/40' => ($stats['active_count'] ?? 0) > 0,
        'border-slate-200/90 bg-white' => ($stats['active_count'] ?? 0) === 0,
    ])>
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="text-slate-700">Cucian Berjalan</span>
            @if (($stats['active_count'] ?? 0) > 0)
                <span class="flex size-2 rounded-full bg-brand-600"></span>
            @endif
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                {{ $stats['active_count'] ?? 0 }}
            </p>
            <span class="text-xs font-semibold text-slate-600">Nota Aktif</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            {{ ($stats['active_count'] ?? 0) > 0 ? 'Sedang dikerjakan di workshop laundry' : 'Tidak ada cucian yang sedang diproses' }}
        </p>
    </div>

    {{-- Kartu 2: Menunggu Pelunasan --}}
    <div @class([
        'relative overflow-hidden rounded-xl border p-5 transition shadow-xs',
        'border-amber-200/90 bg-amber-50/40' => ($stats['unpaid_amount'] ?? 0) > 0,
        'border-slate-200/90 bg-white' => ($stats['unpaid_amount'] ?? 0) === 0,
    ])>
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="text-slate-700">Tagihan Belum Lunas</span>
            @if (($stats['unpaid_amount'] ?? 0) > 0)
                <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">Perlu Bayar</span>
            @endif
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                Rp {{ number_format($stats['unpaid_amount'] ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            {{ ($stats['unpaid_amount'] ?? 0) > 0 ? 'Dapat dilunasi saat serah terima pakaian' : 'Semua nota cucian sudah lunas' }}
        </p>
    </div>

    {{-- Kartu 3: Total Transaksi --}}
    <a
        href="{{ route('customer.transactions.index') }}"
        class="group relative overflow-hidden rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-slate-300 hover:shadow-sm cursor-pointer"
    >
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
            <span class="group-hover:text-slate-900 transition">Riwayat Transaksi</span>
            <span class="font-mono text-[11px] text-slate-400 transition group-hover:translate-x-0.5">&rarr;</span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                {{ $stats['total_count'] ?? 0 }}
            </p>
            <span class="text-xs font-medium text-slate-500">Total Nota</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Seluruh riwayat cuci sejak awal</p>
    </a>
</div>
