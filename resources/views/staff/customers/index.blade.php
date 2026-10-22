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

            <x-ui.button type="button" @click="createModalOpen = true" class="w-full sm:w-auto">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pelanggan
            </x-ui.button>
        </div>

        {{-- Pesan Status & Error Alert --}}
        @if (session('status'))
            <div class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <svg class="size-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Terjadi kesalahan saat memproses data pelanggan:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-0.5 pl-2 font-normal">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Card Metrik Ringkasan --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Total Pelanggan</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.87M16 2.13a4 4 0 010 7.75" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ $totalCustomersCount }}</p>
                <p class="mt-1 text-xs text-slate-500">Akun terdaftar</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Sedang Proses</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-amber-700">{{ $counts['processing'] }}</p>
                <p class="mt-1 text-xs text-slate-500">Sedang dikerjakan</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Siap Diambil</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2v-8a2 2 0 00-2-2M9 8V6a3 3 0 016 0v2" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-sky-700">{{ $counts['ready'] }}</p>
                <p class="mt-1 text-xs text-slate-500">Menunggu diambil</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500">Selesai / Diambil</span>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-emerald-700">{{ $counts['completed'] }}</p>
                <p class="mt-1 text-xs text-slate-500">Sudah diserahkan</p>
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
                                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari nama atau telepon..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-9 text-xs text-slate-900 transition placeholder:text-slate-500 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                >
                                @if ($search !== '')
                                    <a
                                        href="{{ route("{$currentRoutePrefix}.customers.index", ['status' => $statusFilter]) }}"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-600"
                                        title="Hapus pencarian"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Tab Status Pills --}}
                    <div class="no-scrollbar mt-4 flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs">
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'all'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'all',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'all',
                            ])
                        >
                            Semua ({{ $counts['all'] }})
                        </a>
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'processing'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'processing',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'processing',
                            ])
                        >
                            Sedang Proses ({{ $counts['processing'] }})
                        </a>
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'ready'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'ready',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'ready',
                            ])
                        >
                            Siap Diambil ({{ $counts['ready'] }})
                        </a>
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'completed'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'completed',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'completed',
                            ])
                        >
                            Selesai ({{ $counts['completed'] }})
                        </a>
                    </div>
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
                                                <svg class="size-3.5 text-emerald-600 transition group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.151.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.768-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.995-.368-1.78-.769-2.936-2.578-3.025-2.697-.089-.119-.724-.962-.724-1.835 0-.873.456-1.302.619-1.479.163-.177.355-.221.473-.221.118 0 .237.001.341.006.109.006.255-.041.398.303.148.355.503 1.226.547 1.315.044.089.074.192.015.31-.059.119-.089.192-.178.296-.089.103-.187.23-.266.31-.089.089-.182.185-.078.363.103.178.46 0.758.987 1.228.679.605 1.252.793 1.43.882.178.089.281.074.385-.044.104-.119.444-.518.563-.695.118-.178.237-.148.399-.089.163.059 1.035.488 1.213.577.177.089.296.133.34.207.045.074.045.429-.099.834z"/>
                                                </svg>
                                                <span class="group-hover:underline underline-offset-2">{{ $customer->phone }}</span>
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

                                    {{-- Total Nota --}}
                                    <td class="px-5 py-3.5">
                                        @if ($customer->transactions_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                                                {{ $customer->transactions_count }} Nota
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">
                                                Belum Ada Nota
                                            </span>
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
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
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
                                                <svg class="size-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
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
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                    </svg>
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
                                                    <svg class="size-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
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
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
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
                                        <p class="text-xs text-slate-500">{{ $customer->phone ?: '-' }}</p>
                                    </div>
                                </div>
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700">
                                    {{ $customer->transactions_count }} Nota
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
                                    Detail
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
                                    Edit
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
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">
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
                                    <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari nama atau telepon..."
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50/60 py-2 pr-8 pl-8 text-xs text-slate-900 transition placeholder:text-slate-500 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                >
                            </div>
                        </form>

                        {{-- Tab Filter Pills --}}
                        <div class="no-scrollbar mt-3 flex items-center gap-1 overflow-x-auto pb-0.5 text-xs">
                            <a
                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'all'])) }}"
                                @class([
                                    'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                    'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'all',
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'all',
                                ])
                            >
                                Semua ({{ $counts['all'] }})
                            </a>
                            <a
                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'processing'])) }}"
                                @class([
                                    'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                    'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'processing',
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'processing',
                                ])
                            >
                                Proses ({{ $counts['processing'] }})
                            </a>
                            <a
                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'ready'])) }}"
                                @class([
                                    'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                    'bg-brand-700 text-white shadow-2xs' => $statusFilter === 'ready',
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'ready',
                                ])
                            >
                                Siap ({{ $counts['ready'] }})
                            </a>
                        </div>
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
                                            <p class="truncate text-xs text-slate-500">{{ $customer->phone ?: '-' }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-xs font-bold text-slate-600">
                                        {{ $customer->transactions_count }} Nota
                                    </span>
                                </div>

                                {{-- Action Bar on Each Card in Master List --}}
                                <div class="flex items-center justify-end gap-1.5 pt-1 border-t border-slate-100/70">
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
                                                class="inline-flex items-center gap-1 text-emerald-700 font-semibold hover:text-emerald-800 transition group"
                                                title="Buka obrolan WhatsApp ke {{ $selectedCustomer->name }}"
                                            >
                                                <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.151.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.768-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.995-.368-1.78-.769-2.936-2.578-3.025-2.697-.089-.119-.724-.962-.724-1.835 0-.873.456-1.302.619-1.479.163-.177.355-.221.473-.221.118 0 .237.001.341.006.109.006.255-.041.398.303.148.355.503 1.226.547 1.315.044.089.074.192.015.31-.059.119-.089.192-.178.296-.089.103-.187.23-.266.31-.089.089-.182.185-.078.363.103.178.46 0.758.987 1.228.679.605 1.252.793 1.43.882.178.089.281.074.385-.044.104-.119.444-.518.563-.695.118-.178.237-.148.399-.089.163.059 1.035.488 1.213.577.177.089.296.133.34.207.045.074.045.429-.099.834z"/>
                                                </svg>
                                                <span class="group-hover:underline underline-offset-2">WhatsApp: {{ $selectedCustomer->phone }}</span>
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
                                    <svg class="size-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
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
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
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
                                        <svg class="size-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
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
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
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
                                <p class="mt-0.5 text-xs font-bold text-slate-800">
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
                                    <span class="text-sm font-bold text-slate-900 sm:text-base">{{ $activeTransaction->transaction_code }}</span>
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
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
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
                                            Berat: <strong class="text-slate-700">{{ $firstDetail?->weight ?? 1 }} kg</strong>
                                            @if ($firstDetail?->unit_price)
                                                × Rp {{ number_format($firstDetail->unit_price, 0, ',', '.') }}
                                            @endif
                                            @if ($service?->description)
                                                • {{ $service->description }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-sm font-extrabold text-slate-900">
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
                                        <svg class="size-3.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
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
                                <span class="text-lg font-extrabold text-slate-900">
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
                                            <td class="py-2.5 font-bold text-slate-900 group-hover:text-brand-700">
                                                <div class="flex items-center gap-1.5">
                                                    @if ($isRowSelected)
                                                        <span class="size-1.5 rounded-full bg-brand-600"></span>
                                                    @endif
                                                    <span>{{ $prevTrx->transaction_code }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-slate-500">
                                                {{ $prevTrx->created_at->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="py-2.5">
                                                {{ $prevService?->name ?? 'Cuci Komplit' }}
                                            </td>
                                            <td class="py-2.5">
                                                <span class="rounded bg-sky-50 px-1.5 py-0.5 text-xs font-semibold text-sky-700">
                                                    {{ $prevService?->estimated_duration ?? 3 }} Hari
                                                </span>
                                            </td>
                                            <td class="py-2.5 font-semibold text-slate-900">
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
                                            <p class="font-bold text-slate-900">{{ $prevTrx->transaction_code }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $prevTrx->created_at->translatedFormat('d M Y') }} &middot;
                                                {{ $prevService?->name ?? 'Cuci Komplit' }}
                                            </p>
                                        </div>

                                        <x-ui.status-badge :status="$prevTrx->status" size="sm" />
                                    </div>

                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                        <span class="text-xs text-slate-500">{{ $prevService?->estimated_duration ?? 3 }} hari</span>
                                        <span class="text-sm font-semibold text-slate-900">
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
                            Total riwayat tersimpan: {{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }} transaksi. Klik baris untuk melihat detail nota di atas.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Tambah Pelanggan Baru --}}
        <div
            x-cloak
            x-show="createModalOpen"
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 bg-slate-900/40 backdrop-blur-xs sm:items-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-customer-title"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="my-auto w-full max-w-md max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6"
                @click.outside="createModalOpen = false"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 id="create-customer-title" class="text-base font-bold text-slate-900">Tambah Pelanggan Baru</h3>
                    <button type="button" @click="createModalOpen = false" aria-label="Tutup dialog" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route("{$currentRoutePrefix}.customers.store") }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Nama Lengkap Pelanggan" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="phone"
                            required
                            placeholder="Contoh: 081234567890"
                            @blur="$event.target.value = formatPhone($event.target.value)"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        >
                        <p class="mt-1 text-xs text-slate-500">Otomatis diformat ke standar lokal <code class="font-semibold text-slate-600">08...</code> (misal mengetik <code class="text-slate-600">+628...</code> atau <code class="text-slate-600">628...</code> otomatis disesuaikan).</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                        <textarea name="address" rows="3" placeholder="Contoh: Jl. Merpati No. 12, RT 02/04" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <button type="button" @click="createModalOpen = false" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-brand-700 px-4 text-xs font-semibold text-white shadow-xs hover:bg-brand-800">Simpan Pelanggan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit Data Pelanggan --}}
        <div
            x-cloak
            x-show="editModalOpen"
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 bg-slate-900/40 backdrop-blur-xs sm:items-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-customer-title"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="my-auto w-full max-w-md max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6"
                @click.outside="editModalOpen = false"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 id="edit-customer-title" class="text-base font-bold text-slate-900">Edit Data Pelanggan</h3>
                    <button type="button" @click="editModalOpen = false" aria-label="Tutup dialog" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form method="POST" :action="'/' + '{{ $currentRoutePrefix }}' + '/customers/' + editForm.id" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="phone"
                            x-model="editForm.phone"
                            required
                            @blur="editForm.phone = formatPhone(editForm.phone)"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        >
                        <p class="mt-1 text-xs text-slate-500">Otomatis diformat ke standar lokal <code class="font-semibold text-slate-600">08...</code> (misal mengetik <code class="text-slate-600">+628...</code> atau <code class="text-slate-600">628...</code> otomatis disesuaikan).</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                        <textarea name="address" x-model="editForm.address" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <button type="button" @click="editModalOpen = false" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-brand-700 px-4 text-xs font-semibold text-white shadow-xs hover:bg-brand-800">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Konfirmasi Hapus Pelanggan --}}
        <div
            x-cloak
            x-show="deleteModalOpen"
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 bg-slate-900/40 backdrop-blur-xs sm:items-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-customer-title"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="my-auto w-full max-w-sm max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6"
                @click.outside="deleteModalOpen = false"
            >
                <div class="flex size-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 id="delete-customer-title" class="mt-3 text-base font-bold text-slate-900">Hapus Data Pelanggan?</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Apakah Anda yakin ingin menghapus pelanggan <strong class="text-slate-800" x-text="deleteForm.name"></strong>? Data yang telah dihapus tidak dapat dikembalikan.
                </p>
                <form method="POST" :action="'/' + '{{ $currentRoutePrefix }}' + '/customers/' + deleteForm.id" class="mt-5 flex items-center justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">Ya, Hapus Pelanggan</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
