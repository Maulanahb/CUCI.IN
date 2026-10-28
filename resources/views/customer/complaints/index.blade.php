@php
    $currentStatus = request('status');
    $hasFilters = request()->filled('search') || request()->filled('status');

    $complaintsMap = $complaints->getCollection()->mapWithKeys(function ($c) {
        return [$c->id => [
            'id' => $c->id,
            'code' => $c->complaint_code,
            'subject' => $c->subject,
            'description' => $c->description,
            'status' => $c->status,
            'created_at' => $c->created_at->translatedFormat('d M Y, H:i') . ' WIB',
            'created_at_human' => $c->created_at->diffForHumans(),
            'deadline_at' => $c->deadline_at->translatedFormat('d M Y, H:i') . ' WIB',
            'deadline_human' => $c->deadline_at->diffForHumans(),
            'is_past_deadline' => $c->status !== 'Selesai' && now()->gt($c->deadline_at),
            'response' => $c->response,
            'handler_name' => $c->handler?->name ?? 'Staf Operasional',
            'handled_at' => $c->handled_at ? $c->handled_at->translatedFormat('d M Y, H:i') . ' WIB' : null,
            'photo_url' => $c->photo_url,
            'has_photo' => (bool) $c->photo_path,
            'transaction_code' => $c->transaction?->transaction_code,
            'transaction_status' => $c->transaction?->status?->value ?? '-',
            'transaction_date' => $c->transaction?->created_at?->translatedFormat('d M Y'),
            'transaction_total' => 'Rp ' . number_format($c->transaction?->total_amount ?? 0, 0, ',', '.'),
            'transaction_url' => $c->transaction ? route('customer.transactions.index', ['selected' => $c->transaction->transaction_code]) : '#',
            'details_url' => route('customer.complaints.show', $c),
        ]];
    });
@endphp

