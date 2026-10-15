<x-layouts::app title="Daftar Pengaduan">
    <div class="space-y-6">
        {{-- Breadcrumb & Header Halaman --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1" aria-label="Breadcrumb">
                    <span class="text-brand-700 font-semibold">CUCI.IN</span>
                    <span class="text-slate-300">/</span>
                    <span>Portal Pelanggan</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-700 font-medium">Pengaduan</span>
                </nav>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Daftar Pengaduan</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Lacak status laporan keluhan cucian Anda beserta tanggapan dan komitmen penanganan maksimal 2 hari kerja.
                </p>
            </div>

            {{-- Tombol Aksi: Buat Pengaduan --}}
            <div class="shrink-0">
                <a
                    href="{{ route('customer.complaints.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Ajukan Pengaduan</span>
                </a>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik Pengaduan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Kartu 1: Total Pengaduan --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pengaduan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 shrink-0">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_count'] }} Laporan</p>
                <p class="mt-1 text-xs text-slate-400">Arsip riwayat seluruh pengaduan</p>
            </div>

            {{-- Kartu 2: Menunggu & Diproses --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Sedang Ditangani</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700 shrink-0">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-2xl font-extrabold text-amber-700 tracking-tight">
                        {{ $stats['pending_count'] + $stats['in_progress_count'] }}
                    </p>
                    <span class="text-xs text-slate-500 font-medium">
                        ({{ $stats['pending_count'] }} Menunggu, {{ $stats['in_progress_count'] }} Diproses)
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Sedang ditindaklanjuti oleh staf</p>
            </div>

            {{-- Kartu 3: Selesai Ditangani --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Terselesaikan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 shrink-0">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-emerald-700 tracking-tight">{{ $stats['resolved_count'] }} Laporan</p>
                <p class="mt-1 text-xs text-slate-400">Pengaduan tuntas diselesaikan</p>
            </div>
        </div>

        {{-- Bar Pencarian & Filter Status --}}
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-xs">
            <form method="GET" action="{{ route('customer.complaints.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="size-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode komplain (CMP-...), subjek, atau kode transaksi..."
                        style="padding-left: 2.75rem;"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-2 pr-3 text-xs sm:text-sm text-slate-800 placeholder-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                    />
                </div>

                {{-- Dropdown Filter & Reset --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 shadow-xs focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                    >
                        <option value="">Semua Status Pengaduan</option>
                        @foreach (['Menunggu', 'Diproses', 'Selesai'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>

                    <a
                        href="{{ route('customer.complaints.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-600 shadow-xs transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <svg class="size-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Pengaduan --}}
        @if ($complaints->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-200/90 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Pengaduan & Nota</th>
                                <th class="py-3.5 px-4 sm:px-6">Subjek Keluhan</th>
                                <th class="py-3.5 px-4 sm:px-6 text-center">Status</th>
                                <th class="py-3.5 px-4 sm:px-6">Target SLA 2 Hari</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($complaints as $complaint)
                                @php
                                    $statusBadge = match ($complaint->status) {
                                        'Menunggu' => 'bg-amber-50 border-amber-200 text-amber-800',
                                        'Diproses' => 'bg-sky-50 border-sky-200 text-sky-800',
                                        'Selesai' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                                        default => 'bg-slate-100 border-slate-200 text-slate-700',
                                    };

                                    $isPastDeadline = $complaint->status !== 'Selesai' && now()->gt($complaint->deadline_at);
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50/70">
                                    {{-- Kolom 1: Kode Komplain & Nota --}}
                                    <td class="py-4 px-4 sm:px-6 align-top">
                                        <div class="font-bold text-slate-900 tracking-tight">
                                            {{ $complaint->complaint_code }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                            <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] font-medium text-slate-700">
                                                {{ $complaint->transaction?->transaction_code ?? '-' }}
                                            </span>
                                            <span>•</span>
                                            <span>{{ $complaint->created_at->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Subjek & Ringkasan Deskripsi --}}
                                    <td class="py-4 px-4 sm:px-6 align-top max-w-xs sm:max-w-md">
                                        <p class="font-semibold text-slate-900">{{ $complaint->subject }}</p>
                                        <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $complaint->description }}</p>
                                        @if ($complaint->response)
                                            <div class="mt-2 inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 border border-emerald-100">
                                                <svg class="size-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Telah ditanggapi staf
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom 3: Status --}}
                                    <td class="py-4 px-4 sm:px-6 align-top text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                                            {{ $complaint->status }}
                                        </span>
                                    </td>

                                    {{-- Kolom 4: SLA Target --}}
                                    <td class="py-4 px-4 sm:px-6 align-top">
                                        <div class="text-xs font-medium text-slate-700">
                                            {{ $complaint->deadline_at->translatedFormat('d M Y, H:i') }} WIB
                                        </div>
                                        @if ($complaint->status === 'Selesai')
                                            <span class="mt-1 inline-block text-[11px] text-emerald-600 font-medium">
                                                ✓ Selesai
                                            </span>
                                        @elseif ($isPastDeadline)
                                            <span class="mt-1 inline-flex items-center gap-1 text-[11px] font-bold text-rose-600">
                                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Melewati SLA
                                            </span>
                                        @else
                                            <span class="mt-1 inline-block text-[11px] text-amber-700 font-medium">
                                                Maks {{ $complaint->deadline_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom 5: Aksi Detail --}}
                                    <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('customer.complaints.show', $complaint) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                                        >
                                            <span>Rincian</span>
                                            <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($complaints->hasPages())
                    <div class="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
                        {{ $complaints->links() }}
                    </div>
                @endif
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-8 sm:p-12 text-center shadow-xs">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand-50 text-brand-700 shrink-0">
                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base sm:text-lg font-bold text-slate-900">Belum Ada Pengaduan</h3>
                <p class="mt-1.5 max-w-md mx-auto text-xs sm:text-sm text-slate-500">
                    Semua cucian Anda berjalan dengan baik. Jika terdapat ketidaksesuaian pakaian, noda, atau keterlambatan, silakan laporkan melalui formulir pengaduan.
                </p>
                <div class="mt-5 flex justify-center gap-3">
                    @if (request()->hasAny(['search', 'status']))
                        <a
                            href="{{ route('customer.complaints.index') }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50"
                        >
                            Reset Filter
                        </a>
                    @endif
                    <a
                        href="{{ route('customer.complaints.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-brand-700"
                    >
                        Ajukan Pengaduan Sekarang
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
