{{-- ============================================================
    INFORMASI & RIWAYAT PEMBAYARAN (MVP-07)
============================================================= --}}
@php
    $paymentStatus = $transaction->payment_status;
    $paymentBadgeClasses = match ($paymentStatus) {
        'Lunas' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'DP' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-rose-50 text-rose-700 border-rose-200',
    };
@endphp

<div
    x-data="{ showPaymentModal: false, paymentAmount: {{ (int) $transaction->remaining_amount }} }"
    class="space-y-4"
>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-sm font-semibold text-slate-900">
                        Informasi & Riwayat Pembayaran
                    </h2>
                    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $paymentBadgeClasses }}">
                        {{ $paymentStatus }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    Status pembayaran kasir, rincian pelunasan, dan riwayat transaksi keuangan.
                </p>
            </div>

            @if (! $transaction->isPaidOff())
                <button
                    type="button"
                    @click="showPaymentModal = true"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16" />
                    </svg>
                    <span>Catat Pembayaran / Pelunasan</span>
                </button>
            @endif
        </div>

        {{-- Summary Cards --}}
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <span class="text-xs font-medium text-slate-500">Total Tagihan</span>
                <p class="mt-1 text-base font-bold text-slate-900">
                    Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                <span class="text-xs font-medium text-emerald-800">Total Sudah Dibayar</span>
                <p class="mt-1 text-base font-bold text-emerald-600">
                    Rp {{ number_format($transaction->total_paid, 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4">
                <span class="text-xs font-medium text-rose-800">Sisa Tagihan</span>
                <p class="mt-1 text-base font-bold {{ $transaction->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-600' }}">
                    Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Payment History Table --}}
        <div class="mt-6">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                Catatan Transaksi Pembayaran
            </h3>
            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 uppercase text-slate-500">
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
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-700">
                                    {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                        {{ $payment->payment_type ?? 'Pembayaran' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $payment->method }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $payment->creator?->name ?? 'Kasir' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    Belum ada riwayat pembayaran untuk transaksi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Catat Pembayaran / Pelunasan --}}
    @if (! $transaction->isPaidOff())
        <div
            x-show="showPaymentModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
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
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            Catat Pembayaran
                        </h3>
                        <p class="text-xs text-slate-500">
                            Transaksi: {{ $transaction->transaction_code }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showPaymentModal = false"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                        aria-label="Tutup modal"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form
                    action="{{ route("{$currentRoutePrefix}.transactions.payments.store", $transaction) }}"
                    method="POST"
                    class="mt-5 space-y-4"
                >
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">
                            Metode Pembayaran <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="method"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        >
                            <option value="Tunai">Tunai (Cash)</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Kartu Debit">Kartu Debit</option>
                        </select>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="block text-xs font-medium text-slate-600">
                                Nominal Pembayaran (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <button
                                type="button"
                                @click="paymentAmount = {{ (int) $transaction->remaining_amount }}"
                                class="text-[11px] font-semibold text-brand-600 hover:underline cursor-pointer"
                            >
                                Lunas Penuh (Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }})
                            </button>
                        </div>
                        <input
                            type="number"
                            name="amount"
                            x-model="paymentAmount"
                            min="1"
                            max="{{ (int) $transaction->remaining_amount }}"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-semibold text-slate-900 transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        />
                        <p class="mt-1 text-[11px] text-slate-400">
                            Sisa tagihan saat ini: Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2.5 pt-2">
                        <button
                            type="button"
                            @click="showPaymentModal = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700 cursor-pointer"
                        >
                            Konfirmasi & Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
