@php
    $currentStatus = request('status');
    $hasFilters = request()->filled('search') || request()->filled('status');
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'staff';

    $complaintsPayloadMap = $complaints->mapWithKeys(function ($complaint) use ($routePrefix) {
        return [
            $complaint->id => [
                'id' => $complaint->id,
                'complaint_code' => $complaint->complaint_code,
                'subject' => $complaint->subject,
                'description' => $complaint->description,
                'status' => $complaint->status,
                'deadline_formatted' => $complaint->deadline_at->translatedFormat('d M Y, H:i') . ' WIB',
                'deadline_human' => $complaint->deadline_at->diffForHumans(),
                'is_overdue' => $complaint->status !== 'Selesai' && $complaint->deadline_at->isPast(),
                'created_at_formatted' => $complaint->created_at->translatedFormat('d M Y, H:i') . ' WIB',
                'photo_url' => $complaint->photo_url,
                'response' => $complaint->response ?? '',
                'customer_name' => $complaint->transaction?->customer?->name ?? 'Pelanggan Umum',
                'customer_phone' => $complaint->transaction?->customer?->phone ?? '-',
                'transaction_code' => $complaint->transaction?->transaction_code ?? '-',
                'transaction_status' => $complaint->transaction?->status?->value ?? (string) $complaint->transaction?->status,
                'transaction_details' => $complaint->transaction?->details->map(fn($d) => [
                    'name' => $d->service?->name ?? 'Layanan Laundry',
                    'qty' => $d->weight ?? $d->quantity ?? 1,
                    'unit' => $d->service?->unit ?? 'kg',
                ])->values()->all() ?? [],
                'update_url' => route($routePrefix . '.complaints.update', $complaint),
                'show_url' => route($routePrefix . '.complaints.show', $complaint),
                'pos_url' => $complaint->transaction_id ? route($routePrefix . '.transactions.show', $complaint->transaction_id) : null,
            ]
        ];
    })->all();
@endphp

