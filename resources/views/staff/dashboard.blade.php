<x-layouts::app title="Dashboard Staff">
    <div class="space-y-6">
        {{-- Banner Sapaan Staff & Aksi Cepat --}}
        <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Fokus pada antrean hari ini: terima cucian, perbarui status pengerjaan, dan siapkan cucian yang sudah selesai.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button :href="route('staff.transactions.create')" size="sm">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Transaksi Baru</span>
                    </x-ui.button>
                    <x-ui.button :href="route('staff.customers.index')" variant="secondary" size="sm">
                        <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                        <span>Cari Pelanggan</span>
                    </x-ui.button>
                    <x-ui.button :href="route('transactions.lookup')" variant="secondary" size="sm">
                        <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cek Nota</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- KPI Operasional Hari Ini --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Nota Masuk Hari Ini --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-colors hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition-colors group-hover:text-brand-600">Nota Masuk Hari Ini</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($stats['transactions_today'], 0, ',', '.') }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-brand-600">
                    <span>Dibuat hari ini</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>

            {{-- Perlu Dikerjakan --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-colors hover:border-sky-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition-colors group-hover:text-sky-700">Perlu Dikerjakan</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($stats['in_progress'], 0, ',', '.') }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-sky-700">
                    <span>Status Diterima &amp; Diproses</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>

            {{-- Terlambat --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="group block rounded-xl border border-rose-200/70 bg-white p-5 shadow-xs transition-colors hover:border-rose-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition-colors group-hover:text-rose-600">Terlambat</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-rose-700">{{ number_format($stats['overdue'], 0, ',', '.') }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-rose-600">
                    <span>Lewat estimasi selesai</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>

            {{-- Siap Diambil --}}
            <a
                href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-colors hover:border-emerald-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition-colors group-hover:text-emerald-700">Siap Diambil</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($stats['ready_for_pickup'], 0, ',', '.') }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600">
                    <span>Menunggu diambil pelanggan</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </p>
            </a>
        </div>

        {{-- Pipeline Antrean & Status Operasional --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
            <div class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean &amp; Status Operasional Cucian</h3>
                    <p class="text-xs text-slate-500">Pantau proses cucian dan perbarui status pengerjaan.</p>
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
                        <p class="text-2xl font-extrabold tabular-nums text-sky-900">{{ number_format($statusCounts['diterima'], 0, ',', '.') }}</p>
                        <svg class="size-4 text-sky-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    @if ($overdueByStatus['diterima'] > 0)
                        <p class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-rose-600">
                            <span class="size-1.5 rounded-full bg-rose-500" aria-hidden="true"></span>
                            {{ number_format($overdueByStatus['diterima'], 0, ',', '.') }} terlambat
                        </p>
                    @else
                        <p class="mt-1 text-xs text-sky-800">Antrean masuk baru</p>
                    @endif
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
                        <p class="text-2xl font-extrabold tabular-nums text-amber-900">{{ number_format($statusCounts['diproses'], 0, ',', '.') }}</p>
                        <svg class="size-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    @if ($overdueByStatus['diproses'] > 0)
                        <p class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-rose-600">
                            <span class="size-1.5 rounded-full bg-rose-500" aria-hidden="true"></span>
                            {{ number_format($overdueByStatus['diproses'], 0, ',', '.') }} terlambat
                        </p>
                    @else
                        <p class="mt-1 text-xs text-amber-800">Sedang dicuci &amp; disetrika</p>
                    @endif
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
                        <p class="text-2xl font-extrabold tabular-nums text-emerald-900">{{ number_format($statusCounts['selesai'], 0, ',', '.') }}</p>
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
                        <p class="text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($statusCounts['diambil'], 0, ',', '.') }}</p>
                        <svg class="size-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="mt-1 text-xs text-slate-600">Sudah diserahkan</p>
                </a>
            </div>
        </div>

        {{-- Grid Konten: Prioritas Hari Ini & Kas --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri (2/3 width): Tabel Prioritas Hari Ini --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Prioritas Hari Ini</h3>
                            <p class="text-xs text-slate-500">Cucian terlambat ditampilkan lebih dulu, 6 per halaman.</p>
                        </div>
                    </div>
                    <a
                        href="{{ route('staff.transactions.index') }}"
                        class="text-xs font-semibold text-brand-600 transition-colors hover:text-brand-700"
                    >
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if ($queueTransactions->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-slate-700">Belum Ada Antrean</h4>
                        <p class="mt-1 text-xs text-slate-500">Semua cucian sudah diserahkan. Input penerimaan baru lewat kasir POS.</p>
                        <x-ui.button :href="route('staff.transactions.create')" size="sm" class="mt-4">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Buka Kasir POS</span>
                        </x-ui.button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-500">
                                    <th class="pb-2.5 font-semibold">Nota</th>
                                    <th class="pb-2.5 font-semibold">Pelanggan</th>
                                    <th class="pb-2.5 font-semibold">Estimasi</th>
                                    <th class="pb-2.5 font-semibold">Status</th>
                                    <th class="pb-2.5 font-semibold">Pembayaran</th>
                                    <th class="pb-2.5 font-semibold">Tagihan</th>
                                    <th class="pb-2.5 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($queueTransactions as $trx)
                                    <tr @class([
                                        'transition-colors hover:bg-slate-50/70',
                                        'bg-rose-50/40' => $trx->queue_is_overdue,
                                    ])>
                                        <td class="py-3 font-mono font-bold text-slate-900">
                                            {{ $trx->transaction_code }}
                                        </td>
                                        <td class="py-3">
                                            <p class="font-semibold text-slate-900">{{ $trx->customer?->name ?? 'Pelanggan Umum' }}</p>
                                            <p class="text-xs text-slate-500">{{ $trx->customer?->phone ?? '-' }}</p>
                                        </td>
                                        <td class="py-3">
                                            @if ($trx->status === \App\Enums\TransactionStatus::Selesai)
                                                <x-ui.badge tone="success" size="sm">Siap diambil</x-ui.badge>
                                            @elseif ($trx->queue_is_overdue)
                                                <x-ui.badge tone="danger" size="sm">Terlambat {{ $trx->queue_hours }} jam</x-ui.badge>
                                            @elseif ($trx->estimated_completed_at)
                                                <x-ui.badge tone="neutral" size="sm">Sisa {{ $trx->queue_hours }} jam</x-ui.badge>
                                            @else
                                                <span class="text-xs text-slate-500">-</span>
                                            @endif
                                            @if ($trx->estimated_completed_at)
                                                <p class="mt-1 text-xs text-slate-500">{{ $trx->estimated_completed_at->format('d/m H:i') }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <x-ui.status-badge :status="$trx->status" size="sm" />
                                        </td>
                                        <td class="py-3">
                                            <x-ui.payment-badge :status="$trx->queue_payment_status" size="sm" />
                                        </td>
                                        <td class="py-3 font-bold text-slate-900">
                                            <x-ui.money :value="$trx->total_amount" />
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <x-ui.button
                                                    :href="route('staff.transactions.show', $trx)"
                                                    variant="soft"
                                                    size="xs"
                                                >
                                                    Detail
                                                </x-ui.button>
                                                <x-ui.button
                                                    :href="route('staff.transactions.print', $trx)"
                                                    variant="secondary"
                                                    size="xs"
                                                    target="_blank"
                                                    rel="noopener"
                                                    title="Cetak Struk Thermal"
                                                >
                                                    Cetak
                                                </x-ui.button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($queueTransactions->hasPages())
                        <div class="mt-4 border-t border-slate-100 pt-3">
                            {{ $queueTransactions->links() }}
                        </div>
                    @endif
                @endif

                <x-dashboard.activity-feed :activities="$recentActivities" prefix="staff" />
            </div>

            {{-- Kolom Kanan (1/3 width): Kas Hari Ini, Katalog & Pelanggan Baru --}}
            <div class="space-y-6 lg:col-span-1">
                {{-- Card Kas Hari Ini --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">Kas Hari Ini</h3>
                        <p class="text-xs text-slate-500">Rekap pembayaran sah untuk tutup shift.</p>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-medium text-slate-500">Total uang masuk</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">
                            <x-ui.money :value="$cashTotal" />
                        </p>
                    </div>

                    <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-3">
                        @foreach ($cashBreakdown as $method)
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="size-2 rounded-full {{ $method['bar_class'] }}" aria-hidden="true"></span>
                                    {{ $method['method'] }}
                                </span>
                                <span class="flex items-baseline gap-2">
                                    <span class="font-semibold text-slate-900">
                                        <x-ui.money :value="$method['total']" />
                                    </span>
                                    <span class="w-9 text-right tabular-nums text-slate-500">{{ number_format($method['percentage'], 1, ',', '.') }}%</span>
                                </span>
                            </div>
                        @endforeach

                        @if ($cashTotal <= 0)
                            <p class="text-xs text-slate-500">Belum ada pembayaran sah hari ini.</p>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                        <div class="rounded-lg bg-emerald-50 px-3 py-2">
                            <p class="text-xs font-medium text-emerald-700">Nota Lunas</p>
                            <p class="mt-0.5 text-lg font-extrabold tabular-nums text-emerald-800">{{ number_format($paidTodayCount, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg bg-amber-50 px-3 py-2">
                            <p class="text-xs font-medium text-amber-700">Belum Lunas</p>
                            <p class="mt-0.5 text-lg font-extrabold tabular-nums text-amber-800">{{ number_format($unpaidTodayCount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

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
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition-colors hover:border-slate-200">
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
                        <x-ui.button :href="route('staff.transactions.create')" size="sm" class="w-full">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Buka Kasir POS Baru</span>
                        </x-ui.button>
                    </div>
                </div>

                <x-dashboard.new-customers :customers="$recentCustomers" prefix="staff" />
            </div>
        </div>
    </div>
</x-layouts::app>
