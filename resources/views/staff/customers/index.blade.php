@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
@endphp

<x-layouts::app title="Data Pelanggan">
    <div
        class="space-y-6"
        x-data="customerManager({{ Js::from($customersPayloadMap) }}, @js($autoOpenCustomerId))"
    >
        {{-- Sub-deskripsi & Aksi Utama Halaman --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-500 sm:text-sm">
                    Kelola direktori kontak pelanggan, riwayat transaksi cucian, dan buat pesanan POS secara instan.
                </p>
            </div>

            <x-ui.button
                type="button"
                @click="openCreate()"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-98"
            >
                <x-lucide-user-plus class="size-4" />
                <span>+ Tambah Pelanggan</span>
            </x-ui.button>
        </div>

        {{-- Pesan Status & Error Alert --}}
        @if (session('status'))
            <x-ui.alert variant="success" dismissible>
                {{ session('status') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive" title="Terjadi kendala saat memproses data pelanggan:" dismissible>
                <ul class="list-inside list-disc space-y-0.5 pl-1 font-normal text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- Card Metrik Ringkasan Pelanggan --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs transition hover:border-slate-300">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500">Total Pelanggan</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 shadow-2xs">
                        <x-lucide-users class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-black font-mono tabular-nums tracking-tight text-slate-900">{{ $totalCustomersCount }}</p>
                <p class="mt-0.5 text-2xs text-slate-400 font-medium">Akun terdaftar</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-2xs transition hover:border-amber-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500">Sedang Proses</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700 shadow-2xs">
                        <x-lucide-clock class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-black font-mono tabular-nums tracking-tight text-amber-600">{{ $counts['processing'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400 font-medium">Sedang dicuci / disetrika</p>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-white p-4 shadow-2xs transition hover:border-sky-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500">Siap Diambil</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-700 shadow-2xs">
                        <x-lucide-package-check class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-black font-mono tabular-nums tracking-tight text-sky-600">{{ $counts['ready'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400 font-medium">Selesai di rak simpan</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-2xs transition hover:border-emerald-200">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-slate-500">Selesai / Diambil</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 shadow-2xs">
                        <x-lucide-check-circle-2 class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-black font-mono tabular-nums tracking-tight text-emerald-600">{{ $counts['completed'] }}</p>
                <p class="mt-0.5 text-2xs text-slate-400 font-medium">Sudah diserahkan</p>
            </div>
        </div>

        {{-- TABEL UTAMA PELANGGAN (KONSISTEN SELALU FULL-WIDTH) --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
            {{-- Header Tabel & Kontrol Pencarian / Filter --}}
            <div class="border-b border-slate-100 p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Pelanggan Laundry</h2>
                        <p class="text-xs text-slate-500">Klik baris pelanggan untuk membuka riwayat nota dan kelola data di panel samping.</p>
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
                                placeholder="Cari nama, telepon, alamat..."
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

                {{-- Tab Filter Status (Pines UI Segmented Control) --}}
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
                            <th class="w-3/12 px-5 py-3.5">Pelanggan</th>
                            <th class="w-2/12 px-5 py-3.5">Kontak / WhatsApp</th>
                            <th class="w-3/12 px-5 py-3.5">Alamat Domisili</th>
                            <th class="w-2/12 px-5 py-3.5">Total Nota</th>
                            <th class="w-2/12 px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($customers as $customer)
                            @php
                                $cData = $customersPayloadMap[$customer->id] ?? null;
                            @endphp
                            <tr
                                @click="openDetail({{ $customer->id }})"
                                class="transition hover:bg-slate-50/70 cursor-pointer group"
                            >
                                {{-- Pelanggan --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 border border-brand-200/60 text-xs font-mono font-bold text-brand-800 shadow-2xs">
                                            {{ $cData['initials'] ?? 'CU' }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 group-hover:text-brand-700 transition">{{ $customer->name }}</span>
                                            <p class="text-xs text-slate-500">
                                                Terdaftar {{ $cData['registered_at'] ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kontak --}}
                                <td class="px-5 py-3.5" @click.stop>
                                    @if ($customer->phone)
                                        <a
                                            href="{{ $customer->whatsapp_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 font-bold font-mono tabular-nums text-slate-800 hover:text-emerald-700 transition group/wa"
                                            title="Buka obrolan WhatsApp ke {{ $customer->name }}"
                                        >
                                            <x-lucide-message-circle class="size-3.5 text-emerald-600 transition group-hover/wa:scale-110" />
                                            <span class="group-hover/wa:underline underline-offset-2">{{ $customer->phone }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-mono">-</span>
                                    @endif
                                </td>

                                {{-- Alamat --}}
                                <td class="px-5 py-3.5">
                                    <span class="line-clamp-1 max-w-[240px] text-slate-600" title="{{ $customer->address ?: '-' }}">
                                        {{ $customer->address ?: '-' }}
                                    </span>
                                </td>

                                {{-- Total Nota & Status --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        @if ($customer->transactions_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-sky-50 border border-sky-200/80 px-2 py-0.5 text-2xs font-bold font-mono tabular-nums text-sky-800 shadow-2xs">
                                                {{ $customer->transactions_count }} Nota
                                            </span>
                                            @if ($customer->transactions_count >= 10)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 border border-amber-200/90 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 shadow-2xs" title="Pelanggan loyal (>10 transaksi)">
                                                    <x-lucide-crown class="size-3 text-amber-600 shrink-0" />
                                                    <span>Loyal</span>
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-slate-50 border border-slate-200/60 px-2 py-0.5 text-2xs font-medium text-slate-400">
                                                Belum Ada Nota
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Aksi Tombol (Segmented Button Group Pines UI - Sejajar Presisi) --}}
                                <td class="px-5 py-3.5 text-right" @click.stop>
                                    <div class="inline-flex items-center rounded-xl border border-slate-200/90 bg-slate-100/60 p-0.5 shadow-2xs gap-0.5">
                                        {{-- Tombol Detail: Aqua Cyan / Brand --}}
                                        <button
                                            type="button"
                                            @click="openDetail({{ $customer->id }})"
                                            class="group/act inline-flex items-center justify-center gap-1 rounded-lg border border-brand-200/80 bg-brand-50/90 px-2 py-1 text-2xs font-bold text-brand-800 shadow-2xs transition-all duration-150 hover:bg-brand-600 hover:text-white hover:border-brand-600 active:scale-95 cursor-pointer"
                                            title="Buka panel profil dan riwayat cucian"
                                        >
                                            <x-lucide-eye class="size-3.5 text-brand-700 transition-colors duration-150 group-hover/act:text-white" />
                                            <span>Detail</span>
                                        </button>

                                        {{-- Tombol Edit: Warm Amber --}}
                                        <button
                                            type="button"
                                            @click="openEdit({{ $customer->id }})"
                                            class="group/act inline-flex items-center justify-center gap-1 rounded-lg border border-amber-200/80 bg-amber-50/90 px-2 py-1 text-2xs font-bold text-amber-900 shadow-2xs transition-all duration-150 hover:bg-amber-500 hover:text-white hover:border-amber-500 active:scale-95 cursor-pointer"
                                            title="Edit data kontak dan alamat domisili"
                                        >
                                            <x-lucide-pencil class="size-3.5 text-amber-700 transition-colors duration-150 group-hover/act:text-white" />
                                            <span>Edit</span>
                                        </button>

                                        {{-- Tombol Hapus (Jika 0 Transaksi) atau Indikator Terkunci (Fixed Width: w-[76px]) --}}
                                        @if ($customer->transactions_count === 0)
                                            <button
                                                type="button"
                                                @click="openDelete({{ json_encode($cData) }})"
                                                class="group/act inline-flex w-[76px] items-center justify-center gap-1 rounded-lg border border-rose-200/80 bg-rose-50/90 px-2 py-1 text-2xs font-bold text-rose-900 shadow-2xs transition-all duration-150 hover:bg-rose-600 hover:text-white hover:border-rose-600 active:scale-95 cursor-pointer"
                                                title="Hapus pelanggan (hanya diperbolehkan untuk yang belum memiliki riwayat transaksi)"
                                            >
                                                <x-lucide-trash-2 class="size-3.5 text-rose-700 transition-colors duration-150 group-hover/act:text-white" />
                                                <span>Hapus</span>
                                            </button>
                                        @else
                                            <span
                                                class="inline-flex w-[76px] items-center justify-center gap-1 rounded-lg border border-slate-200/80 bg-slate-100/90 px-2 py-1 text-2xs font-semibold text-slate-500 select-none cursor-not-allowed"
                                                title="Terkunci: Pelanggan memiliki riwayat transaksi aktif/selesai"
                                            >
                                                <x-lucide-lock class="size-3.5 text-slate-400" />
                                                <span>Terkunci</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 shadow-2xs">
                                        <x-lucide-users class="size-6" />
                                    </div>
                                    <p class="mt-3 text-sm font-bold text-slate-800">Tidak ada pelanggan ditemukan</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        @if ($search !== '')
                                            Tidak ada hasil untuk kata kunci "{{ $search }}". Coba periksa ejaan nama atau nomor telepon.
                                        @else
                                            Belum ada pelanggan terdaftar dalam filter ini.
                                        @endif
                                    </p>
                                    <button
                                        type="button"
                                        @click="openCreate()"
                                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-700 transition cursor-pointer"
                                    >
                                        <x-lucide-user-plus class="size-4" />
                                        <span>Daftarkan Pelanggan Baru</span>
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card List View (Layar Kecil) --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($customers as $customer)
                    @php
                        $cData = $customersPayloadMap[$customer->id] ?? null;
                    @endphp
                    <div
                        @click="openDetail({{ $customer->id }})"
                        class="p-4 transition space-y-3 cursor-pointer hover:bg-slate-50/70"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 border border-brand-200/60 font-mono font-bold text-brand-800 text-sm shadow-2xs">
                                    {{ $cData['initials'] ?? 'CU' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-sm text-slate-900 truncate">{{ $customer->name }}</p>
                                    <p class="text-xs text-slate-500 font-mono tabular-nums">{{ $customer->phone ?: 'Tanpa nomor' }}</p>
                                </div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-md bg-slate-100 px-2 py-0.5 text-2xs font-bold font-mono text-slate-700">
                                {{ $customer->transactions_count }} Nota
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 line-clamp-1">
                            <x-lucide-map-pin class="inline size-3.5 text-slate-400 mr-1" />
                            {{ $customer->address ?: 'Alamat belum dicatat' }}
                        </p>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-2.5" @click.stop>
                            <span class="text-2xs text-slate-400 font-medium">Terdaftar {{ $cData['registered_at'] ?? '-' }}</span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openEdit({{ $customer->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-amber-200/80 bg-amber-50/80 px-2.5 py-1 text-2xs font-bold text-amber-800 shadow-2xs hover:bg-amber-500 hover:text-white transition cursor-pointer active:scale-95"
                                >
                                    <x-lucide-pencil class="size-3 text-amber-600" />
                                    <span>Edit</span>
                                </button>
                                <button
                                    type="button"
                                    @click="openDetail({{ $customer->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-brand-600 px-3 py-1 text-2xs font-bold text-white shadow-2xs hover:bg-brand-700 transition cursor-pointer active:scale-95"
                                >
                                    <x-lucide-eye class="size-3 text-brand-200" />
                                    <span>Buka Panel &rarr;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        <p class="text-sm font-semibold">Tidak ada pelanggan ditemukan.</p>
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

        {{-- ========================================================================= --}}
        {{-- UNIFIED SLIDE-OVER SHEET DRAWER (PINES UI COMPONENT)                      --}}
        {{-- Mode: 'detail' (Profil & Riwayat) | 'edit' (Inline Edit) | 'create' (Baru)--}}
        {{-- ========================================================================= --}}
        <x-ui.sheet
            show="drawerOpen"
            maxWidth="xl"
            @close="closeDrawer()"
        >
            {{-- Custom Docked Header --}}
            <x-slot:header>
                <div class="flex items-center justify-between gap-3 w-full pr-2">
                    {{-- Judul Mode Detail --}}
                    <div x-show="drawerMode === 'detail'" class="flex items-center gap-3 min-w-0">
                        <div
                            class="size-9 rounded-xl bg-brand-50 border border-brand-200/60 text-brand-700 font-bold text-xs flex items-center justify-center font-mono shrink-0 shadow-2xs"
                            x-text="selectedCustomer?.initials || 'CU'"
                        ></div>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 truncate" x-text="selectedCustomer?.name || 'Profil Pelanggan'"></h2>
                            <p class="text-2xs text-slate-500 font-mono tabular-nums" x-text="selectedCustomer?.phone || '-'"></p>
                        </div>
                    </div>

                    {{-- Judul Mode Edit --}}
                    <div x-show="drawerMode === 'edit'" class="flex items-center gap-2.5 min-w-0">
                        <div class="size-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                            <x-lucide-pencil class="size-4" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Edit Data Pelanggan</h2>
                            <p class="text-2xs text-slate-500">Perbarui kontak dan alamat domisili</p>
                        </div>
                    </div>

                    {{-- Judul Mode Tambah Baru --}}
                    <div x-show="drawerMode === 'create'" class="flex items-center gap-2.5 min-w-0">
                        <div class="size-8 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                            <x-lucide-user-plus class="size-4" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Tambah Pelanggan Baru</h2>
                            <p class="text-2xs text-slate-500">Daftarkan pelanggan ke direktori kasir</p>
                        </div>
                    </div>

                    {{-- Badge Status Order Terkini (Hanya tampil di Mode Detail) --}}
                    <div x-show="drawerMode === 'detail'" class="shrink-0">
                        <template x-if="selectedCustomer?.active_status === 'Diproses' || selectedCustomer?.active_status === 'Diterima'">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-2xs font-bold text-amber-800">
                                <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Sedang Dicuci
                            </span>
                        </template>
                        <template x-if="selectedCustomer?.active_status === 'Selesai'">
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 border border-sky-200 px-2 py-0.5 text-2xs font-bold text-sky-800">
                                <span class="size-1.5 rounded-full bg-sky-500"></span>
                                Siap Diambil
                            </span>
                        </template>
                        <template x-if="!selectedCustomer?.active_status">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-2xs font-medium text-slate-500">
                                Tidak Ada Cucian Aktif
                            </span>
                        </template>
                    </div>
                </div>
            </x-slot:header>

            {{-- ============================================================= --}}
            {{-- ISI BODY 1: MODE DETAIL & RIWAYAT NOTA                         --}}
            {{-- ============================================================= --}}
            <div x-show="drawerMode === 'detail'" class="space-y-5">
                {{-- Hero Quick Actions Bar --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <a
                        :href="selectedCustomer?.create_order_url"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-brand-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-brand-800 transition active:scale-98 cursor-pointer"
                        title="Buka Kasir POS untuk membuat nota cucian baru bagi pelanggan ini"
                    >
                        <x-lucide-plus-circle class="size-4 text-brand-200" />
                        <span>+ Buka Kasir POS</span>
                    </a>

                    <template x-if="selectedCustomer?.whatsapp_url">
                        <a
                            :href="selectedCustomer?.whatsapp_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition active:scale-98 cursor-pointer shadow-2xs shrink-0"
                            title="Buka obrolan WhatsApp"
                        >
                            <x-lucide-message-circle class="size-4 text-emerald-600" />
                            <span>Chat WhatsApp</span>
                        </a>
                    </template>
                </div>

                {{-- Kartu Ringkasan Informasi Profil Pelanggan --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Kontak</span>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="openEdit(selectedCustomerId)"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-2xs font-bold text-slate-700 shadow-2xs hover:border-amber-500 hover:bg-amber-50 hover:text-amber-800 transition cursor-pointer active:scale-95"
                            >
                                <x-lucide-pencil class="size-3 text-slate-400" />
                                <span>Edit Profil</span>
                            </button>
                            <template x-if="!selectedCustomer?.has_transactions">
                                <button
                                    type="button"
                                    @click="openDelete(selectedCustomer)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50/70 px-2 py-1 text-2xs font-bold text-rose-700 shadow-2xs hover:bg-rose-100 transition cursor-pointer"
                                    title="Hapus data pelanggan"
                                >
                                    <x-lucide-trash-2 class="size-3" />
                                    <span>Hapus</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-xs">
                        <div>
                            <span class="text-slate-400 text-2xs uppercase tracking-wider font-semibold">Nomor WhatsApp</span>
                            <p class="mt-0.5 font-bold font-mono tabular-nums text-slate-800 text-sm" x-text="selectedCustomer?.phone || '-'"></p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-2xs uppercase tracking-wider font-semibold">Terdaftar Sejak</span>
                            <p class="mt-0.5 font-semibold text-slate-800" x-text="selectedCustomer?.registered_at || '-'"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 text-2xs uppercase tracking-wider font-semibold">Alamat Domisili</span>
                            <p class="mt-0.5 font-medium text-slate-700" x-text="selectedCustomer?.address || 'Alamat domisili belum dicatat'"></p>
                        </div>
                    </div>

                    {{-- Metric Pengeluaran & Total Nota --}}
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        <div class="rounded-xl bg-slate-50 p-2.5">
                            <span class="text-2xs text-slate-500 font-medium">Total Pesanan</span>
                            <p class="mt-0.5 font-black text-slate-900 font-mono tabular-nums text-sm">
                                <span x-text="selectedCustomer?.total_transactions || 0"></span> Nota
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-2.5">
                            <span class="text-2xs text-slate-500 font-medium">Total Belanja (Akumulasi)</span>
                            <p class="mt-0.5 font-black text-brand-700 font-mono tabular-nums text-sm" x-text="selectedCustomer?.total_spent_formatted || 'Rp 0'"></p>
                        </div>
                    </div>
                </div>

                {{-- Daftar Riwayat Transaksi Nota Cucian --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <x-lucide-receipt class="size-4 text-brand-600" />
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Riwayat Nota Cucian Terakhir</h3>
                        </div>
                        <span class="text-2xs text-slate-400 font-medium" x-text="(selectedCustomer?.transactions?.length || 0) + ' Transaksi'"></span>
                    </div>

                    {{-- List Item Transaksi --}}
                    <div class="space-y-2">
                        <template x-for="trx in selectedCustomer?.transactions" :key="trx.id">
                            <div class="rounded-xl border border-slate-200/90 bg-white p-3 shadow-2xs hover:border-slate-300 transition">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-slate-900" x-text="trx.code"></span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset"
                                            :class="{
                                                'bg-sky-50 text-sky-800 ring-sky-300': trx.status === 'Selesai',
                                                'bg-amber-50 text-amber-800 ring-amber-300': trx.status === 'Diproses',
                                                'bg-slate-50 text-slate-700 ring-slate-300': trx.status === 'Diterima',
                                                'bg-emerald-50 text-emerald-800 ring-emerald-300': trx.status === 'Diambil',
                                                'bg-rose-50 text-rose-800 ring-rose-300': trx.status === 'Dibatalkan'
                                            }"
                                            x-text="trx.status"
                                        ></span>
                                    </div>
                                    <span class="font-mono font-extrabold text-xs text-slate-900 tabular-nums" x-text="trx.total_formatted"></span>
                                </div>

                                <div class="mt-1 flex items-center justify-between text-2xs text-slate-500">
                                    <span class="truncate max-w-[200px]" x-text="trx.services_summary"></span>
                                    <span class="tabular-nums shrink-0" x-text="trx.date"></span>
                                </div>

                                <div class="mt-2.5 flex items-center justify-between border-t border-slate-100 pt-2">
                                    <span
                                        class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                                        :class="trx.payment_status === 'Lunas' ? 'bg-emerald-50 text-emerald-700' : (trx.payment_status === 'DP' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')"
                                        x-text="trx.payment_status"
                                    ></span>

                                    <a
                                        :href="trx.show_url"
                                        class="inline-flex items-center gap-1 text-2xs font-bold text-brand-700 hover:text-brand-800 hover:underline transition"
                                    >
                                        <span>Buka Detail Nota</span>
                                        <x-lucide-arrow-right class="size-3" />
                                    </a>
                                </div>
                            </div>
                        </template>

                        {{-- Empty State Transaksi --}}
                        <template x-if="!selectedCustomer?.transactions || selectedCustomer?.transactions?.length === 0">
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-slate-500 space-y-2">
                                <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-white border border-slate-200/90 text-slate-400 shadow-2xs">
                                    <x-lucide-inbox class="size-5" />
                                </div>
                                <p class="text-xs font-bold text-slate-800">Belum Ada Riwayat Cucian</p>
                                <p class="text-2xs text-slate-500 max-w-xs mx-auto">
                                    Pelanggan ini belum pernah melakukan pemesanan. Klik tombol "+ Buka Kasir POS" di atas untuk membuat nota pertama.
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- ISI BODY 2: MODE EDIT DATA PELANGGAN (INLINE DI DRAWER)       --}}
            {{-- ============================================================= --}}
            <div x-show="drawerMode === 'edit'" class="space-y-4">
                <form
                    id="form-edit-customer"
                    method="POST"
                    :action="selectedCustomer?.update_url"
                    target="_self"
                    class="space-y-4"
                    @submit="isSubmitting = true"
                >
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_customer_name" class="block text-xs font-bold text-slate-700">
                            Nama Pelanggan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-lucide-user class="size-4" />
                            </div>
                            <input
                                type="text"
                                id="edit_customer_name"
                                name="name"
                                required
                                maxlength="100"
                                x-model="editForm.name"
                                placeholder="Masukkan nama lengkap pelanggan"
                                class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm font-semibold"
                            />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="edit_customer_phone" class="block text-xs font-bold text-slate-700">
                                Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Format: 08xxx</span>
                        </div>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-lucide-phone class="size-4" />
                            </div>
                            <input
                                type="tel"
                                id="edit_customer_phone"
                                name="phone"
                                required
                                maxlength="20"
                                x-model="editForm.phone"
                                @input="editForm.phone = formatPhone($event.target.value)"
                                placeholder="081234567890"
                                class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs font-mono font-bold tabular-nums text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                        </div>
                        <p class="mt-1 text-2xs text-slate-400">Nomor ini otomatis digunakan untuk link WhatsApp struk dan notifikasi cucian.</p>
                    </div>

                    <div>
                        <label for="edit_customer_address" class="block text-xs font-bold text-slate-700">
                            Alamat Domisili <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <div class="relative mt-1.5">
                            <textarea
                                id="edit_customer_address"
                                name="address"
                                rows="3"
                                maxlength="500"
                                x-model="editForm.address"
                                placeholder="Contoh: Jl. Merpati No. 12, Nganjuk"
                                class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 resize-none sm:text-sm"
                            ></textarea>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ============================================================= --}}
            {{-- ISI BODY 3: MODE TAMBAH PELANGGAN BARU (DI DALAM DRAWER)       --}}
            {{-- ============================================================= --}}
            <div x-show="drawerMode === 'create'" class="space-y-4">
                <form
                    id="form-create-customer"
                    method="POST"
                    action="{{ route("{$currentRoutePrefix}.customers.store") }}"
                    target="_self"
                    class="space-y-4"
                    @submit="isSubmitting = true"
                >
                    @csrf

                    <div>
                        <label for="create_customer_name" class="block text-xs font-bold text-slate-700">
                            Nama Pelanggan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-lucide-user class="size-4" />
                            </div>
                            <input
                                type="text"
                                id="create_customer_name"
                                name="name"
                                required
                                maxlength="100"
                                x-model="createForm.name"
                                placeholder="Masukkan nama lengkap pelanggan"
                                class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm font-semibold"
                            />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="create_customer_phone" class="block text-xs font-bold text-slate-700">
                                Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Format: 08xxx</span>
                        </div>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-lucide-phone class="size-4" />
                            </div>
                            <input
                                type="tel"
                                id="create_customer_phone"
                                name="phone"
                                required
                                maxlength="20"
                                x-model="createForm.phone"
                                @input="createForm.phone = formatPhone($event.target.value)"
                                placeholder="081234567890"
                                class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs font-mono font-bold tabular-nums text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                        </div>
                        <p class="mt-1 text-2xs text-slate-400">Format otomatis 08xxx untuk pembuatan link WhatsApp nota.</p>
                    </div>

                    <div>
                        <label for="create_customer_address" class="block text-xs font-bold text-slate-700">
                            Alamat Domisili <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <div class="relative mt-1.5">
                            <textarea
                                id="create_customer_address"
                                name="address"
                                rows="3"
                                maxlength="500"
                                x-model="createForm.address"
                                placeholder="Contoh: Jl. Merpati No. 12, Nganjuk"
                                class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 resize-none sm:text-sm"
                            ></textarea>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Custom Docked Sticky Footer --}}
            <x-slot:footer>
                {{-- Footer Mode Detail --}}
                <div x-show="drawerMode === 'detail'" class="flex items-center justify-between w-full">
                    <span class="text-2xs text-slate-400 font-mono">Tekan Esc untuk tutup</span>
                    <button
                        type="button"
                        @click="closeDrawer()"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer active:scale-98"
                    >
                        Tutup Panel
                    </button>
                </div>

                {{-- Footer Mode Edit --}}
                <div x-show="drawerMode === 'edit'" class="flex items-center justify-end gap-2 w-full">
                    <button
                        type="button"
                        @click="drawerMode = 'detail'"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer active:scale-98"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="const f = document.getElementById('form-edit-customer'); if (f?.reportValidity()) { isSubmitting = true; f.submit(); }"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-brand-800 transition active:scale-98 cursor-pointer disabled:opacity-50"
                    >
                        <x-lucide-check class="size-3.5" />
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'">Simpan Perubahan</span>
                        <kbd class="hidden sm:inline-block rounded-md bg-brand-800/90 px-1.5 py-0.5 text-[10px] font-mono font-medium text-brand-100 border border-brand-600/50 ml-0.5">Ctrl+Enter</kbd>
                    </button>
                </div>

                {{-- Footer Mode Tambah Baru --}}
                <div x-show="drawerMode === 'create'" class="flex items-center justify-end gap-2 w-full">
                    <button
                        type="button"
                        @click="closeDrawer()"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer active:scale-98"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="const f = document.getElementById('form-create-customer'); if (f?.reportValidity()) { isSubmitting = true; f.submit(); }"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-brand-800 transition active:scale-98 cursor-pointer disabled:opacity-50"
                    >
                        <x-lucide-check class="size-3.5" />
                        <span x-text="isSubmitting ? 'Mendaftarkan...' : 'Daftarkan Pelanggan'">Daftarkan Pelanggan</span>
                        <kbd class="hidden sm:inline-block rounded-md bg-brand-800/90 px-1.5 py-0.5 text-[10px] font-mono font-medium text-brand-100 border border-brand-600/50 ml-0.5">Ctrl+Enter</kbd>
                    </button>
                </div>
            </x-slot:footer>
        </x-ui.sheet>

        {{-- Dialog Modal Konfirmasi Hapus Pelanggan --}}
        <x-ui.modal
            show="deleteModalOpen"
            title="Konfirmasi Hapus Pelanggan"
            description="Tindakan ini permanen dan akan menghapus kontak pelanggan dari sistem."
            maxWidth="sm"
        >
            <div class="space-y-3">
                <p class="text-xs text-slate-600">
                    Apakah Anda yakin ingin menghapus pelanggan <span class="font-bold text-slate-900" x-text="deleteForm.name"></span>?
                </p>
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-2.5 text-2xs text-amber-800 flex items-start gap-2">
                    <x-lucide-alert-triangle class="size-4 shrink-0 text-amber-600 mt-0.5" />
                    <span>Penghapusan hanya diperbolehkan untuk pelanggan yang belum memiliki riwayat transaksi cucian apapun.</span>
                </div>

                <form method="POST" :action="deleteForm.actionUrl" class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        @click="deleteModalOpen = false"
                        class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-rose-700 transition cursor-pointer shadow-2xs"
                    >
                        <x-lucide-trash-2 class="size-3.5" />
                        <span>Ya, Hapus Pelanggan</span>
                    </button>
                </form>
            </div>
        </x-ui.modal>
    </div>

    <script>
        (function() {
            function registerCustomerManager() {
                if (typeof Alpine === 'undefined') return;

                Alpine.data('customerManager', (customersMap = {}, autoOpenId = null) => ({
                    drawerOpen: false,
                    drawerMode: 'detail',
                    selectedCustomerId: null,
                    customersMap: customersMap,
                    editForm: { id: '', name: '', phone: '', address: '' },
                    createForm: { name: '', phone: '', address: '' },
                    deleteModalOpen: false,
                    deleteForm: { id: '', name: '', actionUrl: '' },
                    isSubmitting: false,

                    get selectedCustomer() {
                        return this.selectedCustomerId ? (this.customersMap[this.selectedCustomerId] || null) : null;
                    },

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

                    openDetail(id) {
                        if (!this.customersMap[id]) return;
                        this.selectedCustomerId = id;
                        this.drawerMode = 'detail';
                        this.drawerOpen = true;
                    },

                    openEdit(id) {
                        const c = this.customersMap[id];
                        if (!c) return;
                        this.selectedCustomerId = id;
                        this.editForm = {
                            id: c.id,
                            name: c.name,
                            phone: c.phone,
                            address: c.address === '-' ? '' : c.address
                        };
                        this.drawerMode = 'edit';
                        this.drawerOpen = true;
                    },

                    openCreate() {
                        this.createForm = { name: '', phone: '', address: '' };
                        this.drawerMode = 'create';
                        this.drawerOpen = true;
                        setTimeout(() => {
                            const el = document.getElementById('create_customer_name');
                            if (el) el.focus();
                        }, 150);
                    },

                    openDelete(customer) {
                        this.deleteForm = {
                            id: customer.id,
                            name: customer.name,
                            actionUrl: customer.delete_url
                        };
                        this.deleteModalOpen = true;
                    },

                    closeDrawer() {
                        if (this.isSubmitting) return;
                        this.drawerOpen = false;
                        setTimeout(() => {
                            this.drawerMode = 'detail';
                        }, 250);
                    },

                    init() {
                        if (autoOpenId && this.customersMap[autoOpenId]) {
                            this.openDetail(autoOpenId);
                        }

                        window.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape' && this.drawerOpen) {
                                this.closeDrawer();
                                return;
                            }

                            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && this.drawerOpen) {
                                e.preventDefault();
                                e.stopPropagation();

                                if (this.drawerMode === 'edit') {
                                    const form = document.getElementById('form-edit-customer');
                                    if (form && form.reportValidity()) {
                                        this.isSubmitting = true;
                                        form.submit();
                                    }
                                } else if (this.drawerMode === 'create') {
                                    const form = document.getElementById('form-create-customer');
                                    if (form && form.reportValidity()) {
                                        this.isSubmitting = true;
                                        form.submit();
                                    }
                                }
                            }
                        });
                    }
                }));
            }

            if (typeof Alpine === 'undefined') {
                document.addEventListener('alpine:init', registerCustomerManager);
            } else {
                registerCustomerManager();
            }
        })();
    </script>
</x-layouts::app>
