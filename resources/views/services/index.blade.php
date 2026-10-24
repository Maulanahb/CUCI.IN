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
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                    Katalog Layanan Laundry
                </h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Kelola tarif pencucian, durasi estimasi pengerjaan, dan ketersediaan layanan operasional CUCI.IN ({{ $isAdmin ? 'Mode Administrator' : 'Katalog Kasir / Staff' }}).
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($isAdmin)
                    <x-ui.button
                        id="btn-open-create-service"
                        type="button"
                        size="sm"
                        @click="openCreateModal()"
                        class="shadow-xs"
                    >
                        <x-lucide-plus class="size-4" />
                        <span>Tambah Layanan</span>
                    </x-ui.button>
                @endif

                <x-ui.button
                    href="{{ route($currentRoutePrefix . '.transactions.create') }}"
                    variant="secondary"
                    size="sm"
                >
                    <x-lucide-receipt class="size-4 text-brand-600" />
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

        {{-- ============================================================
            KPI STATISTIC CARDS (Aqua Teal + Semantic)
        ============================================================= --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            {{-- Card 1: Total Layanan --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Paket</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <x-lucide-layers class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold tabular-nums tracking-tight text-slate-900 sm:text-2xl">{{ $stats['total'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Seluruh jenis layanan</p>
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
                <p class="mt-2 text-xl font-bold tabular-nums tracking-tight text-emerald-600 sm:text-2xl">{{ $stats['active'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Siap dipilih di kasir</p>
            </div>

            {{-- Card 3: Layanan Nonaktif --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs transition hover:border-slate-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Nonaktif / Tutup</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                        <x-lucide-archive class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold tabular-nums tracking-tight text-slate-600 sm:text-2xl">{{ $stats['inactive'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Layanan diarsipkan</p>
            </div>

            {{-- Card 4: Rata-rata Tarif --}}
            <div class="rounded-2xl border border-brand-100 bg-brand-50/40 p-4 shadow-xs transition hover:border-brand-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-brand-900">Rata-rata Tarif</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                        <x-lucide-coins class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-lg font-bold tabular-nums tracking-tight text-brand-700 sm:text-xl">
                    Rp {{ number_format($stats['avg_price'], 0, ',', '.') }}
                </p>
                <p class="mt-0.5 text-2xs text-brand-600/80">
                    Durasi tercepat: {{ $stats['min_duration'] > 0 ? $stats['min_duration'] . ' jam' : '-' }}
                </p>
            </div>
        </div>

        {{-- ============================================================
            FILTER & SEARCH TOOLBAR (Pines UI Segmented Tabs & Input)
        ============================================================= --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200/90 bg-white p-3.5 shadow-xs sm:flex-row sm:items-center sm:justify-between">
            {{-- Tab Filter Status (Pines UI Segmented Control) --}}
            <x-ui.tabs variant="segmented">
                <x-ui.tab
                    :href="route($currentRoutePrefix . '.services.index', array_merge(request()->except(['status', 'page']), ['status' => 'all']))"
                    :active="$statusFilter === 'all'"
                    :badge="$stats['total']"
                    :badgeTone="$statusFilter === 'all' ? 'brand' : 'neutral'"
                >
                    Semua
                </x-ui.tab>
                <x-ui.tab
                    :href="route($currentRoutePrefix . '.services.index', array_merge(request()->except(['status', 'page']), ['status' => 'active']))"
                    :active="$statusFilter === 'active'"
                    :badge="$stats['active']"
                    :badgeTone="$statusFilter === 'active' ? 'emerald' : 'neutral'"
                >
                    Aktif
                </x-ui.tab>
                <x-ui.tab
                    :href="route($currentRoutePrefix . '.services.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive']))"
                    :active="$statusFilter === 'inactive'"
                    :badge="$stats['inactive']"
                    :badgeTone="$statusFilter === 'inactive' ? 'neutral' : 'neutral'"
                >
                    Nonaktif
                </x-ui.tab>
            </x-ui.tabs>

            {{-- Form Pencarian --}}
            <form method="GET" action="{{ route("{$currentRoutePrefix}.services.index") }}" class="relative w-full sm:w-72">
                @if ($statusFilter !== 'all')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-lucide-search class="size-4 text-slate-400" />
                </div>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari nama layanan..."
                    class="w-full rounded-xl border border-slate-200 bg-white py-2 pr-9 pl-9 text-xs text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 sm:text-sm"
                />
                @if ($search !== '')
                    <a
                        href="{{ route("{$currentRoutePrefix}.services.index", ['status' => $statusFilter]) }}"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition"
                        title="Hapus pencarian"
                    >
                        <x-lucide-x class="size-4" />
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
                    <thead class="border-b border-slate-100 bg-slate-50/75 text-2xs font-bold uppercase tracking-wider text-slate-500 sm:text-xs">
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
                                            <x-lucide-shirt class="size-4" />
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
                                    <span class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold tabular-nums text-brand-700">
                                        Rp {{ number_format($service->price, 0, ',', '.') }} <span class="ml-1 text-2xs font-normal text-brand-600/80">/kg</span>
                                    </span>
                                </td>

                                {{-- Estimasi Durasi --}}
                                <td class="px-5 py-4 text-slate-600">
                                    <div class="flex items-center gap-1.5 text-xs font-medium sm:text-sm">
                                        <x-lucide-clock class="size-4 text-slate-400" />
                                        <span>{{ $service->estimated_duration }} Jam</span>
                                        @if ($service->estimated_duration >= 24)
                                            <span class="text-2xs text-slate-400">
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
                                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset transition cursor-pointer select-none active:scale-95',
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
                                <td class="px-5 py-4 text-center">
                                    @if ($service->transaction_details_count > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                            <span class="tabular-nums">{{ $service->transaction_details_count }}</span>
                                            <span class="text-2xs font-normal">order</span>
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
                                            >
                                                <x-lucide-pencil class="size-4" />
                                            </x-ui.button>

                                            {{-- Tombol Hapus --}}
                                            <x-ui.button
                                                type="button"
                                                variant="{{ $service->transaction_details_count > 0 ? 'muted' : 'danger-outline' }}"
                                                size="icon"
                                                @click="openDeleteModal({{ json_encode($service) }})"
                                                title="{{ $service->transaction_details_count > 0 ? 'Layanan memiliki riwayat transaksi (Gunakan nonaktifkan)' : 'Hapus Layanan' }}"
                                            >
                                                <x-lucide-trash-2 class="size-4" />
                                            </x-ui.button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-lucide-inbox class="size-6 text-slate-400" />
                                    </div>
                                    <h3 class="mt-3 text-sm font-semibold text-slate-900">Tidak ada layanan ditemukan</h3>
                                    <p class="mt-1 text-xs text-slate-500">
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
                                placeholder="Contoh: Cuci Komplit Kilat, Cuci Karpet Tebal"
                            />
                        </div>
                    </div>

                    {{-- Baris: Harga & Estimasi Durasi --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="create_price" required>
                                Tarif Satuan (Rp/kg)
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

                    {{-- Deskripsi Layanan --}}
                    <div>
                        <x-ui.label for="create_description" optional>
                            Deskripsi Layanan
                        </x-ui.label>
                        <textarea
                            id="create_description"
                            name="description"
                            rows="3"
                            maxlength="1000"
                            placeholder="Detail proses pengerjaan, perlakuan khusus, atau fasilitas layanan..."
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 sm:text-sm"
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
                description="Perbarui tarif, estimasi durasi, atau keterangan paket."
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

                    {{-- Baris: Harga & Estimasi Durasi --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="edit_price" required>
                                Tarif Satuan (Rp/kg)
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

                    {{-- Deskripsi Layanan --}}
                    <div>
                        <x-ui.label for="edit_description" optional>
                            Deskripsi Layanan
                        </x-ui.label>
                        <textarea
                            id="edit_description"
                            name="description"
                            x-model="editForm.description"
                            rows="3"
                            maxlength="1000"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 sm:text-sm"
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
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50/60 p-3 text-xs text-amber-800">
                        <p class="font-semibold">Catatan Snapshot Harga:</p>
                        <p class="mt-0.5 text-2xs text-amber-700">
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
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">
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
                    <p class="text-xs text-slate-500">Rincian spesifikasi paket dan simulasi biaya cucian.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                {{-- Grid Atribut --}}
                <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <div>
                        <span class="text-2xs text-slate-400">Tarif Satuan</span>
                        <p class="mt-0.5 text-sm font-bold tabular-nums text-brand-700" x-text="formatRupiah(detailData.price) + ' / kg'"></p>
                    </div>
                    <div>
                        <span class="text-2xs text-slate-400">Estimasi Durasi</span>
                        <p class="mt-0.5 text-sm font-bold tabular-nums text-slate-800" x-text="detailData.estimated_duration + ' Jam'"></p>
                    </div>
                    <div>
                        <span class="text-2xs text-slate-400">Status Layanan</span>
                        <p class="mt-0.5 text-xs font-semibold" :class="detailData.is_active ? 'text-emerald-600' : 'text-slate-500'" x-text="detailData.is_active ? 'Tersedia (Aktif)' : 'Nonaktif'"></p>
                    </div>
                    <div>
                        <span class="text-2xs text-slate-400">Total Digunakan</span>
                        <p class="mt-0.5 text-xs font-semibold tabular-nums text-slate-800" x-text="detailData.transaction_details_count + ' Transaksi'"></p>
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
                        <span class="text-2xs font-medium text-brand-600">Formula: Berat × Tarif</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="w-1/2">
                            <label class="text-2xs font-semibold text-slate-600">Berat Cucian (kg):</label>
                            <input
                                type="number"
                                min="0.1"
                                step="0.5"
                                x-model.number="detailData.calcWeight"
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs tabular-nums text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-200"
                            />
                        </div>
                        <div class="w-1/2">
                            <label class="text-2xs font-semibold text-slate-600">Hasil Subtotal:</label>
                            <div class="mt-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold tabular-nums text-brand-700 border border-brand-100">
                                <span x-text="formatRupiah(detailData.calcWeight * detailData.price)"></span>
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
