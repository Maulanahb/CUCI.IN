@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
@endphp

<x-layouts::app title="Transaksi Baru (POS)">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Header & Deskripsi Halaman --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Kasir POS — Transaksi Baru</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Input data penerimaan laundry baru. Pilih pelanggan, tentukan layanan, dan timbang berat cucian.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route("{$currentRoutePrefix}.customers.index") }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-2xs transition hover:bg-slate-50"
                >
                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    <span>Kelola Pelanggan</span>
                </a>
            </div>
        </div>

        {{-- Alert Notifikasi Status --}}
        @if (session('status'))
            <div class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <svg class="size-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Alert Error Global (jika ada kesalahan validasi selain per-field) --}}
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Terdapat kesalahan pada input transaksi:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-0.5 pl-2 font-normal">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Kasir POS --}}
        <form
            id="transaction-form"
            method="POST"
            action="{{ route("{$currentRoutePrefix}.transactions.store") }}"
            class="space-y-6"
        >
            @csrf

            {{-- Kartu 1: Pemilihan Pelanggan --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Data Pelanggan</h2>
                            <p class="text-[11px] text-slate-500">Pilih pelanggan yang melakukan order cucian.</p>
                        </div>
                    </div>
                    <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Wajib</span>
                </div>

                <div>
                    <label for="customer_id" class="block text-xs font-semibold text-slate-700">
                        Nama / Telepon Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1.5">
                        <select
                            id="customer_id"
                            name="customer_id"
                            @class([
                                'w-full rounded-xl border bg-white px-3.5 py-2.5 text-xs text-slate-800 transition focus:outline-none focus:ring-2 sm:text-sm',
                                'border-rose-300 focus:border-rose-400 focus:ring-rose-200' => $errors->has('customer_id'),
                                'border-slate-300 focus:border-brand-500 focus:ring-brand-100' => ! $errors->has('customer_id'),
                            ])
                        >
                            <option value="">-- Pilih Pelanggan --</option>
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
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-[11px] text-slate-400">
                        Pelanggan belum terdaftar? Anda dapat menambahkannya melalui menu <a href="{{ route("{$currentRoutePrefix}.customers.index") }}" class="font-medium text-brand-600 underline hover:text-brand-700">Data Pelanggan</a>.
                    </p>
                </div>
            </div>

            {{-- Kartu 2: Pemilihan Layanan & Timbangan Berat --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Layanan & Timbangan Cucian</h2>
                            <p class="text-[11px] text-slate-500">Tentukan jenis paket layanan laundry dan timbang berat aktual.</p>
                        </div>
                    </div>
                    <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Item 1</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Pilihan Layanan --}}
                    <div>
                        <label for="service_id" class="block text-xs font-semibold text-slate-700">
                            Jenis Layanan <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1.5">
                            <select
                                id="service_id"
                                name="items[0][service_id]"
                                @class([
                                    'w-full rounded-xl border bg-white px-3.5 py-2.5 text-xs text-slate-800 transition focus:outline-none focus:ring-2 sm:text-sm',
                                    'border-rose-300 focus:border-rose-400 focus:ring-rose-200' => $errors->has('items.0.service_id') || $errors->has('items'),
                                    'border-slate-300 focus:border-brand-500 focus:ring-brand-100' => ! ($errors->has('items.0.service_id') || $errors->has('items')),
                                ])
                            >
                                <option value="">-- Pilih Layanan --</option>
                                @foreach ($services as $service)
                                    <option
                                        value="{{ $service->id }}"
                                        {{ (string) old('items.0.service_id') === (string) $service->id ? 'selected' : '' }}
                                    >
                                        {{ $service->name }} — Rp {{ number_format($service->price, 0, ',', '.') }}/kg
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @error('items.0.service_id')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Berat Cucian --}}
                    <div>
                        <label for="weight" class="block text-xs font-semibold text-slate-700">
                            Berat Cucian (Kg) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <input
                                id="weight"
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="items[0][weight]"
                                value="{{ old('items.0.weight') }}"
                                placeholder="0.00"
                                @class([
                                    'w-full rounded-xl border bg-white pr-12 pl-3.5 py-2.5 text-xs text-slate-800 transition focus:outline-none focus:ring-2 sm:text-sm',
                                    'border-rose-300 focus:border-rose-400 focus:ring-rose-200' => $errors->has('items.0.weight'),
                                    'border-slate-300 focus:border-brand-500 focus:ring-brand-100' => ! $errors->has('items.0.weight'),
                                ])
                            />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                <span class="text-xs font-semibold text-slate-400">Kg</span>
                            </div>
                        </div>

                        @error('items.0.weight')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @error('items')
                    <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kartu 3: Catatan Otomatisasi & Tombol Aksi --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs leading-relaxed text-slate-500">
                            Kode nota unik, estimasi waktu selesai, dan kalkulasi total transaksi dihitung otomatis oleh server menggunakan tarif resmi layanan.
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <a
                            href="{{ route("{$currentRoutePrefix}.dashboard") }}"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            id="btn-submit-transaction"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Transaksi</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts::app>
