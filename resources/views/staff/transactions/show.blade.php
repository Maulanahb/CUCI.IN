@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
@endphp

<x-layouts::app title="Detail Transaksi {{ $transaction->transaction_code }}">

    <div class="space-y-6">

        {{-- Aksi halaman --}}
        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-ui.button
                :href="route($currentRoutePrefix . '.transactions.index')"
                variant="ghost"
                size="sm"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </x-ui.button>

            <x-ui.button
                id="btn-print-receipt"
                :href="route($currentRoutePrefix . '.transactions.print', $transaction)"
                variant="secondary"
                size="sm"
                target="_blank"
                rel="noopener"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Nota
            </x-ui.button>

            <x-ui.button
                id="btn-back-to-create"
                :href="route($currentRoutePrefix . '.transactions.create')"
                size="sm"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Transaksi Baru
            </x-ui.button>
        </div>

        {{-- ============================================================
            FLASH PESAN SUKSES
        ============================================================= --}}
        @if (session('success'))
            <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <svg
                    class="mt-0.5 size-5 shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <p>
                    {{ session('success') }}
                </p>
            </div>
        @endif


        {{-- ============================================================
            ERROR VALIDATION
        ============================================================= --}}
        @if ($errors->any() && ! ($errors->has('amount') || $errors->has('method')))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                <p class="font-semibold">
                    Terjadi kesalahan:
                </p>

                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- ============================================================
            INFORMASI UTAMA
        ============================================================= --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Informasi Customer --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <svg
                            class="size-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Informasi Pelanggan
                        </h2>

                        <p class="text-xs text-slate-500">
                            Data customer transaksi
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    {{-- Nama --}}
                    <div>
                        <p class="text-xs text-slate-500">
                            Nama Pelanggan
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $transaction->customer?->name ?? 'Guest' }}
                        </p>
                    </div>

                    {{-- Telepon --}}
                    @if ($transaction->customer?->phone)
                        <div>
                            <p class="text-xs text-slate-500">
                                Nomor Telepon
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ $transaction->customer->phone }}
                            </p>
                        </div>
                    @endif

                    {{-- Email --}}
                    @if ($transaction->customer?->email)
                        <div>
                            <p class="text-xs text-slate-500">
                                Email
                            </p>

                            <p class="mt-1 break-all text-sm font-medium text-slate-700">
                                {{ $transaction->customer->email }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Informasi Transaksi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <svg
                            class="size-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Informasi Transaksi
                        </h2>

                        <p class="text-xs text-slate-500">
                            Informasi utama transaksi laundry
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Kode Transaksi --}}
                    <div>
                        <p class="text-xs text-slate-500">
                            Kode Transaksi
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $transaction->transaction_code }}
                        </p>
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <p class="text-xs text-slate-500">
                            Tanggal Transaksi
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $transaction->created_at?->format('d F Y, H:i') ?? '-' }}
                        </p>
                    </div>

                    {{-- Estimasi --}}
                    <div>
                        <p class="text-xs text-slate-500">
                            Estimasi Selesai
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $transaction->estimated_completed_at?->format('d F Y, H:i') ?? '-' }}
                        </p>
                    </div>

                    {{-- Total --}}
                    <div>
                        <p class="text-xs text-slate-500">
                            Total Transaksi
                        </p>

                        <p class="mt-1 text-lg font-bold text-brand-700">
                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

        </div>


        {{-- ============================================================
            DETAIL LAYANAN
        ============================================================= --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-sm font-semibold text-slate-900">
                    Detail Layanan
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Layanan yang digunakan dalam transaksi ini.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">
                                Layanan
                            </th>
                            <th class="px-6 py-3 font-semibold">
                                Berat
                            </th>
                            <th class="px-6 py-3 font-semibold">
                                Harga / Unit
                            </th>
                            <th class="px-6 py-3 text-right font-semibold">
                                Subtotal
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transaction->details as $detail)
                            <tr class="hover:bg-slate-50">
                                {{-- Service --}}
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900">
                                        {{ $detail->service?->name ?? 'Layanan' }}
                                    </p>
                                </td>

                                {{-- Weight --}}
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format((float) $detail->weight, 2, ',', '.') }} {{ $detail->unit ?? 'kg' }}
                                </td>

                                {{-- Unit Price --}}
                                <td class="px-6 py-4 text-slate-600">
                                    Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                                </td>

                                {{-- Subtotal --}}
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="4"
                                    class="px-6 py-10 text-center"
                                >
                                    <p class="text-sm font-medium text-slate-600">
                                        Belum ada detail layanan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        {{-- ============================================================
            INFORMASI & RIWAYAT PEMBAYARAN (MVP-07)
        ============================================================= --}}
        @php
            $paymentStatus = $transaction->payment_status;
            $remainingAmount = (float) $transaction->remaining_amount;
        @endphp

        <div
            x-data="{
                showPaymentModal: {{ $errors->has('amount') || $errors->has('method') ? 'true' : 'false' }},
                submitting: false,
                method: 'Tunai',
                paymentAmount: {{ $remainingAmount }},
                cashReceived: {{ $remainingAmount }},
                remaining: {{ $remainingAmount }},
                formatRupiah(value) {
                    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value) || 0);
                },
                get change() {
                    return Math.max(0, (Number(this.cashReceived) || 0) - (Number(this.paymentAmount) || 0));
                },
                get hasChange() {
                    return this.method === 'Tunai' && (Number(this.cashReceived) || 0) > (Number(this.paymentAmount) || 0);
                },
                openPayment() {
                    this.showPaymentModal = true;
                    setTimeout(() => this.$refs.paymentAmount?.focus(), 50);
                }
            }"
            @keydown.escape.window="showPaymentModal = false"
            x-init="if (showPaymentModal) { setTimeout(() => $refs.paymentAmount?.focus(), 80); }"
            class="space-y-4"
        >
            <x-ui.card padding="p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-sm font-semibold text-slate-900">
                                Informasi & Riwayat Pembayaran
                            </h2>
                            <x-ui.payment-badge :status="$paymentStatus" size="sm" />
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            Status pembayaran kasir, rincian pelunasan, dan riwayat transaksi keuangan.
                        </p>
                    </div>

                    @if (! $transaction->isPaidOff())
                        <x-ui.button type="button" @click="openPayment()" class="w-full sm:w-auto">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16" />
                            </svg>
                            <span>Catat Pembayaran / Pelunasan</span>
                        </x-ui.button>
                    @endif
                </div>

                {{-- Ringkasan tagihan --}}
                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <span class="text-xs font-medium text-slate-600">Total Tagihan</span>
                        <p class="mt-1 text-lg font-bold text-slate-900">
                            <x-ui.money :value="$transaction->total_amount" />
                        </p>
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                        <span class="text-xs font-medium text-emerald-800">Sudah Dibayar</span>
                        <p class="mt-1 text-lg font-bold text-emerald-700">
                            <x-ui.money :value="$transaction->total_paid" />
                        </p>
                    </div>

                    <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4">
                        <span class="text-xs font-medium text-rose-800">Sisa Tagihan</span>
                        <p class="mt-1 text-lg font-bold text-rose-700">
                            <x-ui.money :value="$transaction->remaining_amount" />
                        </p>
                    </div>
                </div>

                {{-- Riwayat pembayaran --}}
                <div class="mt-6">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Catatan Transaksi Pembayaran
                    </h3>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full min-w-[560px] text-left text-xs">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Waktu</th>
                                    <th class="px-4 py-3 font-semibold">Jenis</th>
                                    <th class="px-4 py-3 font-semibold">Metode</th>
                                    <th class="px-4 py-3 font-semibold">Kasir</th>
                                    <th class="px-4 py-3 text-right font-semibold">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($transaction->payments as $payment)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-3 font-medium text-slate-700">
                                            {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                                {{ $payment->payment_type ?? 'Pembayaran' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $payment->method }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $payment->creator?->name ?? 'Kasir' }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                            <x-ui.money :value="$payment->amount" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                            Belum ada riwayat pembayaran untuk transaksi ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-ui.card>

            {{-- Modal catat pembayaran --}}
            @if (! $transaction->isPaidOff())
                <div
                    x-show="showPaymentModal"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="payment-modal-title"
                >
                    <div
                        x-show="showPaymentModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showPaymentModal = false"
                        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
                    ></div>

                    <div
                        x-show="showPaymentModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="relative w-full max-w-md rounded-2xl bg-white p-5 shadow-xl sm:p-6"
                    >
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h3 id="payment-modal-title" class="text-base font-semibold text-slate-900">
                                    Catat Pembayaran
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Nota {{ $transaction->transaction_code }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="showPaymentModal = false"
                                aria-label="Tutup dialog pembayaran"
                                class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            >
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form
                            action="{{ route("{$currentRoutePrefix}.transactions.payments.store", $transaction) }}"
                            method="POST"
                            class="mt-5 space-y-4"
                            @submit="submitting = true"
                        >
                            @csrf

                            @if ($errors->has('amount') || $errors->has('method'))
                                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700" role="alert">
                                    {{ $errors->first('amount') ?: $errors->first('method') }}
                                </div>
                            @endif

                            <div>
                                <label for="payment-method" class="mb-1.5 block text-xs font-medium text-slate-600">
                                    Metode Pembayaran <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="method"
                                    id="payment-method"
                                    x-model="method"
                                    required
                                    class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                >
                                    <option value="Tunai">Tunai</option>
                                    <option value="QRIS">QRIS</option>
                                    <option value="Transfer">Transfer Bank</option>
                                </select>
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <label for="payment-amount" class="block text-xs font-medium text-slate-600">
                                        Nominal Pembayaran (Rp) <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-xs text-slate-500">
                                        Sisa: <x-ui.money :value="$remainingAmount" />
                                    </span>
                                </div>

                                <div class="mb-2 flex flex-wrap gap-1.5">
                                    <button type="button" @click="paymentAmount = remaining; cashReceived = remaining"
                                        class="rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 transition hover:bg-brand-100">
                                        Uang Pas
                                    </button>
                                    <button type="button" @click="paymentAmount = Math.min(remaining, 50000)"
                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                        Rp 50.000
                                    </button>
                                    <button type="button" @click="paymentAmount = Math.min(remaining, 100000)"
                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                        Rp 100.000
                                    </button>
                                </div>

                                <input
                                    type="number"
                                    name="amount"
                                    id="payment-amount"
                                    x-ref="paymentAmount"
                                    x-model="paymentAmount"
                                    min="100"
                                    max="{{ $remainingAmount }}"
                                    inputmode="numeric"
                                    required
                                    class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-900 tabular-nums transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                />
                            </div>

                            <div x-show="method === 'Tunai'" x-cloak class="space-y-2">
                                <label for="cash-received" class="block text-xs font-medium text-slate-600">
                                    Uang Diterima (Rp)
                                </label>
                                <input
                                    type="number"
                                    id="cash-received"
                                    x-model="cashReceived"
                                    min="0"
                                    inputmode="numeric"
                                    class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-900 tabular-nums transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                />
                                <p x-show="hasChange" class="text-xs text-emerald-700">
                                    Kembalian: Rp <span class="font-semibold tabular-nums" x-text="formatRupiah(change)"></span>
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <x-ui.button type="button" variant="secondary" @click="showPaymentModal = false">
                                    Batal
                                </x-ui.button>
                                <x-ui.button type="submit" x-bind:disabled="submitting">
                                    <svg x-show="submitting" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true" x-cloak>
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span x-text="submitting ? 'Menyimpan...' : 'Konfirmasi & Simpan'">Konfirmasi & Simpan</span>
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================================
            UPDATE STATUS OPERASIONAL (DENGAN ROLLBACK GUARD & ALASAN)
        ============================================================= --}}
        @php
            $isTerminal = in_array($statusValue, ['Diambil', 'Dibatalkan']);
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Status Operasional Cucian
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        Perbarui status transaksi sesuai dengan proses pengerjaan laundry.
                    </p>
                </div>

                {{-- Status Saat Ini --}}
                <div>
                    <span class="text-xs text-slate-500">
                        Status saat ini
                    </span>

                    <div class="mt-1">
                        <x-ui.status-badge :status="$transaction->status" />
                    </div>
                </div>
            </div>

            @if ($isTerminal)
                {{-- Banner Final / Terminal --}}
                <div class="mt-6 rounded-xl border p-4 {{ $statusValue === 'Diambil' ? 'border-emerald-200 bg-emerald-50/60 text-emerald-900' : 'border-rose-200 bg-rose-50/60 text-rose-900' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $statusValue === 'Diambil' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                            @if ($statusValue === 'Diambil')
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-bold">
                                {{ $statusValue === 'Diambil' ? 'Transaksi Telah Selesai & Cucian Diambil' : 'Transaksi Dibatalkan' }}
                            </h3>
                            <p class="mt-0.5 text-xs {{ $statusValue === 'Diambil' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $statusValue === 'Diambil' ? 'Cucian fisik telah diserahkan kepada pelanggan. Status transaksi ini telah ditutup permanen.' : 'Transaksi ini telah dibatalkan dan tidak dapat diproses lebih lanjut.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Form Update dengan Alpine.js Guard --}}
                <form
                    x-data="{
                        currentStatus: @js($statusValue),
                        selectedStatus: @js(old('status', $statusValue)),
                        notes: @js(old('notes', '')),
                        rank(s) {
                            return { 'Diterima': 1, 'Diproses': 2, 'Selesai': 3, 'Diambil': 4, 'Dibatalkan': 0 }[s] ?? 0;
                        },
                        isRollback() {
                            if (this.selectedStatus === 'Dibatalkan' || this.selectedStatus === this.currentStatus) return false;
                            return this.rank(this.selectedStatus) < this.rank(this.currentStatus);
                        },
                        needsNotes() {
                            return this.isRollback() || this.selectedStatus === 'Dibatalkan';
                        }
                    }"
                    method="POST"
                    action="{{ route("{$currentRoutePrefix}.transactions.status", $transaction) }}"
                    class="mt-6 space-y-4"
                >
                    @csrf
                    @method('PATCH')

                    {{-- Error validation alerts --}}
                    @if ($errors->has('status') || $errors->has('notes'))
                        <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 text-xs text-rose-700">
                            <p class="font-semibold">{{ $errors->first('status') ?: $errors->first('notes') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        {{-- Select Status --}}
                        <div class="md:col-span-2">
                            <label
                                for="status"
                                class="mb-1.5 block text-xs font-medium text-slate-600"
                            >
                                Ubah Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                x-model="selectedStatus"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="Diterima" @selected($statusValue === 'Diterima')>
                                    1. Diterima (Antrean Masuk)
                                </option>
                                <option value="Diproses" @selected($statusValue === 'Diproses')>
                                    2. Diproses (Dicuci & Disetrika)
                                </option>
                                <option value="Selesai" @selected($statusValue === 'Selesai')>
                                    3. Selesai (Siap Diambil)
                                </option>
                                <option value="Diambil" @selected($statusValue === 'Diambil')>
                                    4. Diambil (Diserahkan ke Pelanggan)
                                </option>
                                <option value="Dibatalkan" @selected($statusValue === 'Dibatalkan')>
                                    &times; Dibatalkan (Batalkan Transaksi)
                                </option>
                            </select>
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-end">
                            <button
                                type="submit"
                                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                            >
                                Perbarui Status
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Warning & Mandatory Reason Field when Rolling Back or Cancelling --}}
                    <div
                        x-show="needsNotes()"
                        x-cloak
                        x-transition:enter="ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="rounded-xl border p-4 transition-all"
                        :class="selectedStatus === 'Dibatalkan' ? 'border-rose-200 bg-rose-50/60' : 'border-amber-200 bg-amber-50/60'"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-7 shrink-0 items-center justify-center rounded-lg text-xs"
                                :class="selectedStatus === 'Dibatalkan' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <div class="flex-1">
                                <p
                                    class="text-xs font-semibold"
                                    :class="selectedStatus === 'Dibatalkan' ? 'text-rose-900' : 'text-amber-900'"
                                    x-text="selectedStatus === 'Dibatalkan' ? 'Perhatian: Transaksi akan dibatalkan.' : 'Perhatian: Anda memundurkan status cucian dari ' + currentStatus + ' ke ' + selectedStatus + '.'"
                                ></p>

                                <p
                                    class="mt-0.5 text-xs"
                                    :class="selectedStatus === 'Dibatalkan' ? 'text-rose-700' : 'text-amber-700'"
                                >
                                    Tuliskan alasan koreksi/pembatalan di bawah ini agar tercatat di audit trail sistem.
                                </p>

                                <div class="mt-3">
                                    <label
                                        for="notes"
                                        class="mb-1 block text-xs font-semibold"
                                        :class="selectedStatus === 'Dibatalkan' ? 'text-rose-800' : 'text-amber-800'"
                                    >
                                        Alasan Koreksi Status <span class="text-rose-600">*</span>
                                    </label>

                                    <textarea
                                        name="notes"
                                        id="notes"
                                        rows="2"
                                        x-model="notes"
                                        :required="needsNotes()"
                                        placeholder="Contoh: Salah klik status oleh kasir / pakaian perlu dicuci ulang karena noda belum tuntas"
                                        class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-xs text-slate-800 shadow-2xs outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>


        {{-- ============================================================
            TIMELINE STATUS
        ============================================================= --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-slate-900">
                    Alur Status Cucian
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Tahapan proses transaksi laundry.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                {{-- Diterima --}}
                <div
                    class="rounded-xl border p-4
                    {{ in_array($statusValue, ['Diterima', 'Diproses', 'Selesai', 'Diambil'])
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ in_array($statusValue, ['Diterima', 'Diproses', 'Selesai', 'Diambil'])
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            1
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Diterima
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Cucian masuk
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Diproses --}}
                <div
                    class="rounded-xl border p-4
                    {{ in_array($statusValue, ['Diproses', 'Selesai', 'Diambil'])
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ in_array($statusValue, ['Diproses', 'Selesai', 'Diambil'])
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            2
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Diproses
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Cucian dikerjakan
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Selesai --}}
                <div
                    class="rounded-xl border p-4
                    {{ in_array($statusValue, ['Selesai', 'Diambil'])
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ in_array($statusValue, ['Selesai', 'Diambil'])
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            3
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Selesai
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Siap diambil
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Diambil --}}
                <div
                    class="rounded-xl border p-4
                    {{ $statusValue === 'Diambil'
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ $statusValue === 'Diambil'
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            4
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Diambil
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Transaksi selesai
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Dibatalkan --}}
                <div
                    class="rounded-xl border p-4
                    {{ $statusValue === 'Dibatalkan'
                        ? 'border-rose-200 bg-rose-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ $statusValue === 'Dibatalkan'
                                ? 'bg-rose-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            !
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Dibatalkan
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Transaksi dibatalkan
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- ============================================================
            AUDIT TRAIL / RIWAYAT PEMBARUAN STATUS
        ============================================================= --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        Riwayat Pembaruan Status
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Catatan audit trail seluruh perubahan status pengerjaan cucian.
                    </p>
                </div>
                <span class="text-xs font-medium text-slate-500">
                    Total: {{ $transaction->statusHistories->count() }} catatan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Waktu Perubahan</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Petugas (Diubah Oleh)</th>
                            <th class="px-5 py-3 font-semibold">Catatan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transaction->statusHistories->sortByDesc('id') as $history)
                            @php
                                $histStatus = $history->status instanceof \App\Enums\TransactionStatus
                                    ? $history->status->value
                                    : (string) $history->status;
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    <div class="font-medium text-slate-800">
                                        {{ $history->changed_at?->translatedFormat('d F Y, H:i:s') ?? '-' }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        {{ $history->changed_at?->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <x-ui.status-badge :status="$histStatus" size="sm" />
                                </td>
                                <td class="px-5 py-3.5 text-xs font-medium text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-6 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                            {{ strtoupper(substr($history->changedBy?->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <span>{{ $history->changedBy?->name ?? 'Sistem' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    @if ($history->notes)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800 border border-amber-200">
                                            <svg class="size-3.5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ $history->notes }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-xs text-slate-500">
                                    Belum ada catatan riwayat status untuk transaksi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-layouts::app>
