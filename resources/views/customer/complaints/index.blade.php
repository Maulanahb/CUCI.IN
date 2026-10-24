@php
    $currentStatus = request('status');
    $hasFilters = request()->filled('search') || request()->filled('status');
@endphp

<x-layouts::app title="Daftar Pengaduan">
    <div class="space-y-6">
        {{-- Header Halaman & Aksi --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Daftar Pengaduan</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
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

        {{-- Kartu Ringkasan Statistik Pengaduan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Kartu 1: Total Pengaduan --}}
            <x-ui.card padding="p-5" class="shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengaduan</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 shrink-0">
                        <x-lucide-message-square class="size-5" aria-hidden="true" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900 tracking-tight">{{ $stats['total_count'] }} Laporan</p>
                <p class="mt-1 text-xs text-slate-500">Arsip riwayat seluruh pengaduan Anda</p>
            </x-ui.card>

            {{-- Kartu 2: Sedang Ditangani --}}
            <x-ui.card padding="p-5" class="shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Sedang Ditangani</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 shrink-0">
                        <x-lucide-clock class="size-5" aria-hidden="true" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-amber-700 tracking-tight">
                        {{ $stats['pending_count'] + $stats['in_progress_count'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">
                        ({{ $stats['pending_count'] }} Menunggu, {{ $stats['in_progress_count'] }} Diproses)
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Sedang ditindaklanjuti tim operasional</p>
            </x-ui.card>

            {{-- Kartu 3: Selesai Ditangani --}}
            <x-ui.card padding="p-5" class="shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Terselesaikan</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 shrink-0">
                        <x-lucide-check-circle-2 class="size-5" aria-hidden="true" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-emerald-700 tracking-tight">{{ $stats['resolved_count'] }} Laporan</p>
                <p class="mt-1 text-xs text-slate-500">Pengaduan tuntas diselesaikan</p>
            </x-ui.card>
        </div>

        {{-- Quick Status Filter Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none sm:gap-2">
            @php
                $statusTabs = [
                    ['label' => 'Semua Pengaduan', 'status' => null],
                    ['label' => 'Menunggu Tindak Lanjut', 'status' => 'Menunggu'],
                    ['label' => 'Sedang Diproses', 'status' => 'Diproses'],
                    ['label' => 'Tuntas Selesai', 'status' => 'Selesai'],
                ];
            @endphp

            @foreach ($statusTabs as $tab)
                @php
                    $isActive = ($tab['status'] === null && $currentStatus === null) || ($currentStatus === $tab['status']);
                @endphp
                <a
                    href="{{ route('customer.complaints.index', array_filter(array_merge(request()->except(['page', 'status']), ['status' => $tab['status']]))) }}"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-semibold transition {{ $isActive ? 'border-brand-600 bg-brand-50 text-brand-700 shadow-xs ring-1 ring-brand-200' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Bar Pencarian & Filter Form --}}
        <x-ui.card padding="p-4 sm:p-5">
            <form method="GET" action="{{ route('customer.complaints.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-search class="size-4" aria-hidden="true" />
                    </div>
                    <input
                        type="search"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode tiket (CMP-...), subjek keluhan, atau nomor nota (TRK-...)..."
                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50/50 pl-10 pr-4 text-xs sm:text-sm text-slate-900 placeholder-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-200"
                    />
                </div>

                {{-- Dropdown Filter & Reset --}}
                <div class="flex flex-wrap items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="h-10 rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-800 shadow-xs focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-200"
                    >
                        <option value="">Semua Status Pengaduan</option>
                        @foreach (['Menunggu', 'Diproses', 'Selesai'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>

                    <x-ui.button
                        :href="route('customer.complaints.index')"
                        variant="secondary"
                        class="{{ $hasFilters ? '' : 'pointer-events-none opacity-50' }}"
                        :aria-disabled="$hasFilters ? 'false' : 'true'"
                    >
                        <x-lucide-rotate-ccw class="size-3.5" aria-hidden="true" />
                        <span>Reset</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Tabel Pengaduan --}}
        @if ($complaints->isNotEmpty())
            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th scope="col" class="py-3.5 px-5">Tiket & Nota Transaksi</th>
                                <th scope="col" class="py-3.5 px-5">Subjek Keluhan</th>
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
                                    <td class="py-4 px-5 align-top">
                                        <a
                                            href="{{ route('customer.complaints.show', $complaint) }}"
                                            class="font-mono font-bold tracking-tight text-brand-700 hover:text-brand-800 hover:underline"
                                        >
                                            {{ $complaint->complaint_code }}
                                        </a>
                                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                            <span class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-semibold text-slate-700">
                                                {{ $complaint->transaction?->transaction_code ?? '-' }}
                                            </span>
                                            <span>&middot;</span>
                                            <span>{{ $complaint->created_at->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Subjek & Ringkasan Deskripsi --}}
                                    <td class="py-4 px-5 align-top max-w-xs sm:max-w-md">
                                        <p class="font-semibold text-slate-900">{{ $complaint->subject }}</p>
                                        <p class="mt-1 text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ $complaint->description }}</p>
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
                                    <td class="py-4 px-5 align-top text-center">
                                        @if ($complaint->status === 'Menunggu')
                                            <x-ui.badge tone="info" size="sm" dot>
                                                Menunggu
                                            </x-ui.badge>
                                        @elseif ($complaint->status === 'Diproses')
                                            <x-ui.badge tone="warning" size="sm" dot pulse>
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
                                    <td class="py-4 px-5 align-top">
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

                                    {{-- Kolom 5: Aksi Detail --}}
                                    <td class="py-4 px-5 align-top text-right whitespace-nowrap">
                                        <x-ui.button
                                            :href="route('customer.complaints.show', $complaint)"
                                            variant="soft"
                                            size="xs"
                                        >
                                            <span>Rincian</span>
                                            <x-lucide-arrow-right class="size-3.5" aria-hidden="true" />
                                        </x-ui.button>
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

                        <a href="{{ route('customer.complaints.show', $complaint) }}" class="block p-4 transition-colors hover:bg-slate-50/70">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-mono font-bold tracking-tight text-brand-700">{{ $complaint->complaint_code }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        <span class="font-mono font-semibold">{{ $complaint->transaction?->transaction_code ?? '-' }}</span> &middot;
                                        {{ $complaint->created_at->translatedFormat('d M Y') }}
                                    </p>
                                </div>

                                @if ($complaint->status === 'Menunggu')
                                    <x-ui.badge tone="info" size="sm" dot class="shrink-0">
                                        Menunggu
                                    </x-ui.badge>
                                @elseif ($complaint->status === 'Diproses')
                                    <x-ui.badge tone="warning" size="sm" dot pulse class="shrink-0">
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

                            <p class="mt-2.5 font-semibold text-slate-900">{{ $complaint->subject }}</p>
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500 leading-relaxed">{{ $complaint->description }}</p>

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

                                <span class="text-xs font-semibold text-brand-700 inline-flex items-center gap-1">
                                    <span>Lihat Rincian</span>
                                    <x-lucide-arrow-right class="size-3" aria-hidden="true" />
                                </span>
                            </div>
                        </a>
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
    </div>
</x-layouts::app>