<x-layouts::app title="Kelola Pengaduan Pelanggan">
    <div
        class="space-y-7"
        x-data="{
            complaintsMap: {{ Js::from($complaintsPayloadMap) }},
            selectedComplaint: null,
            sheetOpen: false,
            statusValue: 'Menunggu',
            responseValue: '',
            isSubmitting: false,

            openComplaintSheet(id) {
                this.selectedComplaint = this.complaintsMap[id] || null;
                if (!this.selectedComplaint) return;
                this.statusValue = this.selectedComplaint.status;
                this.responseValue = this.selectedComplaint.response || '';
                this.sheetOpen = true;
            },
            closeComplaintSheet() {
                this.sheetOpen = false;
            }
        }"
    >
        @if (session('status'))
            <x-ui.alert variant="success" dismissible>
                {{ session('status') }}
            </x-ui.alert>
        @endif
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Kelola Pengaduan Pelanggan</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Tinjau keluhan, tanggapi pelanggan, dan pantau batas waktu penanganan.
                </p>
            </div>
        </div>

        <section aria-label="Ringkasan pengaduan" class="grid grid-cols-2 overflow-hidden rounded-2xl border border-slate-200 bg-white lg:grid-cols-4">
            <div class="border-b border-r border-slate-200 p-5 sm:p-6 lg:border-b-0">
                <p class="text-sm font-medium text-slate-600">Total tiket</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">{{ $stats['total'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Seluruh pengaduan tercatat</p>
            </div>
            <div class="border-b border-slate-200 p-5 sm:p-6 lg:border-b-0 lg:border-r">
                <p class="text-sm font-medium text-slate-600">Menunggu respons</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-amber-700">{{ $stats['pending'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Belum ditindaklanjuti</p>
            </div>
            <div class="border-r border-slate-200 p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Sedang diproses</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">{{ $stats['in_progress'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Dalam penanganan staf</p>
            </div>
            <div class="p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Selesai ditangani</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-emerald-700">{{ $stats['resolved'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Pengaduan selesai</p>
            </div>
        </section>

        {{-- Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <form method="GET" action="{{ route(request()->route()->getName()) }}" class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                {{-- Tabs Status --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('status', 'page'))) }}"
                        @class([
                            'inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                            'bg-brand-600 text-white' => empty($currentStatus),
                            'bg-slate-100 text-slate-600 hover:bg-slate-200' => ! empty($currentStatus),
                        ])
                    >
                        Semua ({{ $stats['total'] }})
                    </a>
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('page'), ['status' => 'Menunggu'])) }}"
                        @class([
                            'inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                            'bg-brand-600 text-white' => $currentStatus === 'Menunggu',
                            'bg-slate-100 text-slate-600 hover:bg-slate-200' => $currentStatus !== 'Menunggu',
                        ])
                    >
                        Menunggu ({{ $stats['pending'] }})
                    </a>
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('page'), ['status' => 'Diproses'])) }}"
                        @class([
                            'inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                            'bg-brand-600 text-white' => $currentStatus === 'Diproses',
                            'bg-slate-100 text-slate-600 hover:bg-slate-200' => $currentStatus !== 'Diproses',
                        ])
                    >
                        Diproses ({{ $stats['in_progress'] }})
                    </a>
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('page'), ['status' => 'Selesai'])) }}"
                        @class([
                            'inline-flex min-h-11 items-center rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                            'bg-brand-600 text-white' => $currentStatus === 'Selesai',
                            'bg-slate-100 text-slate-600 hover:bg-slate-200' => $currentStatus !== 'Selesai',
                        ])
                    >
                        Selesai ({{ $stats['resolved'] }})
                    </a>
                </div>

                {{-- Input Pencarian --}}
                <div class="flex items-center gap-2">
                    @if ($currentStatus)
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                    @endif
                    <div class="relative w-full sm:w-72">
                        <x-lucide-search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            name="search"
                            aria-label="Cari kode tiket, nota, atau nama pelanggan"
                            value="{{ request('search') }}"
                            placeholder="Cari kode tiket, nota, nama..."
                            class="w-full rounded-lg border border-slate-200 min-h-11 py-2.5 pl-9 pr-3 text-base sm:text-sm text-slate-800 placeholder-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        >
                    </div>
                    <x-ui.button type="submit">Cari</x-ui.button>
                    @if ($hasFilters)
                        <a href="{{ route(request()->route()->getName()) }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Pengaduan --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @if ($complaints->isEmpty())
                <div class="flex flex-col items-center justify-center p-12 text-center">
                    <div class="flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <x-lucide-inbox class="size-7" />
                    </div>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900">Tidak ada pengaduan ditemukan</h3>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm">
                        @if ($hasFilters)
                            Tidak ada tiket komplain yang cocok dengan kriteria filter atau pencarian Anda.
                        @else
                            Belum ada pengaduan kendala yang diajukan oleh pelanggan saat ini.
                        @endif
                    </p>
                    @if ($hasFilters)
                        <a href="{{ route(request()->route()->getName()) }}" class="mt-4 text-sm font-semibold text-brand-600 hover:text-brand-700">
                            Bersihkan Filter
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="border-b border-slate-200/80 bg-slate-50/75 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="py-3.5 pl-5 pr-3">Kode Tiket</th>
                                <th scope="col" class="px-3 py-3.5">Transaksi & Pelanggan</th>
                                <th scope="col" class="px-3 py-3.5">Kendala / Keluhan</th>
                                <th scope="col" class="px-3 py-3.5">Batas SLA</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($complaints as $complaint)
                                @php
                                    $isOverdue = $complaint->status !== 'Selesai' && $complaint->deadline_at->isPast();
                                @endphp
                                <tr class="transition hover:bg-slate-50/60 cursor-pointer" @click="openComplaintSheet({{ $complaint->id }})">
                                    <td class="whitespace-nowrap py-4 pl-5 pr-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-slate-900">{{ $complaint->complaint_code }}</span>
                                            @if ($complaint->photo_path)
                                                <span class="inline-flex items-center gap-0.5 rounded-full bg-slate-100 px-1.5 py-0.5 text-sm font-medium text-slate-600" title="Memiliki lampiran foto">
                                                    <x-lucide-camera class="size-3" />
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 text-sm text-slate-500">{{ $complaint->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <p class="font-semibold text-slate-900">{{ $complaint->transaction?->customer?->name ?? 'Pelanggan Umum' }}</p>
                                        <p class="font-mono text-xs text-brand-600">
                                            {{ $complaint->transaction?->transaction_code ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-4 max-w-xs">
                                        <p class="font-semibold text-slate-900 truncate">{{ $complaint->subject }}</p>
                                        <p class="mt-0.5 line-clamp-1 text-sm text-slate-500">{{ $complaint->description }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <span @class([
                                                'font-medium',
                                                'text-red-600 font-semibold' => $isOverdue,
                                                'text-slate-600' => ! $isOverdue,
                                            ])>
                                                {{ $complaint->deadline_at->translatedFormat('d M Y') }}
                                            </span>
                                            @if ($isOverdue)
                                                <span class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-bold text-red-700">Terlambat</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-500">{{ $complaint->deadline_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        @if ($complaint->status === 'Menunggu')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-sm font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                <span class="size-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu
                                            </span>
                                        @elseif ($complaint->status === 'Diproses')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-sm font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                                <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                Diproses
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-sm font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                Selesai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-3 pr-5 text-right">
                                        @php
                                            $showRoute = request()->routeIs('admin.*')
                                                ? route('admin.complaints.show', $complaint)
                                                : route('staff.complaints.show', $complaint);
                                        @endphp
                                        <a
                                            href="{{ $showRoute }}"
                                            @click.stop.prevent="openComplaintSheet({{ $complaint->id }})"
                                            class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 cursor-pointer"
                                        >
                                            <span>Tindak Lanjuti</span>
                                            <x-lucide-arrow-right class="size-3.5" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($complaints->hasPages())
                    <div class="border-t border-slate-200/80 px-4 py-3">
                        {{ $complaints->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Slide-Over Sheet Drawer: Tindak Lanjut Pengaduan --}}
        <x-ui.sheet show="sheetOpen" maxWidth="xl">
            <x-slot:header>
                <div class="flex items-center justify-between gap-4 w-full pr-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-base font-bold tracking-tight text-slate-900" x-text="selectedComplaint?.complaint_code"></span>
                            <template x-if="selectedComplaint?.status === 'Menunggu'">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu
                                </span>
                            </template>
                            <template x-if="selectedComplaint?.status === 'Diproses'">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                    <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    Diproses
                                </span>
                            </template>
                            <template x-if="selectedComplaint?.status === 'Selesai'">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                    Selesai
                                </span>
                            </template>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500" x-text="'Diajukan: ' + (selectedComplaint?.created_at_formatted || '-')"></p>
                    </div>

                    <div class="text-right shrink-0">
                        <span
                            class="inline-flex items-center gap-1 text-xs font-medium"
                            :class="selectedComplaint?.is_overdue ? 'text-rose-600 font-bold' : 'text-slate-500'"
                            x-text="selectedComplaint?.is_overdue ? 'Melewati SLA' : selectedComplaint?.deadline_human"
                        ></span>
                    </div>
                </div>
            </x-slot:header>

            <div class="space-y-6 text-sm">
                {{-- 1. Kartu Detail Keluhan --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4.5 space-y-3.5 shadow-2xs">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Subjek Keluhan</span>
                        <h3 class="mt-1 font-bold text-slate-900 text-base" x-text="selectedComplaint?.subject"></h3>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Uraian Masalah / Kendala</span>
                        <p class="mt-1 text-slate-700 leading-relaxed whitespace-pre-line" x-text="selectedComplaint?.description"></p>
                    </div>

                    {{-- Bukti Foto Lampiran --}}
                    <template x-if="selectedComplaint?.photo_url">
                        <div class="border-t border-slate-100 pt-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Bukti Foto Kendala / Pakaian</span>
                                <a
                                    :href="selectedComplaint?.photo_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800"
                                >
                                    <x-lucide-zoom-in class="size-3.5" />
                                    <span>Lihat Ukuran Penuh</span>
                                </a>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img
                                    :src="selectedComplaint?.photo_url"
                                    alt="Foto Bukti Komplain"
                                    class="max-h-72 w-full object-contain mx-auto"
                                />
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 2. Kartu Rincian Transaksi Terkait --}}
                <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nota Transaksi Terkait</span>
                        <template x-if="selectedComplaint?.pos_url">
                            <a
                                :href="selectedComplaint?.pos_url"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800"
                            >
                                <span>Buka Nota POS</span>
                                <x-lucide-external-link class="size-3" />
                            </a>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80">
                            <span class="text-slate-400 block font-medium">Nomor Nota</span>
                            <span class="font-mono font-bold text-slate-900 mt-0.5 block text-sm" x-text="selectedComplaint?.transaction_code"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80">
                            <span class="text-slate-400 block font-medium">Status Cucian</span>
                            <span class="font-semibold text-slate-800 mt-0.5 block text-sm" x-text="selectedComplaint?.transaction_status"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80">
                            <span class="text-slate-400 block font-medium">Pelanggan</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block" x-text="selectedComplaint?.customer_name"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80">
                            <span class="text-slate-400 block font-medium">No. Telepon / WA</span>
                            <span class="font-mono text-slate-800 mt-0.5 block" x-text="selectedComplaint?.customer_phone"></span>
                        </div>
                    </div>

                    <template x-if="selectedComplaint?.transaction_details?.length > 0">
                        <div class="border-t border-slate-200/80 pt-2.5">
                            <span class="text-xs font-medium text-slate-500 block mb-1.5">Layanan Cucian:</span>
                            <div class="space-y-1">
                                <template x-for="(item, idx) in selectedComplaint?.transaction_details" :key="idx">
                                    <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-white/70 border border-slate-100">
                                        <span class="font-medium text-slate-800" x-text="item.name"></span>
                                        <span class="font-mono text-slate-600" x-text="item.qty + ' ' + item.unit"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 3. Formulir Tindak Lanjut & Tanggapan --}}
                <div class="rounded-2xl border border-brand-200/80 bg-brand-50/30 p-4 space-y-3.5 shadow-2xs">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 tracking-tight">Formulir Tindak Lanjut & Solusi</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui status dan berikan pesan penjelasan untuk pelanggan.</p>
                    </div>

                    <form
                        :action="selectedComplaint?.update_url"
                        method="POST"
                        class="space-y-3.5"
                        @submit="isSubmitting = true"
                    >
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="drawer_status" class="block text-xs font-bold text-slate-700 mb-1">
                                Status Penanganan <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="drawer_status"
                                name="status"
                                x-model="statusValue"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 font-semibold focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            >
                                <option value="Menunggu">Menunggu Tindak Lanjut</option>
                                <option value="Diproses">Sedang Diproses</option>
                                <option value="Selesai">Selesai Ditangani</option>
                            </select>
                        </div>

                        <div>
                            <label for="drawer_response" class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggapan / Solusi untuk Pelanggan
                            </label>
                            <textarea
                                id="drawer_response"
                                name="response"
                                x-model="responseValue"
                                rows="3"
                                placeholder="Contoh: Kami telah memeriksa pakaian Anda dan akan melakukan pencucian ulang gratis..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-bold text-white shadow-xs transition hover:bg-brand-800 active:scale-98 disabled:opacity-50 cursor-pointer"
                        >
                            <template x-if="isSubmitting">
                                <x-lucide-loader-2 class="size-4 animate-spin text-white" />
                            </template>
                            <template x-if="!isSubmitting">
                                <x-lucide-send class="size-4 text-white" />
                            </template>
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Tindak Lanjut'">Simpan Tindak Lanjut</span>
                        </button>
                    </form>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-between w-full">
                    <a
                        :href="selectedComplaint?.show_url"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition"
                    >
                        <x-lucide-external-link class="size-3.5 text-slate-400" />
                        <span>Buka Halaman Penuh</span>
                    </a>

                    <button
                        type="button"
                        @click="closeComplaintSheet()"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-200 focus:outline-none cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </x-slot:footer>
        </x-ui.sheet>
    </div>
</x-layouts::app>
