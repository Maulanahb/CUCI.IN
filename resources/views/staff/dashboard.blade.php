<x-layouts::app title="Dashboard Staff">
    <div class="space-y-6">
        {{-- Banner Sapaan Staff & Quick Action Kasir --}}
        <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                        <span class="size-2 rounded-full bg-brand-600"></span>
                        Staff Operasional & Kasir
                    </span>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Kelola penerimaan laundry, timbangan cucian, pencatatan transaksi, dan pembaruan status pengerjaan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('staff.transactions.create') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Transaksi Baru</span>
                    </a>
                    <a
                        href="{{ route('staff.customers.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98"
                    >
                        <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                        <span>Data Pelanggan</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistik Operasional Hari Ini (4 Metric Cards) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Transaksi Hari Ini --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Transaksi Hari Ini</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:scale-105">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $stats['transactions_today'] }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-brand-600">
                    <span>Dibuat hari ini</span>
                    <span class="transition group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>

            {{-- Pelanggan Terdaftar --}}
            <a
                href="{{ route('staff.customers.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Pelanggan Terdaftar</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:scale-105">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $stats['total_customers'] }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600">
                    <span>Data customer laundry</span>
                    <span class="transition group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>

            {{-- Layanan Aktif --}}
            <a
                href="{{ route('staff.services.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Layanan Tersedia</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:scale-105">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $stats['active_services'] }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-indigo-600">
                    <span>Katalog layanan</span>
                    <span class="transition group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>

            {{-- Siap Diambil Pelanggan --}}
            <a
                href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">Siap Diambil</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600 transition group-hover:scale-105">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $statusCounts['selesai'] ?? 0 }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-teal-600">
                    <span>Cucian selesai</span>
                    <span class="transition group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>
        </div>

        {{-- Pipeline Antrean & Status Operasional Cucian --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean & Status Operasional Cucian</h3>
                    <p class="text-xs text-slate-500">Pantau proses cucian laundry dan perbarui status pengerjaan.</p>
                </div>
                <a href="{{ route('staff.transactions.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                    Kelola Seluruh Cucian &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                {{-- Diterima --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Diterima']) }}"
                    class="group block rounded-xl border border-sky-100 bg-sky-50/50 p-4 transition-colors hover:border-sky-300 hover:bg-sky-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-sky-800 transition-colors group-hover:text-sky-900">Diterima</span>
                        <span class="inline-flex size-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-extrabold tabular-nums text-sky-900">{{ $statusCounts['diterima'] ?? 0 }}</p>
                        <svg class="size-4 text-sky-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="mt-1 text-xs text-sky-800">Antrean masuk baru</p>
                </a>

                {{-- Diproses --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Diproses']) }}"
                    class="group block rounded-xl border border-amber-100 bg-amber-50/50 p-4 transition-colors hover:border-amber-300 hover:bg-amber-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-amber-800 transition-colors group-hover:text-amber-900">Diproses</span>
                        <span class="inline-flex size-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-extrabold tabular-nums text-amber-900">{{ $statusCounts['diproses'] ?? 0 }}</p>
                        <svg class="size-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="mt-1 text-xs text-amber-800">Sedang dicuci & disetrika</p>
                </a>

                {{-- Selesai --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
                    class="group block rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 transition-colors hover:border-emerald-300 hover:bg-emerald-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-emerald-800 transition-colors group-hover:text-emerald-900">Selesai</span>
                        <span class="inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-extrabold tabular-nums text-emerald-900">{{ $statusCounts['selesai'] ?? 0 }}</p>
                        <svg class="size-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="mt-1 text-xs text-emerald-800">Siap diambil pelanggan</p>
                </a>

                {{-- Diambil --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Diambil']) }}"
                    class="group block rounded-xl border border-slate-200 bg-slate-50/80 p-4 transition-colors hover:border-slate-300 hover:bg-slate-100/80"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-700 transition-colors group-hover:text-slate-900">Diambil</span>
                        <span class="inline-flex size-2 rounded-full bg-slate-400"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-extrabold tabular-nums text-slate-900">{{ $statusCounts['diambil'] ?? 0 }}</p>
                        <svg class="size-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="mt-1 text-xs text-slate-600">Selesai diserahkan</p>
                </a>
            </div>
        </div>

        {{-- Grid Konten 2 Kolom: Antrean Cucian Terkini & Katalog Layanan --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri (2/3 width): Transaksi & Antrean Terkini --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean Cucian Terkini</h3>
                            <p class="text-xs text-slate-500">Order cucian terbaru yang memerlukan penanganan staff.</p>
                        </div>
                    </div>
                    <a
                        href="{{ route('staff.transactions.index') }}"
                        class="text-xs font-semibold text-brand-600 transition hover:text-brand-700"
                    >
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if ($todayTransactions->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-slate-700">Belum Ada Transaksi</h4>
                        <p class="mt-1 text-xs text-slate-500">Gunakan terminal kasir POS untuk menginput cucian pelanggan baru.</p>
                        <a
                            href="{{ route('staff.transactions.create') }}"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Buka Kasir POS</span>
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-500">
                                    <th class="pb-2.5 font-semibold">Nota</th>
                                    <th class="pb-2.5 font-semibold">Pelanggan</th>
                                    <th class="pb-2.5 font-semibold">Total Tagihan</th>
                                    <th class="pb-2.5 font-semibold">Status Pengerjaan</th>
                                    <th class="pb-2.5 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($todayTransactions as $trx)
                                    @php
                                        $statusStr = $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : (string) $trx->status;
                                        $badgeClass = match ($statusStr) {
                                            'Diterima' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'Diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'Diambil' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="py-3 font-mono font-bold text-slate-900">
                                            {{ $trx->transaction_code }}
                                        </td>
                                        <td class="py-3">
                                            <p class="font-semibold text-slate-900">{{ $trx->customer?->name ?? 'Pelanggan Umum' }}</p>
                                            <p class="text-xs text-slate-500">{{ $trx->customer?->phone ?? '-' }}</p>
                                        </td>
                                        <td class="py-3 font-bold text-slate-900">
                                            Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3">
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $badgeClass }}">
                                                {{ $statusStr }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a
                                                    href="{{ route('staff.transactions.show', $trx) }}"
                                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900"
                                                >
                                                    Detail
                                                </a>
                                                <a
                                                    href="{{ route('staff.transactions.print', $trx) }}"
                                                    target="_blank"
                                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-brand-700 shadow-2xs transition hover:bg-brand-50"
                                                    title="Cetak Struk Thermal"
                                                >
                                                    Cetak
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan (1/3 width): Katalog Layanan & Aksi Operasional --}}
            <div class="space-y-6 lg:col-span-1">
                {{-- Card Master Layanan Aktif --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Katalog Layanan</h3>
                            <p class="text-xs text-slate-500">Tarif dan estimasi waktu cuci.</p>
                        </div>
                        <a href="{{ route('staff.services.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                            Semua &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($activeServices as $service)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition hover:border-slate-200">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $service->name }}</h4>
                                    <p class="text-xs text-slate-500">Estimasi {{ $service->estimated_duration }} Jam</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-extrabold text-brand-700">
                                        Rp {{ number_format($service->price, 0, ',', '.') }}
                                    </p>
                                    <span class="text-xs text-slate-500">/ kg</span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-500">Belum ada layanan aktif.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <a
                            href="{{ route('staff.transactions.create') }}"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-600 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Buka Kasir POS Baru</span>
                        </a>
                    </div>
                </div>

                {{-- Card Panduan Operasional Cepat --}}
                <div class="rounded-2xl border border-brand-200/80 bg-gradient-to-br from-brand-50/70 via-white to-brand-50/30 p-5 shadow-xs">
                    <div class="flex items-center gap-2">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900">Tips Kasir Operasional</h4>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-600">
                        Pastikan timbang cucian dengan akurat di halaman Kasir POS. Setelah transaksi disimpan, Anda dapat langsung mencetak nota struk thermal dan menempelkannya ke kantong cucian pelanggan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>