<x-layouts::app title="Daftar Pengaduan">
    <div
        class="space-y-6"
        x-data="{
            complaintsMap: @js($complaintsMap),
            selectedId: null,
            sheetOpen: false,
            get selectedComplaint() {
                return this.selectedId ? (this.complaintsMap[this.selectedId] || null) : null;
            },
            openSheet(id) {
                this.selectedId = id;
                this.sheetOpen = true;
            },
            closeSheet() {
                this.sheetOpen = false;
            }
        }"
    >
        {{-- Header Halaman & Aksi --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Daftar Pengaduan</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Lacak status laporan kendala cucian Anda beserta tanggapan dan komitmen penanganan maksimal 2 hari kerja (SLA).
                </p>
            </div>

            {{-- Tombol Aksi: Buat Pengaduan --}}
            <div class="shrink-0">
                <x-ui.button :href="route('customer.complaints.create')" variant="primary" size="md">
                    <x-lucide-plus class="size-4" aria-hidden="true" />
                    <span>Ajukan Pengaduan</span>
                </x-ui.button>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik Monolitik --}}
        <section aria-label="Statistik tiket pengaduan" class="grid grid-cols-1 overflow-hidden rounded-2xl border border-slate-200 bg-white sm:grid-cols-3 divide-y divide-slate-200 sm:divide-y-0 sm:divide-x">
            {{-- Kartu 1: Total Pengaduan --}}
            <div class="p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Total pengaduan</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight tabular-nums text-slate-900 sm:text-4xl">
                    {{ $stats['total_count'] }}
                </p>
                <p class="mt-2 text-xs text-slate-500">Arsip riwayat seluruh laporan Anda</p>
            </div>

            {{-- Kartu 2: Sedang Ditangani --}}
            <div class="p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Sedang ditangani</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight tabular-nums text-amber-800 sm:text-4xl">
                    {{ $stats['pending_count'] + $stats['in_progress_count'] }}
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ $stats['pending_count'] }} Menunggu &middot; {{ $stats['in_progress_count'] }} Diproses
                </p>
            </div>

            {{-- Kartu 3: Selesai Ditangani --}}
            <div class="p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Terselesaikan</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight tabular-nums text-emerald-700 sm:text-4xl">
                    {{ $stats['resolved_count'] }}
                </p>
                <p class="mt-2 text-xs text-slate-500">Pengaduan tuntas diselesaikan</p>
            </div>
        </section>

        {{-- Filter & Pencarian Pengaduan (Pola Terpadu Konsisten dengan Staf) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <form method="GET" action="{{ route('customer.complaints.index') }}" class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                {{-- Tabs Status Berhitung --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    @php
                        $statusTabs = [
                            ['label' => 'Semua', 'count' => $stats['total_count'], 'status' => null],
                            ['label' => 'Menunggu', 'count' => $stats['pending_count'], 'status' => 'Menunggu'],
                            ['label' => 'Diproses', 'count' => $stats['in_progress_count'], 'status' => 'Diproses'],
                            ['label' => 'Selesai', 'count' => $stats['resolved_count'], 'status' => 'Selesai'],
                        ];
                    @endphp

                    @foreach ($statusTabs as $tab)
                        @php
                            $isActive = ($tab['status'] === null && empty($currentStatus)) || ($currentStatus === $tab['status']);
                        @endphp
                        <a
                            href="{{ route('customer.complaints.index', array_filter(array_merge(request()->except(['page', 'status']), ['status' => $tab['status']]))) }}"
                            @class([
                                'inline-flex min-h-11 items-center rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors',
                                'bg-brand-700 text-white' => $isActive,
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => ! $isActive,
                            ])
                        >
                            <span>{{ $tab['label'] }}</span>
                            <span class="ml-1.5 text-xs opacity-90 tabular-nums">({{ $tab['count'] }})</span>
                        </a>
                    @endforeach
                </div>

                {{-- Input Pencarian & Reset --}}
                <div class="flex items-center gap-2">
                    @if ($currentStatus)
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                    @endif
                    <div class="relative w-full sm:w-80">
                        <x-lucide-search class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        <input
                            type="search"
                            name="search"
                            id="search-complaints"
                            aria-label="Cari pengaduan"
                            value="{{ request('search') }}"
                            placeholder="Cari kode tiket, subjek, atau nota..."
                            class="min-h-11 w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        />
                    </div>
                    <x-ui.button type="submit" variant="primary">
                        Cari
                    </x-ui.button>
                    @if ($hasFilters)
                        <a
                            href="{{ route('customer.complaints.index') }}"
                            class="inline-flex min-h-11 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        >
                            <x-lucide-rotate-ccw class="size-4 text-slate-500" />
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Pengaduan --}}
        @if ($complaints->isNotEmpty())
            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="py-3.5 px-5">Tiket & Nota Transaksi</th>
                                <th scope="col" class="py-3.5 px-5">Subjek & Uraian Keluhan</th>
                                <th scope="col" class="py-3.5 px-5 text-center">Status</th>
                                <th scope="col" class="py-3.5 px-5">Target SLA (Maks 2 Hari)</th>
                                <th scope="col" class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($complaints as $complaint)
                                @php
                                    $isPastDeadline = $complaint->status !== 'Selesai' && now()->gt($complaint->deadline_at);
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    {{-- Kolom 1: Kode Komplain & Nota --}}
                                    <td class="py-4 px-5 align-top whitespace-nowrap">
                                        <button
                                            type="button"
                                            @click="openSheet({{ $complaint->id }})"
                                            class="font-mono font-bold tracking-tight text-brand-700 hover:text-brand-800 hover:underline text-left cursor-pointer"
                                            title="Buka panel rincian pengaduan"
                                        >
                                            {{ $complaint->complaint_code }}
                                        </button>
                                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                            <span class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-semibold text-slate-700">
                                                {{ $complaint->transaction?->transaction_code ?? '-' }}
                                            </span>
                                            <span>&middot;</span>
                                            <span>{{ $complaint->created_at->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Subjek & Cuplikan Uraian Ringkas (Tanpa Bloat Accordion) --}}
                                    <td class="py-4 px-5 align-top max-w-xs sm:max-w-md">
                                        <button
                                            type="button"
                                            @click="openSheet({{ $complaint->id }})"
                                            class="font-semibold text-slate-900 hover:text-brand-700 transition-colors text-left break-words block cursor-pointer"
                                            title="Klik untuk membuka rincian pengaduan"
                                        >
                                            {{ $complaint->subject }}
                                        </button>
                                        <p class="mt-1 line-clamp-1 text-xs text-slate-500 leading-relaxed">
                                            {{ $complaint->description }}
                                        </p>

                                        @if ($complaint->response)
                                            <div class="mt-2">
                                                <x-ui.badge tone="success" size="sm" class="gap-1.5">
                                                    <x-lucide-check-circle-2 class="size-3.5 text-emerald-600 shrink-0" aria-hidden="true" />
                                                    <span>Telah ditanggapi staf</span>
                                                </x-ui.badge>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom 3: Status --}}
                                    <td class="py-4 px-5 align-top text-center whitespace-nowrap">
                                        @if ($complaint->status === 'Menunggu')
                                            <x-ui.badge tone="warning" size="sm" dot>
                                                Menunggu
                                            </x-ui.badge>
                                        @elseif ($complaint->status === 'Diproses')
                                            <x-ui.badge tone="info" size="sm" dot>
                                                Diproses
                                            </x-ui.badge>
                                        @elseif ($complaint->status === 'Selesai')
                                            <x-ui.badge tone="success" size="sm" dot>
                                                Selesai
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge tone="neutral" size="sm">
                                                {{ $complaint->status }}
                                            </x-ui.badge>
                                        @endif
                                    </td>

                                    {{-- Kolom 4: SLA Target --}}
                                    <td class="py-4 px-5 align-top whitespace-nowrap">
                                        <div class="text-xs font-medium text-slate-700">
                                            {{ $complaint->deadline_at->translatedFormat('d M Y, H:i') }} WIB
                                        </div>
                                        @if ($complaint->status === 'Selesai')
                                            <span class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-emerald-700">
                                                <x-lucide-check-circle-2 class="size-3.5 text-emerald-600" aria-hidden="true" />
                                                <span>Tuntas Selesai</span>
                                            </span>
                                        @elseif ($isPastDeadline)
                                            <span class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-rose-600">
                                                <x-lucide-alert-circle class="size-3.5 text-rose-500" aria-hidden="true" />
                                                <span>Melewati SLA</span>
                                            </span>
                                        @else
                                            <span class="mt-1 inline-block text-xs font-medium text-amber-700">
                                                Maks {{ $complaint->deadline_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom 5: Aksi Detail (Buka Slide-Over Sheet Drawer) --}}
                                    <td class="py-4 px-5 align-top text-right whitespace-nowrap">
                                        <button
                                            type="button"
                                            @click="openSheet({{ $complaint->id }})"
                                            class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-800 active:scale-95 cursor-pointer"
                                            aria-label="Buka panel rincian pengaduan {{ $complaint->complaint_code }}"
                                        >
                                            <span>Rincian</span>
                                            <x-lucide-arrow-right class="size-3.5 text-slate-400" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Kartu Mobile --}}
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($complaints as $complaint)
                        @php
                            $isPastDeadline = $complaint->status !== 'Selesai' && now()->gt($complaint->deadline_at);
                        @endphp

                        <div class="p-4 transition-colors hover:bg-slate-50/70">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <button
                                        type="button"
                                        @click="openSheet({{ $complaint->id }})"
                                        class="font-mono font-bold tracking-tight text-brand-700 hover:underline text-left cursor-pointer"
                                    >
                                        {{ $complaint->complaint_code }}
                                    </button>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        <span class="font-mono font-semibold">{{ $complaint->transaction?->transaction_code ?? '-' }}</span> &middot;
                                        {{ $complaint->created_at->translatedFormat('d M Y') }}
                                    </p>
                                </div>

                                @if ($complaint->status === 'Menunggu')
                                    <x-ui.badge tone="warning" size="sm" dot class="shrink-0">
                                        Menunggu
                                    </x-ui.badge>
                                @elseif ($complaint->status === 'Diproses')
                                    <x-ui.badge tone="info" size="sm" dot class="shrink-0">
                                        Diproses
                                    </x-ui.badge>
                                @elseif ($complaint->status === 'Selesai')
                                    <x-ui.badge tone="success" size="sm" dot class="shrink-0">
                                        Selesai
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge tone="neutral" size="sm" class="shrink-0">
                                        {{ $complaint->status }}
                                    </x-ui.badge>
                                @endif
                            </div>

                            <button
                                type="button"
                                @click="openSheet({{ $complaint->id }})"
                                class="mt-2.5 block text-left font-semibold text-slate-900 break-words hover:text-brand-700 transition-colors cursor-pointer"
                            >
                                {{ $complaint->subject }}
                            </button>

                            <p class="mt-1 line-clamp-2 text-xs text-slate-500 leading-relaxed">
                                {{ $complaint->description }}
                            </p>

                            @if ($complaint->response)
                                <div class="mt-2.5">
                                    <x-ui.badge tone="success" size="sm" class="gap-1.5">
                                        <x-lucide-check-circle-2 class="size-3.5 text-emerald-600 shrink-0" aria-hidden="true" />
                                        <span>Telah ditanggapi staf</span>
                                    </x-ui.badge>
                                </div>
                            @endif

                            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                @if ($complaint->status === 'Selesai')
                                    <span class="text-xs font-medium text-emerald-700 flex items-center gap-1">
                                        <x-lucide-check-circle-2 class="size-3.5 text-emerald-600" aria-hidden="true" />
                                        <span>Tuntas diselesaikan</span>
                                    </span>
                                @elseif ($isPastDeadline)
                                    <span class="text-xs font-bold text-rose-700 flex items-center gap-1">
                                        <x-lucide-alert-circle class="size-3.5 text-rose-500" aria-hidden="true" />
                                        <span>Melewati SLA</span>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-500 flex items-center gap-1">
                                        <x-lucide-clock class="size-3.5 text-slate-400" aria-hidden="true" />
                                        <span>Target: {{ $complaint->deadline_at->translatedFormat('d M Y') }}</span>
                                    </span>
                                @endif

                                <button
                                    type="button"
                                    @click="openSheet({{ $complaint->id }})"
                                    class="text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline inline-flex items-center gap-1 cursor-pointer"
                                >
                                    <span>Lihat Rincian</span>
                                    <x-lucide-arrow-right class="size-3" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginasi --}}
                @if ($complaints->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3.5">
                        {{ $complaints->links() }}
                    </div>
                @endif
            </x-ui.card>
        @else
            {{-- Empty State --}}
            <x-ui.card padding="p-8 sm:p-12" class="text-center">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 shrink-0">
                    <x-lucide-message-square class="size-7" aria-hidden="true" />
                </div>
                <h3 class="mt-4 text-base sm:text-lg font-bold text-slate-900">Belum Ada Pengaduan</h3>
                <p class="mt-1.5 max-w-md mx-auto text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Semua cucian Anda berjalan dengan baik. Jika terdapat ketidaksesuaian pakaian, noda, atau kendala pengerjaan, silakan laporkan melalui formulir pengaduan.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-2.5">
                    @if (request()->hasAny(['search', 'status']))
                        <x-ui.button :href="route('customer.complaints.index')" variant="secondary">
                            <x-lucide-rotate-ccw class="size-4" aria-hidden="true" />
                            <span>Reset Filter</span>
                        </x-ui.button>
                    @endif
                    <x-ui.button :href="route('customer.complaints.create')" variant="primary">
                        <x-lucide-plus class="size-4" aria-hidden="true" />
                        <span>Ajukan Pengaduan Sekarang</span>
                    </x-ui.button>
                </div>
            </x-ui.card>
        @endif

        {{-- Slide-Over Sheet Drawer: Rincian Pengaduan Lengkap Tanpa Reload --}}
        <x-ui.sheet
            show="sheetOpen"
            maxWidth="xl"
            @close="closeSheet()"
        >
            <x-slot:header>
                <div class="flex items-center justify-between gap-3 w-full pr-2">
                    <div class="flex items-center gap-2.5">
                        <h2 class="font-mono text-base sm:text-lg font-bold text-slate-900 tracking-tight" x-text="selectedComplaint?.code"></h2>

                        <template x-if="selectedComplaint?.status === 'Menunggu'">
                            <x-ui.badge tone="warning" size="sm" dot>Menunggu</x-ui.badge>
                        </template>
                        <template x-if="selectedComplaint?.status === 'Diproses'">
                            <x-ui.badge tone="info" size="sm" dot>Diproses</x-ui.badge>
                        </template>
                        <template x-if="selectedComplaint?.status === 'Selesai'">
                            <x-ui.badge tone="success" size="sm" dot>Selesai</x-ui.badge>
                        </template>
                    </div>
                </div>
            </x-slot:header>

            {{-- Body Panel --}}
            <div x-show="selectedComplaint" class="space-y-6 text-xs sm:text-sm">
                {{-- Stepper Progress 3 Tahap --}}
                <div class="rounded-xl border border-slate-200/90 bg-slate-50/70 p-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 block">Tahapan Penanganan</span>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div>
                            <div
                                class="size-8 mx-auto rounded-full flex items-center justify-center font-bold text-white shadow-xs"
                                :class="selectedComplaint?.status ? 'bg-brand-700' : 'bg-slate-200'"
                            >
                                <x-lucide-check class="size-4" />
                            </div>
                            <p class="mt-1.5 font-bold text-slate-900">Diajukan</p>
                            <p class="text-[11px] text-slate-500" x-text="selectedComplaint?.created_at_human"></p>
                        </div>
                        <div>
                            <div
                                class="size-8 mx-auto rounded-full flex items-center justify-center font-bold shadow-xs"
                                :class="['Diproses', 'Selesai'].includes(selectedComplaint?.status) ? 'bg-brand-700 text-white' : 'bg-white border-2 border-slate-200 text-slate-400'"
                            >
                                <template x-if="selectedComplaint?.status === 'Selesai'">
                                    <x-lucide-check class="size-4" />
                                </template>
                                <template x-if="selectedComplaint?.status !== 'Selesai'">
                                    <x-lucide-clock class="size-4" />
                                </template>
                            </div>
                            <p class="mt-1.5 font-bold text-slate-900">Pemeriksaan</p>
                            <p class="text-[11px] text-slate-500" x-text="['Diproses', 'Selesai'].includes(selectedComplaint?.status) ? 'Sedang ditangani' : 'Dalam antrean'"></p>
                        </div>
                        <div>
                            <div
                                class="size-8 mx-auto rounded-full flex items-center justify-center font-bold shadow-xs"
                                :class="selectedComplaint?.status === 'Selesai' ? 'bg-emerald-600 text-white ring-2 ring-emerald-100' : 'bg-white border-2 border-slate-200 text-slate-400'"
                            >
                                <x-lucide-check-circle-2 class="size-4" />
                            </div>
                            <p class="mt-1.5 font-bold text-slate-900">Selesai</p>
                            <p class="text-[11px] text-slate-500" x-text="selectedComplaint?.status === 'Selesai' ? (selectedComplaint?.handled_at || 'Selesai') : 'Menunggu'"></p>
                        </div>
                    </div>
                </div>

                {{-- Uraian Kendala Pelanggan --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Subjek Keluhan</span>
                        <h3 class="mt-1 font-bold text-slate-900 text-base break-words" x-text="selectedComplaint?.subject"></h3>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Uraian / Kronologi Masalah</span>
                        <div class="mt-1.5 rounded-lg bg-slate-50 p-3 border border-slate-200/70 text-xs text-slate-700 leading-relaxed whitespace-pre-line break-words break-all" x-text="selectedComplaint?.description"></div>
                    </div>

                    {{-- Foto Lampiran --}}
                    <template x-if="selectedComplaint?.has_photo">
                        <div class="border-t border-slate-100 pt-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 block">Foto Bukti Pakaian</span>
                            <a :href="selectedComplaint?.photo_url" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-xl border border-slate-200 bg-slate-50 max-h-56">
                                <img :src="selectedComplaint?.photo_url" alt="Foto Bukti Pengaduan" class="max-h-56 w-full object-contain hover:scale-102 transition duration-200" />
                            </a>
                        </div>
                    </template>
                </div>

                {{-- Tanggapan Resmi Tim Staf CUCI.IN --}}
                <div
                    class="rounded-xl border p-4 space-y-2.5"
                    :class="selectedComplaint?.response ? 'border-emerald-200 bg-emerald-50/20' : 'border-slate-200 bg-white'"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="size-7 rounded-lg flex items-center justify-center shrink-0"
                                :class="selectedComplaint?.response ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                            >
                                <x-lucide-message-square class="size-3.5" />
                            </div>
                            <h4 class="text-xs font-bold text-slate-900">Tanggapan Resmi Tim Staf</h4>
                        </div>
                        <template x-if="selectedComplaint?.handled_at">
                            <span class="text-[11px] text-slate-500" x-text="selectedComplaint?.handled_at"></span>
                        </template>
                    </div>

                    <template x-if="selectedComplaint?.response">
                        <div>
                            <div class="rounded-lg bg-white p-3 border border-emerald-100 text-xs text-slate-800 leading-relaxed whitespace-pre-line break-words break-all" x-text="selectedComplaint?.response"></div>
                            <p class="mt-1.5 text-[11px] text-slate-500">
                                Ditangani oleh: <strong class="font-semibold text-slate-800" x-text="selectedComplaint?.handler_name"></strong>
                            </p>
                        </div>
                    </template>

                    <template x-if="!selectedComplaint?.response">
                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50/70 p-3.5 text-center">
                            <p class="text-xs font-semibold text-slate-700">Dalam antrean pemeriksaan tim operasional workshop.</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">Solusi penanganan akan diberikan maksimal 2 hari kerja.</p>
                        </div>
                    </template>
                </div>

                {{-- Info SLA & Nota Transaksi Terkait --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nota Terkait</span>
                        <a
                            :href="selectedComplaint?.transaction_url"
                            class="font-mono text-xs font-bold text-brand-700 hover:underline flex items-center gap-1"
                        >
                            <span x-text="selectedComplaint?.transaction_code || '-'"></span>
                            <x-lucide-arrow-right class="size-3" />
                        </a>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Target SLA (Maks 2 Hari)</span>
                        <span class="font-semibold text-slate-800" x-text="selectedComplaint?.deadline_at"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Waktu Pengajuan</span>
                        <span class="text-slate-700" x-text="selectedComplaint?.created_at"></span>
                    </div>
                </div>
            </div>

            {{-- Footer Panel --}}
            <x-slot:footer>
                <a
                    :href="selectedComplaint?.details_url"
                    class="inline-flex min-h-9 items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline"
                >
                    <span>Buka Halaman Lengkap</span>
                    <x-lucide-external-link class="size-3.5" />
                </a>

                <x-ui.button
                    type="button"
                    variant="secondary"
                    size="sm"
                    @click="closeSheet()"
                >
                    Tutup
                </x-ui.button>
            </x-slot:footer>
        </x-ui.sheet>
    </div>
</x-layouts::app>
