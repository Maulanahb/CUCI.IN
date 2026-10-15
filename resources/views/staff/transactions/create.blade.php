@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $transactionIndexRoute = Route::has("{$currentRoutePrefix}.transactions.index")
        ? route("{$currentRoutePrefix}.transactions.index")
        : (Route::has('staff.transactions.index') ? route('staff.transactions.index') : route('dashboard'));
    $customerDirectory = $customers->mapWithKeys(fn ($c) => [
        (string) $c->id => [
            'id' => (string) $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'address' => $c->address ?? '',
        ],
    ]);
@endphp

<x-layouts::app title="Transaksi Baru (POS)">
    <div
        class="mx-auto max-w-4xl space-y-6"
        x-data="posTransactionManager()"
    >
        {{-- Header & Navigasi Cepat POS --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Kasir POS — Transaksi Baru</h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Kasir Aktif
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Penerimaan laundry baru dengan kalkulasi multi-layanan dan timbangan real-time.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ $transactionIndexRoute }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98"
                >
                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Daftar Transaksi</span>
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

        {{-- Alert Error Global --}}
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
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Data Pelanggan</h2>
                            <p class="text-[11px] text-slate-500">Pilih pelanggan terdaftar atau daftarkan pelanggan baru langsung.</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        id="btn-open-quick-customer"
                        onclick="openQuickCustomerModal()"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Pelanggan Baru</span>
                    </button>
                </div>

                <div>
                    <label for="customer_id" class="block text-xs font-semibold text-slate-700">
                        Nama / No. Telepon Pelanggan <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <select
                            id="customer_id"
                            name="customer_id"
                            onchange="onCustomerSelectChange(this.value)"
                            @class([
                                'w-full rounded-xl border bg-white pl-10 pr-9 py-2.5 text-xs text-slate-800 transition focus:outline-none focus:ring-2 sm:text-sm',
                                'border-rose-300 focus:border-rose-400 focus:ring-rose-200' => $errors->has('customer_id'),
                                'border-slate-300 focus:border-brand-500 focus:ring-brand-100' => ! $errors->has('customer_id'),
                            ])
                        >
                            <option value="">-- Cari atau Pilih Pelanggan --</option>
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

                    {{-- Kartu Info Preview Pelanggan Terpilih --}}
                    <div
                        id="customer-preview-card"
                        class="mt-3 hidden rounded-xl border border-emerald-200/90 bg-emerald-50/50 p-3.5 transition-all duration-200"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 font-bold text-xs text-emerald-800 shadow-2xs" id="preview-avatar">
                                    PL
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-xs font-bold text-slate-900 sm:text-sm" id="preview-name">-</h3>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800">
                                            <svg class="size-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Pelanggan Terpilih
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600">
                                        <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                                            <svg class="size-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span id="preview-phone">-</span>
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="inline-flex items-center gap-1 text-slate-500">
                                            <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span id="preview-address" class="line-clamp-1 italic text-slate-500">Alamat belum diisi</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                onclick="resetCustomerSelection()"
                                class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-emerald-100 hover:text-slate-700 focus:outline-none"
                                title="Ganti / Bersihkan Pilihan"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <p class="mt-2 text-[11px] text-slate-400">
                        Pelanggan belum terdaftar? Klik tombol <span class="font-medium text-brand-600">Pelanggan Baru</span> di atas untuk mendaftarkannya secara instan.
                    </p>
                </div>
            </div>

            {{-- Kartu 2: Pemilihan Layanan & Timbangan Berat (Multi-Item) --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Layanan & Timbangan Cucian</h2>
                            <p class="text-[11px] text-slate-500">Pilih layanan laundry, input berat, dan kalkulasi subtotal otomatis.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="addItem()"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50/80 px-3 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-100 active:scale-98 shadow-2xs"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Layanan Lain</span>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Baris 0 (Item Utama) --}}
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/40 p-4 transition hover:border-slate-300">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="inline-flex items-center rounded-md bg-brand-100 px-2 py-0.5 text-[11px] font-bold text-brand-800">
                                Item #1
                            </span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500">
                                    Subtotal: <strong class="text-brand-700" x-text="formatRupiah(getSubtotal(items[0]))">Rp 0</strong>
                                </span>
                            </div>
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
                                        x-model="items[0].service_id"
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
                                                {{ $service->name }} — Rp {{ number_format($service->price, 0, ',', '.') }}/kg ({{ $service->estimated_duration }} Jam)
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
                                        x-model="items[0].weight"
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
                    </div>

                    {{-- Baris Tambahan (Item 2, 3, dst.) via Alpine --}}
                    <template x-for="(item, index) in items.slice(1)" :key="index + 1">
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/40 p-4 transition hover:border-slate-300">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="inline-flex items-center rounded-md bg-brand-100 px-2 py-0.5 text-[11px] font-bold text-brand-800" x-text="'Item #' + (index + 2)"></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-500">
                                        Subtotal: <strong class="text-brand-700" x-text="formatRupiah(getSubtotal(items[index + 1]))">Rp 0</strong>
                                    </span>
                                    <button
                                        type="button"
                                        @click="removeItem(index + 1)"
                                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-800"
                                        title="Hapus baris item ini"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">
                                        Jenis Layanan <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="mt-1.5">
                                        <select
                                            :name="'items[' + (index + 1) + '][service_id]'"
                                            x-model="items[index + 1].service_id"
                                            required
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                        >
                                            <option value="">-- Pilih Layanan --</option>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}">
                                                    {{ $service->name }} — Rp {{ number_format($service->price, 0, ',', '.') }}/kg ({{ $service->estimated_duration }} Jam)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">
                                        Berat Cucian (Kg) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative mt-1.5">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            :name="'items[' + (index + 1) + '][weight]'"
                                            x-model="items[index + 1].weight"
                                            required
                                            placeholder="0.00"
                                            class="w-full rounded-xl border border-slate-300 bg-white pr-12 pl-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                        />
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                            <span class="text-xs font-semibold text-slate-400">Kg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                @error('items')
                    <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kartu 3: Ringkasan Kalkulasi Nota POS Real-Time --}}
            <div class="rounded-2xl border border-brand-200/90 bg-gradient-to-br from-brand-50/70 via-white to-brand-50/30 p-5 shadow-xs sm:p-6">
                <div class="flex items-center justify-between border-b border-brand-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Ringkasan Kalkulasi Nota POS</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700">
                        <span class="size-1.5 rounded-full bg-brand-600"></span>
                        Otomatis / Real-Time
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 bg-white/70 p-3 shadow-2xs">
                        <span class="text-[11px] font-medium text-slate-500">Total Berat Cucian:</span>
                        <p class="mt-1 text-base font-bold text-slate-900" x-text="getTotalWeight().toFixed(2) + ' kg'">0.00 kg</p>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-white/70 p-3 shadow-2xs">
                        <span class="text-[11px] font-medium text-slate-500">Estimasi Selesai Terlama:</span>
                        <p class="mt-1 text-base font-bold text-slate-900" x-text="getMaxDuration() + ' Jam'">0 Jam</p>
                    </div>

                    <div class="col-span-2 rounded-xl border border-brand-200 bg-white p-3 shadow-2xs sm:col-span-1">
                        <span class="text-[11px] font-medium text-brand-800">Estimasi Total Bayar:</span>
                        <p class="mt-1 text-xl font-extrabold text-brand-700" x-text="formatRupiah(getTotalAmount())">Rp 0</p>
                    </div>
                </div>
            </div>

            {{-- Kartu 4: Catatan Otomatisasi & Tombol Aksi --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs leading-relaxed text-slate-500">
                            Kode nota unik, estimasi waktu selesai, dan kalkulasi snapshot harga detail transaksi dihitung dan disimpan otomatis oleh server.
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <a
                            href="{{ $transactionIndexRoute }}"
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

        {{-- Modal Tambah Pelanggan Cepat --}}
        <div
            id="modal-quick-customer"
            class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200"
            aria-labelledby="modal-quick-customer-title"
            role="dialog"
            aria-modal="true"
            onclick="if (event.target === this) closeQuickCustomerModal()"
        >
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all sm:my-8">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" id="modal-quick-customer-title">
                                    Tambah Pelanggan Baru
                                </h3>
                                <p class="text-[11px] text-slate-500">Daftarkan pelanggan baru langsung tanpa keluar dari kasir POS.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="closeQuickCustomerModal()"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none"
                            aria-label="Tutup"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Container Error Validasi AJAX --}}
                    <div id="quick-customer-errors" class="mt-4 hidden rounded-xl border border-rose-200 bg-rose-50/80 p-3 text-xs text-rose-800">
                        <div class="flex items-center gap-1.5 font-semibold">
                            <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Gagal menyimpan data pelanggan:</span>
                        </div>
                        <ul id="quick-customer-errors-list" class="mt-1 list-inside list-disc space-y-0.5 pl-2 font-normal"></ul>
                    </div>

                    <form id="form-quick-customer" onsubmit="submitQuickCustomer(event)" class="mt-4 space-y-4">
                        <div>
                            <label for="quick_customer_name" class="block text-xs font-semibold text-slate-700">
                                Nama Pelanggan <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="quick_customer_name"
                                    name="name"
                                    required
                                    placeholder="Contoh: Budi Santoso"
                                    class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="quick_customer_phone" class="block text-xs font-semibold text-slate-700">
                                    Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-400">Otomatis format 08xxx</span>
                            </div>
                            <div class="relative mt-1.5">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="quick_customer_phone"
                                    name="phone"
                                    required
                                    placeholder="081234567890"
                                    class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="quick_customer_address" class="block text-xs font-semibold text-slate-700">
                                Alamat <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="relative mt-1.5">
                                <div class="pointer-events-none absolute top-3 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <textarea
                                    id="quick_customer_address"
                                    name="address"
                                    rows="2"
                                    placeholder="Contoh: Jl. Merdeka No. 10, RT 02/05"
                                    class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                ></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                            <button
                                type="button"
                                onclick="closeQuickCustomerModal()"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                id="btn-save-quick-customer"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                            >
                                <svg id="btn-save-quick-customer-spinner" class="hidden size-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span id="btn-save-quick-customer-text">Simpan & Pilih</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Alpine POS Manager
        function posTransactionManager() {
            return {
                items: @json(old('items', [['service_id' => '', 'weight' => '']])),
                servicesMap: @json($services->keyBy('id')),
                addItem() {
                    this.items.push({ service_id: '', weight: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                getService(id) {
                    if (!id) return null;
                    return this.servicesMap[id] || null;
                },
                getSubtotal(item) {
                    if (!item) return 0;
                    let s = this.getService(item.service_id);
                    let w = parseFloat(item.weight) || 0;
                    if (!s || w <= 0) return 0;
                    return w * parseFloat(s.price);
                },
                getTotalAmount() {
                    return this.items.reduce((sum, item) => sum + this.getSubtotal(item), 0);
                },
                getTotalWeight() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.weight) || 0), 0);
                },
                getMaxDuration() {
                    let max = 0;
                    for (let item of this.items) {
                        let s = this.getService(item.service_id);
                        if (s && s.estimated_duration > max) {
                            max = s.estimated_duration;
                        }
                    }
                    return max;
                },
                formatRupiah(val) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
                }
            };
        }

        // Direktori data pelanggan untuk update preview instan
        const customersDirectory = @json($customerDirectory);

        function getCustomerInitials(name) {
            if (!name) return 'PL';
            const parts = name.trim().split(/\s+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        }

        function updateCustomerPreview(customerId) {
            const card = document.getElementById('customer-preview-card');
            const strId = customerId ? String(customerId) : '';

            if (!strId || !customersDirectory[strId]) {
                if (card) card.classList.add('hidden');
                return;
            }

            const cust = customersDirectory[strId];
            const nameEl = document.getElementById('preview-name');
            const phoneEl = document.getElementById('preview-phone');
            const addressEl = document.getElementById('preview-address');
            const avatarEl = document.getElementById('preview-avatar');

            if (nameEl) nameEl.textContent = cust.name;
            if (phoneEl) phoneEl.textContent = cust.phone;
            if (avatarEl) avatarEl.textContent = getCustomerInitials(cust.name);

            if (addressEl) {
                if (cust.address && cust.address.trim() !== '') {
                    addressEl.textContent = cust.address;
                    addressEl.classList.remove('italic', 'text-slate-400');
                    addressEl.classList.add('text-slate-600');
                } else {
                    addressEl.textContent = 'Alamat belum diisi';
                    addressEl.classList.add('italic', 'text-slate-400');
                    addressEl.classList.remove('text-slate-600');
                }
            }

            if (card) card.classList.remove('hidden');
        }

        function onCustomerSelectChange(val) {
            updateCustomerPreview(val);
        }

        function resetCustomerSelection() {
            const select = document.getElementById('customer_id');
            if (select) {
                select.value = '';
                updateCustomerPreview('');
                select.focus();
            }
        }

        // Tampilkan preview saat load jika ada opsi lama terpilih
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('customer_id');
            if (select && select.value) {
                updateCustomerPreview(select.value);
            }
        });

        function openQuickCustomerModal() {
            const modal = document.getElementById('modal-quick-customer');
            if (modal) {
                modal.classList.remove('hidden');
                document.getElementById('quick-customer-errors').classList.add('hidden');
                document.getElementById('quick-customer-errors-list').innerHTML = '';
                document.getElementById('form-quick-customer').reset();
                setTimeout(() => {
                    const nameInput = document.getElementById('quick_customer_name');
                    if (nameInput) nameInput.focus();
                }, 100);
            }
        }

        function closeQuickCustomerModal() {
            const modal = document.getElementById('modal-quick-customer');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeQuickCustomerModal();
            }
        });

        async function submitQuickCustomer(event) {
            event.preventDefault();
            const btn = document.getElementById('btn-save-quick-customer');
            const spinner = document.getElementById('btn-save-quick-customer-spinner');
            const btnText = document.getElementById('btn-save-quick-customer-text');
            const errorBox = document.getElementById('quick-customer-errors');
            const errorList = document.getElementById('quick-customer-errors-list');

            const name = document.getElementById('quick_customer_name').value.trim();
            const phone = document.getElementById('quick_customer_phone').value.trim();
            const address = document.getElementById('quick_customer_address').value.trim();

            if (!name || !phone) {
                return;
            }

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Menyimpan...';
            errorBox.classList.add('hidden');
            errorList.innerHTML = '';

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

                const response = await fetch('{{ route("{$currentRoutePrefix}.customers.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        name: name,
                        phone: phone,
                        address: address,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success && data.customer) {
                    const cust = data.customer;
                    const strId = String(cust.id);
                    customersDirectory[strId] = cust;

                    const customerSelect = document.getElementById('customer_id');
                    if (customerSelect) {
                        const option = document.createElement('option');
                        option.value = cust.id;
                        option.textContent = `${cust.name} (${cust.phone})`;
                        option.selected = true;
                        customerSelect.appendChild(option);
                        customerSelect.value = cust.id;
                        updateCustomerPreview(strId);
                    }

                    closeQuickCustomerModal();
                } else if (response.status === 422 && data.errors) {
                    errorList.innerHTML = '';
                    Object.values(data.errors).forEach(errArray => {
                        errArray.forEach(err => {
                            const li = document.createElement('li');
                            li.textContent = err;
                            errorList.appendChild(li);
                        });
                    });
                    errorBox.classList.remove('hidden');
                } else {
                    errorList.innerHTML = `<li>${data.message || 'Gagal menyimpan data pelanggan.'}</li>`;
                    errorBox.classList.remove('hidden');
                }
            } catch (err) {
                errorList.innerHTML = '<li>Terjadi kesalahan koneksi. Silakan coba lagi.</li>';
                errorBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Simpan & Pilih';
            }
        }
    </script>
</x-layouts::app>
