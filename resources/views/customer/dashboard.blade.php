<x-layouts::app title="Dashboard Pelanggan">
    <div class="space-y-6">
        {{-- Banner Selamat Datang Pelanggan --}}
        <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/40 p-6 shadow-xs">
            <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                            <span class="size-1.5 rounded-full bg-brand-600 animate-pulse"></span>
                            Pelanggan CUCI.IN
                        </span>
                        @if ($stats['active_count'] > 0)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 border border-amber-200/60">
                                {{ $stats['active_count'] }} Cucian Sedang Diproses
                            </span>
                        @endif
                    </div>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm max-w-xl">
                        Pantau status pengerjaan pakaian laundry Anda secara langsung serta cek riwayat transaksi dan rincian nota kapan saja.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a
                        href="{{ route('customer.transactions.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20"
                    >
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Semua Riwayat Transaksi</span>
                    </a>
                    <a
                        href="{{ route('transactions.lookup') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <svg class="size-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cek Kode Nota Fisik</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Kartu 1: Cucian Berjalan --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Cucian Berjalan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-sky-50 text-sky-700 shrink-0">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-extrabold text-brand-700 tracking-tight">{{ $stats['active_count'] }}</p>
                    <span class="text-xs text-slate-500 font-medium">Nota Aktif</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Sedang diproses di workshop</p>
            </div>

            {{-- Kartu 2: Menunggu Pelunasan --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Menunggu Pelunasan</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700 shrink-0">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold {{ $stats['unpaid_amount'] > 0 ? 'text-amber-800' : 'text-slate-800' }} tracking-tight">
                        Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-1 text-xs text-slate-400">
                    {{ $stats['unpaid_amount'] > 0 ? 'Dapat dilunasi saat pengambilan' : 'Semua tagihan lunas' }}
                </p>
            </div>

            {{-- Kartu 3: Total Transaksi --}}
            <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Riwayat Transaksi</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 shrink-0">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $stats['total_count'] }}</p>
                    <span class="text-xs text-slate-500 font-medium">Nota Laundry</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Riwayat sejak akun terdaftar</p>
            </div>
        </div>

        {{-- Section Utama: Cucian Aktif & Status Pelacakan (FR-27) --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">Cucian Sedang Berjalan</h3>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">
                            {{ $activeTransactions->count() }} Aktif
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        Pantau proses pengerjaan cucian aktif Anda dari tahap Diterima hingga Selesai siap diambil.
                    </p>
                </div>
                @if ($activeTransactions->isNotEmpty())
                    <a
                        href="{{ route('customer.transactions.index') }}"
                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 self-start sm:self-auto"
                    >
                        <span>Lihat di Portal Transaksi</span>
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif
            </div>

            @if ($activeTransactions->isEmpty())
                {{-- State ketika tidak ada cucian yang sedang diproses --}}
                <div class="py-12 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="mt-3 text-sm font-semibold text-slate-900">Tidak Ada Cucian Aktif</h4>
                    <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                        Semua pakaian cucian Anda telah selesai diambil, atau Anda belum menyerahkan pakaian baru di gerai CUCI.IN.
                    </p>
                    <div class="mt-5 flex justify-center gap-3">
                        <a
                            href="{{ route('customer.transactions.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                        >
                            <span>Lihat Riwayat Sebelumnya</span>
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                {{-- Daftar Card Cucian Aktif dengan Stepper Visual Lifecycle --}}
                <div class="mt-5 space-y-4">
                    @foreach ($activeTransactions as $trx)
                        @php
                            $statusVal = $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : (string) $trx->status;
                            $paid = (float) $trx->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                            $remaining = max(0.0, (float) $trx->total_amount - $paid);
                            $isPaidOff = $remaining <= 0;

                            // Indeks tahap stepper: Diterima (1), Diproses (2), Selesai (3), Diambil (4)
                            $currentStep = match ($statusVal) {
                                'Diterima' => 1,
                                'Diproses' => 2,
                                'Selesai' => 3,
                                'Diambil' => 4,
                                default => 1,
                            };
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50/40 p-4 sm:p-5 transition hover:border-brand-200 hover:bg-white hover:shadow-xs">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/70 pb-3.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-slate-900 bg-white border border-slate-200 rounded-md px-2.5 py-1">
                                        {{ $trx->transaction_code }}
                                    </span>
                                    @if ($statusVal === 'Selesai')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                            <span class="size-1.5 rounded-full bg-emerald-600"></span>
                                            Siap Diambil di Gerai
                                        </span>
                                    @elseif ($statusVal === 'Diproses')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                            <span class="size-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                            Sedang Dicuci / Disetrika
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800">
                                            <span class="size-1.5 rounded-full bg-sky-600"></span>
                                            Diterima / Antrean
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span>Masuk: {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</span>
                                    @if ($trx->estimated_completed_at)
                                        <span class="text-slate-300">•</span>
                                        <span class="font-medium text-brand-700">Estimasi: {{ $trx->estimated_completed_at->translatedFormat('d M, H:i') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Visual Stepper Lifecycle Cucian (PRD BR-06) --}}
                            <div class="py-5 px-1 sm:px-4">
                                <div class="relative">
                                    {{-- Garis progress background --}}
                                    <div class="absolute left-6 right-6 top-3.5 h-1 -translate-y-1/2 bg-slate-200" aria-hidden="true"></div>
                                    {{-- Garis progress aktif --}}
                                    @php
                                        $progressWidth = match ($currentStep) {
                                            1 => '0%',
                                            2 => '50%',
                                            3 => '100%',
                                            default => '0%',
                                        };
                                    @endphp
                                    <div class="absolute left-6 top-3.5 h-1 -translate-y-1/2 bg-brand-600 transition-all duration-500" style="width: {{ $progressWidth }};" aria-hidden="true"></div>

                                    {{-- Titik-titik tahap --}}
                                    <div class="relative flex justify-between">
                                        {{-- Step 1: Diterima --}}
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 1 ? 'bg-brand-600 text-white ring-4 ring-brand-100' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                                @if ($currentStep > 1)
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    1
                                                @endif
                                            </div>
                                            <p class="mt-2 text-[11px] font-bold {{ $currentStep >= 1 ? 'text-slate-900' : 'text-slate-400' }}">Diterima</p>
                                            <p class="text-[10px] text-slate-400 hidden sm:block">Ditimbang di kasir</p>
                                        </div>

                                        {{-- Step 2: Diproses --}}
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 2 ? 'bg-brand-600 text-white ring-4 ring-brand-100' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                                @if ($currentStep > 2)
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    2
                                                @endif
                                            </div>
                                            <p class="mt-2 text-[11px] font-bold {{ $currentStep >= 2 ? 'text-slate-900' : 'text-slate-400' }}">Diproses</p>
                                            <p class="text-[10px] text-slate-400 hidden sm:block">Dicuci & disetrika</p>
                                        </div>

                                        {{-- Step 3: Selesai --}}
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 3 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                                @if ($currentStep >= 3)
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    3
                                                @endif
                                            </div>
                                            <p class="mt-2 text-[11px] font-bold {{ $currentStep >= 3 ? 'text-emerald-800' : 'text-slate-400' }}">Selesai</p>
                                            <p class="text-[10px] {{ $currentStep >= 3 ? 'text-emerald-600 font-medium' : 'text-slate-400' }} hidden sm:block">Siap diambil</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Card: Layanan, Status Pembayaran & Tombol Detail --}}
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-slate-200/70 pt-3 text-xs">
                                <div class="text-slate-600">
                                    <span class="font-medium text-slate-800">Layanan:</span>
                                    {{ $trx->details->map(fn($d) => ($d->service->name ?? 'Layanan').' ('.(($d->weight ?? 0) + 0).' kg)')->implode(', ') ?: 'Layanan Laundry' }}
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400">Status Pembayaran:</span>
                                        @if ($isPaidOff)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                                Lunas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                                Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>

                                    <a
                                        href="{{ route('customer.transactions.index', ['selected' => $trx->transaction_code]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-white border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition"
                                    >
                                        <span>Rincian Nota</span>
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Section Riwayat Transaksi Terbaru (5 Transaksi Terakhir) --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Pesanan Terbaru</h3>
                    <p class="text-xs text-slate-500">Daftar transaksi laundry terakhir Anda di sistem CUCI.IN</p>
                </div>
                <a
                    href="{{ route('customer.transactions.index') }}"
                    class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1"
                >
                    <span>Lihat Semua Riwayat ({{ $stats['total_count'] }})</span>
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-xs text-slate-500">Belum ada data transaksi yang tercatat.</p>
                </div>
            @else
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($recentTransactions as $transaction)
                        @php
                            $statusVal = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
                            $paid = (float) $transaction->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                            $remaining = max(0.0, (float) $transaction->total_amount - $paid);
                            $isPaidOff = $remaining <= 0;
                        @endphp
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between py-3.5 transition hover:bg-slate-50/70 rounded-lg px-2">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 shrink-0">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold font-mono text-slate-900">{{ $transaction->transaction_code }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 pl-12 sm:pl-0">
                                <div class="text-right">
                                    <p class="text-xs font-bold text-slate-900">Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</p>
                                    <span class="text-[10px] {{ $isPaidOff ? 'text-emerald-600 font-semibold' : 'text-amber-600 font-medium' }}">
                                        {{ $isPaidOff ? 'Lunas' : 'Belum Lunas' }}
                                    </span>
                                </div>

                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ match ($statusVal) {
                                    'Diterima' => 'bg-sky-50 text-sky-700 border border-sky-200/60',
                                    'Diproses' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                    'Selesai' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                    'Diambil' => 'bg-slate-100 text-slate-700 border border-slate-200/60',
                                    'Dibatalkan' => 'bg-rose-50 text-rose-700 border border-rose-200/60',
                                    default => 'bg-slate-100 text-slate-700',
                                } }}">
                                    {{ $statusVal }}
                                </span>

                                <a
                                    href="{{ route('customer.transactions.index', ['selected' => $transaction->transaction_code]) }}"
                                    class="text-xs font-semibold text-brand-600 hover:text-brand-700"
                                >
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
