<x-layouts::app title="Dashboard Admin">
    <div class="space-y-6">
        {{-- Banner Selamat Datang Admin --}}
        <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Pantau seluruh aktivitas operasional laundry, antrean cucian, dan master data sistem dari sini.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button :href="route('admin.transactions.create')" size="sm">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Transaksi Baru</span>
                    </x-ui.button>
                    <x-ui.button :href="route('admin.services.index')" variant="secondary" size="sm">
                        <svg class="size-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                        </svg>
                        <span>Kelola Layanan</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Ringkasan Data Operasional (ringkas) --}}
        <div class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-200/70 sm:grid-cols-4">
            <div class="bg-white px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Total Pengguna</p>
                <p class="mt-1 text-lg font-extrabold tabular-nums text-slate-900">{{ number_format($stats['total_users'], 0, ',', '.') }}</p>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="group bg-white px-4 py-3 transition-colors hover:bg-brand-50/60">
                <p class="text-xs font-medium text-slate-500 transition-colors group-hover:text-brand-700">Total Pelanggan</p>
                <p class="mt-1 text-lg font-extrabold tabular-nums text-slate-900">{{ number_format($stats['total_customers'], 0, ',', '.') }}</p>
            </a>
            <a href="{{ route('admin.services.index') }}" class="group bg-white px-4 py-3 transition-colors hover:bg-brand-50/60">
                <p class="text-xs font-medium text-slate-500 transition-colors group-hover:text-brand-700">Layanan Aktif</p>
                <p class="mt-1 text-lg font-extrabold tabular-nums text-slate-900">{{ number_format($stats['total_services'], 0, ',', '.') }}</p>
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="group bg-white px-4 py-3 transition-colors hover:bg-brand-50/60">
                <p class="text-xs font-medium text-slate-500 transition-colors group-hover:text-brand-700">Total Transaksi</p>
                <p class="mt-1 text-lg font-extrabold tabular-nums text-slate-900">{{ number_format($stats['total_transactions'], 0, ',', '.') }}</p>
            </a>
        </div>

        {{-- KPI Keuangan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Pemasukan Hari Ini --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Pemasukan Hari Ini</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.5 0-4.5 1.12-4.5 2.5S9.5 13 12 13s4.5 1.12 4.5 2.5S14.5 18 12 18m0-10c1.38 0 2.6.35 3.43.91M12 8V6m0 12v2m0-2c-1.38 0-2.6-.35-3.43-.91M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">
                    <x-ui.money :value="$finance['today']" />
                </p>
                @if ($finance['trend'] === null)
                    <p class="mt-1 text-xs text-slate-500">Belum ada pemasukan kemarin sebagai pembanding.</p>
                @else
                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs">
                        <span @class([
                            'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 font-semibold tabular-nums',
                            'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/70' => $finance['trend'] >= 0,
                            'bg-rose-50 text-rose-700 ring-1 ring-rose-200/70' => $finance['trend'] < 0,
                        ])>
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $finance['trend'] >= 0 ? 'M4 14l6-6 4 4 6-8' : 'M4 10l6 6 4-4 6 8' }}" />
                            </svg>
                            {{ number_format(abs($finance['trend']), 1, ',', '.') }}%
                        </span>
                        <span class="text-slate-500">dibanding kemarin</span>
                    </p>
                @endif
            </div>

            {{-- Pemasukan Bulan Ini --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Pemasukan Bulan Ini</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">
                    <x-ui.money :value="$finance['month']" />
                </p>
                <p class="mt-1 text-xs text-slate-500">Akumulasi pembayaran sah bulan berjalan.</p>
            </div>

            {{-- Piutang Berjalan --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Piutang Berjalan</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">
                    <x-ui.money :value="$finance['receivables']" />
                </p>
                <p class="mt-1 text-xs text-slate-500">Sisa tagihan transaksi yang belum lunas.</p>
            </div>

            {{-- Transaksi Hari Ini --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Transaksi Hari Ini</span>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($finance['transactions_today'], 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    Rata-rata <x-ui.money :value="$finance['average_ticket']" /> per nota.
                </p>
            </div>
        </div>

        {{-- Grafik Pemasukan & Status Pembayaran --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Bar Chart Pemasukan 7 Hari --}}
            <x-ui.card padding="p-5 sm:p-6" class="lg:col-span-2">
                <div class="flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">Pemasukan 7 Hari Terakhir</h3>
                        <p class="text-xs text-slate-500">Hanya pembayaran tunai, transfer, dan QRIS yang sudah sah.</p>
                    </div>
                    <div class="mt-1 shrink-0 text-left sm:mt-0 sm:text-right">
                        <p class="text-lg font-extrabold text-slate-900">
                            <x-ui.money :value="$weeklyTotal" />
                        </p>
                        <p class="text-xs text-slate-500">total 7 hari</p>
                    </div>
                </div>

                <div class="mt-6" x-data="{ activeDay: null }">
                    <div class="relative h-48">
                        <div class="pointer-events-none absolute inset-x-0 top-0 bottom-6 flex flex-col justify-between" aria-hidden="true">
                            @for ($line = 0; $line < 4; $line++)
                                <div class="border-t border-dashed border-slate-100"></div>
                            @endfor
                        </div>

                        <div class="relative flex h-full items-end gap-2 pb-6 sm:gap-4">
                            @foreach ($weeklyRevenue as $index => $day)
                                <div
                                    class="group relative flex h-full flex-1 flex-col items-center justify-end"
                                    @mouseenter="activeDay = {{ $index }}"
                                    @mouseleave="activeDay = null"
                                    @focusin="activeDay = {{ $index }}"
                                    @focusout="activeDay = null"
                                >
                                    <div
                                        class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 w-max -translate-x-1/2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-center text-xs font-semibold text-white opacity-0 shadow-lg transition-opacity duration-150"
                                        :class="{ 'opacity-100': activeDay === {{ $index }} }"
                                        role="tooltip"
                                    >
                                        <span class="block">{{ $day['label'] }}, {{ $day['full_label'] }}</span>
                                        <span class="block font-bold text-brand-300">Rp {{ number_format($day['total'], 0, ',', '.') }}</span>
                                    </div>

                                    <button
                                        type="button"
                                        class="flex w-full max-w-12 cursor-pointer items-end justify-center rounded-t-md px-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                                        aria-label="{{ $day['label'] }}, {{ $day['full_label'] }}: Rp {{ number_format($day['total'], 0, ',', '.') }}"
                                        style="height: {{ $day['total'] > 0 ? $day['height'] : 2 }}%"
                                    >
                                        <span @class([
                                            'h-full w-full rounded-t-md transition-colors duration-150',
                                            'bg-brand-500 group-hover:bg-brand-600' => $day['total'] > 0,
                                            'bg-slate-200 group-hover:bg-slate-300' => $day['total'] <= 0,
                                        ])></span>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="pointer-events-none absolute inset-x-0 bottom-0 flex gap-2 sm:gap-4" aria-hidden="true">
                            @foreach ($weeklyRevenue as $day)
                                <p @class([
                                    'flex-1 text-center text-xs font-medium',
                                    'font-bold text-brand-700' => $loop->last,
                                    'text-slate-500' => ! $loop->last,
                                ])>{{ $day['label'] }}</p>
                            @endforeach
                        </div>
                    </div>

                    <details class="mt-4 border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-500 transition-colors hover:text-slate-700">Lihat data tabel</summary>
                        <table class="mt-2 w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-500">
                                    <th class="pb-2 font-semibold">Tanggal</th>
                                    <th class="pb-2 text-right font-semibold">Pemasukan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($weeklyRevenue as $day)
                                    <tr>
                                        <td class="py-1.5">{{ $day['label'] }}, {{ $day['full_label'] }}</td>
                                        <td class="py-1.5 text-right font-semibold">
                                            <x-ui.money :value="$day['total']" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    <span class="text-xs text-slate-500">Metode bayar 30 hari terakhir:</span>
                    @if ($methodTotal > 0)
                        @foreach ($methodBreakdown as $method)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200/70">
                                <span class="size-1.5 rounded-full {{ $method['bar_class'] }}"></span>
                                {{ $method['method'] }} &middot; {{ number_format($method['percentage'], 1, ',', '.') }}%
                            </span>
                        @endforeach
                    @else
                        <span class="text-xs text-slate-500">Belum ada pembayaran sah.</span>
                    @endif
                </div>
            </x-ui.card>

            {{-- Donut Status Pembayaran --}}
            <x-ui.card padding="p-5 sm:p-6" class="flex flex-col">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 sm:text-base">Status Pembayaran</h3>
                    <p class="text-xs text-slate-500">Seluruh transaksi aktif di sistem.</p>
                </div>

                @if ($paymentTotal > 0)
                    <div class="mt-4 flex flex-1 flex-col items-center gap-5 sm:flex-row lg:flex-col">
                        @php($circumference = 2 * M_PI * 50)
                        <div class="relative size-40 shrink-0">
                            <svg viewBox="0 0 120 120" class="size-full -rotate-90" role="img" aria-label="Komposisi status pembayaran: {{ $paymentBreakdown->map(fn ($segment) => $segment['status'].' '.$segment['count'])->implode(', ') }}">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="14" />
                                @php($offset = 0)
                                @foreach ($paymentBreakdown as $segment)
                                    @php($length = ($segment['percentage'] / 100) * $circumference)
                                    @if ($length > 0)
                                        <circle
                                            cx="60"
                                            cy="60"
                                            r="50"
                                            fill="none"
                                            stroke="{{ $segment['color'] }}"
                                            stroke-width="14"
                                            stroke-dasharray="{{ $length }} {{ $circumference - $length }}"
                                            stroke-dashoffset="{{ -$offset }}"
                                        />
                                        @php($offset += $length)
                                    @endif
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p class="text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($paymentTotal, 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-500">transaksi</p>
                            </div>
                        </div>

                        <ul class="w-full space-y-2">
                            @foreach ($paymentBreakdown as $segment)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                        <span class="size-2 rounded-full {{ $segment['dot'] }}" aria-hidden="true"></span>
                                        {{ $segment['status'] }}
                                    </span>
                                    <span class="flex items-baseline gap-2 text-right">
                                        <span class="text-xs font-bold text-slate-900">{{ number_format($segment['count'], 0, ',', '.') }}</span>
                                        <span class="w-10 text-xs tabular-nums text-slate-500">{{ number_format($segment['percentage'], 1, ',', '.') }}%</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-bold text-slate-700">Belum Ada Transaksi</p>
                        <p class="mt-1 text-xs text-slate-500">Komposisi pembayaran muncul setelah transaksi pertama dibuat.</p>
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- Pipeline Status Cucian Real-Time --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean & Status Operasional Cucian</h3>
                    <p class="text-xs text-slate-500">Distribusi status pengerjaan cucian di seluruh sistem.</p>
                </div>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                    Lihat Seluruh Transaksi &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                {{-- Diterima --}}
                <a
                    href="{{ route('admin.transactions.index', ['status' => 'Diterima']) }}"
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
                    href="{{ route('admin.transactions.index', ['status' => 'Diproses']) }}"
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
                    href="{{ route('admin.transactions.index', ['status' => 'Selesai']) }}"
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
                    href="{{ route('admin.transactions.index', ['status' => 'Diambil']) }}"
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

        {{-- Grid Konten 2 Kolom: Transaksi Terkini & Katalog Layanan --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri: Tabel Transaksi Terkini + Aktivitas & Pelanggan (2/3 width) --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Card Tabel Transaksi Terbaru --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Transaksi Terbaru</h3>
                                <p class="text-xs text-slate-500">Riwayat penerimaan laundry terbaru, 5 per halaman.</p>
                            </div>
                        </div>
                        <a
                            href="{{ route('admin.transactions.index') }}"
                            class="text-xs font-semibold text-brand-600 transition hover:text-brand-700"
                        >
                            Semua Transaksi &rarr;
                        </a>
                    </div>

                    @if ($recentTransactions->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-700">Belum Ada Transaksi</h4>
                            <p class="mt-1 text-xs text-slate-500">Gunakan kasir POS untuk menginput penerimaan laundry baru.</p>
                            <x-ui.button :href="route('admin.transactions.create')" size="sm" class="mt-4">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Buat Transaksi POS</span>
                            </x-ui.button>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-500">
                                        <th class="pb-2.5 font-semibold">Nota</th>
                                        <th class="pb-2.5 font-semibold">Pelanggan</th>
                                        <th class="pb-2.5 font-semibold">Total</th>
                                        <th class="pb-2.5 font-semibold">Status</th>
                                        <th class="pb-2.5 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach ($recentTransactions as $trx)
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
                                                <x-ui.status-badge :status="$trx->status" size="sm" />
                                            </td>
                                            <td class="py-3 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <x-ui.button
                                                        :href="route('admin.transactions.show', $trx)"
                                                        variant="soft"
                                                        size="xs"
                                                    >
                                                        Detail
                                                    </x-ui.button>
                                                    <x-ui.button
                                                        :href="route('admin.transactions.print', $trx)"
                                                        variant="secondary"
                                                        size="xs"
                                                        target="_blank"
                                                        rel="noopener"
                                                        title="Cetak Struk"
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

                        @if ($recentTransactions->hasPages())
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                {{ $recentTransactions->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                <x-dashboard.activity-feed :activities="$recentActivities" prefix="admin" />
            </div>

            {{-- Kolom Kanan: Top Layanan, Master Layanan Aktif & Pelanggan Baru (1/3 width) --}}
            <div class="space-y-6 lg:col-span-1">
                {{-- Card Layanan Terlaris 30 Hari --}}
                <x-ui.card padding="p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Layanan Terlaris</h3>
                            <p class="text-xs text-slate-500">Kontribusi 30 hari terakhir.</p>
                        </div>
                        <a href="{{ route('admin.transactions.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                            Transaksi &rarr;
                        </a>
                    </div>

                    @if ($topServices->isEmpty())
                        <p class="py-6 text-center text-xs text-slate-500">Belum ada layanan terjual dalam 30 hari terakhir.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($topServices as $service)
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-xs font-semibold text-slate-900">{{ $service->service_name }}</p>
                                        <p class="shrink-0 text-xs font-bold tabular-nums text-slate-700">
                                            {{ number_format($service->quantity, 1, ',', '.') }} kg
                                        </p>
                                    </div>
                                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-brand-500" style="width: {{ $service->share }}%"></div>
                                    </div>
                                    <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                        <span>Pendapatan</span>
                                        <span class="font-semibold text-brand-700">
                                            <x-ui.money :value="$service->revenue" />
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>

                {{-- Card Master Layanan Aktif --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Katalog Layanan</h3>
                            <p class="text-xs text-slate-500">Tarif aktif per kilogram.</p>
                        </div>
                        <a href="{{ route('admin.services.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                            Kelola &rarr;
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
                        <x-ui.button :href="route('admin.services.index')" variant="soft" size="sm" class="w-full">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah / Kelola Master Layanan</span>
                        </x-ui.button>
                    </div>
                </div>

                <x-dashboard.new-customers :customers="$recentCustomers" prefix="admin" />
            </div>
        </div>

        {{-- Catatan Operasional --}}
        <div class="flex items-start gap-3 rounded-2xl border border-brand-200/80 bg-gradient-to-r from-brand-50/70 via-white to-brand-50/30 px-5 py-4 shadow-xs">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900">Operasional Laundry</h4>
                <p class="mt-1 text-xs leading-relaxed text-slate-600">
                    Admin memiliki akses penuh untuk mengelola master layanan, memantau daftar transaksi, mencetak struk kasir thermal, serta mengelola data pelanggan.
                </p>
            </div>
        </div>
    </div>
</x-layouts::app>
