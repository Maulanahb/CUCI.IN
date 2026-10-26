@php
    $currentStatus = request('status');
    $hasFilters = request()->filled('search') || request()->filled('status');
@endphp

<x-layouts::app title="Kelola Pengaduan Pelanggan">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Kelola Pengaduan Pelanggan</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Pantau tiket kendala cucian pelanggan, berikan tanggapan solusi, dan perbarui status pengerjaan sesuai target SLA 2 hari kerja.
                </p>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Pengaduan --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Tiket</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 shrink-0">
                        <x-lucide-message-square class="size-4.5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold font-mono tabular-nums text-slate-900 tracking-tight sm:text-3xl">
                        {{ $stats['total'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Tiket</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Seluruh pengaduan tercatat</p>
            </div>

            {{-- Menunggu --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Menunggu Tindak Lanjut</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 shrink-0">
                        <x-lucide-clock class="size-4.5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold font-mono tabular-nums text-amber-700 tracking-tight sm:text-3xl">
                        {{ $stats['pending'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Perlu Respons</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Belum diproses petugas</p>
            </div>

            {{-- Diproses --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Sedang Dikerjakan</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 shrink-0">
                        <x-lucide-refresh-cw class="size-4.5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold font-mono tabular-nums text-blue-700 tracking-tight sm:text-3xl">
                        {{ $stats['in_progress'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Proses Cuci Ulang/Cek</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Sedang ditindaklanjuti staf</p>
            </div>

            {{-- Selesai --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Selesai Ditangani</p>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 shrink-0">
                        <x-lucide-check-circle-2 class="size-4.5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-bold font-mono tabular-nums text-emerald-700 tracking-tight sm:text-3xl">
                        {{ $stats['resolved'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Tuntas</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Solusi telah dikonfirmasi</p>
            </div>
        </div>

        {{-- Filter & Pencarian --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
            <form method="GET" action="{{ route(request()->route()->getName()) }}" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                {{-- Tabs Status --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('status', 'page'))) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                            'bg-brand-600 text-white shadow-xs' => empty($currentStatus),
                            'bg-slate-100 text-slate-600 hover:bg-slate-200' => ! empty($currentStatus),
                        ])
                    >
                        Semua ({{ $stats['total'] }})
                    </a>
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('page'), ['status' => 'Menunggu'])) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                            'bg-amber-600 text-white shadow-xs' => $currentStatus === 'Menunggu',
                            'bg-amber-50 text-amber-700 hover:bg-amber-100' => $currentStatus !== 'Menunggu',
                        ])
                    >
                        Menunggu ({{ $stats['pending'] }})
                    </a>
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('page'), ['status' => 'Diproses'])) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                            'bg-blue-600 text-white shadow-xs' => $currentStatus === 'Diproses',
                            'bg-blue-50 text-blue-700 hover:bg-blue-100' => $currentStatus !== 'Diproses',
                        ])
                    >
                        Diproses ({{ $stats['in_progress'] }})
                    </a>
                    <a
                        href="{{ route(request()->route()->getName(), array_merge(request()->except('page'), ['status' => 'Selesai'])) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                            'bg-emerald-600 text-white shadow-xs' => $currentStatus === 'Selesai',
                            'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' => $currentStatus !== 'Selesai',
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
                            value="{{ request('search') }}"
                            placeholder="Cari kode tiket, nota, nama..."
                            class="w-full rounded-lg border border-slate-200 py-1.5 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        >
                    </div>
                    <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-700">
                        Cari
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route(request()->route()->getName()) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Pengaduan --}}
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
            @if ($complaints->isEmpty())
                <div class="flex flex-col items-center justify-center p-12 text-center">
                    <div class="flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <x-lucide-inbox class="size-7" />
                    </div>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900">Tidak ada pengaduan ditemukan</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm">
                        @if ($hasFilters)
                            Tidak ada tiket komplain yang cocok dengan kriteria filter atau pencarian Anda.
                        @else
                            Belum ada pengaduan kendala yang diajukan oleh pelanggan saat ini.
                        @endif
                    </p>
                    @if ($hasFilters)
                        <a href="{{ route(request()->route()->getName()) }}" class="mt-4 text-xs font-semibold text-brand-600 hover:text-brand-700">
                            Bersihkan Filter
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider">
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
                                <tr class="transition hover:bg-slate-50/60">
                                    <td class="whitespace-nowrap py-4 pl-5 pr-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-slate-900">{{ $complaint->complaint_code }}</span>
                                            @if ($complaint->photo_path)
                                                <span class="inline-flex items-center gap-0.5 rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600" title="Memiliki lampiran foto">
                                                    <x-lucide-camera class="size-3" />
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 text-[11px] text-slate-400">{{ $complaint->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <p class="font-semibold text-slate-900">{{ $complaint->transaction?->customer?->name ?? 'Pelanggan Umum' }}</p>
                                        <p class="font-mono text-[11px] text-brand-600">
                                            {{ $complaint->transaction?->transaction_code ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-4 max-w-xs">
                                        <p class="font-semibold text-slate-900 truncate">{{ $complaint->subject }}</p>
                                        <p class="mt-0.5 line-clamp-1 text-[11px] text-slate-500">{{ $complaint->description }}</p>
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
                                                <span class="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">Terlambat</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400">{{ $complaint->deadline_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        @if ($complaint->status === 'Menunggu')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                <span class="size-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu
                                            </span>
                                        @elseif ($complaint->status === 'Diproses')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                                <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                Diproses
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
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
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-brand-700 shadow-2xs transition hover:border-brand-300 hover:bg-brand-50"
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
    </div>
</x-layouts::app>
