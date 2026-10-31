@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $isAdmin = auth()->user()?->role === 'admin';
@endphp

<x-layouts::app title="Katalog Layanan Laundry">
    <div
        class="space-y-7"
        x-data="{
            createModalOpen: false,
            editModalOpen: false,
            deleteModalOpen: false,
            detailModalOpen: false,
            activeService: null,
            createForm: {
                unit: 'kg',
                service_type: 'reguler',
                is_express: false,
                minimum_order: 1.00
            },
            editForm: {
                id: '',
                name: '',
                price: '',
                unit: 'kg',
                service_type: 'reguler',
                is_express: false,
                minimum_order: 1.00,
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
                unit: 'kg',
                unit_label: 'Kg',
                service_type: 'reguler',
                type_label: 'Reguler',
                is_express: false,
                minimum_order: 1.00,
                estimated_duration: 0,
                description: '',
                is_active: true,
                transaction_details_count: 0,
                calcQty: 3
            },
            openCreateModal() {
                this.createForm = {
                    unit: 'kg',
                    service_type: 'reguler',
                    is_express: false,
                    minimum_order: 1.00
                };
                this.createModalOpen = true;
            },
            openEditModal(service) {
                this.editForm = {
                    id: service.id,
                    name: service.name,
                    price: Math.round(parseFloat(service.price)),
                    unit: service.unit || 'kg',
                    service_type: service.service_type || 'reguler',
                    is_express: Boolean(service.is_express),
                    minimum_order: service.minimum_order ? parseFloat(service.minimum_order) : 1.00,
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
                const unitLabel = service.unit === 'pcs' ? 'Pcs' : (service.unit === 'meter' ? 'M²' : 'Kg');
                const typeLabel = service.service_type === 'express' || service.is_express
                    ? 'Paket Kilat / Express'
                    : (service.service_type === 'khusus' ? 'Perawatan Khusus' : (service.service_type === 'satuan' ? 'Cuci Satuan' : 'Reguler'));

                this.detailData = {
                    name: service.name,
                    price: parseFloat(service.price),
                    unit: service.unit || 'kg',
                    unit_label: unitLabel,
                    service_type: service.service_type || 'reguler',
                    type_label: typeLabel,
                    is_express: Boolean(service.is_express),
                    minimum_order: service.minimum_order ? parseFloat(service.minimum_order) : 1.00,
                    estimated_duration: service.estimated_duration,
                    description: service.description || 'Tidak ada keterangan tambahan.',
                    is_active: Boolean(service.is_active),
                    transaction_details_count: service.transaction_details_count || 0,
                    calcQty: service.unit === 'pcs' ? 1 : 3
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
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Katalog Layanan Laundry
                </h1>
                <p class="mt-2 text-sm text-slate-600">Tarif, satuan, dan estimasi pengerjaan untuk setiap layanan.
                    <span class="mt-1 block text-xs text-slate-500">{{ $isAdmin ? 'Mode Administrator' : 'Katalog Kasir / Staff' }}</span>
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if ($isAdmin)
                    <x-ui.button
                        id="btn-open-create-service"
                        type="button"
                        size="md"
                        @click="openCreateModal()"
                        class="shadow-xs"
                    >
                        <x-lucide-plus class="size-4" />
                        <span>Tambah Layanan</span>
                    </x-ui.button>
                @endif

                <x-ui.button
                    href="{{ route($currentRoutePrefix . '.transactions.create') }}"
                    :variant="$isAdmin ? 'secondary' : 'primary'"
                    size="md"
                >
                    <x-lucide-receipt class="size-4" />
                    <span>Kasir POS</span>
                </x-ui.button>
            </div>
        </div>

        {{-- ============================================================
            ALERT NOTIFIKASI
        ============================================================= --}}
        @if (session('status'))
            <x-ui.alert variant="success" dismissible>
                {{ session('status') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive" title="Perhatian:" dismissible>
                <ul class="mt-1 list-inside list-disc space-y-0.5 pl-1 font-normal">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <section aria-label="Ringkasan layanan" class="grid grid-cols-2 overflow-hidden rounded-2xl border border-slate-200 bg-white lg:grid-cols-4">
            <div class="min-w-0 border-b border-r lg:border-b-0 border-slate-200 p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Total paket</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">{{ $stats['total'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Seluruh layanan</p>
            </div>
            <div class="min-w-0 border-b lg:border-b-0 lg:border-r border-slate-200 p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Layanan aktif</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-emerald-700">{{ $stats['active'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Siap dipilih di kasir</p>
            </div>
            <div class="min-w-0 border-r border-slate-200 p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Paket kilat</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">{{ $stats['express'] ?? 0 }}</p>
                <p class="mt-2 text-xs text-slate-500">Layanan express</p>
            </div>
            <div class="min-w-0  border-slate-200 p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Rata-rata tarif</p>
                <p class="mt-4 text-2xl font-semibold tracking-tight tabular-nums text-slate-900">Rp {{ number_format($stats['avg_price'], 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-slate-500">Durasi tercepat: {{ $stats['min_duration'] > 0 ? $stats['min_duration'] . ' jam' : '-' }}</p>
            </div>
        </section>

        {{-- ============================================================
            FILTER & SEARCH TOOLBAR
        ============================================================= --}}
        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                {{-- Tab Filter Status --}}
                <x-ui.tabs variant="segmented" class="flex-wrap gap-2" aria-label="Filter ketersediaan layanan">
                    <x-ui.tab
                        variant="segmented"
                        class="min-h-11 text-sm"
                        :href="route($currentRoutePrefix . '.services.index', array_merge(request()->except(['status', 'page']), ['status' => 'all']))"
                        :active="$statusFilter === 'all'"
                        :badge="$stats['total']"
                        :badgeTone="$statusFilter === 'all' ? 'brand' : 'neutral'"
                    >
                        Semua Status
                    </x-ui.tab>
                    <x-ui.tab
                        variant="segmented"
                        class="min-h-11 text-sm"
                        :href="route($currentRoutePrefix . '.services.index', array_merge(request()->except(['status', 'page']), ['status' => 'active']))"
                        :active="$statusFilter === 'active'"
                        :badge="$stats['active']"
                        :badgeTone="$statusFilter === 'active' ? 'emerald' : 'neutral'"
                    >
                        Aktif
                    </x-ui.tab>
                    <x-ui.tab
                        variant="segmented"
                        class="min-h-11 text-sm"
                        :href="route($currentRoutePrefix . '.services.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive']))"
                        :active="$statusFilter === 'inactive'"
                        :badge="$stats['inactive']"
                        :badgeTone="$statusFilter === 'inactive' ? 'neutral' : 'neutral'"
                    >
                        Nonaktif
                    </x-ui.tab>
                </x-ui.tabs>

                {{-- Form Pencarian --}}
                <form method="GET" action="{{ route("{$currentRoutePrefix}.services.index") }}" class="relative w-full shrink-0 sm:w-72">
                    @if ($statusFilter !== 'all')
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                    @if (($typeFilter ?? 'all') !== 'all')
                        <input type="hidden" name="type" value="{{ $typeFilter }}">
                    @endif
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-lucide-search class="size-4 text-slate-500" />
                    </div>
                    <input
                        type="text"
                        name="q"
                        aria-label="Cari layanan berdasarkan nama atau deskripsi"
                        value="{{ $search }}"
                        placeholder="Cari nama atau deskripsi..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-2 pr-9 pl-9 text-sm text-slate-900 shadow-2xs transition placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                    />
                    @if ($search !== '')
                        <a
                            href="{{ route("{$currentRoutePrefix}.services.index", ['status' => $statusFilter, 'type' => $typeFilter ?? 'all']) }}"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-600 transition"
                            title="Hapus pencarian"
                        >
                            <x-lucide-x class="size-4" />
                        </a>
                    @endif
                </form>
            </div>

            {{-- Filter Chips Kategori & Paket Lanjutan --}}
            <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100 text-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 shrink-0 mr-1">Kategori:</span>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['type', 'page']), ['type' => 'all'])) }}"
                    @class([
                        'inline-flex items-center min-h-11 rounded-lg px-3 py-2 font-semibold transition-colors shrink-0',
                        'bg-brand-700 text-white' => ($typeFilter ?? 'all') === 'all',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200' => ($typeFilter ?? 'all') !== 'all',
                    ])
                >
                    Semua Jenis
                </a>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['type', 'page']), ['type' => 'express'])) }}"
                    @class([
                        'inline-flex items-center gap-1 min-h-11 rounded-lg px-3 py-2 font-semibold transition-colors shrink-0',
                        'bg-brand-700 text-white' => ($typeFilter ?? 'all') === 'express',
                        'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' => ($typeFilter ?? 'all') !== 'express',
                    ])
                >
                    <x-lucide-zap class="size-3.5 text-current {{ ($typeFilter ?? 'all') === 'express' ? 'text-white' : '' }}" />
                    <span>Paket Kilat / Express</span>
                </a>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['type', 'page']), ['type' => 'kiloan'])) }}"
                    @class([
                        'inline-flex items-center min-h-11 rounded-lg px-3 py-2 font-semibold transition-colors shrink-0',
                        'bg-brand-700 text-white' => ($typeFilter ?? 'all') === 'kiloan',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200' => ($typeFilter ?? 'all') !== 'kiloan',
                    ])
                >
                    Cuci Kiloan (Kg)
                </a>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['type', 'page']), ['type' => 'satuan'])) }}"
                    @class([
                        'inline-flex items-center min-h-11 rounded-lg px-3 py-2 font-semibold transition-colors shrink-0',
                        'bg-brand-700 text-white' => ($typeFilter ?? 'all') === 'satuan',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200' => ($typeFilter ?? 'all') !== 'satuan',
                    ])
                >
                    Cuci Satuan (Pcs)
                </a>

                <a
                    href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['type', 'page']), ['type' => 'khusus'])) }}"
                    @class([
                        'inline-flex items-center min-h-11 rounded-lg px-3 py-2 font-semibold transition-colors shrink-0',
                        'bg-brand-700 text-white' => ($typeFilter ?? 'all') === 'khusus',
                        'bg-slate-100 text-slate-600 hover:bg-slate-200' => ($typeFilter ?? 'all') !== 'khusus',
                    ])
                >
                    Perawatan Khusus
                </a>
            </div>
        </div>

        {{-- ============================================================
            TABEL MASTER DATA LAYANAN
        ============================================================= --}}
        <div class="lg:overflow-hidden lg:rounded-2xl lg:border lg:border-slate-200 lg:bg-white">
            <div class="lg:overflow-x-auto" tabindex="0" role="region" aria-label="Daftar layanan">
                <table class="block w-full text-left text-sm lg:table">
                    <thead class="hidden border-b border-slate-100 bg-slate-50/75 lg:table-header-group text-xs font-bold uppercase tracking-wider text-slate-500 ">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Nama Layanan & Kategori</th>
                            <th scope="col" class="px-5 py-3.5">Tarif Dinamis</th>
                            <th scope="col" class="px-5 py-3.5">Estimasi Durasi</th>
                            <th scope="col" class="px-5 py-3.5">Status Ketersediaan</th>
                            <th scope="col" class="px-5 py-3.5 text-center">Riwayat Transaksi</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:table-row-group lg:divide-y lg:divide-slate-100">
                        @forelse ($services as $service)
                            <tr class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-colors lg:table-row lg:rounded-none lg:border-0 lg:hover:bg-slate-50/70">
                                {{-- Nama Layanan & Kategori --}}
                                <td class="block px-5 py-3 lg:table-cell lg:py-4">
                                    <div class="flex items-start gap-3">
                                        <div @class([
                                            'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg',
                                            'bg-amber-100 text-amber-700 shadow-2xs' => $service->is_express,
                                            'bg-brand-50 text-brand-700' => ! $service->is_express,
                                        ])>
                                            @if ($service->is_express)
                                                <x-lucide-zap class="size-4" />
                                            @elseif ($service->unit === 'pcs')
                                                <x-lucide-tag class="size-4" />
                                            @elseif ($service->unit === 'meter')
                                                <x-lucide-ruler class="size-4" />
                                            @else
                                                <x-lucide-shirt class="size-4" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="text-base font-semibold text-slate-900">{{ $service->name }}</p>
                                                @if ($service->is_express)
                                                    <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-100 px-1.5 py-0.5 text-xs font-extrabold text-amber-800 border border-amber-300">
                                                        ⚡ KILAT
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-semibold text-slate-600">
                                                    {{ $service->unit_label }}
                                                </span>
                                            </div>
                                            <p class="mt-0.5 line-clamp-2 text-sm text-slate-500">
                                                {{ $service->description ?: 'Tanpa keterangan khusus.' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tarif Dinamis --}}
                                <td class="block px-5 py-2 font-semibold text-slate-800 lg:table-cell lg:py-4">
                                    <span @class([
                                        'inline-flex flex-wrap items-baseline rounded-lg px-2.5 py-2 text-base font-semibold tabular-nums',
                                        'bg-amber-50 text-amber-800 border border-amber-200' => $service->is_express,
                                        'bg-brand-50 text-brand-700' => ! $service->is_express,
                                    ])>
                                        Rp {{ number_format($service->price, 0, ',', '.') }}
                                        <span class="ml-1 text-xs font-normal opacity-80">/ {{ $service->unit_label }}</span>
                                    </span>
                                    @if ($service->minimum_order > 1)
                                        <span class="block text-xs font-medium text-slate-500 mt-0.5">
                                            Min: {{ (float) $service->minimum_order }} {{ $service->unit_label }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Estimasi Durasi --}}
                                <td class="block px-5 py-2 text-slate-600 lg:table-cell lg:py-4">
                                    <div class="flex flex-wrap items-center gap-1.5 text-sm font-medium ">
                                        <span class="lg:hidden">Estimasi:</span>
                                        <x-lucide-clock @class([
                                            'size-4',
                                            'text-amber-500 font-bold' => $service->is_express,
                                            'text-slate-500' => ! $service->is_express,
                                        ]) />
                                        <span @class([
                                            'font-semibold text-amber-900' => $service->is_express,
                                            'text-slate-700' => ! $service->is_express,
                                        ])>
                                            {{ $service->duration_label }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Status Ketersediaan --}}
                                <td class="block px-5 py-3 lg:table-cell lg:py-4">
                                    @if ($isAdmin)
                                        <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                title="Klik untuk mengubah status ketersediaan"
                                                @class([
                                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-sm font-medium ring-1 ring-inset transition cursor-pointer select-none active:scale-95',
                                                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20 hover:bg-emerald-100/70' => $service->is_active,
                                                    'bg-slate-50 text-slate-600 ring-slate-600/15 hover:bg-slate-100' => ! $service->is_active,
                                                ])
                                            >
                                                <svg class="size-1.5 shrink-0 {{ $service->is_active ? 'fill-emerald-500' : 'fill-slate-400' }}" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                </svg>
                                                <span>{{ $service->is_active ? 'Tersedia' : 'Nonaktif' }}</span>
                                            </button>
                                        </form>
                                    @else
                                        <x-ui.badge :tone="$service->is_active ? 'success' : 'neutral'" size="md" dot>
                                            {{ $service->is_active ? 'Tersedia' : 'Nonaktif' }}
                                        </x-ui.badge>
                                    @endif
                                </td>

                                {{-- Penggunaan Transaksi --}}
                                <td class="block px-5 py-2 text-slate-600 lg:table-cell lg:py-4 lg:text-center">
                                    <span class="mr-1 lg:hidden">Riwayat transaksi:</span>
                                    @if ($service->transaction_details_count > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-sm font-semibold text-blue-700">
                                            <span class="tabular-nums">{{ $service->transaction_details_count }}</span>
                                            <span class="text-xs font-normal">order</span>
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-500">0 order</span>
                                    @endif
                                </td>

                                {{-- Aksi Kolom --}}
                                <td class="mt-auto block border-t border-slate-100 px-5 py-3 text-right lg:table-cell lg:border-0 lg:py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Tombol Detail / Preview --}}
                                        <x-ui.button
                                            type="button"
                                            variant="soft"
                                            size="icon"
                                            @click="openDetailModal({{ json_encode($service) }})"
                                            title="Lihat Detail & Simulasi"
                                            aria-label="Lihat detail dan simulasi {{ $service->name }}"
                                        >
                                            <x-lucide-eye class="size-4" />
                                        </x-ui.button>

                                        @if ($isAdmin)
                                            {{-- Tombol Edit --}}
                                            <x-ui.button
                                                type="button"
                                                variant="soft-warning"
                                                size="icon"
                                                @click="openEditModal({{ json_encode($service) }})"
                                                title="Ubah Layanan"
                                                aria-label="Ubah layanan {{ $service->name }}"
                                            >
                                                <x-lucide-pencil class="size-4" />
                                            </x-ui.button>

                                            {{-- Tombol Hapus --}}
                                            <x-ui.button
                                                type="button"
                                                variant="{{ $service->transaction_details_count > 0 ? 'muted' : 'danger-outline' }}"
                                                size="icon"
                                                @click="openDeleteModal({{ json_encode($service) }})"
                                                aria-label="Hapus layanan {{ $service->name }}"
                                                title="{{ $service->transaction_details_count > 0 ? 'Layanan memiliki riwayat transaksi (Gunakan nonaktifkan)' : 'Hapus Layanan' }}"
                                            >
                                                <x-lucide-trash-2 class="size-4" />
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="block rounded-2xl border border-slate-200 bg-white sm:col-span-2 lg:table-row lg:border-0">
                                <td colspan="6" class="block px-5 py-12 lg:table-cell text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                        <x-lucide-inbox class="size-6 text-slate-500" />
                                    </div>
                                    <h3 class="mt-3 text-base font-semibold text-slate-900">Tidak ada layanan ditemukan</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $search !== '' ? 'Tidak ada hasil untuk kata kunci "' . $search . '".' : 'Belum ada data layanan laundry yang tersimpan.' }}
                                    </p>
                                    @if ($isAdmin && $search === '')
                                        <div class="mt-4">
                                            <x-ui.button
                                                type="button"
                                                size="sm"
                                                @click="openCreateModal()"
                                            >
                                                <x-lucide-plus class="size-4" />
                                                <span>Buat Layanan Pertama</span>
                                            </x-ui.button>
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
            <x-ui.modal
                show="createModalOpen"
                title="Tambah Layanan Baru"
                description="Daftarkan paket layanan laundry baru ke dalam sistem."
                maxWidth="lg"
            >
                <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-4">
                    @csrf

                    {{-- Nama Layanan --}}
                    <div>
                        <x-ui.label for="create_name" required>
                            Nama Layanan
                        </x-ui.label>
                        <div class="mt-1.5">
                            <x-ui.input
                                id="create_name"
                                type="text"
                                name="name"
                                required
                                maxlength="100"
                                placeholder="Contoh: Cuci Komplit Kilat 6 Jam, Cuci Sepatu Deep Clean"
                            />
                        </div>
                    </div>

                    {{-- Baris: Satuan Unit & Kategori Layanan --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="create_unit" required>
                                Satuan Hitung (Unit)
                            </x-ui.label>
                            <select
                                id="create_unit"
                                name="unit"
                                x-model="createForm.unit"
                                required
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-2xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                            >
                                <option value="kg">Kilogram (Kg) — Cuci Kiloan</option>
                                <option value="pcs">Satuan / Potong (Pcs) — Jas, Selimut, Sepatu</option>
                                <option value="meter">Meter Persegi (M²) — Karpet, Gorden</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="create_service_type" required>
                                Tipe / Paket Layanan
                            </x-ui.label>
                            <select
                                id="create_service_type"
                                name="service_type"
                                x-model="createForm.service_type"
                                @change="if (createForm.service_type === 'express') createForm.is_express = true"
                                required
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-2xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                            >
                                <option value="reguler">Reguler Standar</option>
                                <option value="express">⚡ Paket Kilat / Express</option>
                                <option value="satuan">Cuci Satuan Khusus</option>
                                <option value="khusus">Perawatan Khusus (Sepatu/Karpet)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris: Harga & Estimasi Durasi --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="create_price" required>
                                Tarif Satuan (Rp / <span x-text="createForm.unit === 'pcs' ? 'Pcs' : (createForm.unit === 'meter' ? 'M²' : 'Kg')"></span>)
                            </x-ui.label>
                            <div class="mt-1.5">
                                <x-ui.input
                                    id="create_price"
                                    type="number"
                                    name="price"
                                    required
                                    min="1"
                                    step="100"
                                    placeholder="8000"
                                    prefix="Rp"
                                    class="tabular-nums"
                                />
                            </div>
                        </div>

                        <div>
                            <x-ui.label for="create_estimated_duration" required>
                                Estimasi Durasi (Jam)
                            </x-ui.label>
                            <div class="mt-1.5">
                                <x-ui.input
                                    id="create_estimated_duration"
                                    type="number"
                                    name="estimated_duration"
                                    required
                                    min="1"
                                    max="720"
                                    placeholder="24"
                                    suffix="Jam"
                                    class="tabular-nums"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Baris: Order Minimum & Toggle Express --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="create_minimum_order" optional>
                                Order Minimum
                            </x-ui.label>
                            <div class="mt-1.5">
                                <x-ui.input
                                    id="create_minimum_order"
                                    type="number"
                                    name="minimum_order"
                                    x-model="createForm.minimum_order"
                                    min="0.1"
                                    step="0.5"
                                    placeholder="1.00"
                                    class="tabular-nums"
                                />
                            </div>
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="relative flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    name="is_express"
                                    value="1"
                                    x-model="createForm.is_express"
                                    class="size-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                />
                                <span class="text-sm font-bold text-amber-900 flex items-center gap-1">
                                    <x-lucide-zap class="size-3.5 text-amber-500 fill-amber-500" />
                                    Tandai Sebagai Paket Kilat
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Deskripsi Layanan --}}
                    <div>
                        <x-ui.label for="create_description" optional>
                            Deskripsi Layanan
                        </x-ui.label>
                        <textarea
                            id="create_description"
                            name="description"
                            rows="2"
                            maxlength="1000"
                            placeholder="Detail proses pengerjaan, perlakuan khusus, atau fasilitas layanan..."
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-2xs transition placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                        ></textarea>
                    </div>

                    {{-- Toggle Switch Status Aktif --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                        <x-ui.switch
                            id="create_is_active"
                            name="is_active"
                            value="1"
                            :checked="true"
                            label="Aktifkan Layanan Sekarang"
                            description="Layanan yang aktif dapat langsung dipilih oleh kasir pada form pesanan transaksi baru."
                        />
                    </div>

                    {{-- Tombol Aksi Form --}}
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="createModalOpen = false"
                        >
                            Batal
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            size="sm"
                            class="shadow-xs"
                        >
                            <x-lucide-plus class="size-4" />
                            <span>Simpan Layanan</span>
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.modal>

            {{-- ============================================================
                MODAL 2: UBAH LAYANAN (Hanya Admin)
            ============================================================= --}}
            <x-ui.modal
                show="editModalOpen"
                title="Ubah Data Layanan"
                description="Perbarui tarif, unit hitung, estimasi durasi, atau spesifikasi paket."
                maxWidth="lg"
            >
                <form
                    method="POST"
                    :action="'{{ url('admin/services') }}/' + editForm.id"
                    class="space-y-4"
                >
                    @csrf
                    @method('PUT')

                    {{-- Nama Layanan --}}
                    <div>
                        <x-ui.label for="edit_name" required>
                            Nama Layanan
                        </x-ui.label>
                        <div class="mt-1.5">
                            <x-ui.input
                                id="edit_name"
                                type="text"
                                name="name"
                                x-model="editForm.name"
                                required
                                maxlength="100"
                            />
                        </div>
                    </div>

                    {{-- Baris: Satuan Unit & Kategori Layanan --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="edit_unit" required>
                                Satuan Hitung (Unit)
                            </x-ui.label>
                            <select
                                id="edit_unit"
                                name="unit"
                                x-model="editForm.unit"
                                required
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-2xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                            >
                                <option value="kg">Kilogram (Kg) — Cuci Kiloan</option>
                                <option value="pcs">Satuan / Potong (Pcs) — Jas, Selimut, Sepatu</option>
                                <option value="meter">Meter Persegi (M²) — Karpet, Gorden</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="edit_service_type" required>
                                Tipe / Paket Layanan
                            </x-ui.label>
                            <select
                                id="edit_service_type"
                                name="service_type"
                                x-model="editForm.service_type"
                                @change="if (editForm.service_type === 'express') editForm.is_express = true"
                                required
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-2xs transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                            >
                                <option value="reguler">Reguler Standar</option>
                                <option value="express">⚡ Paket Kilat / Express</option>
                                <option value="satuan">Cuci Satuan Khusus</option>
                                <option value="khusus">Perawatan Khusus (Sepatu/Karpet)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris: Harga & Estimasi Durasi --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="edit_price" required>
                                Tarif Satuan (Rp / <span x-text="editForm.unit === 'pcs' ? 'Pcs' : (editForm.unit === 'meter' ? 'M²' : 'Kg')"></span>)
                            </x-ui.label>
                            <div class="mt-1.5">
                                <x-ui.input
                                    id="edit_price"
                                    type="number"
                                    name="price"
                                    x-model="editForm.price"
                                    required
                                    min="1"
                                    step="100"
                                    prefix="Rp"
                                    class="tabular-nums"
                                />
                            </div>
                        </div>

                        <div>
                            <x-ui.label for="edit_estimated_duration" required>
                                Estimasi Durasi (Jam)
                            </x-ui.label>
                            <div class="mt-1.5">
                                <x-ui.input
                                    id="edit_estimated_duration"
                                    type="number"
                                    name="estimated_duration"
                                    x-model="editForm.estimated_duration"
                                    required
                                    min="1"
                                    max="720"
                                    suffix="Jam"
                                    class="tabular-nums"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Baris: Order Minimum & Toggle Express --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="edit_minimum_order" optional>
                                Order Minimum
                            </x-ui.label>
                            <div class="mt-1.5">
                                <x-ui.input
                                    id="edit_minimum_order"
                                    type="number"
                                    name="minimum_order"
                                    x-model="editForm.minimum_order"
                                    min="0.1"
                                    step="0.5"
                                    class="tabular-nums"
                                />
                            </div>
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="relative flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    name="is_express"
                                    value="1"
                                    x-model="editForm.is_express"
                                    class="size-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                />
                                <span class="text-sm font-bold text-amber-900 flex items-center gap-1">
                                    <x-lucide-zap class="size-3.5 text-amber-500 fill-amber-500" />
                                    Tandai Sebagai Paket Kilat
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Deskripsi Layanan --}}
                    <div>
                        <x-ui.label for="edit_description" optional>
                            Deskripsi Layanan
                        </x-ui.label>
                        <textarea
                            id="edit_description"
                            name="description"
                            x-model="editForm.description"
                            rows="2"
                            maxlength="1000"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-2xs transition placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 "
                        ></textarea>
                    </div>

                    {{-- Toggle Switch Status Aktif --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                        <x-ui.switch
                            id="edit_is_active"
                            name="is_active"
                            value="1"
                            x-model="editForm.is_active"
                            label="Ketersediaan Layanan"
                            description="Aktifkan agar layanan muncul sebagai pilihan transaksi kasir."
                        />
                    </div>

                    {{-- Info Snapshot Note --}}
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50/60 p-3 text-sm text-amber-800">
                        <p class="font-semibold">Catatan Snapshot Harga:</p>
                        <p class="mt-0.5 text-xs text-amber-700">
                            Perubahan tarif ini hanya berlaku untuk transaksi baru yang akan datang. Transaksi lama tetap mempertahankan nominal riil saat transaksi dibuat.
                        </p>
                    </div>

                    {{-- Tombol Aksi Form --}}
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="editModalOpen = false"
                        >
                            Batal
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            size="sm"
                            class="shadow-xs"
                        >
                            <x-lucide-check class="size-4" />
                            <span>Simpan Perubahan</span>
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.modal>

            {{-- ============================================================
                MODAL 3: KONFIRMASI HAPUS / PROTEKSI TRANSAKSI
            ============================================================= --}}
            <x-ui.modal
                show="deleteModalOpen"
                maxWidth="md"
                :title="null"
            >
                {{-- Kondisi 1: Layanan sudah memiliki transaksi --}}
                <template x-if="deleteForm.transactionCount > 0">
                    <div>
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <x-lucide-alert-triangle class="size-6" />
                        </div>
                        <h3 class="mt-4 text-base font-bold text-slate-900">Layanan Tidak Dapat Dihapus</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Layanan <span class="font-semibold text-slate-900" x-text="'&ldquo;' + deleteForm.name + '&rdquo;'"></span> sudah tercatat dalam
                            <span class="font-bold text-amber-700" x-text="deleteForm.transactionCount + ' riwayat transaksi'"></span>. Sesuai aturan integritas operasional, data historis tidak boleh dihapus.
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            Solusi: Anda dapat menonaktifkan layanan ini agar tidak lagi muncul sebagai opsi transaksi baru.
                        </p>
                        <div class="mt-6 flex items-center justify-end border-t border-slate-100 pt-4">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="deleteModalOpen = false"
                            >
                                Tutup
                            </x-ui.button>
                        </div>
                    </div>
                </template>

                {{-- Kondisi 2: Layanan belum pernah digunakan transaksi (Aman dihapus) --}}
                <template x-if="deleteForm.transactionCount === 0">
                    <div>
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                            <x-lucide-trash-2 class="size-6" />
                        </div>
                        <h3 class="mt-4 text-base font-bold text-slate-900">Konfirmasi Hapus Layanan</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
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
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="deleteModalOpen = false"
                            >
                                Batal
                            </x-ui.button>
                            <x-ui.button
                                type="submit"
                                variant="danger"
                                size="sm"
                                class="shadow-xs"
                            >
                                <x-lucide-trash-2 class="size-4" />
                                <span>Hapus Permanen</span>
                            </x-ui.button>
                        </form>
                    </div>
                </template>
            </x-ui.modal>
        @endif

        {{-- ============================================================
            MODAL 4: DETAIL LAYANAN & SIMULASI KALKULASI SUB-TOTAL
        ============================================================= --}}
        <x-ui.modal
            show="detailModalOpen"
            maxWidth="lg"
            :title="null"
        >
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-4">
                <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <x-lucide-info class="size-5" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" x-text="detailData.name"></h3>
                    <p class="text-sm text-slate-500">Rincian spesifikasi paket dan simulasi biaya cucian.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                {{-- Grid Atribut --}}
                <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <div>
                        <span class="text-xs text-slate-500">Tarif Dinamis</span>
                        <p class="mt-0.5 text-sm font-bold tabular-nums text-brand-700" x-text="formatRupiah(detailData.price) + ' / ' + detailData.unit_label"></p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Estimasi Durasi</span>
                        <p class="mt-0.5 text-sm font-bold tabular-nums text-slate-800" x-text="detailData.estimated_duration + ' Jam' + (detailData.is_express ? ' (⚡ Kilat)' : '')"></p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Kategori & Tipe</span>
                        <p class="mt-0.5 text-sm font-semibold text-slate-700" x-text="detailData.type_label"></p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Order Minimum</span>
                        <p class="mt-0.5 text-sm font-semibold text-slate-700" x-text="detailData.minimum_order + ' ' + detailData.unit_label"></p>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <span class="text-sm font-semibold text-slate-700">Keterangan Layanan:</span>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600" x-text="detailData.description"></p>
                </div>

                {{-- Simulasi Subtotal Berdasarkan Unit --}}
                <div class="rounded-xl border border-brand-200/80 bg-brand-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-brand-900">Simulasi Subtotal Kasir</span>
                        <span class="text-xs font-medium text-brand-600">Formula: Kuantitas × Tarif</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="w-1/2">
                            <label class="text-xs font-semibold text-slate-600">
                                Kuantitas (<span x-text="detailData.unit_label"></span>):
                            </label>
                            <input
                                type="number"
                                min="0.1"
                                step="0.5"
                                x-model.number="detailData.calcQty"
                                aria-label="Kuantitas simulasi layanan"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm tabular-nums text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-200"
                            />
                        </div>
                        <div class="w-1/2">
                            <label class="text-xs font-semibold text-slate-600">Hasil Subtotal:</label>
                            <div class="mt-1 rounded-lg bg-white px-2.5 py-1.5 text-sm font-bold tabular-nums text-brand-700 border border-brand-100">
                                <span x-text="formatRupiah(detailData.calcQty * detailData.price)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end border-t border-slate-100 pt-4">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    size="sm"
                    @click="detailModalOpen = false"
                >
                    Tutup
                </x-ui.button>
            </div>
        </x-ui.modal>

    </div>
</x-layouts::app>
