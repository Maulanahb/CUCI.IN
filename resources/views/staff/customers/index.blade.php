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
    >
        {{-- Header & Sub-deskripsi Halaman --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Data Pelanggan</h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                    Kelola direktori kontak pelanggan, riwayat cucian, serta pemantauan status order laundry secara terpadu.
                </p>
            </div>
            <div>
                <button
                    type="button"
                    @click="createModalOpen = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Pelanggan</span>
                </button>
            </div>
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

        {{-- Card Metrik Ringkasan (Executive KPI Grid) --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-xs">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Total Pelanggan</span>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $totalCustomersCount }}</span>
                    <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-700">Akun</span>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-xs">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Sedang Proses</span>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold tracking-tight text-amber-600">{{ $counts['processing'] }}</span>
                    <span class="rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">Cucian</span>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-xs">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Siap Diambil</span>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold tracking-tight text-sky-600">{{ $counts['ready'] }}</span>
                    <span class="rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-bold text-sky-700">Siap</span>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-xs">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Selesai / Diambil</span>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold tracking-tight text-emerald-600">{{ $counts['completed'] }}</span>
                    <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">Selesai</span>
                </div>
            </div>
        </div>

        {{-- Kontainer Utama --}}
        @if (! $selectedCustomer)
            {{-- VIEW A: Full Data Table (Saat Belum Ada Pelanggan Yang Dipilih) --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white shadow-xs">
                {{-- Header Tabel & Kontrol Pencarian/Filter --}}
                <div class="border-b border-slate-100 p-4 sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Daftar Pelanggan</h2>
                            <p class="text-xs text-slate-400">Pilih pelanggan untuk melihat rincian nota atau kelola data melalui tombol aksi.</p>
                        </div>

                        {{-- Search Input Form --}}
                        <form method="GET" action="{{ route("{$currentRoutePrefix}.customers.index") }}" class="w-full sm:w-72">
                            @if ($statusFilter !== 'all')
                                <input type="hidden" name="status" value="{{ $statusFilter }}">
                            @endif
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
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
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
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
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'all',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'all',
                            ])
                        >
                            Semua ({{ $counts['all'] }})
                        </a>
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'processing'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'processing',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'processing',
                            ])
                        >
                            Sedang Proses ({{ $counts['processing'] }})
                        </a>
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'ready'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'ready',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'ready',
                            ])
                        >
                            Siap Diambil ({{ $counts['ready'] }})
                        </a>
                        <a
                            href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'completed'])) }}"
                            @class([
                                'shrink-0 rounded-lg px-3 py-1.5 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'completed',
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
                        <thead class="border-b border-slate-100 bg-slate-50/60 text-[11px] font-bold tracking-wider text-slate-400 uppercase">
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
                                                <p class="text-[11px] text-slate-400">
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
                                            <span class="text-slate-400">-</span>
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
                                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-bold text-brand-700">
                                                {{ $customer->transactions_count }} Nota
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">
                                                Belum Ada Nota
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Tombol Aksi --}}
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- 1. Tombol Detail --}}
                                            <a
                                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                                                    'customer_id' => $customer->id,
                                                    'q' => $search,
                                                    'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                                    'page' => request('page'),
                                                ])) }}"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                                title="Lihat profil dan riwayat nota"
                                            >
                                                <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>Detail</span>
                                            </a>

                                            {{-- 2. Tombol Edit --}}
                                            <button
                                                type="button"
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $customer->id,
                                                    'name' => $customer->name,
                                                    'phone' => $customer->phone,
                                                    'address' => $customer->address ?? '',
                                                ]) }})"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                                title="Edit data pelanggan"
                                            >
                                                <svg class="size-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                                <span>Edit</span>
                                            </button>

                                            {{-- 3. Tombol Hapus --}}
                                            @if ($customer->transactions_count > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-xs font-medium text-slate-400 cursor-not-allowed"
                                                    title="Pelanggan memiliki {{ $customer->transactions_count }} riwayat nota dan tidak dapat dihapus."
                                                >
                                                    <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                    </svg>
                                                    <span class="sr-only sm:not-sr-only">Terkunci</span>
                                                </span>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="openDeleteModal({{ json_encode([
                                                        'id' => $customer->id,
                                                        'name' => $customer->name,
                                                    ]) }})"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50/50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 shadow-2xs transition hover:border-rose-300 hover:bg-rose-100 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                                                    title="Hapus data pelanggan"
                                                >
                                                    <svg class="size-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center">
                                        <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <p class="mt-3 text-sm font-bold text-slate-900">Data Pelanggan Tidak Ditemukan</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Silakan sesuaikan kata kunci atau filter status pencarian Anda.</p>
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
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-700">
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
                                <a
                                    href="{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                                        'customer_id' => $customer->id,
                                        'q' => $search,
                                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                        'page' => request('page'),
                                    ])) }}"
                                    class="flex-1 text-center rounded-lg border border-slate-200 bg-white py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50"
                                >
                                    Detail
                                </a>
                                <button
                                    type="button"
                                    @click="openEditModal({{ json_encode([
                                        'id' => $customer->id,
                                        'name' => $customer->name,
                                        'phone' => $customer->phone,
                                        'address' => $customer->address ?? '',
                                    ]) }})"
                                    class="flex-1 rounded-lg border border-amber-200 bg-amber-50/70 py-2 text-xs font-semibold text-amber-800"
                                >
                                    Edit
                                </button>
                                @if ($customer->transactions_count === 0)
                                    <button
                                        type="button"
                                        @click="openDeleteModal({{ json_encode([
                                            'id' => $customer->id,
                                            'name' => $customer->name,
                                        ]) }})"
                                        class="flex-1 rounded-lg border border-rose-200 bg-rose-50/70 py-2 text-xs font-semibold text-rose-700"
                                    >
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
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
                <div class="lg:col-span-5 xl:col-span-4 rounded-2xl border border-slate-200/90 bg-white shadow-xs">
                    {{-- Header List Pelanggan --}}
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-900">Daftar Pelanggan</h2>
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
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
                                    <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari nama atau telepon..."
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50/60 py-2 pr-8 pl-8 text-xs text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                >
                            </div>
                        </form>

                        {{-- Tab Filter Pills --}}
                        <div class="no-scrollbar mt-3 flex items-center gap-1 overflow-x-auto pb-0.5 text-[11px]">
                            <a
                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'all'])) }}"
                                @class([
                                    'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                    'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'all',
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'all',
                                ])
                            >
                                Semua ({{ $counts['all'] }})
                            </a>
                            <a
                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'processing'])) }}"
                                @class([
                                    'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                    'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'processing',
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'processing',
                                ])
                            >
                                Proses ({{ $counts['processing'] }})
                            </a>
                            <a
                                href="{{ route("{$currentRoutePrefix}.customers.index", array_filter(['q' => $search, 'status' => 'ready'])) }}"
                                @class([
                                    'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                    'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'ready',
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
                                    'border-l-4 border-brand-600 bg-brand-50/60' => $isSelected,
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
                                            <p class="truncate text-[11px] text-slate-500">{{ $customer->phone ?: '-' }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">
                                        {{ $customer->transactions_count }} Nota
                                    </span>
                                </div>

                                {{-- Action Bar on Each Card in Master List --}}
                                <div class="flex items-center justify-end gap-1.5 pt-1 border-t border-slate-100/70">
                                    <a
                                        href="{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                                            'customer_id' => $customer->id,
                                            'q' => $search,
                                            'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                            'page' => request('page'),
                                        ])) }}"
                                        @class([
                                            'inline-flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-semibold transition',
                                            'bg-brand-600 text-white shadow-2xs' => $isSelected,
                                            'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => ! $isSelected,
                                        ])
                                    >
                                        {{ $isSelected ? 'Terpilih' : 'Detail' }}
                                    </a>

                                    <button
                                        type="button"
                                        @click="openEditModal({{ json_encode([
                                            'id' => $customer->id,
                                            'name' => $customer->name,
                                            'phone' => $customer->phone,
                                            'address' => $customer->address ?? '',
                                        ]) }})"
                                        class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800"
                                        title="Edit Pelanggan"
                                    >
                                        Edit
                                    </button>

                                    @if ($customer->transactions_count === 0)
                                        <button
                                            type="button"
                                            @click="openDeleteModal({{ json_encode([
                                                'id' => $customer->id,
                                                'name' => $customer->name,
                                            ]) }})"
                                            class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50/70 px-2 py-1 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-100"
                                            title="Hapus Pelanggan"
                                        >
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-slate-400">
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
                @include('staff.customers.partials.detail-panel')
            </div>
        @endif

        {{-- Modal CRUD Pelanggan (Create, Edit, Delete) --}}
        @include('staff.customers.partials.modals')
    </div>
</x-layouts::app>
