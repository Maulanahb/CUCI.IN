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
                    Arsip riwayat transaksi laundry Anda beserta rincian nota, status pengerjaan, dan pelunasan.
                </p>
            </div>

            {{-- Header Kanan (Dikosongkan karena murni tampilan detail transaksi tanpa tombol cetak) --}}
            <div></div>
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

        {{-- Konten Utama: Tabel Transaksi (Lebar Penuh & Ringkas) --}}
        @if ($paginatedTransactions->isNotEmpty())
            <div class="space-y-4 print:hidden">
                <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                            <thead class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="py-3 px-4">Kode Transaksi</th>
                                    <th scope="col" class="py-3 px-4">Tanggal Masuk</th>
                                    <th scope="col" class="py-3 px-4">Layanan</th>
                                    <th scope="col" class="py-3 px-4">Total Tagihan</th>
                                    <th scope="col" class="py-3 px-4">Status Pembayaran</th>
                                    <th scope="col" class="py-3 px-4">Status Cucian</th>
                                    <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($paginatedTransactions as $trx)
                                    @php
                                        $statusVal = $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : (string) $trx->status;
                                        $paidAmount = (float) $trx->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                                        $remainingAmount = max(0.0, (float) $trx->total_amount - $paidAmount);
                                        $isPaidOff = $remainingAmount <= 0.001;
                                        $hasDp = ! $isPaidOff && $paidAmount > 0;
                                        $servicesText = $trx->details->map(fn($d) => $d->service?->name)->filter()->join(', ');
                                        $totalWeight = $trx->details->sum('weight');
                                        $isSelected = $selectedTransaction && $selectedTransaction->id === $trx->id;
                                    @endphp
                                    <tr
                                        onclick="window.location.href='{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}'"
                                        class="cursor-pointer transition-colors hover:bg-brand-50/50 {{ $isSelected ? 'bg-brand-50/70 font-medium' : '' }}"
                                        title="Klik untuk melihat detail nota {{ $trx->transaction_code }}"
                                    >
                                        {{-- Kode Transaksi --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap font-bold text-brand-700">
                                            <div class="flex items-center gap-1.5">
                                                @if (in_array($statusVal, ['Diterima', 'Diproses', 'Selesai'], true))
                                                    <span class="size-2 rounded-full bg-emerald-500 ring-2 ring-emerald-200" style="width: 8px; height: 8px;"></span>
                                                @endif
                                                <span>{{ $trx->transaction_code }}</span>
                                            </div>
                                        </td>

                                        {{-- Tanggal Masuk --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                            {{ $trx->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        </td>

                                        {{-- Layanan --}}
                                        <td class="py-3.5 px-4">
                                            <div class="max-w-[220px]">
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

                                        {{-- Total Tagihan & Dibayar --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <p class="font-bold text-slate-900">
                                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                            </p>
                                            @if ($paidAmount > 0)
                                                <p class="text-[11px] text-slate-500">
                                                    Dibayar: <span class="font-semibold text-brand-700">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                                                    @if ($hasDp)
                                                        <span class="text-amber-700 font-medium">(DP Kasir)</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Status Pembayaran --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
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
                                                    Belum Lunas (DP Kasir)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                                    <span class="size-1.5 rounded-full bg-rose-500" style="width: 6px; height: 6px;"></span>
                                                    Belum Bayar
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Status Cucian --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <x-status-badge :status="$trx->status" />
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                            <a
                                                href="{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                                                title="Lihat Detail Nota"
                                            >
                                                <svg class="w-3.5 h-3.5 shrink-0" width="14" height="14" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Detail</span>
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

            {{-- Pop-up Modal Biasa: Detail Transaksi Cucian (Sesuai Database) --}}
            @if ($selectedTransaction)
                @php
                    $selPaid = (float) $selectedTransaction->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                    $selRemaining = max(0.0, (float) $selectedTransaction->total_amount - $selPaid);
                    $selIsPaidOff = $selRemaining <= 0.001;
                    $selStatusVal = $selectedTransaction->status instanceof \App\Enums\TransactionStatus ? $selectedTransaction->status->value : (string) $selectedTransaction->status;
                    $closeModalUrl = request()->fullUrlWithQuery(['selected' => null]);
                    $latestPayment = $selectedTransaction->payments->last();
                    $creatorName = $latestPayment?->creator?->name;
                @endphp

                <div
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-slate-900/50 backdrop-blur-xs transition-opacity"
                    @keydown.escape.window="window.location.href = '{{ $closeModalUrl }}'"
                >
                    {{-- Modal Box Standar --}}
                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl transition-all overflow-hidden"
                        @click.outside="window.location.href = '{{ $closeModalUrl }}'"
                    >
                        {{-- Header Modal --}}
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 sm:text-lg">
                                    Detail Transaksi
                                </h3>
                                <p class="text-xs font-semibold text-brand-700">
                                    {{ $selectedTransaction->transaction_code }}
                                </p>
                            </div>
                            <a
                                href="{{ $closeModalUrl }}"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus:outline-none"
                                title="Tutup detail (Esc)"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>

                        {{-- Isi Modal --}}
                        <div class="p-5 space-y-4 text-xs sm:text-sm">
                            {{-- Info Transaksi --}}
                            <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3.5">
                                <div>
                                    <p class="text-[11px] font-medium text-slate-400">Tanggal Masuk</p>
                                    <p class="mt-0.5 font-semibold text-slate-800">
                                        {{ $selectedTransaction->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-medium text-slate-400">Status Cucian</p>
                                    <div class="mt-0.5">
                                        <x-status-badge :status="$selectedTransaction->status" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[11px] font-medium text-slate-400">Estimasi Selesai</p>
                                    <p class="mt-0.5 font-semibold text-slate-800">
                                        {{ $selectedTransaction->estimated_completed_at ? $selectedTransaction->estimated_completed_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Menunggu update' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-medium text-slate-400">Status Pembayaran</p>
                                    @if ($selIsPaidOff)
                                        <span class="mt-0.5 inline-block rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            Lunas
                                        </span>
                                    @else
                                        <span class="mt-0.5 inline-block rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                            Belum Lunas
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Tabel Rincian Layanan (transaction_details) --}}
                            <div>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Rincian Layanan</p>
                                <div class="overflow-x-auto rounded-lg border border-slate-200/80">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                                            <tr>
                                                <th class="py-2 px-3">Layanan</th>
                                                <th class="py-2 px-3 text-center">Berat</th>
                                                <th class="py-2 px-3 text-right">Harga/kg</th>
                                                <th class="py-2 px-3 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-slate-700">
                                            @forelse ($selectedTransaction->details as $item)
                                                <tr>
                                                    <td class="py-2.5 px-3">
                                                        <p class="font-semibold text-slate-800">{{ $item->service?->name ?? 'Layanan' }}</p>
                                                        @if ($item->service?->description)
                                                            <p class="text-[11px] text-slate-400">{{ $item->service->description }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                        {{ number_format($item->weight, 1) }} kg
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right font-semibold text-slate-900 whitespace-nowrap">
                                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-3 px-3 text-center text-slate-400">Tidak ada rincian item</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Rincian Tagihan & Pembayaran --}}
                            <div class="space-y-1.5 border-t border-slate-100 pt-3 text-xs">
                                <div class="flex justify-between text-slate-600">
                                    <span>Total Tagihan:</span>
                                    <span class="font-bold text-slate-900">
                                        Rp {{ number_format($selectedTransaction->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Sudah Dibayar:</span>
                                    <span class="font-semibold text-brand-700">
                                        Rp {{ number_format($selPaid, 0, ',', '.') }}
                                        @if ($latestPayment)
                                            <span class="font-normal text-slate-400">({{ $latestPayment->method }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-t border-slate-100 pt-2 text-sm">
                                    <span class="font-bold text-slate-800">Sisa Tagihan:</span>
                                    <span class="font-extrabold {{ $selRemaining > 0 ? 'text-amber-800' : 'text-emerald-700' }}">
                                        Rp {{ number_format($selRemaining, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal: Tombol Aksi --}}
                        <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-3 flex items-center justify-between">
                            @if ($selStatusVal !== 'Dibatalkan')
                                <a
                                    href="{{ route('customer.complaints.create', ['transaction_code' => $selectedTransaction->transaction_code]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50/60 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 hover:text-rose-800"
                                >
                                    <svg class="size-3.5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Laporkan Masalah</span>
                                </a>
                            @else
                                <div></div>
                            @endif

                            <a
                                href="{{ $closeModalUrl }}"
                                class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-slate-800 focus:outline-none"
                            >
                                Tutup
                            </a>
                        </div>
                    </div>
                </div>
            @endif
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
