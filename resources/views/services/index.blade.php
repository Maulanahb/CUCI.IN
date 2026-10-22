@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $isAdmin = auth()->user()?->role === 'admin';
@endphp

<x-layouts::app title="Katalog Layanan Laundry">
    <div
        class="space-y-6"
        x-data="{
            createModalOpen: false,
            editModalOpen: false,
            deleteModalOpen: false,
            detailModalOpen: false,
            activeService: null,
            editForm: {
                id: '',
                name: '',
                price: '',
                estimated_duration: '',
                description: '',
                is_active: true
            },
            deleteForm: {
                id: '',
                name: '',
                transactionCount: 0
            },
            detailData: {
                name: '',
                price: 0,
                estimated_duration: 0,
                description: '',
                is_active: true,
                transaction_details_count: 0,
                calcWeight: 3
            },
            openCreateModal() {
                this.createModalOpen = true;
            },
            openEditModal(service) {
                this.editForm = {
                    id: service.id,
                    name: service.name,
                    price: Math.round(parseFloat(service.price)),
                    estimated_duration: service.estimated_duration,
                    description: service.description || '',
                    is_active: Boolean(service.is_active)
                };
                this.editModalOpen = true;
            },
            openDeleteModal(service) {
                this.deleteForm = {
                    id: service.id,
                    name: service.name,
                    transactionCount: service.transaction_details_count || 0
                };
                this.deleteModalOpen = true;
            },
            openDetailModal(service) {
                this.detailData = {
                    name: service.name,
                    price: parseFloat(service.price),
                    estimated_duration: service.estimated_duration,
                    description: service.description || 'Tidak ada keterangan tambahan.',
                    is_active: Boolean(service.is_active),
                    transaction_details_count: service.transaction_details_count || 0,
                    calcWeight: 3
                };
                this.detailModalOpen = true;
            },
            formatRupiah(val) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
            }
        }"
    >
        {{-- ============================================================
            HEADER & ACTIONS
        ============================================================= --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Katalog Layanan Laundry
                    </h1>
                    @if ($isAdmin)
                        <span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-[11px] font-semibold text-brand-800">
                            Mode Administrator
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">
                            Katalog Kasir / Staff
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Kelola tarif pencucian, durasi estimasi pengerjaan, dan ketersediaan layanan operasional CUCI.IN.
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($isAdmin)
                    <button
                        type="button"
                        id="btn-open-create-service"
                        @click="openCreateModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-700 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98 sm:text-sm"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Layanan</span>
                    </button>
                @endif

                <a
                    href="{{ route("{$currentRoutePrefix}.transactions.create") }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-medium text-slate-700 shadow-2xs transition hover:border-slate-300 hover:bg-slate-100 sm:text-sm"
                >
                    <svg class="size-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Kasir POS</span>
                </a>
            </div>
        </div>

        {{-- ============================================================
            ALERT NOTIFIKASI
        ============================================================= --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 text-xs font-medium text-emerald-800 shadow-2xs sm:text-sm">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-xs text-rose-800 shadow-2xs sm:text-sm">
                <div class="flex items-center gap-2 font-semibold">
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Perhatian:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-0.5 pl-2 font-normal">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================
            KPI STATISTIC CARDS (Aqua Teal + Semantic)
        ============================================================= --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            {{-- Card 1: Total Layanan --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Paket</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $stats['total'] }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Seluruh jenis layanan</p>
            </div>

            {{-- Card 2: Layanan Aktif --}}
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-xs transition hover:border-emerald-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Layanan Aktif</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold tracking-tight text-emerald-600 sm:text-2xl">{{ $stats['active'] }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Siap dipilih di kasir</p>
            </div>

            {{-- Card 3: Layanan Nonaktif --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Nonaktif / Tutup</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold tracking-tight text-slate-600 sm:text-2xl">{{ $stats['inactive'] }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Layanan diarsipkan</p>
            </div>

            {{-- Card 4: Rata-rata Tarif --}}
            <div class="rounded-2xl border border-brand-100 bg-brand-50/40 p-4 shadow-xs transition hover:border-brand-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-brand-900">Rata-rata Tarif</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-lg font-bold tracking-tight text-brand-700 sm:text-xl">
                    Rp {{ number_format($stats['avg_price'], 0, ',', '.') }}
                </p>
                <p class="mt-0.5 text-[11px] text-brand-600/80">
                    Durasi tercepat: {{ $stats['min_duration'] > 0 ? $stats['min_duration'] . ' jam' : '-' }}
                </p>
            </div>
        </div>

        {{-- ============================================================
            FILTER & SEARCH TOOLBAR
        ============================================================= --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs sm:flex-row sm:items-center sm:justify-between">
            {{-- Tab Filter Status --}}
            <div class="flex items-center gap-1.5 overflow-x-auto">
                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
                    @class([
                        'rounded-xl px-3 py-1.5 text-xs font-semibold transition',
                        'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'all',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900' => $statusFilter !== 'all',
                    ])
                >
                    Semua ({{ $stats['total'] }})
                </a>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                    @class([
                        'rounded-xl px-3 py-1.5 text-xs font-semibold transition',
                        'bg-emerald-600 text-white shadow-2xs' => $statusFilter === 'active',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900' => $statusFilter !== 'active',
                    ])
                >
                    Aktif ({{ $stats['active'] }})
                </a>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                    @class([
                        'rounded-xl px-3 py-1.5 text-xs font-semibold transition',
                        'bg-slate-700 text-white shadow-2xs' => $statusFilter === 'inactive',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900' => $statusFilter !== 'inactive',
                    ])
                >
                    Nonaktif ({{ $stats['inactive'] }})
                </a>
            </div>

            {{-- Form Pencarian --}}
            <form method="GET" action="{{ route("{$currentRoutePrefix}.services.index") }}" class="relative w-full sm:w-72">
                @if ($statusFilter !== 'all')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari nama layanan..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-9 text-xs text-slate-800 transition placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                />
                @if ($search !== '')
                    <a
                        href="{{ route("{$currentRoutePrefix}.services.index", ['status' => $statusFilter]) }}"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                        title="Hapus pencarian"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        {{-- ============================================================
            TABEL MASTER DATA LAYANAN
        ============================================================= --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 sm:text-xs">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Nama Layanan</th>
                            <th scope="col" class="px-5 py-3.5">Tarif Satuan (kg)</th>
                            <th scope="col" class="px-5 py-3.5">Estimasi Durasi</th>
                            <th scope="col" class="px-5 py-3.5">Status Ketersediaan</th>
                            <th scope="col" class="px-5 py-3.5 text-center">Riwayat Transaksi</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($services as $service)
                            <tr class="transition hover:bg-slate-50/70">
                                {{-- Nama Layanan & Deskripsi --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900">{{ $service->name }}</p>
                                            <p class="mt-0.5 line-clamp-1 text-xs text-slate-400">
                                                {{ $service->description ?: 'Tanpa keterangan khusus.' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tarif Satuan --}}
                                <td class="px-5 py-4 font-semibold text-slate-800">
                                    <span class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">
                                        Rp {{ number_format($service->price, 0, ',', '.') }} <span class="ml-1 text-[10px] font-normal text-brand-600/80">/kg</span>
                                    </span>
                                </td>

                                {{-- Estimasi Durasi --}}
                                <td class="px-5 py-4 text-slate-600">
                                    <div class="flex items-center gap-1.5 text-xs font-medium sm:text-sm">
                                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ $service->estimated_duration }} Jam</span>
                                        @if ($service->estimated_duration >= 24)
                                            <span class="text-[11px] text-slate-400">
                                                ({{ round($service->estimated_duration / 24, 1) }} hari)
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Ketersediaan --}}
                                <td class="px-5 py-4">
                                    @if ($isAdmin)
                                        <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                title="Klik untuk mengubah status ketersediaan"
                                                @class([
                                                    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold transition hover:opacity-85',
                                                    'border-emerald-200 bg-emerald-50 text-emerald-700' => $service->is_active,
                                                    'border-slate-200 bg-slate-100 text-slate-600' => ! $service->is_active,
                                                ])
                                            >
                                                <span @class([
                                                    'size-1.5 rounded-full',
                                                    'bg-emerald-500' => $service->is_active,
                                                    'bg-slate-400' => ! $service->is_active,
                                                ])></span>
                                                <span>{{ $service->is_active ? 'Tersedia' : 'Nonaktif' }}</span>
                                            </button>
                                        </form>
                                    @else
                                        <span @class([
                                            'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold',
                                            'border-emerald-200 bg-emerald-50 text-emerald-700' => $service->is_active,
                                            'border-slate-200 bg-slate-100 text-slate-600' => ! $service->is_active,
                                        ])>
                                            <span @class([
                                                'size-1.5 rounded-full',
                                                'bg-emerald-500' => $service->is_active,
                                                'bg-slate-400' => ! $service->is_active,
                                            ])></span>
                                            <span>{{ $service->is_active ? 'Tersedia' : 'Nonaktif' }}</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Penggunaan Transaksi --}}
                                <td class="px-5 py-4 text-center">
                                    @if ($service->transaction_details_count > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                            <span>{{ $service->transaction_details_count }}</span>
                                            <span class="text-[10px] font-normal">order</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">0 order</span>
                                    @endif
                                </td>

                                {{-- Aksi Kolom --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Tombol Detail / Preview --}}
                                        <x-ui.button
                                            type="button"
                                            variant="soft"
                                            size="icon"
                                            @click="openDetailModal({{ json_encode($service) }})"
                                            title="Lihat Detail & Simulasi"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </x-ui.button>

                                        @if ($isAdmin)
                                            {{-- Tombol Edit --}}
                                            <x-ui.button
                                                type="button"
                                                variant="soft-warning"
                                                size="icon"
                                                @click="openEditModal({{ json_encode($service) }})"
                                                title="Ubah Layanan"
                                            >
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </x-ui.button>

                                            {{-- Tombol Hapus --}}
                                            <x-ui.button
                                                type="button"
                                                variant="{{ $service->transaction_details_count > 0 ? 'muted' : 'danger-outline' }}"
                                                size="icon"
                                                @click="openDeleteModal({{ json_encode($service) }})"
                                                title="{{ $service->transaction_details_count > 0 ? 'Layanan memiliki riwayat transaksi (Gunakan nonaktifkan)' : 'Hapus Layanan' }}"
                                            >
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-3 text-sm font-semibold text-slate-900">Tidak ada layanan ditemukan</h3>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $search !== '' ? 'Tidak ada hasil untuk kata kunci "' . $search . '".' : 'Belum ada data layanan laundry yang tersimpan.' }}
                                    </p>
                                    @if ($isAdmin && $search === '')
                                        <div class="mt-4">
                                            <button
                                                type="button"
                                                @click="openCreateModal()"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-700 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-800"
                                            >
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                <span>Buat Layanan Pertama</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($services->hasPages())
                <div class="border-t border-slate-100 px-5 py-3.5">
                    {{ $services->links() }}
                </div>
            @endif
        </div>

        {{-- ============================================================
            MODAL 1: TAMBAH LAYANAN (Hanya Admin)
        ============================================================= --}}
        @if ($isAdmin)
            <div
                x-cloak
                x-show="createModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
            >
                <div
                    x-show="createModalOpen"
                    x-transition.opacity
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
                    @click="createModalOpen = false"
                ></div>

                <div
                    x-show="createModalOpen"
                    x-transition.scale.origin.bottom
                    class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all sm:p-7"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Tambah Layanan Baru</h3>
                                <p class="text-xs text-slate-500">Daftarkan paket layanan laundry baru ke dalam sistem.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="createModalOpen = false"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.services.store') }}" class="mt-5 space-y-4">
                        @csrf

                        {{-- Nama Layanan --}}
                        <div>
                            <label for="create_name" class="block text-xs font-semibold text-slate-700">
                                Nama Layanan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="create_name"
                                type="text"
                                name="name"
                                required
                                maxlength="100"
                                placeholder="Contoh: Cuci Komplit Kilat, Cuci Karpet Tebal"
                                class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                        </div>

                        {{-- Baris: Harga & Estimasi Durasi --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="create_price" class="block text-xs font-semibold text-slate-700">
                                    Tarif Satuan (Rp/kg) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-xs font-semibold text-slate-400">Rp</span>
                                    </div>
                                    <input
                                        id="create_price"
                                        type="number"
                                        name="price"
                                        required
                                        min="1"
                                        step="100"
                                        placeholder="8000"
                                        class="w-full rounded-xl border border-slate-300 py-2.5 pr-3.5 pl-9 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="create_estimated_duration" class="block text-xs font-semibold text-slate-700">
                                    Estimasi Durasi (Jam) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <input
                                        id="create_estimated_duration"
                                        type="number"
                                        name="estimated_duration"
                                        required
                                        min="1"
                                        max="720"
                                        placeholder="24"
                                        class="w-full rounded-xl border border-slate-300 py-2.5 pr-12 pl-3.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                    />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-xs font-semibold text-slate-400">Jam</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Deskripsi Layanan --}}
                        <div>
                            <label for="create_description" class="block text-xs font-semibold text-slate-700">
                                Deskripsi Layanan <span class="text-[11px] font-normal text-slate-400">(Opsional)</span>
                            </label>
                            <textarea
                                id="create_description"
                                name="description"
                                rows="3"
                                maxlength="1000"
                                placeholder="Detail proses pengerjaan, perlakuan khusus, atau fasilitas layanan..."
                                class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            ></textarea>
                        </div>

                        {{-- Checkbox Status Aktif --}}
                        <div class="flex items-center gap-2.5 pt-1">
                            <input
                                id="create_is_active"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                                class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            <label for="create_is_active" class="text-xs font-medium text-slate-700 sm:text-sm">
                                Langsung aktifkan layanan (dapat langsung dipilih saat transaksi)
                            </label>
                        </div>

                        {{-- Tombol Aksi Form --}}
                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                            <button
                                type="button"
                                @click="createModalOpen = false"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-700 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Layanan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================
                MODAL 2: UBAH LAYANAN (Hanya Admin)
            ============================================================= --}}
            <div
                x-cloak
                x-show="editModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
            >
                <div
                    x-show="editModalOpen"
                    x-transition.opacity
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
                    @click="editModalOpen = false"
                ></div>

                <div
                    x-show="editModalOpen"
                    x-transition.scale.origin.bottom
                    class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all sm:p-7"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Ubah Data Layanan</h3>
                                <p class="text-xs text-slate-500">Perbarui tarif, estimasi durasi, atau keterangan paket.</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="editModalOpen = false"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form
                        method="POST"
                        :action="'{{ url('admin/services') }}/' + editForm.id"
                        class="mt-5 space-y-4"
                    >
                        @csrf
                        @method('PUT')

                        {{-- Nama Layanan --}}
                        <div>
                            <label for="edit_name" class="block text-xs font-semibold text-slate-700">
                                Nama Layanan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="edit_name"
                                type="text"
                                name="name"
                                x-model="editForm.name"
                                required
                                maxlength="100"
                                class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                        </div>

                        {{-- Baris: Harga & Estimasi Durasi --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit_price" class="block text-xs font-semibold text-slate-700">
                                    Tarif Satuan (Rp/kg) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-xs font-semibold text-slate-400">Rp</span>
                                    </div>
                                    <input
                                        id="edit_price"
                                        type="number"
                                        name="price"
                                        x-model="editForm.price"
                                        required
                                        min="1"
                                        step="100"
                                        class="w-full rounded-xl border border-slate-300 py-2.5 pr-3.5 pl-9 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="edit_estimated_duration" class="block text-xs font-semibold text-slate-700">
                                    Estimasi Durasi (Jam) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <input
                                        id="edit_estimated_duration"
                                        type="number"
                                        name="estimated_duration"
                                        x-model="editForm.estimated_duration"
                                        required
                                        min="1"
                                        max="720"
                                        class="w-full rounded-xl border border-slate-300 py-2.5 pr-12 pl-3.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                                    />
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-xs font-semibold text-slate-400">Jam</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Deskripsi Layanan --}}
                        <div>
                            <label for="edit_description" class="block text-xs font-semibold text-slate-700">
                                Deskripsi Layanan <span class="text-[11px] font-normal text-slate-400">(Opsional)</span>
                            </label>
                            <textarea
                                id="edit_description"
                                name="description"
                                x-model="editForm.description"
                                rows="3"
                                maxlength="1000"
                                class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            ></textarea>
                        </div>

                        {{-- Checkbox Status Aktif --}}
                        <div class="flex items-center gap-2.5 pt-1">
                            <input
                                id="edit_is_active"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                x-model="editForm.is_active"
                                class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            <label for="edit_is_active" class="text-xs font-medium text-slate-700 sm:text-sm">
                                Layanan aktif dan dapat dipilih di form kasir transaksi
                            </label>
                        </div>

                        {{-- Info Snapshot Note --}}
                        <div class="rounded-xl border border-amber-200/70 bg-amber-50/60 p-3 text-xs text-amber-800">
                            <p class="font-semibold">Catatan Snapshot Harga:</p>
                            <p class="mt-0.5 text-[11px] text-amber-700">
                                Perubahan tarif ini hanya berlaku untuk transaksi baru yang akan datang. Transaksi lama tetap mempertahankan nominal riil saat transaksi dibuat.
                            </p>
                        </div>

                        {{-- Tombol Aksi Form --}}
                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                            <button
                                type="button"
                                @click="editModalOpen = false"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-700 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================
                MODAL 3: KONFIRMASI HAPUS / PROTEKSI TRANSAKSI
            ============================================================= --}}
            <div
                x-cloak
                x-show="deleteModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
            >
                <div
                    x-show="deleteModalOpen"
                    x-transition.opacity
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
                    @click="deleteModalOpen = false"
                ></div>

                <div
                    x-show="deleteModalOpen"
                    x-transition.scale.origin.bottom
                    class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all"
                >
                    {{-- Kondisi 1: Layanan sudah memiliki transaksi --}}
                    <template x-if="deleteForm.transactionCount > 0">
                        <div>
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Layanan Tidak Dapat Dihapus</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Layanan <span class="font-semibold text-slate-900" x-text="'&ldquo;' + deleteForm.name + '&rdquo;'"></span> sudah tercatat dalam
                                <span class="font-bold text-amber-700" x-text="deleteForm.transactionCount + ' riwayat transaksi'"></span>. Sesuai aturan integritas operasional, data historis tidak boleh dihapus.
                            </p>
                            <p class="mt-2 text-xs text-slate-500">
                                Solusi: Anda dapat menonaktifkan layanan ini agar tidak lagi muncul sebagai opsi transaksi baru.
                            </p>
                            <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    @click="deleteModalOpen = false"
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Kondisi 2: Layanan belum pernah digunakan transaksi (Aman dihapus) --}}
                    <template x-if="deleteForm.transactionCount === 0">
                        <div>
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Konfirmasi Hapus Layanan</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Apakah Anda yakin ingin menghapus layanan <span class="font-semibold text-slate-900" x-text="'&ldquo;' + deleteForm.name + '&rdquo;'"></span>?
                                Tindakan ini bersifat permanen karena layanan belum memiliki riwayat order.
                            </p>
                            <form
                                method="POST"
                                :action="'{{ url('admin/services') }}/' + deleteForm.id"
                                class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="button"
                                    @click="deleteModalOpen = false"
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20 active:scale-98"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6" />
                                    </svg>
                                    <span>Hapus Permanen</span>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        @endif

        {{-- ============================================================
            MODAL 4: DETAIL LAYANAN & SIMULASI KALKULASI SUB-TOTAL
        ============================================================= --}}
        <div
            x-cloak
            x-show="detailModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            <div
                x-show="detailModalOpen"
                x-transition.opacity
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
                @click="detailModalOpen = false"
            ></div>

            <div
                x-show="detailModalOpen"
                x-transition.scale.origin.bottom
                class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all sm:p-7"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="detailData.name"></h3>
                            <p class="text-xs text-slate-500">Rincian spesifikasi paket dan simulasi biaya cucian.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="detailModalOpen = false"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    {{-- Grid Atribut --}}
                    <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                        <div>
                            <span class="text-[11px] text-slate-400">Tarif Satuan</span>
                            <p class="mt-0.5 text-sm font-bold text-brand-700" x-text="formatRupiah(detailData.price) + ' / kg'"></p>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400">Estimasi Durasi</span>
                            <p class="mt-0.5 text-sm font-bold text-slate-800" x-text="detailData.estimated_duration + ' Jam'"></p>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400">Status Layanan</span>
                            <p class="mt-0.5 text-xs font-semibold" :class="detailData.is_active ? 'text-emerald-600' : 'text-slate-500'" x-text="detailData.is_active ? 'Tersedia (Aktif)' : 'Nonaktif'"></p>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400">Total Digunakan</span>
                            <p class="mt-0.5 text-xs font-semibold text-slate-800" x-text="detailData.transaction_details_count + ' Transaksi'"></p>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <span class="text-xs font-semibold text-slate-700">Keterangan Layanan:</span>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600" x-text="detailData.description"></p>
                    </div>

                    {{-- Simulasi Subtotal Berdasarkan Berat --}}
                    <div class="rounded-xl border border-brand-200/80 bg-brand-50/50 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-900">Simulasi Subtotal Kasir</span>
                            <span class="text-[11px] font-medium text-brand-600">Formula: Berat × Tarif</span>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="w-1/2">
                                <label class="text-[11px] font-semibold text-slate-600">Berat Cucian (kg):</label>
                                <input
                                    type="number"
                                    min="0.1"
                                    step="0.5"
                                    x-model.number="detailData.calcWeight"
                                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-200"
                                />
                            </div>
                            <div class="w-1/2">
                                <label class="text-[11px] font-semibold text-slate-600">Hasil Subtotal:</label>
                                <div class="mt-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-brand-700 border border-brand-100">
                                    <span x-text="formatRupiah(detailData.calcWeight * detailData.price)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        @click="detailModalOpen = false"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app>
