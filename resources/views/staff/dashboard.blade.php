<x-layouts::app title="Dashboard Kasir & Operasional">
    <div
        class="space-y-6"
        x-data="{
            initShortcuts() {
                window.addEventListener('keydown', (e) => {
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
                    if (e.key === 'F2') {
                        e.preventDefault();
                        window.location.href = '{{ route('staff.transactions.create') }}';
                    }
                });
            }
        }"
        x-init="initShortcuts()"
    >
        {{-- Banner Sapaan Staff & Aksi Cepat --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }} &bull; Kelola antrean cucian hari ini: input nota baru, perbarui tahapan cuci, dan serahkan cucian yang telah tuntas.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button :href="route('staff.transactions.create')" size="sm" class="shadow-xs">
                        <x-lucide-plus class="size-4" aria-hidden="true" />
                        <span>Buka Kasir POS</span>
                        <kbd class="hidden rounded-md border border-brand-500/40 bg-brand-800/80 px-1.5 py-0.5 text-[10px] font-mono font-bold text-white sm:inline shadow-2xs">F2</kbd>
                    </x-ui.button>
                    <x-ui.button :href="route('staff.customers.index')" variant="secondary" size="sm">
                        <x-lucide-users class="size-4 text-slate-500" aria-hidden="true" />
                        <span>Cari Pelanggan</span>
                    </x-ui.button>
                    <x-ui.button :href="route('transactions.lookup')" variant="secondary" size="sm">
                        <x-lucide-search class="size-4 text-slate-500" aria-hidden="true" />
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
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-brand-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 group-hover:text-brand-700 transition">Nota Masuk Hari Ini</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-lucide-receipt class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 sm:text-3xl">
                    {{ number_format($stats['transactions_today'], 0, ',', '.') }}
                </p>
                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-brand-700">
                    <span>Transaksi dibuat hari ini</span>
                    <x-lucide-arrow-right class="size-3.5 transition-transform group-hover:translate-x-1" />
                </p>
            </a>

            {{-- Perlu Dikerjakan --}}
            <a
                href="{{ route('staff.transactions.index', ['status' => 'Diterima']) }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-sky-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 group-hover:text-sky-700 transition">Perlu Dikerjakan</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                        <x-lucide-clock class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 sm:text-3xl">
                    {{ number_format($stats['in_progress'], 0, ',', '.') }}
                </p>
                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-sky-700">
                    <span>Status Diterima &amp; Diproses</span>
                    <x-lucide-arrow-right class="size-3.5 transition-transform group-hover:translate-x-1" />
                </p>
            </a>

            {{-- Terlambat --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="group block rounded-xl border {{ $stats['overdue'] > 0 ? 'border-rose-200 bg-rose-50/20' : 'border-slate-200/80 bg-white' }} p-5 shadow-xs transition hover:border-rose-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 group-hover:text-rose-700 transition">Terlambat</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <x-lucide-alert-triangle class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold tabular-nums text-rose-700 sm:text-3xl">
                    {{ number_format($stats['overdue'], 0, ',', '.') }}
                </p>
                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-rose-600">
                    @if ($stats['overdue'] > 0)
                        <span>Lewat estimasi selesai!</span>
                    @else
                        <span>Seluruh pesanan tepat waktu</span>
                    @endif
                    <x-lucide-arrow-right class="size-3.5 transition-transform group-hover:translate-x-1" />
                </p>
            </a>

            {{-- Siap Diambil --}}
            <a
                href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:border-emerald-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 group-hover:text-emerald-700 transition">Siap Diambil</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                        <x-lucide-check-circle-2 class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900 sm:text-3xl">
                    {{ number_format($stats['ready_for_pickup'], 0, ',', '.') }}
                </p>
                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-emerald-700">
                    <span>Menunggu diambil pelanggan</span>
                    <x-lucide-arrow-right class="size-3.5 transition-transform group-hover:translate-x-1" />
                </p>
            </a>
        </div>

        {{-- Pipeline Antrean & Status Operasional Cucian --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
            <div class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 sm:text-base">
                        Pipeline &amp; Status Operasional Antrean
                    </h2>
                    <p class="text-xs text-slate-500">
                        Klik pada kartu status untuk memfilter daftar transaksi operasional laundry.
                    </p>
                </div>
                <a href="{{ route('staff.transactions.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline">
                    <span>Kelola Seluruh Antrean</span>
                    <x-lucide-arrow-right class="size-3.5" />
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                {{-- Diterima --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Diterima']) }}"
                    class="group block rounded-xl border border-sky-100 bg-sky-50/40 p-4 transition hover:border-sky-300 hover:bg-sky-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-sky-800">1. Diterima</span>
                        <span class="inline-flex size-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold tabular-nums text-sky-950">{{ number_format($statusCounts['diterima'], 0, ',', '.') }}</p>
                        <x-lucide-chevron-right class="size-4 text-sky-600 transition-transform group-hover:translate-x-0.5" />
                    </div>
                    @if ($overdueByStatus['diterima'] > 0)
                        <p class="mt-1 inline-flex items-center gap-1 text-2xs font-bold text-rose-600">
                            <span class="size-1 rounded-full bg-rose-500" aria-hidden="true"></span>
                            {{ number_format($overdueByStatus['diterima'], 0, ',', '.') }} terlambat
                        </p>
                    @else
                        <p class="mt-1 text-2xs font-medium text-sky-800">Antrean masuk baru</p>
                    @endif
                </a>

                {{-- Diproses --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Diproses']) }}"
                    class="group block rounded-xl border border-amber-100 bg-amber-50/40 p-4 transition hover:border-amber-300 hover:bg-amber-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-amber-800">2. Diproses</span>
                        <span class="inline-flex size-2 rounded-full bg-amber-500 animate-pulse"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold tabular-nums text-amber-950">{{ number_format($statusCounts['diproses'], 0, ',', '.') }}</p>
                        <x-lucide-chevron-right class="size-4 text-amber-600 transition-transform group-hover:translate-x-0.5" />
                    </div>
                    @if ($overdueByStatus['diproses'] > 0)
                        <p class="mt-1 inline-flex items-center gap-1 text-2xs font-bold text-rose-600">
                            <span class="size-1 rounded-full bg-rose-500" aria-hidden="true"></span>
                            {{ number_format($overdueByStatus['diproses'], 0, ',', '.') }} terlambat
                        </p>
                    @else
                        <p class="mt-1 text-2xs font-medium text-amber-800">Sedang dicuci &amp; disetrika</p>
                    @endif
                </a>

                {{-- Selesai --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
                    class="group block rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 transition hover:border-emerald-300 hover:bg-emerald-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-emerald-800">3. Selesai</span>
                        <span class="inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold tabular-nums text-emerald-950">{{ number_format($statusCounts['selesai'], 0, ',', '.') }}</p>
                        <x-lucide-chevron-right class="size-4 text-emerald-600 transition-transform group-hover:translate-x-0.5" />
                    </div>
                    <p class="mt-1 text-2xs font-medium text-emerald-800">Siap diambil pelanggan</p>
                </a>

                {{-- Diambil --}}
                <a
                    href="{{ route('staff.transactions.index', ['status' => 'Diambil']) }}"
                    class="group block rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-slate-300 hover:bg-slate-100/60"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-slate-700">4. Diambil</span>
                        <span class="inline-flex size-2 rounded-full bg-slate-400"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold tabular-nums text-slate-900">{{ number_format($statusCounts['diambil'], 0, ',', '.') }}</p>
                        <x-lucide-chevron-right class="size-4 text-slate-500 transition-transform group-hover:translate-x-0.5" />
                    </div>
                    <p class="mt-1 text-2xs font-medium text-slate-500">Tuntas diserahkan</p>
                </a>
            </div>
        </div>

        {{-- Grid Konten: Prioritas Hari Ini & Kas --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri (2/3 width): Tabel Prioritas Hari Ini & Feed --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2 space-y-6">
                {{-- Prioritas Hari Ini --}}
                <div>
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <x-lucide-list-ordered class="size-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean Prioritas Pengerjaan</h3>
                                <p class="text-xs text-slate-500">Cucian yang mendekati atau melewati tenggat waktu ditampilkan lebih awal.</p>
                            </div>
                        </div>
                        <a
                            href="{{ route('staff.transactions.index') }}"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline"
                        >
                            <span>Semua Nota</span>
                            <x-lucide-arrow-right class="size-3.5" />
                        </a>
                    </div>

                    @if ($queueTransactions->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                <x-lucide-check-circle-2 class="size-6" />
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-800">Belum Ada Antrean</h4>
                            <p class="mt-1 text-xs text-slate-500 max-w-sm">
                                Semua cucian sudah diserahkan. Input penerimaan baru lewat kasir POS.
                            </p>
                            <x-ui.button :href="route('staff.transactions.create')" size="sm" class="mt-4 shadow-xs">
                                <x-lucide-plus class="size-4" />
                                <span>Buka Kasir POS Baru</span>
                            </x-ui.button>
                        </div>
                    @else
                        {{-- Desktop Table --}}
                        <div class="hidden overflow-x-auto sm:block -mx-5 sm:-mx-6">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50/50 text-2xs font-bold uppercase tracking-wider text-slate-500">
                                        <th scope="col" class="py-3 px-5 sm:px-6">Kode Nota</th>
                                        <th scope="col" class="py-3 px-4">Pelanggan</th>
                                        <th scope="col" class="py-3 px-4">Estimasi Selesai</th>
                                        <th scope="col" class="py-3 px-4">Tahap Pengerjaan</th>
                                        <th scope="col" class="py-3 px-4">Pembayaran</th>
                                        <th scope="col" class="py-3 px-4 text-right">Tagihan</th>
                                        <th scope="col" class="py-3 px-5 sm:px-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach ($queueTransactions as $trx)
                                        <tr @class([
                                            'transition hover:bg-slate-50/80',
                                            'bg-rose-50/30' => $trx->queue_is_overdue,
                                        ])>
                                            {{-- Nota Clickable Link --}}
                                            <td class="py-3.5 px-5 sm:px-6 font-semibold">
                                                <a
                                                    href="{{ route('staff.transactions.show', $trx) }}"
                                                    class="font-sans font-bold tracking-tight text-brand-700 hover:text-brand-800 hover:underline inline-flex items-center gap-1"
                                                >
                                                    {{ $trx->transaction_code }}
                                                    <x-lucide-external-link class="size-3 text-slate-400" />
                                                </a>
                                            </td>

                                            {{-- Pelanggan --}}
                                            <td class="py-3.5 px-4">
                                                <p class="font-semibold text-slate-900 leading-snug">{{ $trx->customer?->name ?? 'Pelanggan Umum' }}</p>
                                                <p class="text-2xs text-slate-500 tabular-nums">{{ $trx->customer?->phone ?? '-' }}</p>
                                            </td>

                                            {{-- Estimasi --}}
                                            <td class="py-3.5 px-4">
                                                @if ($trx->queue_is_overdue)
                                                    <x-ui.badge tone="danger" size="xs" dot pulse>
                                                        Terlambat {{ $trx->queue_hours }}j
                                                    </x-ui.badge>
                                                @elseif ($trx->status !== \App\Enums\TransactionStatus::Selesai && $trx->estimated_completed_at)
                                                    <x-ui.badge tone="neutral" size="xs">
                                                        Sisa {{ $trx->queue_hours }} jam
                                                    </x-ui.badge>
                                                @endif
                                                @if ($trx->estimated_completed_at)
                                                    <p class="mt-0.5 text-2xs text-slate-500 tabular-nums">{{ $trx->estimated_completed_at->format('d/m, H:i') }}</p>
                                                @else
                                                    <span class="text-2xs text-slate-400">-</span>
                                                @endif
                                            </td>

                                            {{-- Status --}}
                                            <td class="py-3.5 px-4">
                                                <x-ui.status-badge :status="$trx->status" size="sm" />
                                            </td>

                                            {{-- Pembayaran --}}
                                            <td class="py-3.5 px-4">
                                                <x-ui.payment-badge :status="$trx->queue_payment_status" size="sm" />
                                            </td>

                                            {{-- Tagihan --}}
                                            <td class="py-3.5 px-4 text-right font-bold tabular-nums text-slate-900">
                                                <x-ui.money :value="$trx->total_amount" />
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="py-3.5 px-5 sm:px-6 text-right">
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
                                                        title="Cetak Struk Thermal Kasir"
                                                    >
                                                        <x-lucide-printer class="size-3.5" />
                                                    </x-ui.button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Card Queue List --}}
                        <div class="divide-y divide-slate-100 sm:hidden">
                            @foreach ($queueTransactions as $trx)
                                <div class="py-3.5 space-y-2 {{ $trx->queue_is_overdue ? 'bg-rose-50/30' : '' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <a
                                                href="{{ route('staff.transactions.show', $trx) }}"
                                                class="font-bold tracking-tight text-brand-700 hover:underline"
                                            >
                                                {{ $trx->transaction_code }}
                                            </a>
                                            <p class="text-xs font-semibold text-slate-900 mt-0.5">
                                                {{ $trx->customer?->name ?? 'Pelanggan Umum' }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold tabular-nums text-slate-900">
                                                <x-ui.money :value="$trx->total_amount" />
                                            </p>
                                            <x-ui.payment-badge :status="$trx->queue_payment_status" size="sm" class="mt-1" />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-2 text-2xs text-slate-500">
                                        <div class="flex items-center gap-1.5">
                                            <x-ui.status-badge :status="$trx->status" size="sm" />
                                            @if ($trx->queue_is_overdue)
                                                <span class="font-bold text-rose-600">Terlambat {{ $trx->queue_hours }}j</span>
                                            @elseif ($trx->estimated_completed_at)
                                                <span>{{ $trx->estimated_completed_at->format('d/m, H:i') }}</span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <x-ui.button :href="route('staff.transactions.show', $trx)" variant="soft" size="xs">
                                                Detail
                                            </x-ui.button>
                                            <x-ui.button :href="route('staff.transactions.print', $trx)" variant="secondary" size="xs" target="_blank" rel="noopener">
                                                <x-lucide-printer class="size-3.5" />
                                            </x-ui.button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($queueTransactions->hasPages())
                            <div class="border-t border-slate-100 pt-3">
                                {{ $queueTransactions->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Feed Aktivitas Riwayat Perubahan Status --}}
                <div class="border-t border-slate-100 pt-5">
                    <x-dashboard.activity-feed :activities="$recentActivities" prefix="staff" />
                </div>
            </div>

            {{-- Kolom Kanan (1/3 width): Kas Tutup Shift, Katalog & Pelanggan --}}
            <div class="space-y-6 lg:col-span-1">
                {{-- Card Kas Hari Ini (Rekap Tutup Shift) --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                <x-lucide-wallet class="size-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Kas Hari Ini</h3>
                                <p class="text-2xs text-slate-500">Rekap uang masuk untuk tutup shift kasir.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-medium text-slate-500">Total uang masuk</p>
                        <p class="mt-1 text-2xl font-black tabular-nums text-slate-900 sm:text-3xl">
                            <x-ui.money :value="$cashTotal" />
                        </p>
                    </div>

                    {{-- Rincian Metode Pembayaran --}}
                    <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-3">
                        @foreach ($cashBreakdown as $method)
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="size-2 rounded-full {{ $method['bar_class'] }}" aria-hidden="true"></span>
                                    {{ $method['method'] }}
                                </span>
                                <span class="flex items-baseline gap-2">
                                    <span class="font-bold tabular-nums text-slate-900">
                                        <x-ui.money :value="$method['total']" />
                                    </span>
                                    <span class="w-10 text-right text-2xs text-slate-500 tabular-nums">
                                        {{ number_format($method['percentage'], 1, ',', '.') }}%
                                    </span>
                                </span>
                            </div>
                        @endforeach

                        @if ($cashTotal <= 0)
                            <p class="text-xs text-slate-500">Belum ada pembayaran sah hari ini.</p>
                        @endif
                    </div>

                    {{-- Status Tagihan Hari Ini --}}
                    <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-3">
                            <p class="text-2xs font-semibold text-emerald-800">Nota Lunas</p>
                            <p class="mt-0.5 text-lg font-bold tabular-nums text-emerald-900">
                                {{ number_format($paidTodayCount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50/40 p-3">
                            <p class="text-2xs font-semibold text-amber-800">Belum Lunas</p>
                            <p class="mt-0.5 text-lg font-bold tabular-nums text-amber-900">
                                {{ number_format($unpaidTodayCount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card Master Layanan Aktif --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <x-lucide-tag class="size-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Katalog Layanan</h3>
                                <p class="text-2xs text-slate-500">Tarif dan estimasi durasi cuci.</p>
                            </div>
                        </div>
                        <a href="{{ route('staff.services.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline">
                            <span>Katalog</span>
                            <x-lucide-arrow-right class="size-3" />
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($activeServices as $service)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/40 p-3 transition hover:border-slate-200 hover:bg-slate-50">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $service->name }}</h4>
                                    <p class="text-2xs text-slate-500">Estimasi {{ $service->estimated_duration }} Jam</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold tabular-nums text-brand-700">
                                        Rp {{ number_format($service->price, 0, ',', '.') }}
                                    </p>
                                    <span class="text-2xs text-slate-400">/ kg</span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-500">Belum ada layanan aktif.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <x-ui.button :href="route('staff.transactions.create')" size="sm" class="w-full shadow-xs">
                            <x-lucide-plus class="size-4" />
                            <span>Buka Kasir POS Baru</span>
                        </x-ui.button>
                    </div>
                </div>

                {{-- Pelanggan Baru --}}
                <x-dashboard.new-customers :customers="$recentCustomers" prefix="staff" />
            </div>
        </div>
    </div>
</x-layouts::app>
