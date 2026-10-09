<x-layouts::app title="Riwayat Transaksi">
    <div class="space-y-6" x-data="{
        selectedTrxCode: '{{ $selectedTransaction?->transaction_code }}',
        copied: false,
        copyCode(code) {
            navigator.clipboard.writeText(code);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }">
        {{-- Breadcrumb & Header Halaman --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between print:hidden">
            <div>
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1" aria-label="Breadcrumb">
                    <span class="text-brand-700 font-semibold">CUCI.IN</span>
                    <span class="text-slate-300">/</span>
                    <span>Portal Pelanggan</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700 font-medium">Riwayat Transaksi</span>
                </nav>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat Transaksi</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Lihat riwayat transaksi laundry Anda beserta rincian status pengerjaan dan pelunasan.
                </p>
            </div>

            {{-- Tombol Aksi Header --}}
            <div class="flex flex-wrap items-center gap-2.5">
                @if ($selectedTransaction)
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                    >
                        <svg class="w-4 h-4 text-slate-500 shrink-0" width="16" height="16" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Nota
                    </button>
                @endif
            </div>
        </div>

        {{-- 4 Kartu Statistik Ringkasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
            {{-- Kartu 1: Total Transaksi --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Transaksi</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 shrink-0" style="width: 36px; height: 36px;">
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_transactions'] }} Nota</p>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" width="14" height="14" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Sepanjang tahun {{ now()->year }}</span>
                </div>
            </div>

            {{-- Kartu 2: Cucian Berjalan --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Cucian Berjalan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-sky-50 text-sky-700 shrink-0" style="width: 36px; height: 36px;">
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-brand-700 tracking-tight">{{ $stats['active_orders'] }} Pesanan</p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                    <span class="inline-flex items-center gap-1">
                        <span class="size-1.5 rounded-full bg-brand-500" style="width: 6px; height: 6px;"></span>
                        {{ $stats['in_progress_count'] }} Diproses
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1">
                        <span class="size-1.5 rounded-full bg-emerald-500" style="width: 6px; height: 6px;"></span>
                        {{ $stats['ready_count'] }} Siap Diambil
                    </span>
                </div>
            </div>

            {{-- Kartu 3: Menunggu Pelunasan --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700/80">Menunggu Pelunasan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700 shrink-0" style="width: 36px; height: 36px;">
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-amber-900 tracking-tight">
                    Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-amber-700/90">
                    <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" width="14" height="14" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $stats['unpaid_count'] }} transaksi belum lunas (DP)</span>
                </div>
            </div>

            {{-- Kartu 4: Total Pengeluaran --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pengeluaran</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 shrink-0" style="width: 36px; height: 36px;">
                        <svg class="w-4 h-4 shrink-0" width="16" height="16" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">
                    Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" width="14" height="14" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Akumulasi layanan laundry</span>
                </div>
            </div>
        </div>

        {{-- Bar Pencarian & Filter --}}
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-xs print:hidden">
            <form method="GET" action="{{ route('customer.transactions.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" width="16" height="16" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode transaksi, paket layanan..."
                        style="padding-left: 2.75rem;"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-2 pr-3 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                {{-- Dropdown Filter & Reset --}}
                <div class="flex flex-wrap items-center gap-2.5 sm:pr-1">
                    {{-- Filter Status Cucian --}}
                    <select
                        name="status_cucian"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 shadow-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    >
                        <option value="">Semua Status Cucian</option>
                        @foreach (['Diterima', 'Diproses', 'Selesai', 'Diambil', 'Dibatalkan'] as $status)
                            <option value="{{ $status }}" @selected(request('status_cucian') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Filter Status Pembayaran --}}
                    <select
                        name="status_pembayaran"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 shadow-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    >
                        <option value="">Semua Pembayaran</option>
                        <option value="Lunas" @selected(request('status_pembayaran') === 'Lunas')>Lunas</option>
                        <option value="Belum Lunas" @selected(request('status_pembayaran') === 'Belum Lunas')>Belum Lunas</option>
                    </select>

                    {{-- Tombol Reset (Langsung di samping dropdown seperti pada foto) --}}
                    <a
                        href="{{ route('customer.transactions.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-600 shadow-xs transition hover:bg-slate-50 hover:text-slate-900"
                        title="Reset semua filter pencarian"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" width="14" height="14" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Konten Utama: 2 Kolom (Tabel Transaksi di Kiri & Rincian Dokumen di Kanan) --}}
        @if ($paginatedTransactions->isNotEmpty())
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
                {{-- Kolom Kiri: Tabel Transaksi (Lebar 8 dari 12 kolom) --}}
                <div class="lg:col-span-7 xl:col-span-8 space-y-4 print:hidden">
                    <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                                <thead class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th scope="col" class="py-3 px-3.5">Kode Transaksi</th>
                                        <th scope="col" class="py-3 px-3.5">Tanggal Masuk</th>
                                        <th scope="col" class="py-3 px-3.5">Layanan</th>
                                        <th scope="col" class="py-3 px-3.5">Total</th>
                                        <th scope="col" class="py-3 px-3.5">Dibayar</th>
                                        <th scope="col" class="py-3 px-3.5">Sisa Tagihan</th>
                                        <th scope="col" class="py-3 px-3.5">Status Pembayaran</th>
                                        <th scope="col" class="py-3 px-3.5">Status Cucian</th>
                                        <th scope="col" class="py-3 px-3.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($paginatedTransactions as $trx)
                                        @php
                                            $paidAmount = (float) $trx->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                                            $remainingAmount = max(0.0, (float) $trx->total_amount - $paidAmount);
                                            $isPaidOff = $remainingAmount <= 0.001;
                                            $hasDp = ! $isPaidOff && $paidAmount > 0;
                                            $servicesText = $trx->details->map(fn($d) => $d->service?->name)->filter()->join(', ');
                                            $totalWeight = $trx->details->sum('weight');
                                            $isSelected = $selectedTransaction && $selectedTransaction->id === $trx->id;
                                        @endphp
                                        <tr
                                            class="transition-colors hover:bg-brand-50/40 {{ $isSelected ? 'bg-brand-50/60 font-medium' : '' }}"
                                        >
                                            {{-- Kode Transaksi --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                                <a
                                                    href="{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}"
                                                    class="inline-flex items-center gap-1.5 font-bold text-brand-700 hover:text-brand-900 transition"
                                                >
                                                    @if (in_array($trx->status, ['Diterima', 'Diproses', 'Selesai']))
                                                        <span class="size-2 rounded-full bg-emerald-500 ring-2 ring-emerald-200" style="width: 8px; height: 8px;"></span>
                                                    @endif
                                                    <span>{{ $trx->transaction_code }}</span>
                                                </a>
                                            </td>

                                            {{-- Tanggal Masuk --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap text-xs text-slate-500">
                                                {{ $trx->created_at->translatedFormat('d M Y, H:i') }} WIB
                                            </td>

                                            {{-- Layanan --}}
                                            <td class="py-3.5 px-3.5">
                                                <div class="max-w-[180px]">
                                                    <p class="font-semibold text-slate-800 truncate" title="{{ $servicesText ?: 'Layanan Laundry' }}">
                                                        {{ $servicesText ?: 'Layanan Laundry' }}
                                                    </p>
                                                    @if ($totalWeight > 0)
                                                        <p class="text-[11px] text-slate-400">
                                                            Berat {{ number_format($totalWeight, 1) }} kg
                                                        </p>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Total Tagihan --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap font-bold text-slate-900">
                                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                            </td>

                                            {{-- Sudah Dibayar --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                                <p class="font-semibold text-slate-800">
                                                    Rp {{ number_format($paidAmount, 0, ',', '.') }}
                                                </p>
                                                @if ($hasDp)
                                                    <p class="text-[11px] font-medium text-brand-600">(DP Kasir)</p>
                                                @endif
                                            </td>

                                            {{-- Sisa Tagihan --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                                @if ($remainingAmount > 0)
                                                    <span class="font-bold text-amber-800">
                                                        Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400">Rp 0</span>
                                                @endif
                                            </td>

                                            {{-- Status Pembayaran --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                                @if ($isPaidOff)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                                        <svg class="w-3 h-3 shrink-0" width="12" height="12" style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Lunas
                                                    </span>
                                                @elseif ($hasDp)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                                        <span class="size-1.5 rounded-full bg-amber-500" style="width: 6px; height: 6px;"></span>
                                                        Belum Lunas
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                                        <span class="size-1.5 rounded-full bg-rose-500" style="width: 6px; height: 6px;"></span>
                                                        Belum Bayar
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Status Cucian --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                                @php
                                                    $statusClasses = match ($trx->status) {
                                                        'Diterima', 'Diproses' => 'bg-brand-50 border-brand-200 text-brand-700',
                                                        'Selesai' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                                                        'Diambil' => 'bg-slate-100 border-slate-200 text-slate-700',
                                                        'Dibatalkan' => 'bg-rose-50 border-rose-200 text-rose-700',
                                                        default => 'bg-slate-50 border-slate-200 text-slate-700',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                                    <span class="size-1.5 rounded-full bg-current" style="width: 6px; height: 6px;"></span>
                                                    {{ $trx->status }}
                                                </span>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="py-3.5 px-3.5 whitespace-nowrap text-center">
                                                <a
                                                    href="{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}"
                                                    class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-2xs transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                                                    style="width: 32px; height: 32px;"
                                                    title="Lihat Rincian"
                                                >
                                                    <svg class="w-4 h-4 shrink-0" width="16" height="16" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="border-t border-slate-100 px-4 py-3 sm:px-6">
                            {{ $paginatedTransactions->links() }}
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Rincian Dokumen Transaksi (Lebar 5 dari 12 kolom) --}}
                <div class="lg:col-span-5 xl:col-span-4 print:w-full print:block">
                    @if ($selectedTransaction)
                        @php
                            $selPaid = (float) $selectedTransaction->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                            $selRemaining = max(0.0, (float) $selectedTransaction->total_amount - $selPaid);
                            $selIsPaidOff = $selRemaining <= 0.001;
                            $firstDetail = $selectedTransaction->details->first();
                            $selTotalWeight = $selectedTransaction->details->sum('weight');
                        @endphp

                        <div class="rounded-2xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
                            {{-- Header Panel Rincian --}}
                            <div class="border-b border-slate-100 bg-slate-50/70 p-5">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rincian Dokumen Transaksi</p>
                                    @if ($selIsPaidOff)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            ✓ Lunas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                            ● Belum Lunas
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
                                        {{ $selectedTransaction->transaction_code }}
                                    </h3>
                                    <button
                                        type="button"
                                        @click="copyCode('{{ $selectedTransaction->transaction_code }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-100"
                                        title="Salin Kode Transaksi"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" width="14" height="14" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span x-text="copied ? 'Disalin!' : 'Salin'"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="p-5 space-y-5 text-xs sm:text-sm">
                                {{-- Detail Informasi Tanggal & Layanan --}}
                                <div class="space-y-2.5 divide-y divide-slate-100 text-slate-600">
                                    <div class="flex justify-between items-center pt-1">
                                        <span class="text-slate-400">Tanggal Masuk:</span>
                                        <span class="font-semibold text-slate-800 text-right">
                                            {{ $selectedTransaction->created_at->translatedFormat('d F Y, H:i') }} WIB
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center pt-2.5">
                                        <span class="text-slate-400">Estimasi Selesai:</span>
                                        <span class="font-semibold text-brand-700 text-right">
                                            {{ $selectedTransaction->estimated_completed_at ? $selectedTransaction->estimated_completed_at->translatedFormat('d F Y, H:i') . ' WIB' : 'Menyesuaikan antrean' }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-start pt-2.5">
                                        <span class="text-slate-400">Paket Layanan:</span>
                                        <span class="font-semibold text-slate-800 text-right max-w-[200px]">
                                            {{ $selectedTransaction->details->map(fn($d) => $d->service?->name)->filter()->join(', ') ?: '-' }}
                                        </span>
                                    </div>

                                    @if ($selTotalWeight > 0)
                                        <div class="flex justify-between items-center pt-2.5">
                                            <span class="text-slate-400">Berat / Kuantitas:</span>
                                            <span class="font-semibold text-slate-800">
                                                {{ number_format($selTotalWeight, 1) }} kg
                                            </span>
                                        </div>
                                    @endif

                                    @if ($firstDetail && $firstDetail->unit_price > 0)
                                        <div class="flex justify-between items-center pt-2.5">
                                            <span class="text-slate-400">Tarif Satuan:</span>
                                            <span class="font-semibold text-slate-800">
                                                Rp {{ number_format($firstDetail->unit_price, 0, ',', '.') }} / kg
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Status Pengerjaan --}}
                                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Pengerjaan</p>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 border border-brand-200 px-2.5 py-0.5 text-xs font-semibold text-brand-700">
                                            ● {{ $selectedTransaction->status }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-xs text-slate-600">
                                        @switch($selectedTransaction->status)
                                            @case('Diterima')
                                                Pakaian Anda telah diterima oleh kasir dan sedang dalam antrean pencucian.
                                                @break
                                            @case('Diproses')
                                                Pakaian Anda saat ini berada di antrean pencucian, pengeringan, atau setrika.
                                                @break
                                            @case('Selesai')
                                                Cucian telah selesai diproses bersih & wangi! Siap diambil di kasir.
                                                @break
                                            @case('Diambil')
                                                Cucian telah diserahkan dan selesai diambil. Terima kasih telah mencuci di CUCI.IN!
                                                @break
                                            @case('Dibatalkan')
                                                Transaksi laundry ini telah dibatalkan.
                                                @break
                                            @default
                                                Status pembaruan terkini untuk pesanan Anda.
                                        @endswitch
                                    </p>

                                    {{-- Riwayat Status Singkat jika tersedia --}}
                                    @if ($selectedTransaction->statusHistories->isNotEmpty())
                                        <div class="mt-3 border-t border-slate-200/60 pt-3 space-y-2">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Riwayat Status:</p>
                                            <div class="space-y-1.5">
                                                @foreach ($selectedTransaction->statusHistories as $history)
                                                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                                                        <span class="font-medium text-slate-700">• {{ $history->status }}</span>
                                                        <span>{{ $history->changed_at ? \Carbon\Carbon::parse($history->changed_at)->translatedFormat('d M, H:i') : '' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Riwayat Pembayaran --}}
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Riwayat Pembayaran</p>
                                    @if ($selectedTransaction->payments->isNotEmpty())
                                        <div class="space-y-2">
                                            @foreach ($selectedTransaction->payments as $payment)
                                                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-white p-2.5 text-xs">
                                                    <div>
                                                        <p class="font-semibold text-slate-800">
                                                            {{ $payment->payment_type }}
                                                            <span class="text-slate-400 font-normal">({{ $payment->method }})</span>
                                                        </p>
                                                        <p class="text-[11px] text-slate-400">
                                                            {{ $payment->paid_at ? $payment->paid_at->translatedFormat('d M Y, H:i') : $payment->created_at->translatedFormat('d M Y, H:i') }} WIB
                                                        </p>
                                                    </div>
                                                    <span class="font-bold text-slate-900">
                                                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 text-xs text-slate-500 text-center">
                                            Belum ada catatan pembayaran.
                                        </div>
                                    @endif

                                    {{-- Kalkulasi Total & Sisa Tagihan --}}
                                    <div class="mt-3.5 space-y-2 border-t border-slate-100 pt-3">
                                        <div class="flex justify-between text-xs text-slate-600">
                                            <span>Total Tagihan:</span>
                                            <span class="font-bold text-slate-900">Rp {{ number_format($selectedTransaction->total_amount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-600">
                                            <span>Sudah Dibayar:</span>
                                            <span class="font-bold text-brand-700">Rp {{ number_format($selPaid, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-baseline border-t border-dashed border-slate-200 pt-2">
                                            <div>
                                                <p class="text-xs font-bold text-slate-900">Sisa Tagihan Pelunasan</p>
                                                @if ($selRemaining > 0)
                                                    <p class="text-[11px] text-amber-700">Perlu dilunasi saat pengambilan</p>
                                                @else
                                                    <p class="text-[11px] text-emerald-600">Lunas, tidak ada tagihan</p>
                                                @endif
                                            </div>
                                            <span class="text-base font-extrabold {{ $selRemaining > 0 ? 'text-amber-800' : 'text-emerald-700' }}">
                                                Rp {{ number_format($selRemaining, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tombol Cetak Nota Ini --}}
                                <div class="pt-2 print:hidden">
                                    <button
                                        type="button"
                                        onclick="window.print()"
                                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-700 py-3 px-4 text-xs sm:text-sm font-semibold text-white shadow-xs transition hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                                    >
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        Cetak Nota Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-slate-500">
                            Pilih transaksi dari daftar di sebelah kiri untuk melihat rincian dokumen lengkap.
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Tampilan Kosong (Empty State Sebenarnya Jika Tidak Ada Data) --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-8 sm:p-12 text-center shadow-xs">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-700 shrink-0" style="width: 56px; height: 56px;">
                    <svg class="w-7 h-7 shrink-0" width="28" height="28" style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base sm:text-lg font-bold text-slate-900">Belum Ada Transaksi</h3>
                <p class="mt-1.5 max-w-md mx-auto text-xs sm:text-sm text-slate-500">
                    Riwayat transaksi Anda akan tampil di sini. Mulai percayakan kebutuhan laundry pakaian, jas, maupun bed cover Anda bersama CUCI.IN.
                </p>
                @if (request()->hasAny(['search', 'status_cucian', 'status_pembayaran']))
                    <div class="mt-5">
                        <a
                            href="{{ route('customer.transactions.index') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-brand-800"
                        >
                            Reset Filter Pencarian
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>
