<x-layouts::app title="Dashboard Admin">
    <div
        class="space-y-6"
        x-data="{
            initShortcuts() {
                window.addEventListener('keydown', (e) => {
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
                    if (e.key === 'F2') {
                        e.preventDefault();
                        window.location.href = '{{ route('admin.transactions.create') }}';
                    }
                });
            }
        }"
        x-init="initShortcuts()"
    >
        {{-- Banner Sapaan Administrator & Aksi Cepat --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }} &bull; Pantau kesehatan finansial, antrean pengerjaan cucian, dan kelola seluruh master data sistem dari sini.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button :href="route('admin.transactions.create')" size="sm" class="shadow-xs">
                        <x-lucide-plus class="size-4" aria-hidden="true" />
                        <span>Transaksi Baru</span>
                        <kbd class="hidden rounded-md border border-brand-500/40 bg-brand-800/80 px-1.5 py-0.5 text-[10px] font-mono font-bold text-white sm:inline shadow-2xs">F2</kbd>
                    </x-ui.button>
                    <x-ui.button :href="route('admin.services.index')" variant="secondary" size="sm">
                        <x-lucide-sparkles class="size-4 text-brand-600" aria-hidden="true" />
                        <span>Kelola Layanan</span>
                    </x-ui.button>
                    <x-ui.button :href="route('admin.customers.index')" variant="secondary" size="sm">
                        <x-lucide-users class="size-4 text-slate-500" aria-hidden="true" />
                        <span>Data Pelanggan</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Ringkasan Data Operasional / Master Data Strip --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            {{-- Total Pengguna --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Pengguna</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <x-lucide-shield-check class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold font-mono tabular-nums text-slate-900 sm:text-2xl">
                    {{ number_format($stats['total_users'], 0, ',', '.') }}
                </p>
                <p class="mt-0.5 text-xs text-slate-400">Admin &amp; Kasir</p>
            </div>

            {{-- Total Pelanggan --}}
            <a
                href="{{ route('admin.customers.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs transition hover:border-brand-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-700">Total Pelanggan</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:bg-brand-100">
                        <x-lucide-users class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold font-mono tabular-nums text-slate-900 sm:text-2xl">
                    {{ number_format($stats['total_customers'], 0, ',', '.') }}
                </p>
                <p class="mt-0.5 flex items-center gap-1 text-xs font-semibold text-brand-700">
                    <span>Lihat direktori</span>
                    <x-lucide-arrow-right class="size-3 transition-transform group-hover:translate-x-0.5" />
                </p>
            </a>

            {{-- Layanan Aktif --}}
            <a
                href="{{ route('admin.services.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs transition hover:border-brand-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-700">Layanan Aktif</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                        <x-lucide-tag class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold font-mono tabular-nums text-slate-900 sm:text-2xl">
                    {{ number_format($stats['total_services'], 0, ',', '.') }}
                </p>
                <p class="mt-0.5 flex items-center gap-1 text-xs font-semibold text-emerald-700">
                    <span>Katalog paket</span>
                    <x-lucide-arrow-right class="size-3 transition-transform group-hover:translate-x-0.5" />
                </p>
            </a>

            {{-- Total Transaksi --}}
            <a
                href="{{ route('admin.transactions.index') }}"
                class="group block rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs transition hover:border-brand-300 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 transition group-hover:text-brand-700">Total Transaksi</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-sky-50 text-sky-600 transition group-hover:bg-sky-100">
                        <x-lucide-receipt class="size-4" />
                    </div>
                </div>
                <p class="mt-2 text-xl font-bold font-mono tabular-nums text-slate-900 sm:text-2xl">
                    {{ number_format($stats['total_transactions'], 0, ',', '.') }}
                </p>
                <p class="mt-0.5 flex items-center gap-1 text-xs font-semibold text-sky-700">
                    <span>Riwayat nota</span>
                    <x-lucide-arrow-right class="size-3 transition-transform group-hover:translate-x-0.5" />
                </p>
            </a>
        </div>

        {{-- KPI Keuangan Utama --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Pemasukan Hari Ini --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Pemasukan Hari Ini</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                        <x-lucide-wallet class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-slate-900 sm:text-3xl">
                    <x-ui.money :value="$finance['today']" />
                </p>
                @if ($finance['trend'] === null)
                    <p class="mt-1 text-xs text-slate-500">Belum ada pemasukan kemarin sebagai pembanding.</p>
                @else
                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs">
                        <span @class([
                            'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 font-semibold font-mono tabular-nums',
                            'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/70' => $finance['trend'] >= 0,
                            'bg-rose-50 text-rose-700 ring-1 ring-rose-200/70' => $finance['trend'] < 0,
                        ])>
                            @if ($finance['trend'] >= 0)
                                <x-lucide-trending-up class="size-3" />
                            @else
                                <x-lucide-trending-down class="size-3" />
                            @endif
                            {{ number_format(abs($finance['trend']), 1, ',', '.') }}%
                        </span>
                        <span class="text-slate-500">dibanding kemarin</span>
                    </p>
                @endif
            </div>

            {{-- Pemasukan Bulan Ini --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Pemasukan Bulan Ini</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-lucide-calendar-days class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-slate-900 sm:text-3xl">
                    <x-ui.money :value="$finance['month']" />
                </p>
                <p class="mt-1 text-xs text-slate-500">Akumulasi pembayaran sah bulan berjalan.</p>
            </div>

            {{-- Piutang Berjalan --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Piutang Berjalan</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                        <x-lucide-hourglass class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-slate-900 sm:text-3xl">
                    <x-ui.money :value="$finance['receivables']" />
                </p>
                <p class="mt-1 text-xs text-slate-500">Sisa tagihan transaksi yang belum lunas.</p>
            </div>

            {{-- Transaksi Hari Ini --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Transaksi Hari Ini</span>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                        <x-lucide-receipt-text class="size-4.5" />
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold font-mono tabular-nums text-slate-900 sm:text-3xl">
                    {{ number_format($finance['transactions_today'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Rata-rata <span class="font-mono font-semibold text-slate-700"><x-ui.money :value="$finance['average_ticket']" /></span> per nota.
                </p>
            </div>
        </div>

        {{-- Grafik Pemasukan & Status Pembayaran --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Area Chart Pemasukan 7 Hari --}}
            @php
                $maxRevenue = (float) ($weeklyRevenue->max('total') ?: 1);
                $avgRevenue = $weeklyRevenue->count() > 0 ? (int) round($weeklyRevenue->avg('total')) : 0;
                $peakDay = $weeklyRevenue->sortByDesc('total')->first();

                // SVG coordinate system with dedicated headroom for floating tooltip:
                $viewWidth = 700;
                $viewHeight = 250;
                $padX = 40;
                $padYTop = 64;
                $padYBottom = 32;
                $plotHeight = $viewHeight - $padYTop - $padYBottom;
                $count = count($weeklyRevenue);
                $stepX = $count > 1 ? ($viewWidth - ($padX * 2)) / ($count - 1) : 0;

                $chartPoints = [];
                foreach ($weeklyRevenue->values() as $idx => $dayItem) {
                    $ptX = $padX + ($idx * $stepX);
                    $val = (float) $dayItem['total'];
                    $ratio = $maxRevenue > 0 ? ($val / $maxRevenue) : 0;
                    $ptY = ($viewHeight - $padYBottom) - ($ratio * $plotHeight);
                    $chartPoints[] = [
                        'x' => round($ptX, 1),
                        'y' => round($ptY, 1),
                        'pctX' => round(($ptX / $viewWidth) * 100, 2),
                        'pctY' => round(($ptY / $viewHeight) * 100, 2),
                        'label' => $dayItem['label'],
                        'full_label' => $dayItem['full_label'],
                        'total' => $val,
                        'formatted' => 'Rp ' . number_format($val, 0, ',', '.'),
                        'is_peak' => ($peakDay && $val > 0 && $val === (float) $peakDay['total']),
                    ];
                }

                $linePath = '';
                $areaPath = '';
                if (!empty($chartPoints)) {
                    $linePath = "M {$chartPoints[0]['x']},{$chartPoints[0]['y']}";
                    for ($i = 0; $i < count($chartPoints) - 1; $i++) {
                        $curr = $chartPoints[$i];
                        $next = $chartPoints[$i + 1];
                        $deltaX = ($next['x'] - $curr['x']) / 2;
                        $cp1x = round($curr['x'] + $deltaX, 1);
                        $cp1y = $curr['y'];
                        $cp2x = round($next['x'] - $deltaX, 1);
                        $cp2y = $next['y'];
                        $linePath .= " C {$cp1x},{$cp1y} {$cp2x},{$cp2y} {$next['x']},{$next['y']}";
                    }
                    $baseY = $viewHeight - $padYBottom;
                    $lastPoint = end($chartPoints);
                    $areaPath = $linePath . " L {$lastPoint['x']},{$baseY} L {$chartPoints[0]['x']},{$baseY} Z";
                }

                $avgY = $maxRevenue > 0 ? round(($viewHeight - $padYBottom) - (($avgRevenue / $maxRevenue) * $plotHeight), 1) : ($viewHeight - $padYBottom);
            @endphp

            <x-ui.card padding="p-5 sm:p-6" class="lg:col-span-2">
                {{-- Header Kartu & Box Stats Ringkas --}}
                <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 ring-1 ring-brand-200/60">
                            <x-lucide-trending-up class="size-4.5" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Pemasukan 7 Hari Terakhir</h3>
                            <p class="text-xs text-slate-500">Hanya pembayaran tunai, transfer, dan QRIS yang sudah sah.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                        @if ($peakDay && $peakDay['total'] > 0)
                            <div class="flex items-center gap-2 rounded-lg border border-amber-200/80 bg-amber-50/70 px-2.5 py-1.5 shadow-2xs">
                                <span class="size-1.5 rounded-full bg-amber-500 ring-2 ring-amber-200"></span>
                                <div class="text-left">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700 leading-none">Puncak ({{ $peakDay['label'] }})</p>
                                    <p class="mt-0.5 font-mono text-xs font-bold text-amber-950 leading-none">Rp {{ number_format($peakDay['total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center gap-2 rounded-lg border border-slate-200/80 bg-slate-50/80 px-3 py-1.5 shadow-2xs">
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 leading-none">Total 7 Hari</p>
                                <p class="mt-0.5 font-mono text-sm font-bold text-brand-700 leading-none tabular-nums">
                                    <x-ui.money :value="$weeklyTotal" />
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chart Canvas Box & Tooltip Container --}}
                <div
                    class="mt-6"
                    x-data="{
                        activeIdx: null,
                        points: {{ Js::from($chartPoints) }}
                    }"
                >
                    <div class="relative w-full rounded-2xl border border-slate-100 bg-gradient-to-b from-brand-50/25 via-slate-50/15 to-white/0 p-3 pt-4 sm:p-4 sm:pt-5">
                        {{-- SVG Canvas --}}
                        <svg viewBox="0 0 700 250" class="h-52 w-full overflow-visible sm:h-60" preserveAspectRatio="none">
                            <defs>
                                {{-- Gradient for Area Fill --}}
                                <linearGradient id="revenueAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0284c7" stop-opacity="0.30" />
                                    <stop offset="65%" stop-color="#0ea5e9" stop-opacity="0.08" />
                                    <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0.00" />
                                </linearGradient>

                                {{-- Line Gradient --}}
                                <linearGradient id="revenueLineGrad" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#0284c7" />
                                    <stop offset="50%" stop-color="#0ea5e9" />
                                    <stop offset="100%" stop-color="#06b6d4" />
                                </linearGradient>

                                {{-- Drop Shadow for Line --}}
                                <filter id="areaGlow" x="-10%" y="-10%" width="120%" height="120%">
                                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#0284c7" flood-opacity="0.25" />
                                </filter>
                            </defs>

                            {{-- Horizontal Grid Lines --}}
                            @for ($g = 0; $g < 4; $g++)
                                @php
                                    $gridY = $padYTop + ($g * ($plotHeight / 3));
                                @endphp
                                <line
                                    x1="{{ $padX }}"
                                    y1="{{ $gridY }}"
                                    x2="{{ $viewWidth - $padX }}"
                                    y2="{{ $gridY }}"
                                    stroke="currentColor"
                                    class="text-slate-100"
                                    stroke-width="1"
                                    stroke-dasharray="4 4"
                                />
                            @endfor

                            {{-- Baseline --}}
                            <line
                                x1="{{ $padX }}"
                                y1="{{ $viewHeight - $padYBottom }}"
                                x2="{{ $viewWidth - $padX }}"
                                y2="{{ $viewHeight - $padYBottom }}"
                                stroke="currentColor"
                                class="text-slate-200"
                                stroke-width="1"
                            />

                            {{-- Average Reference Line (Label placed safely on the left to avoid peak collision) --}}
                            @if ($avgRevenue > 0)
                                <line
                                    x1="{{ $padX }}"
                                    y1="{{ $avgY }}"
                                    x2="{{ $viewWidth - $padX }}"
                                    y2="{{ $avgY }}"
                                    stroke="#94a3b8"
                                    stroke-width="1.25"
                                    stroke-dasharray="3 3"
                                />
                                <text
                                    x="{{ $padX + 8 }}"
                                    y="{{ max($avgY - 6, $padYTop - 4) }}"
                                    text-anchor="start"
                                    font-size="10"
                                    class="fill-slate-400 font-mono font-medium"
                                >
                                    Rata-rata: Rp {{ number_format($avgRevenue, 0, ',', '.') }}
                                </text>
                            @endif

                            {{-- Smooth Area Fill --}}
                            <path d="{{ $areaPath }}" fill="url(#revenueAreaGrad)" class="transition-all duration-300" />

                            {{-- Smooth Spline Line --}}
                            <path
                                d="{{ $linePath }}"
                                fill="none"
                                stroke="url(#revenueLineGrad)"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                filter="url(#areaGlow)"
                                class="transition-all duration-300"
                            />

                            {{-- Dynamic Crosshair Line --}}
                            <template x-if="activeIdx !== null">
                                <line
                                    :x1="points[activeIdx].x"
                                    :y1="{{ $padYTop }}"
                                    :x2="points[activeIdx].x"
                                    :y2="{{ $viewHeight - $padYBottom }}"
                                    stroke="#0284c7"
                                    stroke-width="1.5"
                                    stroke-dasharray="3 3"
                                />
                            </template>

                            {{-- Data Points (Resting State) --}}
                            @foreach ($chartPoints as $pt)
                                @if ($pt['is_peak'])
                                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="8" class="animate-pulse fill-amber-400/30" />
                                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4.5" class="fill-amber-500 stroke-white stroke-2" />
                                @else
                                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3" class="fill-white stroke-brand-500 stroke-2" />
                                @endif
                            @endforeach

                            {{-- Active Point Highlight --}}
                            <template x-if="activeIdx !== null">
                                <circle
                                    :cx="points[activeIdx].x"
                                    :cy="points[activeIdx].y"
                                    r="6.5"
                                    class="fill-brand-600 stroke-white stroke-2"
                                />
                            </template>
                        </svg>

                        {{-- Floating Tooltip Box (Clean Light Elevated Card) --}}
                        <div
                            x-cloak
                            x-show="activeIdx !== null"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="pointer-events-none absolute z-30 mb-2.5 -translate-y-full rounded-xl border border-slate-200/80 bg-white/95 px-3.5 py-2.5 text-slate-800 shadow-xl shadow-slate-300/40 ring-1 ring-slate-900/5 backdrop-blur-md whitespace-nowrap transition-all duration-150"
                            :class="{
                                '-translate-x-[92%]': activeIdx >= 5,
                                '-translate-x-[8%]': activeIdx <= 1,
                                '-translate-x-1/2': activeIdx > 1 && activeIdx < 5
                            }"
                            :style="activeIdx !== null ? `left: ${points[activeIdx].pctX}%; top: ${points[activeIdx].pctY}%;` : ''"
                            role="tooltip"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-2xs font-semibold text-slate-500" x-text="points[activeIdx]?.label + ', ' + points[activeIdx]?.full_label"></span>
                                <template x-if="points[activeIdx]?.is_peak">
                                    <span class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 ring-1 ring-inset ring-amber-500/20">Puncak</span>
                                </template>
                            </div>
                            <p class="mt-1 font-mono text-sm font-bold text-slate-900" x-text="points[activeIdx]?.formatted"></p>
                            @if ($avgRevenue > 0)
                                <template x-if="points[activeIdx]?.total >= {{ $avgRevenue }}">
                                    <div class="mt-1 flex items-center gap-1.5 text-[10px] font-semibold text-emerald-600">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Di atas rata-rata</span>
                                    </div>
                                </template>
                            @endif
                        </div>

                        {{-- Invisible Hitbox Buttons for Mouse & Keyboard Navigation --}}
                        <div class="absolute inset-0 flex" aria-hidden="true">
                            @foreach ($chartPoints as $idx => $pt)
                                <button
                                    type="button"
                                    class="h-full flex-1 cursor-pointer focus:outline-none"
                                    @mouseenter="activeIdx = {{ $idx }}"
                                    @mouseleave="activeIdx = null"
                                    @focusin="activeIdx = {{ $idx }}"
                                    @focusout="activeIdx = null"
                                    aria-label="{{ $pt['label'] }}, {{ $pt['full_label'] }}: {{ $pt['formatted'] }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    {{-- X-Axis Day Labels Aligned With Points --}}
                    <div class="relative mt-2.5 h-6 w-full" aria-hidden="true">
                        @foreach ($chartPoints as $idx => $pt)
                            <span
                                class="absolute -translate-x-1/2 text-center text-xs transition-colors duration-150"
                                :class="activeIdx === {{ $idx }} ? 'font-bold text-brand-700' : '{{ $loop->last ? 'font-bold text-brand-600' : 'text-slate-500 font-medium' }}'"
                                style="left: {{ $pt['pctX'] }}%;"
                            >
                                {{ $pt['label'] }}
                            </span>
                        @endforeach
                    </div>

                    <details class="mt-4 border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-500 transition-colors hover:text-slate-700">
                            Lihat data tabel
                        </summary>
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
                                        <td class="py-1.5 text-right font-semibold font-mono">
                                            <x-ui.money :value="$day['total']" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </div>

                {{-- Bottom Method Payment Breakdown Box Items --}}
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 pt-3.5">
                    <span class="text-xs font-medium text-slate-500">Metode bayar 30 hari terakhir:</span>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($methodTotal > 0)
                            @foreach ($methodBreakdown as $method)
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200/70 bg-slate-50/70 px-2.5 py-1 text-xs font-medium text-slate-700 shadow-2xs">
                                    <span class="size-1.5 rounded-full {{ $method['bar_class'] }}"></span>
                                    {{ $method['method'] }} &middot; <span class="font-mono">{{ number_format($method['percentage'], 1, ',', '.') }}%</span>
                                </span>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-400">Belum ada pembayaran sah.</span>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            {{-- Donut Status Pembayaran --}}
            <x-ui.card padding="p-5 sm:p-6" class="flex flex-col">
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <x-lucide-pie-chart class="size-4.5 text-brand-600" />
                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">Status Pembayaran</h3>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">Seluruh transaksi aktif di sistem.</p>
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
                                <p class="text-2xl font-bold font-mono tabular-nums text-slate-900">{{ number_format($paymentTotal, 0, ',', '.') }}</p>
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
                                        <span class="text-xs font-bold font-mono text-slate-900">{{ number_format($segment['count'], 0, ',', '.') }}</span>
                                        <span class="w-10 text-xs font-mono tabular-nums text-slate-500">{{ number_format($segment['percentage'], 1, ',', '.') }}%</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <x-lucide-credit-card class="size-6" />
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
                    <div class="flex items-center gap-2">
                        <x-lucide-activity class="size-4.5 text-brand-600" />
                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean &amp; Status Operasional Cucian</h3>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">Distribusi status pengerjaan cucian di seluruh sistem.</p>
                </div>
                <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700">
                    <span>Lihat Seluruh Transaksi</span>
                    <x-lucide-arrow-right class="size-3.5" />
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                {{-- Diterima --}}
                <a
                    href="{{ route('admin.transactions.index', ['status' => 'Diterima']) }}"
                    class="group block rounded-xl border border-sky-100 bg-sky-50/50 p-4 transition hover:border-sky-300 hover:bg-sky-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-sky-800 transition group-hover:text-sky-900">Diterima</span>
                        <span class="inline-flex size-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold font-mono tabular-nums text-sky-900">{{ $statusCounts['diterima'] ?? 0 }}</p>
                        <x-lucide-inbox class="size-4 text-sky-600 transition-transform group-hover:scale-110" />
                    </div>
                    <p class="mt-1 text-xs text-sky-700">Antrean masuk baru</p>
                </a>

                {{-- Diproses --}}
                <a
                    href="{{ route('admin.transactions.index', ['status' => 'Diproses']) }}"
                    class="group block rounded-xl border border-amber-100 bg-amber-50/50 p-4 transition hover:border-amber-300 hover:bg-amber-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-amber-800 transition group-hover:text-amber-900">Diproses</span>
                        <span class="inline-flex size-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold font-mono tabular-nums text-amber-900">{{ $statusCounts['diproses'] ?? 0 }}</p>
                        <x-lucide-refresh-cw class="size-4 text-amber-600 transition-transform group-hover:rotate-45" />
                    </div>
                    <p class="mt-1 text-xs text-amber-700">Sedang dicuci &amp; disetrika</p>
                </a>

                {{-- Selesai --}}
                <a
                    href="{{ route('admin.transactions.index', ['status' => 'Selesai']) }}"
                    class="group block rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-emerald-800 transition group-hover:text-emerald-900">Selesai</span>
                        <span class="inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold font-mono tabular-nums text-emerald-900">{{ $statusCounts['selesai'] ?? 0 }}</p>
                        <x-lucide-check-circle-2 class="size-4 text-emerald-600 transition-transform group-hover:scale-110" />
                    </div>
                    <p class="mt-1 text-xs text-emerald-700">Siap diambil pelanggan</p>
                </a>

                {{-- Diambil --}}
                <a
                    href="{{ route('admin.transactions.index', ['status' => 'Diambil']) }}"
                    class="group block rounded-xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-slate-300 hover:bg-slate-100/80"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-700 transition group-hover:text-slate-900">Diambil</span>
                        <span class="inline-flex size-2 rounded-full bg-slate-400"></span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between gap-2">
                        <p class="text-2xl font-bold font-mono tabular-nums text-slate-900">{{ $statusCounts['diambil'] ?? 0 }}</p>
                        <x-lucide-package-check class="size-4 text-slate-500 transition-transform group-hover:scale-110" />
                    </div>
                    <p class="mt-1 text-xs text-slate-600">Selesai diserahkan</p>
                </a>
            </div>
        </div>

        {{-- Grid Konten 2 Kolom: Transaksi Terkini & Katalog Layanan --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri: Tabel Transaksi Terbaru + Activity Feed (2/3 width) --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Card Tabel Transaksi Terbaru --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <x-lucide-receipt class="size-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Transaksi Terbaru</h3>
                                <p class="text-xs text-slate-500">Riwayat penerimaan laundry terbaru, 5 per halaman.</p>
                            </div>
                        </div>
                        <a
                            href="{{ route('admin.transactions.index') }}"
                            class="flex items-center gap-1 text-xs font-semibold text-brand-600 transition hover:text-brand-700"
                        >
                            <span>Semua Transaksi</span>
                            <x-lucide-arrow-right class="size-3.5" />
                        </a>
                    </div>

                    @if ($recentTransactions->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="flex size-12 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                <x-lucide-receipt class="size-6" />
                            </div>
                            <h4 class="mt-3 text-sm font-bold text-slate-700">Belum Ada Transaksi</h4>
                            <p class="mt-1 text-xs text-slate-500">Gunakan kasir POS untuk menginput penerimaan laundry baru.</p>
                            <x-ui.button :href="route('admin.transactions.create')" size="sm" class="mt-4">
                                <x-lucide-plus class="size-4" aria-hidden="true" />
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
                                                <p class="font-mono text-xs text-slate-500">{{ $trx->customer?->phone ?? '-' }}</p>
                                            </td>
                                            <td class="py-3 font-bold font-mono text-slate-900">
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
                                                        <x-lucide-eye class="size-3.5" />
                                                        <span>Detail</span>
                                                    </x-ui.button>
                                                    <x-ui.button
                                                        :href="route('admin.transactions.print', $trx)"
                                                        variant="secondary"
                                                        size="xs"
                                                        target="_blank"
                                                        rel="noopener"
                                                        title="Cetak Struk"
                                                    >
                                                        <x-lucide-printer class="size-3.5" />
                                                        <span>Cetak</span>
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

                {{-- Activity Feed Subcomponent --}}
                <x-dashboard.activity-feed :activities="$recentActivities" prefix="admin" />
            </div>

            {{-- Kolom Kanan: Top Layanan, Master Layanan Aktif & Pelanggan Baru (1/3 width) --}}
            <div class="space-y-6 lg:col-span-1">
                {{-- Card Layanan Terlaris 30 Hari --}}
                <x-ui.card padding="p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <x-lucide-trophy class="size-4 text-brand-600" />
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Layanan Terlaris</h3>
                                <p class="text-xs text-slate-500">Kontribusi 30 hari terakhir.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700">
                            <span>Transaksi</span>
                            <x-lucide-arrow-right class="size-3" />
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
                                        <p class="shrink-0 text-xs font-bold font-mono tabular-nums text-slate-700">
                                            {{ number_format($service->quantity, 1, ',', '.') }} kg
                                        </p>
                                    </div>
                                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-brand-500 transition-all duration-300" style="width: {{ $service->share }}%"></div>
                                    </div>
                                    <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                        <span>Pendapatan</span>
                                        <span class="font-bold font-mono text-brand-700">
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
                        <div class="flex items-center gap-2">
                            <x-lucide-sparkles class="size-4 text-brand-600" />
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Katalog Layanan</h3>
                                <p class="text-xs text-slate-500">Tarif aktif per kilogram.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.services.index') }}" class="flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700">
                            <span>Kelola</span>
                            <x-lucide-arrow-right class="size-3" />
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($activeServices as $service)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition hover:border-slate-200 hover:bg-slate-50">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $service->name }}</h4>
                                    <p class="text-xs text-slate-500">Estimasi {{ $service->estimated_duration }} Jam</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold font-mono text-brand-700">
                                        Rp {{ number_format($service->price, 0, ',', '.') }}
                                    </p>
                                    <span class="text-xs text-slate-400">/ kg</span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-500">Belum ada layanan aktif.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <x-ui.button :href="route('admin.services.index')" variant="soft" size="sm" class="w-full">
                            <x-lucide-plus class="size-4" aria-hidden="true" />
                            <span>Tambah / Kelola Master Layanan</span>
                        </x-ui.button>
                    </div>
                </div>

                {{-- New Customers Subcomponent --}}
                <x-dashboard.new-customers :customers="$recentCustomers" prefix="admin" />
            </div>
        </div>

        {{-- Catatan Operasional Eksekutif --}}
        <div class="flex items-start gap-3 rounded-2xl border border-brand-200/80 bg-gradient-to-r from-brand-50/70 via-white to-brand-50/30 px-5 py-4 shadow-xs">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                <x-lucide-shield class="size-4" aria-hidden="true" />
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900">Hak Akses Penuh Administrator</h4>
                <p class="mt-1 text-xs leading-relaxed text-slate-600">
                    Administrator memiliki otorisasi penuh untuk mengelola master katalog layanan, memantau riwayat transaksi seluruh kasir, mencetak struk thermal, serta memverifikasi data operasional laundry.
                </p>
            </div>
        </div>
    </div>
</x-layouts::app>
