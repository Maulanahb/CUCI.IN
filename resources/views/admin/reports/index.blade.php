<x-layouts::app title="Laporan Operasional & Keuangan">
    <div class="space-y-6">
        {{-- Header & Aksi Cepat --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600 transition">Dashboard</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700 font-semibold">Laporan Operasional</span>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Laporan Operasional & Keuangan
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Rekapitulasi performa transaksi, omzet kasir, piutang, dan unduh dokumen siap cetak.
                </p>
            </div>

            {{-- Kelompok Tombol Ekspor Multi-Format --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Tombol Ekspor PDF Ringkas (DomPDF) --}}
                <a
                    href="{{ route('admin.reports.export.pdf', request()->query()) }}"
                    target="_blank"
                    id="btn-export-pdf"
                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 shadow-2xs transition hover:bg-rose-100 hover:border-rose-300 active:scale-95"
                    title="Cetak atau Unduh Dokumen Ringkasan PDF (DomPDF)"
                >
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h1m-1 4h6m-6 4h6" />
                    </svg>
                    <span>Cetak PDF</span>
                </a>

                {{-- Tombol Ekspor Spreadsheet Excel (.xls) --}}
                <a
                    href="{{ route('admin.reports.export.excel', request()->query()) }}"
                    id="btn-export-excel"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-700 shadow-2xs transition hover:bg-emerald-100 hover:border-emerald-300 active:scale-95"
                    title="Unduh Rekap Spreadsheet Excel (.xls)"
                >
                    <svg class="size-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Ekspor Excel (.xls)</span>
                </a>

                {{-- Tombol Ekspor CSV Streaming (Dataset Besar Memory-Safe P9) --}}
                <a
                    href="{{ route('admin.reports.export.csv', request()->query()) }}"
                    id="btn-export-csv"
                    class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3.5 py-2 text-xs font-bold text-brand-700 shadow-2xs transition hover:bg-brand-100 hover:border-brand-300 active:scale-95"
                    title="Aliran CSV Cepat Hemat Memori untuk Dataset Besar (Materi P9)"
                >
                    <svg class="size-4 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>CSV Streaming</span>
                </a>
            </div>
        </div>

        {{-- Kartu Metrik Ringkasan Kinerja (Agregasi SQL PostgreSQL/MySQL) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Omzet Masuk (Pendapatan Terverifikasi) --}}
            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50/50 to-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Omzet Masuk</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Pembayaran lunas & terverifikasi
                </p>
            </div>

            {{-- Total Tagihan Transaksi --}}
            <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50/50 to-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand-700">Nilai Tagihan Bruto</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-100 text-brand-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">
                    Rp {{ number_format($summary['total_billed'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Akumulasi tagihan {{ number_format($summary['total_transactions']) }} transaksi
                </p>
            </div>

            {{-- Piutang Belum Lunas --}}
            <div class="relative overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50/50 to-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Piutang Berjalan</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">
                    Rp {{ number_format($summary['total_receivables'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Sisa belum lunas / menunggu kasir
                </p>
            </div>

            {{-- Rata-rata Nilai Nota (Average Ticket) --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-600">Rerata Per Nota</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900">
                    Rp {{ number_format($summary['average_ticket'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Rerata omzet per pesanan cucian
                </p>
            </div>
        </div>

        {{-- Panel Filter Lanjutan (Tanggal, Status Pengerjaan, Status Bayar) --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs" x-data="{
            setDateRange(type) {
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

                let start = new Date();
                let end = new Date();

                if (type === 'today') {
                    // hari ini
                } else if (type === '7days') {
                    start.setDate(now.getDate() - 6);
                } else if (type === 'this_month') {
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                } else if (type === 'last_month') {
                    start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    end = new Date(now.getFullYear(), now.getMonth(), 0);
                }

                document.getElementById('input_start_date').value = fmt(start);
                document.getElementById('input_end_date').value = fmt(end);
            }
        }">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="space-y-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    {{-- Input Rentang Tanggal --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:w-2/5">
                        <div>
                            <label for="input_start_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Dari Tanggal
                            </label>
                            <input
                                type="date"
                                id="input_start_date"
                                name="start_date"
                                value="{{ $filters['start_date']->format('Y-m-d') }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                            >
                        </div>
                        <div>
                            <label for="input_end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Sampai Tanggal
                            </label>
                            <input
                                type="date"
                                id="input_end_date"
                                name="end_date"
                                value="{{ $filters['end_date']->format('Y-m-d') }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                            >
                        </div>
                    </div>

                    {{-- Filter Status Cucian & Status Pembayaran --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:w-3/5">
                        <div>
                            <label for="filter_status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Status Cucian
                            </label>
                            <select
                                id="filter_status"
                                name="status"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                            >
                                <option value="all">Semua Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>
                                        {{ $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter_payment" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Status Bayar
                            </label>
                            <select
                                id="filter_payment"
                                name="payment_status"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                            >
                                <option value="all">Semua Status</option>
                                <option value="Lunas" @selected($filters['payment_status'] === 'Lunas')>Lunas</option>
                                <option value="DP" @selected($filters['payment_status'] === 'DP')>DP (Sebagian)</option>
                                <option value="Belum Bayar" @selected($filters['payment_status'] === 'Belum Bayar')>Belum Bayar</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter_search" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Cari Nota / Nama
                            </label>
                            <input
                                type="text"
                                id="filter_search"
                                name="search"
                                value="{{ $filters['search'] }}"
                                placeholder="TRX-XXXX / Nama..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
                            >
                        </div>
                    </div>
                </div>

                {{-- Baris Shortcut Cepat & Tombol Aksi Filter --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-slate-500 font-medium mr-1">Preset Rentang:</span>
                        <button type="button" @click="setDateRange('today')" class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-600 hover:bg-slate-100 font-medium">Hari Ini</button>
                        <button type="button" @click="setDateRange('7days')" class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-600 hover:bg-slate-100 font-medium">7 Hari Terakhir</button>
                        <button type="button" @click="setDateRange('this_month')" class="rounded-lg border border-brand-200 bg-brand-50/50 px-2.5 py-1 text-brand-700 hover:bg-brand-100 font-semibold">Bulan Ini</button>
                        <button type="button" @click="setDateRange('last_month')" class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-600 hover:bg-slate-100 font-medium">Bulan Lalu</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('admin.reports.index') }}"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Reset
                        </a>
                        <button
                            type="submit"
                            id="btn-apply-filter"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-brand-700 transition active:scale-95"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Pratinjau Transaksi Operasional --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Pratinjau Data Transaksi Operasional
                    </h2>
                    <p class="text-xs text-slate-500">
                        Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari total {{ $transactions->total() }} pesanan.
                    </p>
                </div>

                {{-- Status Pembayaran Badge Info --}}
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="size-2 rounded-full bg-emerald-500"></span> Tunai: Rp {{ number_format($summary['payment_methods']['Tunai'], 0, ',', '.') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="size-2 rounded-full bg-cyan-500"></span> QRIS: Rp {{ number_format($summary['payment_methods']['QRIS'], 0, ',', '.') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="size-2 rounded-full bg-indigo-500"></span> Transfer: Rp {{ number_format($summary['payment_methods']['Transfer'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">No. Nota</th>
                            <th scope="col" class="px-4 py-3">Tanggal</th>
                            <th scope="col" class="px-4 py-3">Pelanggan</th>
                            <th scope="col" class="px-4 py-3">Layanan</th>
                            <th scope="col" class="px-4 py-3 text-center">Status Cucian</th>
                            <th scope="col" class="px-4 py-3 text-center">Status Bayar</th>
                            <th scope="col" class="px-5 py-3 text-right">Total Tagihan</th>
                            <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-slate-50/60 transition">
                                {{-- Kode Transaksi --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">
                                        {{ $tx->transaction_code }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Kasir: {{ $tx->creator?->name ?? 'Staf' }}
                                    </div>
                                </td>

                                {{-- Tanggal Masuk --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-800">
                                        {{ $tx->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $tx->created_at->format('H:i') }} WIB
                                    </div>
                                </td>

                                {{-- Pelanggan --}}
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">
                                        {{ $tx->customer?->name ?? 'Pelanggan Umum' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $tx->customer?->phone ?? '-' }}
                                    </div>
                                </td>

                                {{-- Layanan & Qty --}}
                                <td class="px-4 py-3.5">
                                    <div class="text-xs text-slate-800 max-w-xs truncate" title="{{ $tx->details->map(fn($d) => ($d->service?->name ?? 'Layanan').' ('.$d->weight.' '.($d->service?->unit ?? 'kg').')')->implode(', ') }}">
                                        @if ($tx->details->isNotEmpty())
                                            {{ $tx->details->first()->service?->name ?? 'Layanan' }}
                                            <span class="font-semibold text-brand-600">({{ $tx->details->first()->weight }} {{ $tx->details->first()->service?->unit ?? 'kg' }})</span>
                                            @if ($tx->details->count() > 1)
                                                <span class="text-slate-400 text-[11px]">+{{ $tx->details->count() - 1 }} lainnya</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Cucian --}}
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    @php
                                        $statusClass = match ($tx->status->value ?? (string) $tx->status) {
                                            'Diterima' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'Dicuci' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Dikeringkan' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                            'Disetrika' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Siap Diambil' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'Selesai' => 'bg-green-100 text-green-800 border-green-300 font-bold',
                                            'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $statusClass }}">
                                        {{ $tx->status->value ?? $tx->status }}
                                    </span>
                                </td>

                                {{-- Status Pembayaran --}}
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    @php
                                        $payClass = match ($tx->payment_status) {
                                            'Lunas' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'DP' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $payClass }}">
                                        {{ $tx->payment_status }}
                                    </span>
                                </td>

                                {{-- Total Tagihan --}}
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <div class="font-bold text-slate-900">
                                        Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                    </div>
                                    @if ($tx->remaining_amount > 0)
                                        <div class="text-[11px] text-rose-600 font-medium">
                                            Sisa: Rp {{ number_format($tx->remaining_amount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    <a
                                        href="{{ route('admin.transactions.show', $tx) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-800 transition"
                                        title="Buka rincian transaksi"
                                    >
                                        <span>Detail</span>
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-slate-800">Tidak ada transaksi ditemukan</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Coba ubah rentang tanggal atau bersihkan filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Navigasi --}}
            @if ($transactions->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
