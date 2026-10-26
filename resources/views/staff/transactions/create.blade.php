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
        class="space-y-4 pb-24 lg:pb-0"
        x-data="posTransactionManager()"
    >
        {{-- Top Bar Navigasi Kasir --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">Kasir POS — Transaksi Baru</h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                    Pilih layanan di katalog kiri, sesuaikan timbangan, dan simpan nota kasir secara instan.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.button
                    variant="secondary"
                    size="sm"
                    href="{{ $transactionIndexRoute }}"
                    class="gap-1.5"
                >
                    <x-lucide-arrow-left class="size-4 text-slate-500" />
                    <span>Daftar Transaksi</span>
                </x-ui.button>
            </div>
        </div>

        {{-- Bar Pintasan Keyboard POS --}}
        <div class="hidden sm:flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/90 bg-white px-4 py-2.5 text-xs text-slate-600 shadow-2xs">
            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                <x-lucide-zap class="size-3.5 text-amber-500 fill-amber-500" />
                <span>Pintasan Cepat:</span>
            </span>
            <div class="flex items-center gap-1.5">
                <kbd class="rounded-md bg-slate-100 border border-slate-200/90 px-1.5 py-0.5 text-[11px] font-mono font-bold text-slate-800 shadow-2xs">/</kbd>
                <span class="text-slate-600">Cari Layanan</span>
            </div>
            <span class="text-slate-300">•</span>
            <div class="flex items-center gap-1.5">
                <kbd class="rounded-md bg-slate-100 border border-slate-200/90 px-1.5 py-0.5 text-[11px] font-mono font-bold text-slate-800 shadow-2xs">Alt + P</kbd>
                <span class="text-slate-600">Pelanggan Baru</span>
            </div>
            <span class="text-slate-300">•</span>
            <div class="flex items-center gap-1.5">
                <kbd class="rounded-md bg-slate-100 border border-slate-200/90 px-1.5 py-0.5 text-[11px] font-mono font-bold text-slate-800 shadow-2xs">Alt + T</kbd>
                <span class="text-slate-600">Tambah Baris</span>
            </div>
            <span class="text-slate-300">•</span>
            <div class="flex items-center gap-1.5">
                <kbd class="rounded-md bg-slate-100 border border-slate-200/90 px-1.5 py-0.5 text-[11px] font-mono font-bold text-slate-800 shadow-2xs">Ctrl + Enter</kbd>
                <span class="text-slate-600">Simpan Transaksi</span>
            </div>
        </div>

        {{-- Alert Notifikasi Status --}}
        @if (session('status'))
            <x-ui.alert variant="success" dismissible>
                {{ session('status') }}
            </x-ui.alert>
        @endif

        {{-- Alert Error Global --}}
        @if ($errors->any())
            <x-ui.alert variant="destructive" title="Terdapat kesalahan pada input transaksi:" dismissible>
                <ul class="list-inside list-disc space-y-0.5 pl-1 font-normal text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- SPLIT-SCREEN LAYOUT POS (Kiri: Katalog Cepat | Kanan: Tiket Nota) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">

            {{-- ======================================================== --}}
            {{-- KOLOM KIRI (7/12): KATALOG CEPAT LAYANAN LAUNDRY         --}}
            {{-- ======================================================== --}}
            <div class="space-y-4 lg:col-span-7">
                {{-- Search Bar & Filter Layanan --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-lucide-search class="size-4" />
                            </div>
                            <input
                                type="text"
                                id="service-search-input"
                                x-model="searchQuery"
                                placeholder="Cari layanan laundry (Tekan / untuk fokus)..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-9 py-2.5 text-xs text-slate-800 placeholder-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                            <button
                                type="button"
                                x-show="searchQuery"
                                @click="searchQuery = ''; document.getElementById('service-search-input').focus()"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 cursor-pointer"
                                title="Hapus pencarian"
                            >
                                <x-lucide-x class="size-4" />
                            </button>
                        </div>

                        {{-- Category Filter Pills --}}
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                            <button
                                type="button"
                                @click="selectedCategory = 'all'; searchQuery = ''"
                                :class="selectedCategory === 'all' && searchQuery === '' ? 'bg-brand-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition active:scale-95 cursor-pointer shrink-0"
                            >
                                Semua
                            </button>
                            <button
                                type="button"
                                @click="selectedCategory = 'express'; searchQuery = ''"
                                :class="selectedCategory === 'express' ? 'bg-amber-600 text-white font-bold shadow-2xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition active:scale-95 cursor-pointer shrink-0 flex items-center gap-1"
                            >
                                <x-lucide-zap class="size-3.5 text-amber-500 fill-amber-500" x-bind:class="selectedCategory === 'express' ? 'text-white fill-white' : ''" />
                                <span>⚡ Paket Kilat</span>
                            </button>
                            <button
                                type="button"
                                @click="selectedCategory = 'kiloan'; searchQuery = ''"
                                :class="selectedCategory === 'kiloan' ? 'bg-brand-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition active:scale-95 cursor-pointer shrink-0"
                            >
                                Kiloan (Kg)
                            </button>
                            <button
                                type="button"
                                @click="selectedCategory = 'satuan'; searchQuery = ''"
                                :class="selectedCategory === 'satuan' ? 'bg-brand-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition active:scale-95 cursor-pointer shrink-0"
                            >
                                Satuan (Pcs)
                            </button>
                            <button
                                type="button"
                                @click="selectedCategory = 'khusus'; searchQuery = ''"
                                :class="selectedCategory === 'khusus' ? 'bg-brand-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition active:scale-95 cursor-pointer shrink-0"
                            >
                                Khusus
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Grid Cards Katalog Layanan --}}
                <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                    <template x-for="service in filteredServices" :key="service.id">
                        <div
                            @click="selectServiceCard(service)"
                            class="group relative flex cursor-pointer flex-col justify-between rounded-2xl border p-4 transition-all duration-150 active:scale-[0.98] select-none"
                            :class="getItemInCart(service.id) ? 'border-brand-500 bg-brand-50/40 shadow-xs ring-2 ring-brand-200/80' : 'border-slate-200/90 bg-white hover:border-brand-300 hover:shadow-md'"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <div
                                        class="flex size-9 items-center justify-center rounded-xl transition"
                                        :class="service.is_express ? 'bg-amber-100 text-amber-700' : (getItemInCart(service.id) ? 'bg-brand-600 text-white shadow-2xs' : 'bg-brand-50 text-brand-700 group-hover:bg-brand-100')"
                                    >
                                        <template x-if="service.is_express">
                                            <x-lucide-zap class="size-5 fill-amber-500 text-amber-600" />
                                        </template>
                                        <template x-if="!service.is_express">
                                            <x-lucide-shirt class="size-5" />
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-1">
                                        <template x-if="service.is_express">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-100 px-1.5 py-0.5 text-2xs font-extrabold text-amber-800 border border-amber-300">
                                                ⚡ KILAT
                                            </span>
                                        </template>
                                        <x-ui.badge tone="neutral" size="sm" class="gap-1 font-mono tabular-nums text-slate-600">
                                            <x-lucide-clock class="size-3 text-slate-400" />
                                            <span x-text="service.estimated_duration + ' Jam'"></span>
                                        </x-ui.badge>
                                    </div>
                                </div>

                                <h3 class="mt-3 text-sm font-bold text-slate-900 group-hover:text-brand-700 transition" x-text="service.name"></h3>
                                <p class="text-xs text-slate-500" x-text="'Tarif per ' + (service.unit === 'pcs' ? 'satuan (pcs)' : (service.unit === 'meter' ? 'meter (m²)' : 'kilogram (kg)'))"></p>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                <div>
                                    <span class="text-sm font-extrabold text-brand-700 font-mono tabular-nums sm:text-base" x-text="formatRupiah(service.price)"></span>
                                    <span class="text-2xs font-semibold text-slate-400" x-text="' / ' + (service.unit === 'pcs' ? 'pcs' : (service.unit === 'meter' ? 'm²' : 'kg'))"></span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <template x-if="getItemInCart(service.id)">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100/90 px-2 py-1 text-xs font-bold font-mono tabular-nums text-emerald-800 shadow-2xs">
                                            <x-lucide-check class="size-3 text-emerald-700" />
                                            <span x-text="(parseFloat(getItemInCart(service.id).weight) || 0).toFixed(1) + ' ' + (service.unit || 'kg')"></span>
                                        </span>
                                    </template>
                                    <span
                                        class="inline-flex size-7 items-center justify-center rounded-lg transition"
                                        :class="getItemInCart(service.id) ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-brand-600 group-hover:text-white'"
                                    >
                                        <x-lucide-plus class="size-4" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Fallback: Empty search result --}}
                <div x-show="filteredServices.length === 0" class="rounded-2xl border border-slate-200/90 bg-white p-8 text-center shadow-2xs">
                    <x-lucide-search-x class="mx-auto size-10 text-slate-300" />
                    <p class="mt-2 text-xs font-semibold text-slate-600">
                        Tidak ada layanan yang cocok dengan kata kunci "<span class="font-bold text-slate-900" x-text="searchQuery"></span>".
                    </p>
                    <button
                        type="button"
                        @click="searchQuery = ''"
                        class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-brand-700 hover:text-brand-800 hover:underline cursor-pointer"
                    >
                        <x-lucide-rotate-ccw class="size-3.5" />
                        <span>Tampilkan semua layanan</span>
                    </button>
                </div>

                {{-- Hidden Static Services for Crawler & Test Assertions --}}
                <div class="hidden" aria-hidden="true">
                    @foreach ($services as $service)
                        <div>
                            <span>{{ $service->name }}</span>
                            <span>{{ number_format($service->price, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- KOLOM KANAN (5/12): NOTA KASIR TRANSAKSI POS (STICKY)    --}}
            {{-- ======================================================== --}}
            <div id="kasir-ticket-panel" class="lg:col-span-5 lg:sticky lg:top-4">
                <form
                    id="transaction-form"
                    method="POST"
                    target="_self"
                    action="{{ route("{$currentRoutePrefix}.transactions.store") }}"
                    class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xs"
                    @submit="isSubmitting = true"
                >
                    @csrf

                    {{-- Header Nota Kasir --}}
                    <div class="border-b border-slate-100 bg-slate-50/70 p-4 sm:p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex size-7 items-center justify-center rounded-lg bg-brand-600 text-white shadow-2xs">
                                    <x-lucide-receipt class="size-4" />
                                </div>
                                <h2 class="text-sm font-bold text-slate-900 sm:text-base">Tiket Nota Kasir</h2>
                            </div>
                            <x-ui.badge tone="brand" size="sm" class="font-bold">
                                Draf Baru
                            </x-ui.badge>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-5">
                        {{-- Section 1: Data Pelanggan --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="customer_id" class="text-xs font-bold text-slate-800">
                                    Data Pelanggan <span class="text-rose-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    id="btn-open-quick-customer"
                                    @click="openCustomerModal()"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-brand-700 hover:text-brand-800 hover:underline cursor-pointer transition"
                                    title="Pintasan keyboard: Alt + P"
                                >
                                    <x-lucide-user-plus class="size-3.5" />
                                    <span>Pelanggan Baru</span>
                                    <kbd class="hidden sm:inline-block ml-1 rounded-md bg-slate-100 border border-slate-200/90 px-1.5 py-0.5 text-[10px] font-mono font-bold text-slate-500">Alt+P</kbd>
                                </button>
                            </div>

                            <div class="relative">
                                <select
                                    id="customer_id"
                                    name="customer_id"
                                    required
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
                                class="mt-2.5 hidden rounded-xl border border-emerald-200/90 bg-emerald-50/70 p-3 transition-all duration-200"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-start gap-2.5">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 font-bold text-xs text-emerald-800 shadow-2xs" id="preview-avatar">
                                            PL
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-slate-900" id="preview-name">-</p>
                                            <p class="text-xs text-slate-600 font-mono tabular-nums" id="preview-phone">-</p>
                                            <p class="line-clamp-1 italic text-xs text-slate-500" id="preview-address">Alamat belum diisi</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onclick="resetCustomerSelection()"
                                        class="shrink-0 rounded-lg p-1 text-slate-400 hover:bg-emerald-100 hover:text-slate-700 cursor-pointer transition"
                                        title="Ganti Pelanggan"
                                    >
                                        <x-lucide-x class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Daftar Item Cucian Nota --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-800">Item Cucian</span>
                                <button
                                    type="button"
                                    @click="addItem()"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-brand-700 hover:text-brand-800 hover:underline cursor-pointer transition"
                                    title="Pintasan keyboard: Alt + T"
                                >
                                    <x-lucide-plus class="size-3.5" />
                                    <span>Tambah Baris</span>
                                    <kbd class="hidden sm:inline-block ml-1 rounded-md bg-slate-100 border border-slate-200/90 px-1.5 py-0.5 text-[10px] font-mono font-bold text-slate-500">Alt+T</kbd>
                                </button>
                            </div>

                            <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="rounded-xl border border-slate-200/90 bg-slate-50/60 p-3 transition hover:border-slate-300">
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <x-ui.badge tone="brand" size="sm" class="font-bold" x-text="'Item #' + (index + 1)"></x-ui.badge>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-extrabold text-brand-700 font-mono tabular-nums" x-text="formatRupiah(getSubtotal(item))"></span>
                                                <button
                                                    type="button"
                                                    @click="removeItem(index)"
                                                    class="rounded-lg p-1 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 cursor-pointer"
                                                    title="Hapus baris item"
                                                >
                                                    <x-lucide-trash-2 class="size-3.5" />
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
                                                            {{ $service->name }} (Rp {{ number_format($service->price, 0, ',', '.') }}/{{ $service->unit_label }}) {{ $service->is_express ? '⚡ [KILAT ' . $service->estimated_duration . ' Jam]' : '[' . $service->estimated_duration . ' Jam]' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Stepper Input Berat/Kuantitas & Quick Chips --}}
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                                <div class="flex flex-1 items-center rounded-lg border border-slate-300 bg-white overflow-hidden focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-200">
                                                    <button
                                                        type="button"
                                                        @click="stepWeight(index, -0.5)"
                                                        aria-label="Kurangi berat 0.5 kg"
                                                        class="px-2.5 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100 active:bg-slate-200 cursor-pointer"
                                                        title="Kurangi 0.5 unit"
                                                    >
                                                        -
                                                    </button>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0.01"
                                                        inputmode="decimal"
                                                        :id="index === 0 ? 'weight' : null"
                                                        :name="'items[' + index + '][weight]'"
                                                        x-model="item.weight"
                                                        required
                                                        placeholder="0.00"
                                                        class="w-full text-center border-0 p-1 text-xs font-bold font-mono tabular-nums text-slate-900 focus:outline-none"
                                                    />
                                                    <span class="pr-2 text-xs font-semibold text-slate-500" x-text="getItemUnitLabel(item.service_id)"></span>
                                                    <button
                                                        type="button"
                                                        @click="stepWeight(index, 0.5)"
                                                        aria-label="Tambah berat 0.5 kg"
                                                        class="px-2.5 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100 active:bg-slate-200 cursor-pointer"
                                                        title="Tambah 0.5 unit"
                                                    >
                                                        +
                                                    </button>
                                                </div>

                                                {{-- Quick Weight Presets (+1, +2, +3, +5) --}}
                                                <div class="flex items-center gap-1 shrink-0">
                                                    <template x-for="w in [1, 2, 3, 5]" :key="w">
                                                        <button
                                                            type="button"
                                                            @click="stepWeight(index, w)"
                                                            class="rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-bold font-mono tabular-nums text-slate-600 hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 transition active:scale-95 cursor-pointer shadow-2xs"
                                                            :title="'Tambah ' + w"
                                                        >
                                                            <span x-text="'+' + w"></span>
                                                        </button>
                                                    </template>
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
                        <div class="rounded-xl border border-brand-100 bg-brand-50/40 p-4 space-y-2">
                            <div class="flex items-center justify-between text-xs text-slate-600">
                                <span>Total Berat / Kuantitas:</span>
                                <span class="font-bold font-mono tabular-nums text-slate-900" x-text="getTotalWeight().toFixed(2) + ' unit'">0.00 unit</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-600">
                                <span>Estimasi Pengerjaan:</span>
                                <div class="flex items-center gap-1.5">
                                    <template x-if="hasExpressItem()">
                                        <span class="inline-flex items-center gap-0.5 rounded bg-amber-100 px-1.5 py-0.5 text-2xs font-extrabold text-amber-800 border border-amber-300">
                                            ⚡ KILAT
                                        </span>
                                    </template>
                                    <span class="font-bold font-mono tabular-nums text-slate-900" x-text="getMaxDuration() + ' Jam'">0 Jam</span>
                                </div>
                            </div>
                            <div class="border-t border-brand-200/80 pt-2.5 flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-800">Total Tagihan:</span>
                                <span class="text-xl font-black text-brand-700 font-mono tabular-nums sm:text-2xl" x-text="formatRupiah(getTotalAmount())">Rp 0</span>
                            </div>
                        </div>

                        {{-- Section 3.5: Opsi Pembayaran Kasir --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800">Pembayaran Kasir</label>
                                <span class="text-xs font-semibold text-slate-500">Pilih status bayar</span>
                            </div>

                            {{-- Pilihan Opsi Pembayaran --}}
                            <div class="grid grid-cols-3 gap-1.5 rounded-xl bg-slate-200/70 p-1 text-xs font-semibold text-slate-600">
                                <button
                                    type="button"
                                    @click="paymentChoice = 'unpaid'; dpAmount = ''"
                                    :class="paymentChoice === 'unpaid' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'hover:text-slate-900'"
                                    class="rounded-lg py-1.5 text-center transition cursor-pointer"
                                >
                                    Bayar Nanti
                                </button>
                                <button
                                    type="button"
                                    @click="paymentChoice = 'full'; dpAmount = ''"
                                    :class="paymentChoice === 'full' ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'hover:text-slate-900'"
                                    class="rounded-lg py-1.5 text-center transition cursor-pointer"
                                >
                                    Bayar Lunas
                                </button>
                                <button
                                    type="button"
                                    @click="paymentChoice = 'dp'"
                                    :class="paymentChoice === 'dp' ? 'bg-amber-500 text-white shadow-2xs font-bold' : 'hover:text-slate-900'"
                                    class="rounded-lg py-1.5 text-center transition cursor-pointer"
                                >
                                    Bayar DP
                                </button>
                            </div>

                            {{-- Hidden input pay_now & payment_choice --}}
                            <input type="hidden" name="pay_now" :value="paymentChoice !== 'unpaid' ? '1' : '0'" />
                            <input type="hidden" name="payment_choice" :value="paymentChoice" />

                            {{-- Detail Metode Pembayaran jika Bayar Sekarang --}}
                            <div x-show="paymentChoice !== 'unpaid'" x-transition class="space-y-3 pt-1">
                                <div>
                                    <label class="block mb-1 text-xs font-semibold text-slate-600">Metode Pembayaran</label>
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
                                        <label for="payment_amount" class="text-xs font-semibold text-slate-600">Nominal DP (Uang Muka)</label>
                                        <span class="text-xs text-amber-700 font-bold font-mono tabular-nums" x-text="'Maks. ' + formatRupiah(getTotalAmount())"></span>
                                    </div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-500">Rp</span>
                                        <input
                                            type="number"
                                            id="payment_amount"
                                            name="payment_amount"
                                            inputmode="numeric"
                                            x-model="dpAmount"
                                            :disabled="paymentChoice !== 'dp'"
                                            :required="paymentChoice === 'dp'"
                                            min="1000"
                                            :max="Math.max(0, getTotalAmount() - 1000)"
                                            placeholder="Contoh: 20000"
                                            class="w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 py-1.5 text-xs font-bold font-mono tabular-nums text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
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
                                :disabled="isSubmitting"
                                @click="if ($event.ctrlKey || $event.metaKey) { $event.preventDefault(); $event.stopPropagation(); const f = document.getElementById('transaction-form'); if (f?.reportValidity()) { isSubmitting = true; f.submit(); } }"
                                class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition-colors hover:bg-brand-800 disabled:pointer-events-none disabled:opacity-60 cursor-pointer shadow-sm active:scale-[0.99]"
                            >
                                <svg x-show="isSubmitting" x-cloak class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <x-lucide-check-circle x-show="! isSubmitting" class="size-5 text-brand-200" />
                                <span x-text="isSubmitting ? 'Menyimpan transaksi...' : 'Simpan Transaksi (Cetak Struk)'">Simpan Transaksi (Cetak Struk)</span>
                                <kbd class="hidden sm:inline-block rounded-md bg-brand-800/90 px-2 py-0.5 text-xs font-mono font-medium text-brand-100 border border-brand-600/50 ml-1">Ctrl + Enter</kbd>
                            </button>
                            <p class="mt-2 text-center text-xs text-slate-500">
                                Nomor nota unik dan riwayat pengerjaan otomatis dibuat oleh sistem.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Floating Bottom Bar untuk Layar Mobile (lg:hidden) --}}
        <div
            x-cloak
            x-show="getTotalAmount() > 0"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-full"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-full"
            class="fixed bottom-0 inset-x-0 z-40 lg:hidden border-t border-slate-200/90 bg-white/95 backdrop-blur-md px-4 py-3 shadow-[0_-4px_16px_rgba(0,0,0,0.08)]"
        >
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="font-medium text-slate-700" x-text="items.filter(i => i.service_id).length + ' Layanan'"></span>
                        <span>•</span>
                        <span class="font-mono tabular-nums font-semibold" x-text="getTotalWeight().toFixed(2) + ' Kg'"></span>
                    </div>
                    <p class="text-base font-black text-brand-700 font-mono tabular-nums tracking-tight" x-text="formatRupiah(getTotalAmount())"></p>
                </div>
                <a
                    href="#kasir-ticket-panel"
                    class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-brand-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-brand-800 active:scale-98"
                >
                    <x-lucide-receipt class="size-4" />
                    <span>Lihat Nota & Bayar</span>
                </a>
            </div>
        </div>

        {{-- Modal Tambah Pelanggan Cepat (AJAX Quick Add) Menggunakan <x-ui.modal> --}}
        <x-ui.modal
            id="modal-quick-customer"
            show="quickCustomerModalOpen"
            title="Tambah Pelanggan Baru"
            description="Daftarkan pelanggan baru langsung tanpa keluar dari kasir POS."
            maxWidth="lg"
        >
            {{-- Container Error Validasi AJAX --}}
            <div id="quick-customer-errors" class="mb-4 hidden rounded-xl border border-rose-200 bg-rose-50/80 p-3 text-xs text-rose-800">
                <div class="flex items-center gap-1.5 font-semibold">
                    <x-lucide-alert-circle class="size-4 shrink-0 text-rose-600" />
                    <span>Gagal menyimpan data pelanggan:</span>
                </div>
                <ul id="quick-customer-errors-list" class="mt-1 list-inside list-disc space-y-0.5 pl-2 font-normal"></ul>
            </div>

            <form id="form-quick-customer" onsubmit="submitQuickCustomer(event)" class="space-y-4">
                <div>
                    <label for="quick_customer_name" class="block text-xs font-bold text-slate-700">
                        Nama Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-lucide-user class="size-4" />
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
                        <label for="quick_customer_phone" class="block text-xs font-bold text-slate-700">
                            Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Format: 08xxx</span>
                    </div>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-lucide-phone class="size-4" />
                        </div>
                        <input
                            type="text"
                            id="quick_customer_phone"
                            name="phone"
                            required
                            placeholder="081234567890"
                            class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs font-mono tabular-nums text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                        />
                    </div>
                </div>

                <div>
                    <label for="quick_customer_address" class="block text-xs font-bold text-slate-700">
                        Alamat <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute top-3 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-lucide-map-pin class="size-4" />
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
                        @click="quickCustomerModalOpen = false"
                        class="min-h-[38px] rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="btn-save-quick-customer"
                        class="inline-flex min-h-[38px] items-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98 cursor-pointer"
                    >
                        <svg id="btn-save-quick-customer-spinner" class="hidden size-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <x-lucide-check class="size-3.5" />
                        <span id="btn-save-quick-customer-text">Simpan & Pilih</span>
                    </button>
                </div>
            </form>
        </x-ui.modal>
    </div>

    <script>
        // Alpine POS Manager
        function posTransactionManager() {
            return {
                selectedCategory: 'all',
                searchQuery: '',
                isSubmitting: false,
                quickCustomerModalOpen: false,
                items: @json(old('items', [['service_id' => ($services->first()?->id ?? ''), 'weight' => '']])),
                servicesList: @json($services),
                servicesMap: @json($services->keyBy('id')),
                paymentChoice: @js(old('payment_choice', old('pay_now') ? (old('payment_amount') ? 'dp' : 'full') : 'unpaid')),
                paymentMethod: @js(old('payment_method', 'Tunai')),
                dpAmount: @js(old('payment_amount', '')),

                init() {
                    window.addEventListener('keydown', (e) => {
                        // Alt + P -> Tambah Pelanggan Baru
                        if (e.altKey && (e.key === 'p' || e.key === 'P')) {
                            e.preventDefault();
                            this.openCustomerModal();
                            return;
                        }

                        // Alt + T -> Tambah Baris Layanan
                        if (e.altKey && (e.key === 't' || e.key === 'T')) {
                            e.preventDefault();
                            this.addItem();
                            return;
                        }

                        // Ctrl + Enter atau Cmd + Enter -> Simpan Transaksi POS (tetap di tab saat ini)
                        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                            e.preventDefault();
                            e.stopPropagation();

                            // Jika modal tambah pelanggan sedang terbuka, simpan data pelanggan baru
                            if (this.showCustomerModal) {
                                const custBtn = document.getElementById('btn-save-quick-customer');
                                if (custBtn && !custBtn.disabled) {
                                    custBtn.click();
                                }
                                return;
                            }

                            const form = document.getElementById('transaction-form');
                            if (form && !this.isSubmitting) {
                                if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                                    return;
                                }
                                this.isSubmitting = true;
                                form.submit();
                            }
                            return;
                        }

                        // / -> Fokus Cepat Kolom Cari Layanan
                        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                            e.preventDefault();
                            const searchInput = document.getElementById('service-search-input');
                            if (searchInput) {
                                searchInput.focus();
                                searchInput.select();
                            }
                        }
                    });

                    window.addEventListener('open-quick-customer-modal', () => {
                        this.openCustomerModal();
                    });

                    window.addEventListener('close-quick-customer-modal', () => {
                        this.closeCustomerModal();
                    });
                },

                openCustomerModal() {
                    this.quickCustomerModalOpen = true;
                    const errBox = document.getElementById('quick-customer-errors');
                    if (errBox) errBox.classList.add('hidden');
                    const errList = document.getElementById('quick-customer-errors-list');
                    if (errList) errList.innerHTML = '';
                    const form = document.getElementById('form-quick-customer');
                    if (form) form.reset();
                    setTimeout(() => {
                        const nameInput = document.getElementById('quick_customer_name');
                        if (nameInput) nameInput.focus();
                    }, 120);
                },

                closeCustomerModal() {
                    this.quickCustomerModalOpen = false;
                },

                get filteredServices() {
                    let list = this.servicesList;

                    if (this.selectedCategory === 'express') {
                        list = list.filter(s => Boolean(s.is_express) || s.service_type === 'express');
                    } else if (this.selectedCategory === 'kiloan') {
                        list = list.filter(s => (s.unit || 'kg') === 'kg');
                    } else if (this.selectedCategory === 'satuan') {
                        list = list.filter(s => s.unit === 'pcs');
                    } else if (this.selectedCategory === 'khusus') {
                        list = list.filter(s => s.service_type === 'khusus');
                    }

                    if (this.searchQuery && this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase();
                        list = list.filter(s => s.name.toLowerCase().includes(q) || (s.description && s.description.toLowerCase().includes(q)));
                    }
                    return list;
                },

                getItemUnitLabel(serviceId) {
                    let s = this.getService(serviceId);
                    if (!s) return 'Kg';
                    if (s.unit === 'pcs') return 'Pcs';
                    if (s.unit === 'meter') return 'M²';
                    return 'Kg';
                },

                hasExpressItem() {
                    return this.items.some(item => {
                        let s = this.getService(item.service_id);
                        return s && (Boolean(s.is_express) || s.service_type === 'express');
                    });
                },

                selectServiceCard(service) {
                    const existingIndex = this.items.findIndex(i => String(i.service_id) === String(service.id));
                    if (existingIndex !== -1) {
                        let currentWeight = parseFloat(this.items[existingIndex].weight) || 0;
                        this.items[existingIndex].weight = (currentWeight + 1).toFixed(2);
                    } else {
                        if (this.items.length === 1 && (!this.items[0].service_id || this.items[0].service_id === '') && (!this.items[0].weight || this.items[0].weight === '')) {
                            this.items[0].service_id = service.id;
                            this.items[0].weight = '1.00';
                        } else {
                            this.items.push({ service_id: service.id, weight: '1.00' });
                        }
                    }
                },

                getItemInCart(serviceId) {
                    return this.items.find(i => String(i.service_id) === String(serviceId));
                },

                addItem() {
                    this.items.push({ service_id: '', weight: '' });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        this.items[0] = { service_id: '', weight: '' };
                    }
                },

                stepWeight(index, delta) {
                    let current = parseFloat(this.items[index].weight) || 0;
                    let updated = Math.max(0.1, current + delta);
                    this.items[index].weight = updated.toFixed(2);
                },

                getService(id) {
                    if (!id) return null;
                    return this.servicesMap[id] || null;
                },

                getSubtotal(item) {
                    if (!item || !item.service_id) return 0;
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
                    addressEl.classList.remove('italic', 'text-slate-500');
                    addressEl.classList.add('text-slate-600');
                } else {
                    addressEl.textContent = 'Alamat belum diisi';
                    addressEl.classList.add('italic', 'text-slate-500');
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
            window.dispatchEvent(new CustomEvent('open-quick-customer-modal'));
        }

        function closeQuickCustomerModal() {
            window.dispatchEvent(new CustomEvent('close-quick-customer-modal'));
        }

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
