<x-layouts::app title="Riwayat Transaksi">
    <div
        class="space-y-6"
        x-data="{
            selectedTrxCode: '{{ $selectedTransaction?->transaction_code }}',
            copied: false,
            copyCode(code) {
                navigator.clipboard.writeText(code);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        }"
    >
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between print:hidden">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Riwayat Transaksi</h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                    Arsip riwayat transaksi laundry Anda beserta rincian nota, status pengerjaan, dan pelunasan.
                </p>
            </div>
            <div></div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" dismissible class="print:hidden">
                {{ session('status') }}
            </x-ui.alert>
        @endif

        {{-- 4 Kartu Statistik Ringkasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
            {{-- Kartu 1: Total Transaksi --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaksi</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 shrink-0">
                        <x-lucide-receipt class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-slate-900 tracking-tight sm:text-3xl">
                    {{ $stats['total_transactions'] }} <span class="text-xs font-sans font-medium text-slate-500">Nota</span>
                </p>
            </div>

            {{-- Kartu 2: Cucian Berjalan --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-sky-300 hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Cucian Berjalan</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-sky-50 text-sky-700 shrink-0">
                        <x-lucide-refresh-cw class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-brand-700 tracking-tight sm:text-3xl">
                    {{ $stats['active_orders'] }} <span class="text-xs font-sans font-medium text-slate-500">Pesanan</span>
                </p>
            </div>

            {{-- Kartu 3: Menunggu Pelunasan --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-amber-300 hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Menunggu Pelunasan</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700 shrink-0">
                        <x-lucide-hourglass class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-amber-900 tracking-tight sm:text-3xl">
                    Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Kartu 4: Total Pengeluaran --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-emerald-300 hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengeluaran</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 shrink-0">
                        <x-lucide-wallet class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-slate-900 tracking-tight sm:text-3xl">
                    Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Bar Pencarian & Filter --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs print:hidden">
            <form method="GET" action="{{ route('customer.transactions.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-search class="size-4" />
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode transaksi, paket layanan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-10 pr-4 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                {{-- Dropdown Filter & Reset --}}
                <div class="flex flex-wrap items-center gap-2.5 sm:pr-1">
                    {{-- Filter Status Cucian --}}
                    <select
                        name="status_cucian"
                        onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 shadow-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer"
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
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 shadow-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer"
                    >
                        <option value="">Semua Pembayaran</option>
                        <option value="Lunas" @selected(request('status_pembayaran') === 'Lunas')>Lunas</option>
                        <option value="Belum Lunas" @selected(request('status_pembayaran') === 'Belum Lunas')>Belum Lunas</option>
                    </select>

                    {{-- Tombol Reset --}}
                    <a
                        href="{{ route('customer.transactions.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-600 shadow-xs transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900"
                        title="Reset semua filter pencarian"
                    >
                        <x-lucide-rotate-ccw class="size-3.5 text-slate-500" />
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- Konten Utama: Tabel Transaksi --}}
        @if ($paginatedTransactions->isNotEmpty())
            <div class="space-y-4 print:hidden">
                <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                            <thead class="border-b border-slate-200 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-500">
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
                                        $paidAmount = $trx->total_paid;
                                        $remainingAmount = max(0.0, (float) $trx->total_amount - $paidAmount);
                                        $isPaidOff = $remainingAmount <= 0.001;
                                        $hasDp = ! $isPaidOff && $paidAmount > 0;
                                        $servicesText = $trx->details->map(fn($d) => $d->service?->name)->filter()->join(', ');
                                        $totalWeight = $trx->details->sum('weight');
                                        $isSelected = $selectedTransaction && $selectedTransaction->id === $trx->id;
                                    @endphp
                                    <tr
                                        onclick="window.location.href = this.dataset.href"
                                        data-href="{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}"
                                        class="cursor-pointer transition hover:bg-brand-50/50 {{ $isSelected ? 'bg-brand-50/70 font-medium' : '' }}"
                                        title="Klik untuk melihat detail nota {{ $trx->transaction_code }}"
                                    >
                                        {{-- Kode Transaksi --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap font-bold font-mono text-brand-700">
                                            <div class="flex items-center gap-1.5">
                                                @if (in_array($statusVal, ['Diterima', 'Diproses', 'Selesai'], true))
                                                    <span class="size-2 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span>
                                                @endif
                                                <span>{{ $trx->transaction_code }}</span>
                                            </div>
                                        </td>

                                        {{-- Tanggal Masuk --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap text-xs font-mono text-slate-500">
                                            {{ $trx->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        </td>

                                        {{-- Layanan --}}
                                        <td class="py-3.5 px-4">
                                            <div class="max-w-[220px]">
                                                <p class="font-semibold text-slate-800 truncate" title="{{ $servicesText ?: 'Layanan Laundry' }}">
                                                    {{ $servicesText ?: 'Layanan Laundry' }}
                                                </p>
                                                @if ($totalWeight > 0)
                                                    <p class="text-xs font-mono text-slate-500">
                                                        Berat {{ number_format($totalWeight, 1) }} kg
                                                    </p>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Total Tagihan & Dibayar --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <p class="font-bold font-mono text-slate-900">
                                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                            </p>
                                            @if ($paidAmount > 0)
                                                <p class="text-xs text-slate-500 font-mono">
                                                    Dibayar: <span class="font-semibold text-brand-700">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                                                    @if ($hasDp)
                                                        <span class="text-amber-700 font-sans font-medium">(DP Kasir)</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Status Pembayaran --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <x-ui.payment-badge :status="$isPaidOff ? 'Lunas' : ($hasDp ? 'DP' : 'Belum Bayar')" size="sm" />
                                        </td>

                                        {{-- Status Cucian --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <x-ui.status-badge :status="$trx->status" size="sm" />
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                            <a
                                                href="{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1.5 text-xs font-semibold text-brand-700 shadow-2xs transition hover:border-brand-300 hover:bg-brand-100 hover:text-brand-800"
                                                title="Lihat Detail Nota"
                                            >
                                                <x-lucide-eye class="size-3.5 shrink-0" />
                                                <span>Detail</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Kartu Mobile (md:hidden) --}}
                    <div class="divide-y divide-slate-100 md:hidden">
                        @foreach ($paginatedTransactions as $trx)
                            @php
                                $statusVal = $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : (string) $trx->status;
                                $paidAmount = $trx->total_paid;
                                $remainingAmount = max(0.0, (float) $trx->total_amount - $paidAmount);
                                $isPaidOff = $remainingAmount <= 0.001;
                                $hasDp = ! $isPaidOff && $paidAmount > 0;
                                $servicesText = $trx->details->map(fn($d) => $d->service?->name)->filter()->join(', ');
                                $totalWeight = $trx->details->sum('weight');
                                $isSelected = $selectedTransaction && $selectedTransaction->id === $trx->id;
                            @endphp

                            <a
                                href="{{ request()->fullUrlWithQuery(['selected' => $trx->transaction_code]) }}"
                                class="block p-4 transition hover:bg-slate-50 {{ $isSelected ? 'bg-brand-50/60' : '' }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold font-mono text-brand-700 tabular-nums">{{ $trx->transaction_code }}</p>
                                        <p class="mt-0.5 text-xs font-mono text-slate-500">
                                            {{ $trx->created_at?->translatedFormat('d M Y, H:i') }} WIB
                                        </p>
                                    </div>

                                    <x-ui.status-badge :status="$trx->status" size="sm" />
                                </div>

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800">
                                            {{ $servicesText ?: 'Layanan Laundry' }}
                                        </p>
                                        <p class="text-xs font-mono text-slate-500">
                                            {{ number_format((float) $totalWeight, 1, ',', '.') }} kg &middot; {{ $trx->details->count() }} layanan
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-bold font-mono text-slate-900">
                                            <x-ui.money :value="$trx->total_amount" />
                                        </p>
                                        <x-ui.payment-badge :status="$isPaidOff ? 'Lunas' : ($hasDp ? 'DP' : 'Belum Bayar')" size="sm" class="mt-1" />
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                    <span class="text-xs text-slate-500">
                                        @if ($paidAmount > 0)
                                            Dibayar <x-ui.money :value="$paidAmount" class="font-medium font-mono text-slate-600" />
                                        @else
                                            Belum ada pembayaran
                                        @endif
                                    </span>

                                    <span class="text-xs font-semibold text-brand-700 flex items-center gap-1">
                                        <span>Lihat detail</span>
                                        <x-lucide-arrow-right class="size-3" />
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="border-t border-slate-100 px-4 py-3 sm:px-6">
                        {{ $paginatedTransactions->links() }}
                    </div>
                </div>
            </div>

            {{-- Pop-up Modal: Detail Transaksi Cucian (Sesuai Database) --}}
            @if ($selectedTransaction)
                @php
                    $selPaid = $selectedTransaction->total_paid;
                    $selRemaining = max(0.0, (float) $selectedTransaction->total_amount - $selPaid);
                    $selIsPaidOff = $selRemaining <= 0.001;
                    $closeModalUrl = request()->fullUrlWithQuery(['selected' => null]);
                    $latestPayment = $selectedTransaction->payments->whereIn('verification_status', \App\Models\Payment::PAID_STATUSES)->last();
                    $selPendingAmount = $selectedTransaction->pending_verification_amount;
                    $selSubmittable = $selectedTransaction->submittable_amount;
                    $selCanUploadProof = ! $selectedTransaction->status->isTerminal() && $selSubmittable > 0;
                    $proofErrorKeys = ['method', 'amount', 'proof_file'];
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
                                <p class="text-xs font-bold font-mono text-brand-700">
                                    {{ $selectedTransaction->transaction_code }}
                                </p>
                            </div>
                            <a
                                href="{{ $closeModalUrl }}"
                                class="inline-flex size-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none"
                                title="Tutup detail (Esc)"
                            >
                                <x-lucide-x class="size-4" />
                            </a>
                        </div>

                        {{-- Isi Modal --}}
                        <div class="max-h-[70vh] overflow-y-auto p-5 space-y-4 text-xs sm:text-sm">
                            {{-- Info Transaksi --}}
                            <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3.5">
                                <div>
                                    <p class="text-xs font-medium text-slate-500">Tanggal Masuk</p>
                                    <p class="mt-0.5 font-semibold font-mono text-slate-800">
                                        {{ $selectedTransaction->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500">Status Cucian</p>
                                    <div class="mt-0.5">
                                        <x-ui.status-badge :status="$selectedTransaction->status" size="sm" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500">Estimasi Selesai</p>
                                    <p class="mt-0.5 font-semibold font-mono text-slate-800">
                                        {{ $selectedTransaction->estimated_completed_at ? $selectedTransaction->estimated_completed_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Menunggu update' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500">Status Pembayaran</p>
                                    <div class="mt-0.5">
                                        <x-ui.payment-badge :status="$selectedTransaction->payment_status" size="sm" />
                                    </div>
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
                                                            <p class="text-xs text-slate-500">{{ $item->service->description }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center whitespace-nowrap font-mono">
                                                        {{ number_format($item->weight, 1) }} kg
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap font-mono">
                                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right font-semibold font-mono text-slate-900 whitespace-nowrap">
                                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-3 px-3 text-center text-slate-500">Tidak ada rincian item</td>
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
                                    <span class="font-bold font-mono text-slate-900">
                                        Rp {{ number_format($selectedTransaction->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Sudah Dibayar:</span>
                                    <span class="font-semibold font-mono text-brand-700">
                                        Rp {{ number_format($selPaid, 0, ',', '.') }}
                                        @if ($latestPayment)
                                            <span class="font-normal font-sans text-slate-500">({{ $latestPayment->method }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-t border-slate-100 pt-2 text-sm">
                                    <span class="font-bold text-slate-800">Sisa Tagihan:</span>
                                    <span class="font-extrabold font-mono {{ $selRemaining > 0 ? 'text-amber-800' : 'text-emerald-700' }}">
                                        Rp {{ number_format($selRemaining, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Riwayat Pembayaran & Status Verifikasi --}}
                            @if ($selectedTransaction->payments->isNotEmpty())
                                <div>
                                    <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Riwayat Pembayaran</p>
                                    <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200/80">
                                        @foreach ($selectedTransaction->payments->sortByDesc('id') as $payment)
                                            <li class="flex items-start justify-between gap-3 px-3 py-2.5">
                                                <div class="min-w-0">
                                                    <p class="font-bold font-mono tabular-nums text-slate-900">
                                                        <x-ui.money :value="$payment->amount" />
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ $payment->paid_at?->translatedFormat('d M Y, H:i') }} &middot; {{ $payment->method }} &middot; {{ $payment->payment_type }}
                                                    </p>
                                                    @if ($payment->proof_file)
                                                        <a
                                                            href="{{ route('customer.payments.proof', $payment) }}"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800"
                                                        >
                                                            <x-lucide-image class="size-3.5" />
                                                            <span>Lihat bukti</span>
                                                        </a>
                                                    @endif
                                                    @if ($payment->verification_status === 'Ditolak' && $payment->verification_notes)
                                                        <p class="mt-1 text-xs text-rose-700">
                                                            <span class="font-semibold">Alasan ditolak:</span> {{ $payment->verification_notes }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <x-ui.verification-badge :status="$payment->verification_status" size="sm" class="shrink-0" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if ($selPendingAmount > 0)
                                <x-ui.alert variant="warning">
                                    Bukti pembayaran <span class="font-bold font-mono">Rp {{ number_format($selPendingAmount, 0, ',', '.') }}</span> lagi dicek kasir. Status tagihan akan berubah setelah disetujui.
                                </x-ui.alert>
                            @endif

                            {{-- Formulir Unggah Bukti Transfer / QRIS --}}
                            @if ($selCanUploadProof)
                                <form
                                    method="POST"
                                    action="{{ route('customer.transactions.payment-proofs.store', $selectedTransaction) }}"
                                    enctype="multipart/form-data"
                                    class="space-y-3 rounded-xl border border-brand-100 bg-brand-50/40 p-3.5"
                                    x-data="{
                                        method: @js(old('method', 'Transfer')),
                                        amount: @js(old('amount', $selSubmittable)),
                                        maxAmount: {{ $selSubmittable }},
                                        previewUrl: null,
                                        fileName: '',
                                        fileError: '',
                                        submitting: false,
                                        allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
                                        maxBytes: 5 * 1024 * 1024,
                                        pickFile(event) {
                                            const file = event.target.files[0] ?? null;
                                            this.fileError = '';
                                            this.fileName = '';
                                            if (this.previewUrl) {
                                                URL.revokeObjectURL(this.previewUrl);
                                                this.previewUrl = null;
                                            }
                                            if (! file) {
                                                return;
                                            }
                                            if (! this.allowedTypes.includes(file.type)) {
                                                this.fileError = 'Format bukti hanya JPG, PNG, atau WEBP.';
                                                event.target.value = '';
                                                return;
                                            }
                                            if (file.size > this.maxBytes) {
                                                this.fileError = 'Ukuran bukti maksimal 5 MB.';
                                                event.target.value = '';
                                                return;
                                            }
                                            this.fileName = file.name;
                                            this.previewUrl = URL.createObjectURL(file);
                                        },
                                        get canSubmit() {
                                            const value = Number(this.amount) || 0;
                                            return ! this.submitting && this.previewUrl !== null && value > 0 && value <= this.maxAmount;
                                        },
                                    }"
                                    @submit="if (! canSubmit) { $event.preventDefault(); return; } submitting = true"
                                >
                                    @csrf

                                    <div>
                                        <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sudah Transfer / Bayar QRIS?</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Unggah bukti pembayaranmu, nanti kasir yang mengecek dan mengonfirmasi.</p>
                                    </div>

                                    @if ($errors->hasAny($proofErrorKeys))
                                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" role="alert">
                                            {{ $errors->first('proof_file') ?: ($errors->first('amount') ?: $errors->first('method')) }}
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="proof-method" class="mb-1 block text-xs font-bold text-slate-700">Metode</label>
                                            <select
                                                id="proof-method"
                                                name="method"
                                                x-model="method"
                                                required
                                                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                            >
                                                <option value="Transfer">Transfer Bank</option>
                                                <option value="QRIS">QRIS</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="proof-amount" class="mb-1 block text-xs font-bold text-slate-700">Nominal (Rp)</label>
                                            <input
                                                id="proof-amount"
                                                type="number"
                                                name="amount"
                                                x-model="amount"
                                                min="100"
                                                max="{{ $selSubmittable }}"
                                                inputmode="numeric"
                                                required
                                                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs font-bold font-mono tabular-nums text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                            />
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500 font-mono tabular-nums">
                                        Maksimal yang bisa diajukan: Rp {{ number_format($selSubmittable, 0, ',', '.') }}
                                    </p>

                                    <div>
                                        <label for="proof-file" class="mb-1 block text-xs font-bold text-slate-700">Foto Bukti Pembayaran</label>
                                        <input
                                            id="proof-file"
                                            type="file"
                                            name="proof_file"
                                            accept="image/jpeg,image/png,image/webp"
                                            required
                                            @change="pickFile($event)"
                                            class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-700 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-800"
                                        />
                                        <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP, maksimal 5 MB.</p>
                                        <p x-show="fileError" x-text="fileError" x-cloak class="mt-1 text-xs font-semibold text-rose-700" role="alert"></p>
                                    </div>

                                    <template x-if="previewUrl">
                                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                            <img :src="previewUrl" :alt="'Pratinjau ' + fileName" class="max-h-56 w-full object-contain" />
                                        </div>
                                    </template>

                                    <button
                                        type="submit"
                                        x-bind:disabled="! canSubmit"
                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2.5 text-xs font-bold text-white shadow-2xs transition hover:bg-brand-800 disabled:pointer-events-none disabled:opacity-50"
                                    >
                                        <x-lucide-upload class="size-4" />
                                        <span x-text="submitting ? 'Mengirim...' : 'Kirim Bukti Pembayaran'">Kirim Bukti Pembayaran</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Footer Modal: Tombol Aksi --}}
                        <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-3 flex items-center justify-between">
                            @if ($selectedTransaction->status !== \App\Enums\TransactionStatus::Dibatalkan)
                                <a
                                    href="{{ route('customer.complaints.create', ['transaction_code' => $selectedTransaction->transaction_code]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/60 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 hover:text-rose-800"
                                >
                                    <x-lucide-alert-triangle class="size-3.5 shrink-0 text-rose-600" />
                                    <span>Laporkan Masalah</span>
                                </a>
                            @else
                                <div></div>
                            @endif

                            <a
                                href="{{ $closeModalUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-200 focus:outline-none"
                            >
                                Tutup
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Tampilan Kosong (Empty State) --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 sm:p-12 text-center shadow-xs">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 shrink-0">
                    <x-lucide-receipt class="size-7" />
                </div>
                <h2 class="mt-4 text-base sm:text-lg font-bold text-slate-900">Belum Ada Transaksi</h2>
                <p class="mt-1.5 max-w-md mx-auto text-xs sm:text-sm text-slate-500">
                    Riwayat transaksi Anda akan tampil di sini. Mulai percayakan kebutuhan laundry pakaian, jas, maupun bed cover Anda bersama CUCI.IN.
                </p>
                @if (request()->hasAny(['search', 'status_cucian', 'status_pembayaran']))
                    <div class="mt-5">
                        <a
                            href="{{ route('customer.transactions.index') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-700 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-brand-800"
                        >
                            <x-lucide-rotate-ccw class="size-3.5" />
                            <span>Reset Filter Pencarian</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>
