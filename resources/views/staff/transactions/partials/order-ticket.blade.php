{{-- ======================================================== --}}
{{-- NOTA KASIR TRANSAKSI POS (KOLOM KANAN 5/12 - STICKY)    --}}
{{-- ======================================================== --}}
<div class="lg:col-span-5 lg:sticky lg:top-6 lg:max-h-[calc(100vh-3.5rem)] lg:flex lg:flex-col">
    <form
        id="transaction-form"
        method="POST"
        action="{{ route("{$currentRoutePrefix}.transactions.store") }}"
        class="flex flex-col rounded-2xl border border-slate-200/90 bg-white shadow-sm overflow-hidden"
    >
        @csrf

        {{-- Header Nota Kasir --}}
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3.5 sm:px-5 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-xl bg-brand-600 text-white shadow-2xs">
                        <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 sm:text-base">Tiket Nota Kasir</h2>
                        <p class="text-[11px] text-slate-500">Order kasir aktif</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 rounded-md bg-brand-50 border border-brand-200/60 px-2 py-0.5 text-[11px] font-bold text-brand-700">
                    <span class="size-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                    Draf Baru
                </span>
            </div>
        </div>

        {{-- Alert Pemulihan Draf Otomatis --}}
        <div
            x-show="draftRestored"
            x-cloak
            x-transition
            class="flex items-center justify-between gap-2 border-b border-amber-200/90 bg-amber-50 px-4 py-2.5 text-xs text-amber-900"
        >
            <div class="flex items-center gap-1.5 min-w-0">
                <svg class="size-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="truncate">Draf kasir sebelumnya dipulihkan otomatis.</span>
            </div>
            <button
                type="button"
                @click="clearDraft()"
                class="shrink-0 rounded px-2 py-0.5 font-bold text-amber-800 hover:bg-amber-100/80 hover:text-amber-950 transition cursor-pointer"
            >
                Reset Draf
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-5 no-scrollbar">
            {{-- Section 1: Data Pelanggan --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="customer_id" class="text-xs font-bold text-slate-700">
                        Data Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <button
                        type="button"
                        id="btn-open-quick-customer"
                        onclick="openQuickCustomerModal()"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Pelanggan Baru</span>
                    </button>
                </div>

                <div class="relative">
                    <select
                        id="customer_id"
                        name="customer_id"
                        onchange="onCustomerSelectChange(this.value)"
                        @class([
                            'w-full rounded-xl border bg-white px-3 py-2 text-xs text-slate-800 transition focus:outline-none focus:ring-2 sm:text-sm',
                            'border-rose-300 focus:border-rose-400 focus:ring-rose-200' => $errors->has('customer_id'),
                            'border-slate-300 focus:border-brand-500 focus:ring-brand-100' => ! $errors->has('customer_id'),
                        ])
                    >
                        <option value="">-- Pilih / Cari Pelanggan --</option>
                        @foreach ($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                {{ (string) old('customer_id') === (string) $customer->id ? 'selected' : '' }}
                            >
                                {{ $customer->name }} ({{ $customer->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('customer_id')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror

                {{-- Preview Pelanggan Terpilih --}}
                <div
                    id="customer-preview-card"
                    class="mt-2.5 hidden rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 transition-all duration-200"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2.5">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 font-bold text-xs text-emerald-800" id="preview-avatar">
                                PL
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-slate-900" id="preview-name">-</p>
                                <p class="text-[11px] text-slate-600" id="preview-phone">-</p>
                                <p class="line-clamp-1 italic text-[11px] text-slate-500" id="preview-address">Alamat belum diisi</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="resetCustomerSelection()"
                            class="shrink-0 rounded p-1 text-slate-400 hover:bg-emerald-100 hover:text-slate-700 cursor-pointer"
                            title="Ganti Pelanggan"
                            aria-label="Ganti Pelanggan"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Section 2: Daftar Item Cucian Nota --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700">Item Cucian</span>
                    <button
                        type="button"
                        @click="addItem()"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Baris Manual</span>
                    </button>
                </div>

                <div class="space-y-2.5 max-h-[340px] overflow-y-auto pr-1">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-3 transition hover:border-slate-300">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center rounded-md bg-brand-100 px-1.5 py-0.5 text-[10px] font-bold text-brand-800" x-text="'Item #' + (index + 1)"></span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-brand-700" x-text="formatRupiah(getSubtotal(item))"></span>
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="rounded p-1 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 cursor-pointer"
                                        title="Hapus baris item"
                                        aria-label="Hapus baris item"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                {{-- Dropdown Layanan --}}
                                <div>
                                    <select
                                        :id="index === 0 ? 'service_id' : null"
                                        :name="'items[' + index + '][service_id]'"
                                        x-model="item.service_id"
                                        required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-200"
                                    >
                                        <option value="">-- Pilih Layanan --</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}">
                                                {{ $service->name }} (Rp {{ number_format($service->price, 0, ',', '.') }}/kg)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Stepper Input Berat (Kg) --}}
                                <div class="flex items-center gap-2">
                                    <div class="flex flex-1 items-center rounded-lg border border-slate-300 bg-white overflow-hidden focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-200">
                                        <button
                                            type="button"
                                            @click="stepWeight(index, -0.5)"
                                            class="inline-flex items-center justify-center min-w-9 min-h-9 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 active:bg-slate-200 cursor-pointer select-none"
                                            aria-label="Kurangi berat 0.5 kg"
                                        >
                                            -
                                        </button>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            :id="index === 0 ? 'weight' : null"
                                            :name="'items[' + index + '][weight]'"
                                            x-model="item.weight"
                                            required
                                            placeholder="0.00"
                                            class="w-full text-center border-0 p-1 text-xs font-bold text-slate-900 focus:outline-none"
                                        />
                                        <span class="pr-2 text-[10px] font-semibold text-slate-400 select-none">Kg</span>
                                        <button
                                            type="button"
                                            @click="stepWeight(index, 0.5)"
                                            class="inline-flex items-center justify-center min-w-9 min-h-9 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 active:bg-slate-200 cursor-pointer select-none"
                                            aria-label="Tambah berat 0.5 kg"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                @error('items')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Section 3: Ringkasan Kalkulasi & Total --}}
            <div class="rounded-xl border border-brand-100 bg-brand-50/50 p-4 space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Total Berat Cucian:</span>
                    <span class="font-bold text-slate-900" x-text="getTotalWeight().toFixed(2) + ' kg'">0.00 kg</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Estimasi Selesai:</span>
                    <span class="font-bold text-slate-900" x-text="formatDuration(getMaxDuration())">0 Jam</span>
                </div>
                <div class="border-t border-brand-200/80 pt-2 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-800">Total Tagihan:</span>
                    <span class="text-xl font-black text-brand-700" x-text="formatRupiah(getTotalAmount())">Rp 0</span>
                </div>
            </div>

            {{-- Section 3.5: Opsi Pembayaran Kasir --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-800">Pembayaran Kasir</label>
                    <span class="text-[10px] font-semibold text-slate-500">Pilih status bayar saat ini</span>
                </div>

                {{-- Pilihan Opsi Pembayaran --}}
                <div class="grid grid-cols-3 gap-1.5 rounded-lg bg-slate-200/70 p-1 text-xs font-semibold">
                    <button
                        type="button"
                        @click="paymentChoice = 'unpaid'"
                        :class="paymentChoice === 'unpaid' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="rounded-md py-1.5 transition text-center cursor-pointer select-none"
                    >
                        Bayar Nanti
                    </button>
                    <button
                        type="button"
                        @click="paymentChoice = 'full'"
                        :class="paymentChoice === 'full' ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="rounded-md py-1.5 transition text-center cursor-pointer select-none"
                    >
                        Bayar Lunas
                    </button>
                    <button
                        type="button"
                        @click="paymentChoice = 'dp'"
                        :class="paymentChoice === 'dp' ? 'bg-amber-500 text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="rounded-md py-1.5 transition text-center cursor-pointer select-none"
                    >
                        Bayar DP
                    </button>
                </div>

                {{-- Hidden input pay_now --}}
                <input type="hidden" name="pay_now" :value="paymentChoice !== 'unpaid' ? '1' : '0'" />

                {{-- Detail Metode Pembayaran jika Bayar Sekarang --}}
                <div x-show="paymentChoice !== 'unpaid'" x-transition class="space-y-3 pt-1">
                    <div>
                        <label class="block mb-1 text-[11px] font-semibold text-slate-600">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="m in ['Tunai', 'QRIS', 'Transfer']" :key="m">
                                <label
                                    class="flex items-center justify-center gap-1.5 rounded-lg border py-1.5 text-xs font-bold cursor-pointer transition select-none"
                                    :class="paymentMethod === m ? 'border-brand-500 bg-brand-50 text-brand-700 shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'"
                                >
                                    <input type="radio" name="payment_method" :value="m" x-model="paymentMethod" class="sr-only" />
                                    <span x-text="m"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Input Nominal DP jika memilih DP --}}
                    <div x-show="paymentChoice === 'dp'" x-transition>
                        <div class="flex items-center justify-between mb-1">
                            <label for="payment_amount" class="text-[11px] font-semibold text-slate-600">Nominal DP (Uang Muka)</label>
                            <span class="text-[10px] text-amber-700 font-bold" x-text="'Maks. ' + formatRupiah(getTotalAmount())"></span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400">Rp</span>
                            <input
                                type="number"
                                id="payment_amount"
                                name="payment_amount"
                                x-model="dpAmount"
                                min="1000"
                                :max="getTotalAmount()"
                                placeholder="Contoh: 20000"
                                class="w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 py-1.5 text-xs font-semibold text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4: Tombol Simpan Transaksi --}}
            <div class="pt-2">
                <button
                    type="submit"
                    id="btn-submit-transaction"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-xs transition hover:bg-brand-700 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Transaksi (Cetak Struk)</span>
                </button>
                <p class="mt-2 text-center text-[11px] text-slate-400">
                    Nomor nota unik dan riwayat pengerjaan otomatis dibuat oleh server.
                </p>
            </div>
        </div>
    </form>
</div>
