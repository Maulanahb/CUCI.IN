@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
@endphp

<x-layouts::app title="Data Pelanggan">
    <div
        class="space-y-6"
        x-data="{
            createModalOpen: false,
            editModalOpen: false,
            deleteModalOpen: false,
            editForm: { id: '', name: '', phone: '', address: '' },
            deleteForm: { id: '', name: '' },
            formatPhone(val) {
                if (!val) return '';
                let cleaned = val.toString().trim().replace(/[^\d+]/g, '');
                if (cleaned.startsWith('+62')) {
                    let rest = cleaned.substring(3).replace(/^0+/, '');
                    return '0' + rest;
                } else if (cleaned.startsWith('62')) {
                    let rest = cleaned.substring(2).replace(/^0+/, '');
                    return '0' + rest;
                } else if (cleaned.startsWith('8')) {
                    return '0' + cleaned;
                }
                return cleaned.replace(/^\+/, '');
            },
            openEditModal(data) {
                this.editForm = { ...data };
                this.editModalOpen = true;
            },
            openDeleteModal(data) {
                this.deleteForm = { ...data };
                this.deleteModalOpen = true;
            }
        }"
        @keydown.escape.window="createModalOpen = false; editModalOpen = false; deleteModalOpen = false"
    >
        {{-- Sub-deskripsi & aksi halaman --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">
                Kelola direktori kontak pelanggan, riwayat cucian, serta pemantauan status order laundry secara terpadu.
            </p>

            <x-ui.button type="button" @click="createModalOpen = true" class="w-full sm:w-auto shadow-xs">
                <x-lucide-user-plus class="size-4" />
                <span>Tambah Pelanggan</span>
            </x-ui.button>
        </div>

        {{-- Pesan Status & Error Alert --}}
        @if (session('status'))
            <x-ui.alert variant="success" dismissible>
                {{ session('status') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive" title="Terjadi kesalahan saat memproses data pelanggan:" dismissible>
                <ul class="list-inside list-disc space-y-0.5 pl-1 font-normal text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- Card Metrik Ringkasan --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs transition hover:border-slate-300">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Total Pelanggan</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <x-lucide-users class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ $totalCustomersCount }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Akun terdaftar</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-xs transition hover:border-amber-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Sedang Proses</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <x-lucide-clock class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-amber-600">{{ $counts['processing'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Sedang dikerjakan</p>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-white p-4 shadow-xs transition hover:border-sky-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Siap Diambil</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-700">
                        <x-lucide-package-check class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-sky-600">{{ $counts['ready'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Menunggu diambil</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-xs transition hover:border-emerald-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Selesai / Diambil</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <x-lucide-check-circle-2 class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-emerald-600">{{ $counts['completed'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400">Sudah diserahkan</p>
            </div>
        </div>

        {{-- Kontainer Utama --}}
        @if (! $selectedCustomer)
            {{-- VIEW A: Full Data Table (Saat Belum Ada Pelanggan Yang Dipilih) --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                {{-- Header Tabel & Kontrol Pencarian/Filter --}}
                <div class="border-b border-slate-100 p-4 sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Daftar Pelanggan</h2>
                            <p class="text-xs text-slate-500">Pilih pelanggan untuk melihat rincian nota atau kelola data melalui tombol aksi.</p>
                        </div>

                        {{-- Search Input Form --}}
                        <form method="GET" action="{{ route("{$currentRoutePrefix}.customers.index") }}" class="w-full sm:w-72">
                            @if ($statusFilter !== 'all')
                                <input type="hidden" name="status" value="{{ $statusFilter }}">
                            @endif
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <x-lucide-search class="size-4 text-slate-400" />
                                </div>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari nama atau telepon..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-9 text-xs text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                >
                                @if ($search !== '')
                                    <a
                                        href="{{ route("{$currentRoutePrefix}.customers.index", ['status' => $statusFilter]) }}"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition"
                                        title="Hapus pencarian"
                                    >
                                        <x-lucide-x class="size-4" />
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Tab Status Pills (Pines UI Segmented Control) --}}
                    <x-ui.tabs variant="segmented" class="mt-4">
                        <x-ui.tab
                            :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'all']))"
                            :active="$statusFilter === 'all'"
                            :badge="$counts['all']"
                        >
                            Semua
                        </x-ui.tab>
                        <x-ui.tab
                            :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'processing']))"
                            :active="$statusFilter === 'processing'"
                            :badge="$counts['processing']"
                        >
                            Sedang Proses
                        </x-ui.tab>
                        <x-ui.tab
                            :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'ready']))"
                            :active="$statusFilter === 'ready'"
                            :badge="$counts['ready']"
                        >
                            Siap Diambil
                        </x-ui.tab>
                        <x-ui.tab
                            :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'completed']))"
                            :active="$statusFilter === 'completed'"
                            :badge="$counts['completed']"
                        >
                            Selesai
                        </x-ui.tab>
                    </x-ui.tabs>
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-100 bg-slate-50/60 text-xs font-bold tracking-wider text-slate-500 uppercase">
                            <tr>
                                <th class="px-5 py-3.5">Pelanggan</th>
                                <th class="px-5 py-3.5">Kontak / WhatsApp</th>
                                <th class="px-5 py-3.5">Alamat</th>
                                <th class="px-5 py-3.5">Total Nota</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($customers as $customer)
                                @php
                                    $initials = collect(explode(' ', $customer->name))
                                        ->map(fn($part) => mb_substr($part, 0, 1))
                                        ->take(2)
                                        ->implode('');
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    {{-- Pelanggan --}}
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-xs font-bold text-brand-800">
                                                {{ $initials ?: 'CU' }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-900">{{ $customer->name }}</span>
                                                <p class="text-xs text-slate-500">
                                                    Terdaftar {{ $customer->created_at ? $customer->created_at->translatedFormat('d M Y') : '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kontak --}}
                                    <td class="px-5 py-3.5">
                                        @if ($customer->phone)
                                            <a
                                                href="{{ $customer->whatsapp_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 font-medium text-slate-800 hover:text-emerald-700 transition group"
                                                title="Buka obrolan WhatsApp ke {{ $customer->name }}"
                                            >
                                                <x-lucide-message-circle class="size-3.5 text-emerald-600 transition group-hover:scale-110" />
                                                <span class="group-hover:underline underline-offset-2 tabular-nums">{{ $customer->phone }}</span>
                                            </a>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>

                                    {{-- Alamat --}}
                                    <td class="px-5 py-3.5">
                                        <span class="line-clamp-1 max-w-[220px] text-slate-600" title="{{ $customer->address ?: '-' }}">
                                            {{ $customer->address ?: '-' }}
                                        </span>
                                    </td>

                                    {{-- Total Nota & Loyalty --}}
                                    <td class="px-5 py-3.5">
                                        @if ($customer->transactions_count >= 10)
                                            <x-ui.badge tone="warning" size="sm" class="gap-1 font-semibold">
                                                <x-lucide-crown class="size-3 text-amber-500" />
                                                <span class="tabular-nums">{{ $customer->transactions_count }}</span> Nota (Loyal)
                                            </x-ui.badge>
                                        @elseif ($customer->transactions_count >= 5)
                                            <x-ui.badge tone="brand" size="sm" class="gap-1 font-semibold">
                                                <x-lucide-sparkles class="size-3 text-brand-500" />
                                                <span class="tabular-nums">{{ $customer->transactions_count }}</span> Nota (Reguler)
                                            </x-ui.badge>
                                        @elseif ($customer->transactions_count > 0)
                                            <x-ui.badge tone="neutral" size="sm">
                                                <span class="tabular-nums">{{ $customer->transactions_count }}</span> Nota
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge tone="neutral" size="sm" class="text-slate-500">
                                                Belum Ada Nota
                                            </x-ui.badge>
                                        @endif
                                    </td>

                                    {{-- Tombol Aksi --}}
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- 1. Tombol Detail --}}
                                            <x-ui.button
                                                :href="route($currentRoutePrefix . '.customers.index', array_filter([
                                                    'customer_id' => $customer->id,
                                                    'q' => $search,
                                                    'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                                    'page' => request('page'),
                                                ]))"
                                                variant="soft"
                                                size="row"
                                                title="Lihat profil dan riwayat nota"
                                            >
                                                <x-lucide-eye class="size-3.5" />
                                                <span>Detail</span>
                                            </x-ui.button>

                                            {{-- 2. Tombol Edit --}}
                                            <x-ui.button
                                                type="button"
                                                variant="soft-warning"
                                                size="row"
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $customer->id,
                                                    'name' => $customer->name,
                                                    'phone' => $customer->phone,
                                                    'address' => $customer->address ?? '',
                                                ]) }})"
                                                title="Edit data pelanggan"
                                            >
                                                <x-lucide-pencil class="size-3.5 text-amber-600" />
                                                <span>Edit</span>
                                            </x-ui.button>

                                            {{-- 3. Tombol Hapus / Terkunci --}}
                                            @if ($customer->transactions_count > 0)
                                                <x-ui.button
                                                    as="span"
                                                    variant="muted"
                                                    size="row"
                                                    class="cursor-not-allowed"
                                                    title="Pelanggan memiliki {{ $customer->transactions_count }} riwayat nota dan tidak dapat dihapus."
                                                >
                                                    <x-lucide-lock class="size-3.5" />
                                                    <span>Terkunci</span>
                                                </x-ui.button>
                                            @else
                                                <x-ui.button
                                                    type="button"
                                                    variant="danger-outline"
                                                    size="row"
                                                    @click="openDeleteModal({{ json_encode([
                                                        'id' => $customer->id,
                                                        'name' => $customer->name,
                                                    ]) }})"
                                                    title="Hapus data pelanggan"
                                                >
                                                    <x-lucide-trash-2 class="size-3.5 text-rose-600" />
                                                    <span>Hapus</span>
                                                </x-ui.button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center">
                                        <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                            <x-lucide-users class="size-6 text-slate-400" />
                                        </div>
                                        <p class="mt-3 text-sm font-bold text-slate-900">Data Pelanggan Tidak Ditemukan</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Silakan sesuaikan kata kunci atau filter status pencarian Anda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card List View --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        @php
                            $initials = collect(explode(' ', $customer->name))
                                ->map(fn($part) => mb_substr($part, 0, 1))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-xs font-bold text-brand-800">
                                        {{ $initials ?: 'CU' }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900">{{ $customer->name }}</h3>
                                        <p class="text-xs text-slate-500 tabular-nums">{{ $customer->phone ?: '-' }}</p>
                                    </div>
                                </div>
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700">
                                    <span class="tabular-nums">{{ $customer->transactions_count }}</span> Nota
                                </span>
                            </div>

                            @if ($customer->address)
                                <p class="text-xs text-slate-600 line-clamp-1">
                                    {{ $customer->address }}
                                </p>
                            @endif

                            {{-- Mobile Actions Bar --}}
                            <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                                <x-ui.button
                                    :href="route($currentRoutePrefix . '.customers.index', array_filter([
                                        'customer_id' => $customer->id,
                                        'q' => $search,
                                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                        'page' => request('page'),
                                    ]))"
                                    variant="soft"
                                    size="xs"
                                    class="flex-1"
                                >
                                    <x-lucide-eye class="mr-1 size-3" />
                                    <span>Detail</span>
                                </x-ui.button>
                                <x-ui.button
                                    type="button"
                                    variant="soft-warning"
                                    size="xs"
                                    class="flex-1"
                                    @click="openEditModal({{ json_encode([
                                        'id' => $customer->id,
                                        'name' => $customer->name,
                                        'phone' => $customer->phone,
                                        'address' => $customer->address ?? '',
                                    ]) }})"
                                >
                                    <x-lucide-pencil class="mr-1 size-3 text-amber-600" />
                                    <span>Edit</span>
                                </x-ui.button>
                                @if ($customer->transactions_count === 0)
                                    <x-ui.button
                                        type="button"
                                        variant="danger-outline"
                                        size="xs"
                                        class="flex-1"
                                        @click="openDeleteModal({{ json_encode([
                                            'id' => $customer->id,
                                            'name' => $customer->name,
                                        ]) }})"
                                    >
                                        <x-lucide-trash-2 class="mr-1 size-3 text-rose-600" />
                                        <span>Hapus</span>
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-500">
                            Data Tidak Ditemukan.
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Footer --}}
                @if ($customers->hasPages())
                    <div class="border-t border-slate-100 p-4">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>

        @else
            {{-- VIEW B: Split Master-Detail Layout (Saat Pelanggan Tertentu Sedang Dipilih) --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">

                {{-- Kolom Kiri (Master List): Daftar Pelanggan Kompak --}}
                <div class="lg:col-span-5 xl:col-span-4 rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                    {{-- Header List Pelanggan --}}
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-900">Daftar Pelanggan</h2>
                            <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold tabular-nums text-slate-600">
                                {{ $totalCustomersCount }} Total
                            </span>
                        </div>

                        {{-- Form Pencarian Singkat --}}
                        <form method="GET" action="{{ route("{$currentRoutePrefix}.customers.index") }}" class="mt-3">
                            @if ($statusFilter !== 'all')
                                <input type="hidden" name="status" value="{{ $statusFilter }}">
                            @endif
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <x-lucide-search class="size-3.5 text-slate-400" />
                                </div>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari nama atau telepon..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-8 pl-8 text-xs text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                >
                            </div>
                        </form>

                        {{-- Tab Filter Pills (Pines UI Segmented Control) --}}
                        <x-ui.tabs variant="segmented" class="mt-3">
                            <x-ui.tab
                                :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'all']))"
                                :active="$statusFilter === 'all'"
                                :badge="$counts['all']"
                            >
                                Semua
                            </x-ui.tab>
                            <x-ui.tab
                                :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'processing']))"
                                :active="$statusFilter === 'processing'"
                                :badge="$counts['processing']"
                            >
                                Proses
                            </x-ui.tab>
                            <x-ui.tab
                                :href="route($currentRoutePrefix . '.customers.index', array_filter(['q' => $search, 'status' => 'ready']))"
                                :active="$statusFilter === 'ready'"
                                :badge="$counts['ready']"
                            >
                                Siap
                            </x-ui.tab>
                        </x-ui.tabs>
                    </div>

                    {{-- List Item Pelanggan --}}
                    <div class="divide-y divide-slate-100">
                        @forelse ($customers as $customer)
                            @php
                                $isSelected = $selectedCustomer->id === $customer->id;
                                $initials = collect(explode(' ', $customer->name))
                                    ->map(fn($part) => mb_substr($part, 0, 1))
                                    ->take(2)
                                    ->implode('');
                            @endphp
                            <div
                                @class([
                                    'p-3.5 transition space-y-2',
                                    'bg-brand-50/60 ring-1 ring-inset ring-brand-100' => $isSelected,
                                    'hover:bg-slate-50/70' => ! $isSelected,
                                ])
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div @class([
                                            'flex size-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold uppercase transition',
                                            'bg-brand-200 text-brand-900 ring-2 ring-brand-500/30' => $isSelected,
                                            'bg-brand-100 text-brand-800' => ! $isSelected,
                                        ])>
                                            {{ $initials ?: 'CU' }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate text-xs font-bold text-slate-900 sm:text-sm">{{ $customer->name }}</h3>
                                            <p class="truncate text-xs text-slate-500 tabular-nums">{{ $customer->phone ?: '-' }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-semibold tabular-nums text-slate-600">
                                        {{ $customer->transactions_count }} Nota
                                    </span>
                                </div>

                                {{-- Action Bar on Each Card in Master List --}}
                                <div class="flex items-center justify-end gap-1.5 pt-1.5 border-t border-slate-100/70">
                                    <x-ui.button
                                        :href="route($currentRoutePrefix . '.customers.index', array_filter([
                                            'customer_id' => $customer->id,
                                            'q' => $search,
                                            'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                            'page' => request('page'),
                                        ]))"
                                        :variant="$isSelected ? 'primary' : 'soft'"
                                        size="xs"
                                    >
                                        {{ $isSelected ? 'Terpilih' : 'Detail' }}
                                    </x-ui.button>

                                    <x-ui.button
                                        type="button"
                                        variant="soft-warning"
                                        size="xs"
                                        @click="openEditModal({{ json_encode([
                                            'id' => $customer->id,
                                            'name' => $customer->name,
                                            'phone' => $customer->phone,
                                            'address' => $customer->address ?? '',
                                        ]) }})"
                                        title="Edit Pelanggan"
                                    >
                                        Edit
                                    </x-ui.button>

                                    @if ($customer->transactions_count === 0)
                                        <x-ui.button
                                            type="button"
                                            variant="danger-outline"
                                            size="xs"
                                            @click="openDeleteModal({{ json_encode([
                                                'id' => $customer->id,
                                                'name' => $customer->name,
                                            ]) }})"
                                            title="Hapus Pelanggan"
                                        >
                                            Hapus
                                        </x-ui.button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-slate-500">
                                Data Tidak Ditemukan.
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination Footer --}}
                    @if ($customers->hasPages())
                        <div class="border-t border-slate-100 p-3">
                            {{ $customers->links() }}
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan (Detail Panel): Profil & Riwayat Cucian --}}
                <div class="lg:col-span-7 xl:col-span-8 space-y-5">
                    @php
                        $selectedInitials = collect(explode(' ', $selectedCustomer->name))
                            ->map(fn($part) => mb_substr($part, 0, 1))
                            ->take(2)
                            ->implode('');
                        $hasTransactions = $selectedCustomer->transactions()->exists();
                    @endphp

                    {{-- 1. Kartu Info Profil Pelanggan Terpilih --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3.5">
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-sm font-bold text-brand-800">
                                    {{ $selectedInitials ?: 'CU' }}
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 sm:text-lg">{{ $selectedCustomer->name }}</h2>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        @if ($selectedCustomer->phone)
                                             <a
                                                href="{{ $selectedCustomer->whatsapp_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold hover:text-emerald-800 transition group"
                                                title="Buka obrolan WhatsApp ke {{ $selectedCustomer->name }}"
                                            >
                                                <x-lucide-message-circle class="size-3.5 text-emerald-600" />
                                                <span class="group-hover:underline underline-offset-2 tabular-nums">WhatsApp: {{ $selectedCustomer->phone }}</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-500">WhatsApp: -</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Aksi Panel Detail --}}
                            <div class="flex items-center gap-2">
                                <x-ui.button
                                    type="button"
                                    variant="soft-warning"
                                    size="row"
                                    @click="openEditModal({{ json_encode([
                                        'id' => $selectedCustomer->id,
                                        'name' => $selectedCustomer->name,
                                        'phone' => $selectedCustomer->phone,
                                        'address' => $selectedCustomer->address,
                                    ]) }})"
                                    title="Edit data pelanggan"
                                >
                                    <x-lucide-pencil class="size-3.5 text-amber-600" />
                                    <span>Edit</span>
                                </x-ui.button>

                                @if ($hasTransactions)
                                    <x-ui.button
                                        as="span"
                                        variant="muted"
                                        size="row"
                                        class="cursor-not-allowed"
                                        title="Pelanggan memiliki riwayat transaksi dan tidak dapat dihapus."
                                    >
                                        <x-lucide-lock class="size-3.5" />
                                        <span>Terkunci</span>
                                    </x-ui.button>
                                @else
                                    <x-ui.button
                                        type="button"
                                        variant="danger-outline"
                                        size="row"
                                        @click="openDeleteModal({{ json_encode([
                                            'id' => $selectedCustomer->id,
                                            'name' => $selectedCustomer->name,
                                        ]) }})"
                                        title="Hapus data pelanggan"
                                    >
                                        <x-lucide-trash-2 class="size-3.5 text-rose-600" />
                                        <span>Hapus</span>
                                    </x-ui.button>
                                @endif

                                {{-- Tutup Detail (Kembali ke Tampilan Penuh) --}}
                                <x-ui.button
                                    :href="route($currentRoutePrefix . '.customers.index', array_filter([
                                        'q' => $search,
                                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                        'page' => request('page'),
                                    ]))"
                                    variant="secondary"
                                    size="icon"
                                    title="Tutup Detail dan Kembali ke Tabel"
                                >
                                    <x-lucide-x class="size-4" />
                                </x-ui.button>
                            </div>
                        </div>

                        {{-- Metadata Pelanggan --}}
                        <div class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-3 sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-bold tracking-wider text-slate-500 uppercase">Terdaftar Sejak</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800">
                                    {{ $selectedCustomer->created_at ? $selectedCustomer->created_at->translatedFormat('d M Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-bold tracking-wider text-slate-500 uppercase">Total Transaksi</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800 tabular-nums">
                                    {{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }} Nota
                                    <span class="font-normal text-slate-500">(Rp {{ number_format($totalSpent, 0, ',', '.') }})</span>
                                </p>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <p class="text-xs font-bold tracking-wider text-slate-500 uppercase">Alamat</p>
                                <p class="mt-0.5 truncate text-xs font-semibold text-slate-700" title="{{ $selectedCustomer->address ?: '-' }}">
                                    {{ $selectedCustomer->address ?: '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Kartu Rincian Transaksi Terpilih (Muncul jika nota di riwayat diklik) --}}
                    @if ($activeTransaction)
                        @php
                            $firstDetail = $activeTransaction->details->first();
                            $service = $firstDetail?->service;
                            $latestPayment = $activeTransaction->payments->last();
                            $isPaid = $latestPayment && in_array($latestPayment->verification_status, ['Tidak Diperlukan', 'Terverifikasi']);
                        @endphp
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <span class="text-sm font-bold text-slate-900 sm:text-base tabular-nums">{{ $activeTransaction->transaction_code }}</span>
                                    <p class="text-xs text-slate-500">
                                        Diterima: {{ $activeTransaction->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        @if ($latestPayment && $latestPayment->creator)
                                            oleh {{ $latestPayment->creator->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-ui.payment-badge :status="$isPaid ? 'Lunas' : 'Belum Bayar'" size="sm" />

                                    @if ($latestPayment)
                                        <span class="text-xs text-slate-500">{{ $latestPayment->method }}</span>
                                    @endif

                                    {{-- Tombol Tutup Rincian Transaksi --}}
                                    <a
                                        href="{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                                            'customer_id' => $selectedCustomer->id,
                                            'q' => $search,
                                            'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                            'page' => request('page'),
                                        ])) }}"
                                        class="inline-flex size-7 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                        title="Tutup rincian transaksi ini"
                                    >
                                        <x-lucide-x class="size-3.5" />
                                    </a>
                                </div>
                            </div>

                            {{-- Box Detail Transaksi --}}
                            <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-xs font-bold tracking-wider text-slate-500 uppercase">Detail Transaksi</p>

                                <div class="mt-2.5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-bold text-slate-900 sm:text-sm">
                                                {{ $service?->name ?? 'Cuci Komplit Reguler' }}
                                            </h4>
                                            <span class="rounded bg-sky-100 px-1.5 py-0.5 text-xs font-bold text-sky-700 uppercase">
                                                Kategori Reguler
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Berat: <strong class="text-slate-700 tabular-nums">{{ $firstDetail?->weight ?? 1 }} kg</strong>
                                            @if ($firstDetail?->unit_price)
                                                <span class="tabular-nums">× Rp {{ number_format($firstDetail->unit_price, 0, ',', '.') }}</span>
                                            @endif
                                            @if ($service?->description)
                                                • {{ $service->description }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-sm font-extrabold text-slate-900 tabular-nums">
                                            Rp {{ number_format($activeTransaction->total_amount, 0, ',', '.') }}
                                        </p>
                                        <span class="mt-0.5 inline-block rounded bg-sky-50 px-1.5 py-0.5 text-xs font-semibold text-sky-700">
                                            Selesai {{ $service?->estimated_duration ?? 3 }} Hari
                                        </span>
                                    </div>
                                </div>

                                {{-- Baris Estimasi & Status Cucian --}}
                                <div class="mt-3 flex flex-col gap-2 rounded-lg bg-white p-2.5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <x-lucide-clock class="size-3.5 text-sky-600" />
                                        <span>
                                            Estimasi Pengambilan:
                                            <strong class="text-slate-800">
                                                {{ $activeTransaction->estimated_completed_at ? $activeTransaction->estimated_completed_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Menunggu update' }}
                                            </strong>
                                        </span>
                                    </div>
                                    <div>
                                        <x-ui.status-badge :status="$activeTransaction->status" />
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Total Akhir --}}
                            <div class="mt-3.5 flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-xs font-bold tracking-wider text-slate-500 uppercase">Total Akhir :</span>
                                <span class="text-lg font-extrabold text-slate-900 tabular-nums">
                                    Rp {{ number_format($activeTransaction->total_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Tabel Riwayat Transaksi Cucian Sebelumnya --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold tracking-wider text-slate-900 uppercase">
                                Riwayat Transaksi Cucian Sebelumnya
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-brand-700">
                                    {{ $previousTransactions->count() }} Transaksi
                                </span>
                            </div>
                        </div>

                        <div class="mt-3 hidden overflow-x-auto md:block">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-xs font-bold text-slate-500 uppercase">
                                        <th scope="col" class="pb-2.5 font-semibold">No. Nota</th>
                                        <th scope="col" class="pb-2.5 font-semibold">Tgl Masuk</th>
                                        <th scope="col" class="pb-2.5 font-semibold">Kategori Layanan</th>
                                        <th scope="col" class="pb-2.5 font-semibold">Durasi</th>
                                        <th scope="col" class="pb-2.5 font-semibold">Total Biaya</th>
                                        <th scope="col" class="pb-2.5 text-right font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse ($previousTransactions as $prevTrx)
                                        @php
                                            $detail = $prevTrx->details->first();
                                            $prevService = $detail?->service;
                                            $isRowSelected = $activeTransaction && $activeTransaction->id === $prevTrx->id;
                                        @endphp
                                        <tr
                                            onclick="window.location.href='{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                                                'customer_id' => $selectedCustomer->id,
                                                'transaction_id' => $prevTrx->id,
                                                'q' => $search,
                                                'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                                'page' => request('page'),
                                            ])) }}'"
                                            @class([
                                                'group cursor-pointer transition',
                                                'bg-brand-50/70 font-semibold ring-1 ring-inset ring-brand-500/20' => $isRowSelected,
                                                'hover:bg-slate-50/80' => ! $isRowSelected,
                                            ])
                                            title="Klik untuk melihat detail nota {{ $prevTrx->transaction_code }}"
                                        >
                                            <td class="py-2.5 font-bold text-slate-900 group-hover:text-brand-700 tabular-nums">
                                                <div class="flex items-center gap-1.5">
                                                    @if ($isRowSelected)
                                                        <span class="size-1.5 rounded-full bg-brand-600"></span>
                                                    @endif
                                                    <span>{{ $prevTrx->transaction_code }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-slate-500 tabular-nums">
                                                {{ $prevTrx->created_at->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="py-2.5">
                                                {{ $prevService?->name ?? 'Cuci Komplit' }}
                                            </td>
                                            <td class="py-2.5">
                                                <span class="rounded bg-sky-50 px-1.5 py-0.5 text-xs font-semibold tabular-nums text-sky-700">
                                                    {{ $prevService?->estimated_duration ?? 3 }} Hari
                                                </span>
                                            </td>
                                            <td class="py-2.5 font-semibold text-slate-900 tabular-nums">
                                                Rp {{ number_format($prevTrx->total_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 text-right">
                                                <x-ui.status-badge :status="$prevTrx->status" size="sm" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-5 text-center text-xs text-slate-500">
                                                Belum ada riwayat transaksi lampau untuk pelanggan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Kartu riwayat (mobile) --}}
                        <div class="mt-3 space-y-2 md:hidden">
                            @forelse ($previousTransactions as $prevTrx)
                                @php
                                    $detail = $prevTrx->details->first();
                                    $prevService = $detail?->service;
                                    $isRowSelected = $activeTransaction && $activeTransaction->id === $prevTrx->id;
                                    $transactionUrl = route("{$currentRoutePrefix}.customers.index", array_filter([
                                        'customer_id' => $selectedCustomer->id,
                                        'transaction_id' => $prevTrx->id,
                                        'q' => $search,
                                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                        'page' => request('page'),
                                    ]));
                                @endphp

                                <a
                                    href="{{ $transactionUrl }}"
                                    @class([
                                        'block rounded-xl border border-slate-200 p-3.5 transition-colors',
                                        'border-brand-200 bg-brand-50/60' => $isRowSelected,
                                        'hover:bg-slate-50' => ! $isRowSelected,
                                    ])
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 tabular-nums">{{ $prevTrx->transaction_code }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                <span class="tabular-nums">{{ $prevTrx->created_at->translatedFormat('d M Y') }}</span> &middot;
                                                {{ $prevService?->name ?? 'Cuci Komplit' }}
                                            </p>
                                        </div>

                                        <x-ui.status-badge :status="$prevTrx->status" size="sm" />
                                    </div>

                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                        <span class="text-xs text-slate-500 tabular-nums">{{ $prevService?->estimated_duration ?? 3 }} hari</span>
                                        <span class="text-sm font-semibold text-slate-900 tabular-nums">
                                            <x-ui.money :value="$prevTrx->total_amount" />
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p class="rounded-xl border border-slate-200 px-4 py-6 text-center text-xs text-slate-500">
                                    Belum ada riwayat transaksi lampau untuk pelanggan ini.
                                </p>
                            @endforelse
                        </div>

                        <div class="mt-3 border-t border-slate-100 pt-2.5 text-xs text-slate-500">
                            Total riwayat tersimpan: <span class="tabular-nums font-semibold">{{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }}</span> transaksi. Klik baris untuk melihat detail nota di atas.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Tambah Pelanggan Baru --}}
        {{-- ============================================================
            MODAL 1: TAMBAH PELANGGAN BARU
        ============================================================= --}}
        <x-ui.modal
            show="createModalOpen"
            title="Tambah Pelanggan Baru"
            description="Daftarkan kontak pelanggan baru ke dalam sistem laundry."
            maxWidth="md"
        >
            <form method="POST" action="{{ route("{$currentRoutePrefix}.customers.store") }}" class="space-y-4">
                @csrf
                <div>
                    <x-ui.label for="create_name" required>Nama Lengkap</x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.input
                            id="create_name"
                            type="text"
                            name="name"
                            required
                            placeholder="Contoh: Nama Lengkap Pelanggan"
                        />
                    </div>
                </div>

                <div>
                    <x-ui.label for="create_phone" required>Nomor Telepon / WhatsApp</x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.input
                            id="create_phone"
                            type="text"
                            name="phone"
                            required
                            placeholder="Contoh: 081234567890"
                            @blur="$event.target.value = formatPhone($event.target.value)"
                            class="tabular-nums"
                        />
                    </div>
                    <p class="mt-1 text-2xs text-slate-500">
                        Otomatis diformat ke standar lokal <code class="font-semibold text-slate-700">08...</code> (misal <code class="text-slate-600">+628...</code> otomatis disesuaikan).
                    </p>
                </div>

                <div>
                    <x-ui.label for="create_address" optional>Alamat Lengkap</x-ui.label>
                    <textarea
                        id="create_address"
                        name="address"
                        rows="3"
                        placeholder="Contoh: Jl. Merpati No. 12, RT 02/04"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 sm:text-sm"
                    ></textarea>
                </div>

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
                        <span>Simpan Pelanggan</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>

        {{-- ============================================================
            MODAL 2: EDIT DATA PELANGGAN
        ============================================================= --}}
        <x-ui.modal
            show="editModalOpen"
            title="Edit Data Pelanggan"
            description="Perbarui informasi profil dan nomor kontak pelanggan."
            maxWidth="md"
        >
            <form method="POST" :action="'/' + '{{ $currentRoutePrefix }}' + '/customers/' + editForm.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <x-ui.label for="edit_name" required>Nama Lengkap</x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.input
                            id="edit_name"
                            type="text"
                            name="name"
                            x-model="editForm.name"
                            required
                        />
                    </div>
                </div>

                <div>
                    <x-ui.label for="edit_phone" required>Nomor Telepon / WhatsApp</x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.input
                            id="edit_phone"
                            type="text"
                            name="phone"
                            x-model="editForm.phone"
                            required
                            @blur="editForm.phone = formatPhone(editForm.phone)"
                            class="tabular-nums"
                        />
                    </div>
                    <p class="mt-1 text-2xs text-slate-500">
                        Otomatis diformat ke standar lokal <code class="font-semibold text-slate-700">08...</code> (misal <code class="text-slate-600">+628...</code> otomatis disesuaikan).
                    </p>
                </div>

                <div>
                    <x-ui.label for="edit_address" optional>Alamat Lengkap</x-ui.label>
                    <textarea
                        id="edit_address"
                        name="address"
                        x-model="editForm.address"
                        rows="3"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-900 shadow-2xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/15 sm:text-sm"
                    ></textarea>
                </div>

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
            MODAL 3: KONFIRMASI HAPUS PELANGGAN
        ============================================================= --}}
        <x-ui.modal
            show="deleteModalOpen"
            maxWidth="sm"
            :title="null"
        >
            <div>
                <div class="flex size-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                    <x-lucide-trash-2 class="size-6" />
                </div>
                <h3 class="mt-4 text-base font-bold text-slate-900">Hapus Data Pelanggan?</h3>
                <p class="mt-2 text-xs leading-relaxed text-slate-600">
                    Apakah Anda yakin ingin menghapus pelanggan <span class="font-semibold text-slate-900" x-text="'&ldquo;' + deleteForm.name + '&rdquo;'"></span>? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
                </p>
                <form method="POST" :action="'/' + '{{ $currentRoutePrefix }}' + '/customers/' + deleteForm.id" class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
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
                        <span>Ya, Hapus Pelanggan</span>
                    </x-ui.button>
                </form>
            </div>
        </x-ui.modal>
    </div>
</x-layouts::app>
