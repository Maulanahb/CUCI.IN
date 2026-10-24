@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
    $paymentStatus = $transaction->payment_status;
    $isCancelled = $statusValue === 'Dibatalkan';
    $isPaidOff = $transaction->isPaidOff();
    $isPickedUp = $statusValue === 'Diambil';
    $remainingAmount = (float) $transaction->remaining_amount;
    $totalWeight = (float) $transaction->details->sum('weight');
    $itemCount = $transaction->details->count();
@endphp

<x-layouts::app title="Detail Transaksi">

    <div
        class="space-y-6"
        x-data="{
            showPaymentModal: {{ $errors->has('amount') || $errors->has('method') || $errors->has('cash_received') ? 'true' : 'false' }},
            submitting: false,
            method: @js(old('method', 'Tunai')),
            paymentAmount: @js(old('amount', $remainingAmount)),
            cashReceived: @js(old('cash_received', $remainingAmount)),
            remaining: {{ $remainingAmount }},
            triggerEl: null,
            formatRupiah(value) {
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value) || 0);
            },
            get shortfall() {
                return Math.max(0, (Number(this.paymentAmount) || 0) - (Number(this.cashReceived) || 0));
            },
            get change() {
                return Math.max(0, (Number(this.cashReceived) || 0) - (Number(this.paymentAmount) || 0));
            },
            get hasChange() {
                return this.method === 'Tunai' && this.change > 0;
            },
            get isExactCash() {
                return this.method === 'Tunai' && Number(this.cashReceived) > 0 && Number(this.cashReceived) === Number(this.paymentAmount);
            },
            openPayment(event) {
                this.triggerEl = event?.currentTarget ?? null;
                this.showPaymentModal = true;
                setTimeout(() => this.$refs.paymentAmount?.focus(), 80);
            },
            closePayment() {
                if (this.submitting) {
                    return;
                }
                this.showPaymentModal = false;
                setTimeout(() => this.triggerEl?.focus(), 50);
            },
            syncCashToAmount(event) {
                this.paymentAmount = event.target.value;
                this.cashReceived = event.target.value;
            },
            trapFocus(event) {
                if (! this.showPaymentModal) {
                    return;
                }
                const panel = this.$refs.paymentPanel;
                if (! panel) {
                    return;
                }
                const focusable = panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])');
                if (focusable.length === 0) {
                    return;
                }
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (! event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        }"
        @keydown.escape.window="closePayment()"
        @keydown.tab="trapFocus($event)"
        x-effect="document.body.classList.toggle('overflow-hidden', showPaymentModal)"
    >

        {{-- Identitas nota & aksi utama --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.status-badge :status="$transaction->status" />
                        <x-ui.payment-badge :status="$paymentStatus" size="sm" />

                        <span class="text-xs text-slate-500">
                            Dibuat {{ $transaction->created_at?->diffForHumans() ?? '-' }}
                        </span>
                    </div>

                    <div
                        class="mt-2 flex items-start gap-3"
                        x-data="{
                            copied: false,
                            copyCode() {
                                const code = @js($transaction->transaction_code);
                                if (! navigator.clipboard) {
                                    return;
                                }
                                navigator.clipboard.writeText(code).then(() => {
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 1500);
                                }).catch(() => {});
                            }
                        }"
                    >
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <x-lucide-receipt class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nota Kasir</p>
                            <p class="truncate text-xl font-bold tracking-tight text-slate-900 tabular-nums sm:text-2xl">
                                {{ $transaction->transaction_code }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="copyCode()"
                            class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700 cursor-pointer"
                            :aria-label="copied ? 'Kode nota tersalin' : 'Salin kode nota'"
                        >
                            <x-lucide-copy x-show="! copied" class="size-4" />
                            <x-lucide-check x-show="copied" x-cloak class="size-4 text-emerald-600" />
                        </button>
                    </div>

                    <p class="mt-1 break-words text-sm text-slate-600">
                        {{ $transaction->customer?->name ?? 'Pelanggan umum' }}

                        @if ($transaction->customer?->phone)
                            <span class="text-slate-400" aria-hidden="true">&middot;</span>
                            <a href="tel:{{ $transaction->customer->phone }}" class="font-medium text-brand-700 hover:underline">
                                {{ $transaction->customer->phone }}
                            </a>
                        @endif
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 border-t border-slate-100 pt-3 text-xs">
                        <p class="flex items-center gap-1.5">
                            <x-lucide-clock class="size-3.5 text-slate-400" />
                            <span class="text-slate-500">Estimasi selesai:</span>
                            <span class="font-medium text-slate-700">{{ $transaction->estimated_completed_at?->format('d M Y, H:i') ?? '-' }}</span>
                        </p>

                        @if ($transaction->customer?->email)
                            <p class="min-w-0">
                                <span class="text-slate-500">Email</span>
                                <a href="mailto:{{ $transaction->customer->email }}" class="break-all font-medium text-brand-700 hover:underline">
                                    {{ $transaction->customer->email }}
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end lg:flex-col lg:items-end">
                    <div class="sm:text-right">
                        <p class="text-xs font-medium text-slate-500">
                            {{ $isPaidOff ? 'Status Pembayaran' : 'Sisa Tagihan' }}
                        </p>

                        @if ($isCancelled)
                            <p class="text-lg font-semibold text-slate-500">Transaksi dibatalkan</p>
                        @elseif ($isPaidOff)
                            <p class="text-2xl font-bold text-emerald-700">Lunas</p>
                        @else
                            <p class="text-2xl font-bold text-rose-700">
                                <x-ui.money :value="$remainingAmount" />
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if (! $isCancelled && ! $isPickedUp)
                            @if ($isPaidOff)
                                <form
                                    method="POST"
                                    action="{{ route("{$currentRoutePrefix}.transactions.status", $transaction) }}"
                                    onsubmit="return confirm('Tandai cucian sudah diambil pelanggan? Status ini tidak dapat diubah lagi.')"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Diambil">
                                    <x-ui.button type="submit" class="w-full sm:w-auto inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                                        <x-lucide-check-circle class="size-4" />
                                        <span>Tandai Diambil</span>
                                    </x-ui.button>
                                </form>
                            @else
                                <x-ui.button
                                    type="button"
                                    x-ref="paymentTrigger"
                                    @click="openPayment($event)"
                                    class="w-full sm:w-auto inline-flex items-center gap-1.5 shadow-xs cursor-pointer"
                                >
                                    <x-lucide-credit-card class="size-4" />
                                    <span>Catat Pembayaran</span>
                                </x-ui.button>
                            @endif
                        @endif

                        <x-ui.button
                            id="btn-print-receipt"
                            :href="route($currentRoutePrefix . '.transactions.print', $transaction)"
                            variant="{{ ($isCancelled || $isPickedUp) ? 'primary' : 'secondary' }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <x-lucide-printer class="size-4" />
                            <span>Cetak Nota</span>
                            <span class="rounded bg-slate-200/80 px-1 py-0.5 text-xs font-mono text-slate-700">Thermal</span>
                        </x-ui.button>

                        <x-ui.button
                            :href="route($currentRoutePrefix . '.transactions.index')"
                            variant="secondary"
                            class="inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <x-lucide-arrow-left class="size-4" />
                            <span>Kembali</span>
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
            FLASH PESAN SUKSES & ERROR GLOBAL
        ============================================================= --}}
        @if (session('success'))
            <x-ui.alert variant="success" dismissible>
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any() && ! ($errors->has('amount') || $errors->has('method') || $errors->has('cash_received') || $errors->has('status') || $errors->has('notes')))
            <x-ui.alert variant="destructive" title="Terjadi kesalahan input:" dismissible>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- ============================================================
            DETAIL LAYANAN
        ============================================================= --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-lucide-layers class="size-4" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Detail Layanan
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Rincian paket layanan laundry dalam transaksi ini.
                        </p>
                    </div>
                </div>

                <p class="text-xs font-medium text-slate-500">
                    {{ $itemCount }} layanan &middot; {{ number_format($totalWeight, 2, ',', '.') }} kg
                </p>
            </div>

            {{-- Tabel (desktop) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Layanan</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Berat</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Harga / Unit</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transaction->details as $detail)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-900">
                                        {{ $detail->service?->name ?? 'Layanan' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    <span class="tabular-nums">{{ number_format((float) $detail->weight, 2, ',', '.') }} {{ $detail->unit ?? 'kg' }}</span>
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    <x-ui.money :value="$detail->unit_price" />
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-slate-900">
                                    <x-ui.money :value="$detail->subtotal" class="font-semibold text-slate-900" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center">
                                    <p class="text-sm font-medium text-slate-600">
                                        Belum ada detail layanan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($transaction->details->isNotEmpty())
                        <tfoot class="border-t border-slate-200 bg-slate-50">
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-xs text-slate-500">
                                    Total {{ number_format($totalWeight, 2, ',', '.') }} kg
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <x-ui.money :value="$transaction->total_amount" class="text-sm font-bold text-slate-900" />
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Kartu (mobile) --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($transaction->details as $detail)
                    <div class="flex items-start justify-between gap-3 px-5 py-4">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">
                                {{ $detail->service?->name ?? 'Layanan' }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                <span class="tabular-nums">{{ number_format((float) $detail->weight, 2, ',', '.') }} {{ $detail->unit ?? 'kg' }}</span>
                                &times; <x-ui.money :value="$detail->unit_price" />
                            </p>
                        </div>

                        <x-ui.money :value="$detail->subtotal" class="shrink-0 text-sm font-semibold text-slate-900" />
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">
                        Belum ada detail layanan.
                    </p>
                @endforelse

                @if ($transaction->details->isNotEmpty())
                    <div class="flex items-center justify-between bg-slate-50 px-5 py-3">
                        <span class="text-sm font-semibold text-slate-700">Total</span>
                        <x-ui.money :value="$transaction->total_amount" class="text-sm font-bold text-slate-900" />
                    </div>
                @endif
            </div>
        </div>


        {{-- ============================================================
            INFORMASI & RIWAYAT PEMBAYARAN (MVP-07)
        ============================================================= --}}
        <div id="pembayaran" class="space-y-4">
            <x-ui.card padding="p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <x-lucide-credit-card class="size-4" />
                        </div>

                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-sm font-semibold text-slate-900">
                                    Informasi & Riwayat Pembayaran
                                </h2>
                                <x-ui.payment-badge :status="$paymentStatus" size="sm" />
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Status pembayaran kasir, rincian pelunasan, dan riwayat transaksi keuangan.
                            </p>
                        </div>
                    </div>

                    @if (! $transaction->isPaidOff() && ! $isCancelled)
                        <x-ui.button type="button" @click="openPayment($event)" class="w-full sm:w-auto inline-flex items-center gap-1.5 cursor-pointer">
                            <x-lucide-plus class="size-4" />
                            <span>Catat Pembayaran / Pelunasan</span>
                        </x-ui.button>
                    @endif
                </div>

                {{-- Ringkasan tagihan --}}
                <div class="mt-6 grid grid-cols-1 gap-3 {{ $remainingAmount > 0 ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
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

                    @if ($remainingAmount > 0)
                        <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4">
                            <span class="text-xs font-medium text-rose-800">Sisa Tagihan</span>
                            <p class="mt-1 text-lg font-bold text-rose-700">
                                <x-ui.money :value="$remainingAmount" />
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Riwayat pembayaran --}}
                <div class="mt-6">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Catatan Transaksi Pembayaran
                    </h3>

                    {{-- Tabel (desktop) --}}
                    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 md:block">
                        <table class="w-full min-w-[620px] text-left text-xs">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Waktu</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Jenis</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Metode</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Kasir</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($transaction->payments->sortByDesc('id') as $payment)
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

                                            @if ($payment->cash_received !== null)
                                                <span class="mt-0.5 block text-xs font-normal text-slate-500">
                                                    Diterima <x-ui.money :value="$payment->cash_received" /> &middot; Kembali <x-ui.money :value="$payment->change_amount" />
                                                </span>
                                            @endif
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

                    {{-- Kartu (mobile) --}}
                    <div class="space-y-2 md:hidden">
                        @forelse ($transaction->payments->sortByDesc('id') as $payment)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-base font-bold text-slate-900">
                                            <x-ui.money :value="$payment->amount" />
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }} &middot; {{ $payment->method }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                        {{ $payment->payment_type ?? 'Pembayaran' }}
                                    </span>
                                </div>

                                <p class="mt-2 text-xs text-slate-500">
                                    Kasir: {{ $payment->creator?->name ?? 'Kasir' }}
                                </p>

                                @if ($payment->cash_received !== null)
                                    <p class="mt-1 text-xs text-slate-500">
                                        Diterima <x-ui.money :value="$payment->cash_received" /> &middot; Kembali <x-ui.money :value="$payment->change_amount" />
                                    </p>
                                @endif
                            </div>
                        @empty
                            <p class="rounded-xl border border-slate-200 px-4 py-6 text-center text-sm text-slate-500">
                                Belum ada riwayat pembayaran untuk transaksi ini.
                            </p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            {{-- Modal catat pembayaran --}}
            @if (! $transaction->isPaidOff())
                <x-ui.modal
                    id="payment-modal"
                    show="showPaymentModal"
                    title="Catat Pembayaran"
                    description="Nota {{ $transaction->transaction_code }} • Sisa: Rp {{ number_format($remainingAmount, 0, ',', '.') }}"
                    maxWidth="md"
                >
                    <form
                        action="{{ route("{$currentRoutePrefix}.transactions.payments.store", $transaction) }}"
                        method="POST"
                        class="space-y-4"
                        @submit="submitting = true"
                    >
                        @csrf

                        @if ($errors->has('amount') || $errors->has('method') || $errors->has('cash_received'))
                            <div id="payment-error" class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-semibold text-rose-700" role="alert">
                                {{ $errors->first('amount') ?: ($errors->first('cash_received') ?: $errors->first('method')) }}
                            </div>
                        @endif

                        <div>
                            <label for="payment-method" class="mb-1.5 block text-xs font-semibold text-slate-700">
                                Metode Pembayaran <span class="text-rose-600">*</span>
                            </label>
                            <select
                                name="method"
                                id="payment-method"
                                x-model="method"
                                required
                                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="Tunai">Tunai</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <label for="payment-amount" class="block text-xs font-semibold text-slate-700">
                                    Nominal Pembayaran (Rp) <span class="text-rose-600">*</span>
                                </label>
                                <span class="text-xs text-slate-500">
                                    Sisa: <x-ui.money :value="$remainingAmount" />
                                </span>
                            </div>

                            <div class="mb-2 flex flex-wrap gap-1.5">
                                <button type="button" @click="paymentAmount = remaining; cashReceived = remaining"
                                    class="min-h-8 rounded-lg border border-brand-200 bg-brand-50 px-2.5 text-xs font-semibold text-brand-700 transition-colors hover:bg-brand-100 cursor-pointer">
                                    Uang Pas
                                </button>
                                <button type="button" @click="paymentAmount = Math.min(remaining, 50000); cashReceived = paymentAmount"
                                    class="min-h-8 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-100 cursor-pointer">
                                    Rp 50.000
                                </button>
                                <button type="button" @click="paymentAmount = Math.min(remaining, 100000); cashReceived = paymentAmount"
                                    class="min-h-8 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-100 cursor-pointer">
                                    Rp 100.000
                                </button>
                            </div>

                            <input
                                type="number"
                                name="amount"
                                id="payment-amount"
                                x-ref="paymentAmount"
                                x-model="paymentAmount"
                                @input="syncCashToAmount($event)"
                                min="1"
                                max="{{ $remainingAmount }}"
                                inputmode="numeric"
                                required
                                aria-describedby="payment-amount-help"
                                aria-invalid="{{ $errors->has('amount') ? 'true' : 'false' }}"
                                class="h-10 w-full rounded-xl border border-slate-300 px-3 text-xs sm:text-sm font-semibold text-slate-900 tabular-nums transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            />
                            <p id="payment-amount-help" class="mt-1 text-xs text-slate-500">
                                Sisa tagihan saat ini: <x-ui.money :value="$remainingAmount" />
                            </p>
                        </div>

                        <div x-show="method === 'Tunai'" x-cloak class="space-y-2">
                            <label for="cash-received" class="block text-xs font-semibold text-slate-700">
                                Uang Diterima (Rp) <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="number"
                                name="cash_received"
                                id="cash-received"
                                x-model.number="cashReceived"
                                x-bind:min="paymentAmount"
                                :disabled="method !== 'Tunai'"
                                inputmode="numeric"
                                aria-describedby="cash-help"
                                class="h-10 w-full rounded-xl border border-slate-300 px-3 text-xs sm:text-sm font-semibold text-slate-900 tabular-nums transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            />

                            <div id="cash-help" class="min-h-5" role="status" aria-live="polite">
                                <p x-show="shortfall > 0" class="text-xs font-medium text-rose-700">
                                    Kurang Rp <span class="tabular-nums" x-text="formatRupiah(shortfall)"></span>
                                </p>
                                <p x-show="hasChange" class="text-xs font-medium text-emerald-700">
                                    Kembalian Rp <span class="tabular-nums" x-text="formatRupiah(change)"></span>
                                </p>
                                <p x-show="isExactCash" class="text-xs text-slate-500">
                                    Uang pas.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                            <button
                                type="button"
                                @click="closePayment()"
                                class="min-h-[38px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                x-bind:disabled="submitting || (method === 'Tunai' && shortfall > 0) || ! paymentAmount || paymentAmount > remaining"
                                class="inline-flex min-h-[38px] items-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-800 disabled:pointer-events-none disabled:opacity-50 cursor-pointer"
                            >
                                <svg x-show="submitting" class="size-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true" x-cloak>
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <x-lucide-check class="size-4" x-show="! submitting" />
                                <span x-text="submitting ? 'Menyimpan...' : 'Konfirmasi & Simpan'">Konfirmasi & Simpan</span>
                            </button>
                        </div>
                    </form>
                </x-ui.modal>
            @endif
        </div>

        {{-- ============================================================
            UPDATE STATUS OPERASIONAL (DENGAN ROLLBACK GUARD & ALASAN)
        ============================================================= --}}
        @php
            $isTerminal = in_array($statusValue, ['Diambil', 'Dibatalkan']);
        @endphp

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-lucide-refresh-cw class="size-4" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Status Operasional Cucian
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Pantau dan perbarui tahap pengerjaan cucian pelanggan.
                        </p>
                    </div>
                </div>

                {{-- Status Saat Ini --}}
                <div>
                    <span class="text-xs text-slate-500">
                        Status saat ini:
                    </span>

                    <div class="mt-1">
                        <x-ui.status-badge :status="$transaction->status" />
                    </div>
                </div>
            </div>

            {{-- Visual Laundry Pipeline Stepper --}}
            @php
                $steps = [
                    ['id' => 'Diterima', 'number' => 1, 'label' => 'Diterima', 'desc' => 'Antrean Kasir'],
                    ['id' => 'Diproses', 'number' => 2, 'label' => 'Diproses', 'desc' => 'Cuci & Setrika'],
                    ['id' => 'Selesai', 'number' => 3, 'label' => 'Selesai', 'desc' => 'Siap Diambil'],
                    ['id' => 'Diambil', 'number' => 4, 'label' => 'Diambil', 'desc' => 'Diserahkan'],
                ];
                $currentRank = match($statusValue) {
                    'Diterima' => 1,
                    'Diproses' => 2,
                    'Selesai' => 3,
                    'Diambil' => 4,
                    default => 0,
                };
                $nextStatus = match($statusValue) {
                    'Diterima' => 'Diproses',
                    'Diproses' => 'Selesai',
                    'Selesai' => 'Diambil',
                    default => null,
                };
            @endphp

            <div class="mt-5 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 sm:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tahap Pengerjaan Laundry</span>
                    <span class="text-xs font-medium text-slate-500">Tahap {{ $statusValue === 'Dibatalkan' ? 'Dibatalkan' : ($currentRank > 0 ? "{$currentRank} dari 4" : '-') }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($steps as $step)
                        @php
                            $isPassed = $currentRank > $step['number'];
                            $isCurrent = $currentRank === $step['number'];
                        @endphp
                        <div @class([
                            'relative flex flex-col justify-between rounded-xl border p-3 transition-all',
                            'border-emerald-300 bg-emerald-50/60 text-emerald-900 shadow-2xs' => $isPassed,
                            'border-brand-500 bg-brand-50/70 text-brand-950 ring-2 ring-brand-500/20 shadow-xs' => $isCurrent,
                            'border-slate-200 bg-white text-slate-400 opacity-60' => ! $isPassed && ! $isCurrent,
                        ])>
                            <div class="flex items-center justify-between">
                                <div @class([
                                    'flex size-6 items-center justify-center rounded-full text-xs font-bold',
                                    'bg-emerald-600 text-white' => $isPassed,
                                    'bg-brand-600 text-white animate-pulse' => $isCurrent,
                                    'bg-slate-200 text-slate-500' => ! $isPassed && ! $isCurrent,
                                ])>
                                    @if ($isPassed)
                                        <x-lucide-check class="size-3.5" />
                                    @else
                                        <span>{{ $step['number'] }}</span>
                                    @endif
                                </div>
                                @if ($isCurrent)
                                    <span class="rounded bg-brand-600 px-1.5 py-0.5 text-xs font-bold text-white uppercase tracking-wider">Aktif</span>
                                @elseif ($isPassed)
                                    <span class="text-xs font-semibold text-emerald-700">Tuntas</span>
                                @endif
                            </div>
                            <div class="mt-2.5">
                                <p class="text-xs font-bold text-slate-900">{{ $step['label'] }}</p>
                                <p class="text-xs text-slate-500 leading-tight">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tombol Pintasan Aksi Cepat ke Status Berikutnya --}}
                @if ($nextStatus && ! $isTerminal)
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200/80 pt-3">
                        <div class="flex items-center gap-2 text-xs text-slate-600">
                            <x-lucide-zap class="size-4 text-brand-600 shrink-0" />
                            <span>Pintasan Cepat: Majukan ke status berikutnya (<strong>{{ $nextStatus }}</strong>)</span>
                        </div>
                        <form
                            method="POST"
                            action="{{ route("{$currentRoutePrefix}.transactions.status", $transaction) }}"
                            onsubmit="return confirm('Perbarui status cucian ke {{ $nextStatus }}?')"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                            <x-ui.button type="submit" class="w-full sm:w-auto inline-flex items-center gap-1.5 shadow-xs py-1.5 px-3.5 text-xs cursor-pointer">
                                <span>Majukan ke {{ $nextStatus }}</span>
                                <x-lucide-arrow-right class="size-3.5" />
                            </x-ui.button>
                        </form>
                    </div>
                @endif
            </div>

            @if ($isTerminal)
                {{-- Banner Final / Terminal --}}
                <div class="mt-6 rounded-xl border p-4 {{ $statusValue === 'Diambil' ? 'border-emerald-200 bg-emerald-50/60 text-emerald-900' : 'border-rose-200 bg-rose-50/60 text-rose-900' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $statusValue === 'Diambil' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                            @if ($statusValue === 'Diambil')
                                <x-lucide-check-circle class="size-5" />
                            @else
                                <x-lucide-x-circle class="size-5" />
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
                        submitting: false,
                        showStatusHint: false,
                        currentStatus: @js($statusValue),
                        selectedStatus: @js(old('status', '')),
                        notes: @js(old('notes', '')),
                        rank(s) {
                            return { 'Diterima': 1, 'Diproses': 2, 'Selesai': 3, 'Diambil': 4, 'Dibatalkan': 0 }[s] ?? 0;
                        },
                        isRollback() {
                            if (! this.selectedStatus || this.selectedStatus === this.currentStatus) return false;
                            return this.rank(this.selectedStatus) < this.rank(this.currentStatus);
                        },
                        needsNotes() {
                            return this.isRollback();
                        }
                    }"
                    method="POST"
                    action="{{ route("{$currentRoutePrefix}.transactions.status", $transaction) }}"
                    class="mt-6 space-y-4"
                    @submit="submitting = true"
                >
                    @csrf
                    @method('PATCH')

                    {{-- Error validation alerts --}}
                    @if ($errors->has('status'))
                        <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 text-xs text-rose-700" role="alert">
                            <p class="font-semibold">{{ $errors->first('status') }}</p>
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        {{-- Select Status --}}
                        <div class="flex-1">
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
                                x-ref="statusSelect"
                                required
                                :class="showStatusHint && (! selectedStatus || selectedStatus === currentStatus) ? 'border-rose-300 ring-2 ring-rose-500/20' : ''"
                                class="h-11 w-full rounded-lg border border-slate-400 bg-white px-3 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20"
                            >
                                <option value="" disabled @selected(! old('status'))>
                                    Pilih status berikutnya...
                                </option>
                                <option value="Diterima" @selected(old('status') === 'Diterima')>
                                    1. Diterima (Antrean Masuk)
                                </option>
                                <option value="Diproses" @selected(old('status') === 'Diproses')>
                                    2. Diproses (Dicuci & Disetrika)
                                </option>
                                <option value="Selesai" @selected(old('status') === 'Selesai')>
                                    3. Selesai (Siap Diambil)
                                </option>
                                <option value="Diambil" @selected(old('status') === 'Diambil')>
                                    4. Diambil (Diserahkan ke Pelanggan)
                                </option>
                            </select>
                        </div>

                        {{-- Submit Button --}}
                        <div class="sm:shrink-0">
                            <x-ui.button
                                type="submit"
                                class="w-full sm:w-auto sm:min-w-48"
                                x-bind:disabled="submitting"
                                @click="if (! selectedStatus || selectedStatus === currentStatus) { $event.preventDefault(); showStatusHint = true; $refs.statusSelect?.focus(); }"
                            >
                                <svg x-show="submitting" x-cloak class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span x-text="submitting ? 'Menyimpan...' : 'Perbarui Status'">Perbarui Status</span>
                            </x-ui.button>
                        </div>
                    </div>

                    <p
                        x-show="showStatusHint && (! selectedStatus || selectedStatus === currentStatus)"
                        x-cloak
                        class="text-xs font-medium text-rose-700"
                        role="alert"
                    >
                        Pilih status berikutnya dulu sebelum memperbarui.
                    </p>

                    {{-- Dynamic warning & mandatory reason when rolling back --}}
                    <div
                        x-show="needsNotes()"
                        x-cloak
                        x-transition:enter="ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="rounded-xl border border-amber-200 bg-amber-50/60 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                <x-lucide-alert-triangle class="size-4" />
                            </div>

                            <div class="flex-1">
                                <p class="text-xs font-semibold text-amber-900">
                                    Perhatian: Anda memundurkan status cucian dari
                                    <span x-text="currentStatus"></span> ke <span x-text="selectedStatus"></span>.
                                </p>

                                <p class="mt-0.5 text-xs text-amber-800">
                                    Tuliskan alasan koreksi di bawah ini agar tercatat di audit trail sistem.
                                </p>

                                <div class="mt-3">
                                    <label for="notes" class="mb-1 block text-xs font-semibold text-amber-900">
                                        Alasan Koreksi Status <span class="text-rose-600">*</span>
                                    </label>

                                    <textarea
                                        name="notes"
                                        id="notes"
                                        rows="2"
                                        x-model="notes"
                                        :required="needsNotes()"
                                        maxlength="500"
                                        placeholder="Contoh: Salah klik status oleh kasir / pakaian perlu dicuci ulang karena noda belum tuntas"
                                        class="w-full rounded-lg border border-slate-400 bg-white p-2.5 text-sm text-slate-800 outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20"
                                    ></textarea>

                                    @if ($errors->has('notes') && old('status') !== 'Dibatalkan')
                                        <p class="mt-1 text-xs text-rose-700" role="alert">
                                            {{ $errors->first('notes') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Zona berbahaya: pembatalan terpisah dari transisi rutin --}}
                <div
                    class="mt-6 border-t border-slate-200 pt-5"
                    x-data="{
                        confirming: @js(old('status') === 'Dibatalkan'),
                        reason: @js(old('notes', ''))
                    }"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Batalkan transaksi</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Gunakan hanya jika cucian tidak jadi diproses. Tindakan ini permanen.
                            </p>
                        </div>

                            <x-ui.button type="button" variant="danger-outline" @click="confirming = true">
                                Batalkan transaksi
                            </x-ui.button>
                    </div>

                    <div x-show="confirming" x-cloak class="mt-4 rounded-xl border border-rose-200 bg-rose-50/70 p-4">
                        <p class="text-sm font-semibold text-rose-900">
                            Batalkan nota {{ $transaction->transaction_code }}?
                        </p>
                        <p class="mt-1 text-xs text-rose-800">
                            {{ $transaction->customer?->name ?? 'Pelanggan umum' }} &middot;
                            Total <x-ui.money :value="$transaction->total_amount" />.
                            Status akan menjadi <strong>Dibatalkan</strong> dan tidak dapat diubah lagi.
                        </p>

                        <form
                            method="POST"
                            action="{{ route("{$currentRoutePrefix}.transactions.status", $transaction) }}"
                            class="mt-3 space-y-3"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="Dibatalkan">

                            <div>
                                <label for="cancel-notes" class="mb-1 block text-xs font-semibold text-rose-900">
                                    Alasan pembatalan <span class="text-rose-700">*</span>
                                </label>
                                <textarea
                                    name="notes"
                                    id="cancel-notes"
                                    rows="3"
                                    maxlength="500"
                                    required
                                    x-model="reason"
                                    placeholder="Contoh: Pelanggan membatalkan cucian sebelum diproses"
                                    class="w-full rounded-lg border border-rose-300 bg-white p-2.5 text-sm text-slate-800 outline-none transition focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20"
                                ></textarea>

                                @if (old('status') === 'Dibatalkan' && $errors->has('notes'))
                                    <p class="mt-1 text-xs text-rose-700" role="alert">
                                        {{ $errors->first('notes') }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap justify-end gap-2">
                                <x-ui.button type="button" variant="secondary" @click="confirming = false">
                                    Batal
                                </x-ui.button>
                                <x-ui.button type="submit" variant="danger">
                                    Ya, Batalkan Transaksi
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>


        {{-- ============================================================
            AUDIT TRAIL / RIWAYAT PEMBARUAN STATUS
        ============================================================= --}}
        <details class="group rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-5 transition-colors hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                <div class="flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-lucide-history class="size-4" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Riwayat Pembaruan Status
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Catatan audit trail seluruh perubahan status pengerjaan cucian.
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <span class="text-xs font-medium text-slate-500">
                        {{ $transaction->statusHistories->count() }} catatan
                    </span>

                    <x-lucide-chevron-down class="size-4 text-slate-500 transition-transform group-open:rotate-180" />
                </div>
            </summary>

            <div class="border-t border-slate-200">
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
                                            <x-lucide-info class="size-3.5 shrink-0 text-amber-600" />
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
        </details>

    </div>

</x-layouts::app>
