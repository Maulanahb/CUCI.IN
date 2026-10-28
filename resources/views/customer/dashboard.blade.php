<x-layouts::app title="Dashboard Pelanggan">
    <div class="space-y-6">
        {{-- Banner Sapaan Pelanggan & Aksi Cepat --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Selamat Datang, {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-600 max-w-xl">
                        Pantau status pengerjaan pakaian laundry Anda secara langsung serta cek riwayat transaksi dan rincian nota kapan saja.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <x-ui.button :href="route('customer.transactions.index')" size="md">
                        <x-lucide-receipt class="size-4" />
                        <span>Semua Riwayat Transaksi</span>
                    </x-ui.button>
                    <x-ui.button :href="route('transactions.lookup')" variant="secondary" size="md">
                        <x-lucide-search class="size-4" />
                        <span>Cek Kode Nota Fisik</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Kartu Ringkasan Statistik Monolitik (FR-27) --}}
        <section aria-label="Ringkasan akun Anda" class="grid grid-cols-2 overflow-hidden rounded-2xl border border-slate-200 bg-white lg:grid-cols-4">
            {{-- Kartu 1: Cucian Berjalan --}}
            <div class="border-b border-r border-slate-200 p-5 sm:p-6 lg:border-b-0">
                <p class="text-sm font-medium text-slate-600">Cucian berjalan</p>
                <div class="mt-4 flex items-baseline gap-2">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-brand-700 sm:text-4xl">
                        {{ $stats['active_count'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Nota Aktif</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Sedang diproses di workshop</p>
            </div>

            {{-- Kartu 2: Menunggu Pelunasan --}}
            <div class="border-b border-slate-200 p-5 sm:p-6 lg:border-b-0 lg:border-r">
                <p class="text-sm font-medium text-slate-600">Menunggu pelunasan</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight tabular-nums {{ $stats['unpaid_amount'] > 0 ? 'text-amber-800' : 'text-slate-900' }} sm:text-4xl">
                    <x-ui.money :value="$stats['unpaid_amount']" />
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ $stats['unpaid_amount'] > 0 ? 'Dapat dilunasi saat pengambilan' : 'Semua tagihan lunas' }}
                </p>
            </div>

            {{-- Kartu 3: Total Transaksi --}}
            <div class="border-r border-slate-200 p-5 sm:p-6">
                <p class="text-sm font-medium text-slate-600">Total riwayat nota</p>
                <div class="mt-4 flex items-baseline gap-2">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums text-slate-900 sm:text-4xl">
                        {{ $stats['total_count'] }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Nota Laundry</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Riwayat sejak akun terdaftar</p>
            </div>

            {{-- Kartu 4: Pengaduan Aktif (FR-27) --}}
            <a
                href="{{ route('customer.complaints.index') }}"
                class="block p-5 sm:p-6 transition hover:bg-slate-50"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-600">Pengaduan aktif</p>
                    <x-lucide-arrow-right class="size-4 text-slate-400" />
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums {{ ($stats['active_complaints_count'] ?? 0) > 0 ? 'text-rose-700' : 'text-slate-900' }} sm:text-4xl">
                        {{ $stats['active_complaints_count'] ?? 0 }}
                    </p>
                    <span class="text-xs font-medium text-slate-500">Tiket Kendala</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    {{ ($stats['active_complaints_count'] ?? 0) > 0 ? 'Sedang ditangani tim staf' : 'Tidak ada komplain aktif' }}
                </p>
            </a>
        </section>

        {{-- Banner Notifikasi Jika Ada Tiket Komplain Ditangani (FR-27) --}}
        @if (!empty($activeComplaints) && $activeComplaints->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                            <x-lucide-alert-triangle class="size-5" aria-hidden="true" />
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-rose-900">
                                Anda Memiliki {{ $activeComplaints->count() }} Pengaduan Cucian yang Sedang Ditangani
                            </h4>
                            <p class="text-sm text-rose-700/90 mt-0.5">
                                Tiket terbaru: <span class="font-mono font-semibold">{{ $activeComplaints->first()->complaint_code }}</span> &mdash;
                                {{ $activeComplaints->first()->subject }} (Status: {{ $activeComplaints->first()->status }})
                            </p>
                        </div>
                    </div>
                    <a
                        href="{{ route('customer.complaints.show', $activeComplaints->first()) }}"
                        class="inline-flex min-h-11 items-center gap-2 self-start sm:self-auto rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700"
                    >
                        <span>Lihat Respons Petugas</span>
                        <x-lucide-arrow-right class="size-4" aria-hidden="true" />
                    </a>
                </div>
            </div>
        @endif

        {{-- Section Utama: Cucian Aktif & Status Pelacakan (FR-27) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <x-lucide-activity class="size-4.5 text-brand-700" />
                        <h2 class="text-lg font-semibold tracking-tight text-slate-900">Cucian Sedang Berjalan</h2>
                        <x-ui.badge tone="brand" size="xs">
                            {{ $activeTransactions->count() }} Aktif
                        </x-ui.badge>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        Pantau proses pengerjaan cucian aktif Anda dari tahap Diterima hingga Selesai siap diambil.
                    </p>
                </div>
                @if ($activeTransactions->isNotEmpty())
                    <a
                        href="{{ route('customer.transactions.index') }}"
                        class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-1 self-start sm:self-auto"
                    >
                        <span>Lihat di Portal Transaksi</span>
                        <x-lucide-arrow-right class="size-4" />
                    </a>
                @endif
            </div>

            @if ($activeTransactions->isEmpty())
                {{-- State ketika tidak ada cucian yang sedang diproses --}}
                <div class="py-12 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <x-lucide-check-circle-2 class="size-6" />
                    </div>
                    <h3 class="mt-3 text-sm font-semibold text-slate-900">Tidak Ada Cucian Aktif</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                        Semua pakaian cucian Anda telah selesai diambil, atau Anda belum menyerahkan pakaian baru di gerai CUCI.IN.
                    </p>
                    <div class="mt-5 flex justify-center gap-3">
                        <x-ui.button :href="route('customer.transactions.index')" size="sm" class="shadow-xs">
                            <span>Lihat Riwayat Sebelumnya</span>
                            <x-lucide-arrow-right class="size-3.5" />
                        </x-ui.button>
                    </div>
                </div>
            @else
                {{-- Daftar Card Cucian Aktif dengan Stepper Visual Lifecycle --}}
                <div class="mt-5 space-y-4">
                    @foreach ($activeTransactions as $trx)
                        @php
                            $statusVal = $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : (string) $trx->status;
                            $paid = $trx->total_paid;
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
                                    <span class="font-mono text-sm font-bold text-slate-900 bg-white border border-slate-200 rounded-lg px-2.5 py-1 shadow-2xs">
                                        {{ $trx->transaction_code }}
                                    </span>
                                    @if ($statusVal === 'Selesai')
                                        <x-ui.badge tone="success" size="sm" dot>
                                            Siap Diambil di Gerai
                                        </x-ui.badge>
                                    @elseif ($statusVal === 'Diproses')
                                        <x-ui.badge tone="warning" size="sm" dot>
                                            Sedang Dicuci / Disetrika
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge tone="info" size="sm" dot>
                                            Diterima / Antrean
                                        </x-ui.badge>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-sm text-slate-500 font-mono">
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
                                    <div class="absolute left-6 top-3.5 h-1 -translate-y-1/2 bg-brand-700 transition-all duration-500" style="width: {{ $progressWidth }};" aria-hidden="true"></div>

                                    {{-- Titik-titik tahap --}}
                                    <div class="relative flex justify-between">
                                        {{-- Step 1: Diterima --}}
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 1 ? 'bg-brand-700 text-white ring-4 ring-brand-100' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                                @if ($currentStep > 1)
                                                    <x-lucide-check class="size-4" />
                                                @else
                                                    1
                                                @endif
                                            </div>
                                            <p class="mt-2 text-xs font-bold {{ $currentStep >= 1 ? 'text-slate-900' : 'text-slate-500' }}">Diterima</p>
                                            <p class="text-xs text-slate-500 hidden sm:block">Ditimbang di kasir</p>
                                        </div>

                                        {{-- Step 2: Diproses --}}
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 2 ? 'bg-brand-700 text-white ring-4 ring-brand-100' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                                @if ($currentStep > 2)
                                                    <x-lucide-check class="size-4" />
                                                @else
                                                    2
                                                @endif
                                            </div>
                                            <p class="mt-2 text-xs font-bold {{ $currentStep >= 2 ? 'text-slate-900' : 'text-slate-500' }}">Diproses</p>
                                            <p class="text-xs text-slate-500 hidden sm:block">Dicuci &amp; disetrika</p>
                                        </div>

                                        {{-- Step 3: Selesai --}}
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 3 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-200' }} text-xs font-bold transition">
                                                <span class="{{ $currentStep >= 3 ? '' : 'text-slate-500' }}">
                                                    @if ($currentStep >= 3)
                                                        <x-lucide-check class="size-4" />
                                                    @else
                                                        3
                                                    @endif
                                                </span>
                                            </div>
                                            <p class="mt-2 text-xs font-bold {{ $currentStep >= 3 ? 'text-emerald-800' : 'text-slate-500' }}">Selesai</p>
                                            <p class="text-xs {{ $currentStep >= 3 ? 'text-emerald-700 font-medium' : 'text-slate-500' }} hidden sm:block">Siap diambil</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Card: Layanan, Status Pembayaran & Tombol Detail --}}
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-slate-200/70 pt-3 text-sm">
                                <div class="text-slate-600">
                                    <span class="font-medium text-slate-800">Layanan:</span>
                                    <span class="font-semibold text-slate-900">
                                        {{ $trx->details->map(fn($d) => ($d->service->name ?? 'Layanan').' ('.(($d->weight ?? 0) + 0).' kg)')->implode(', ') ?: 'Layanan Laundry' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-500">Status Pembayaran:</span>
                                        @if ($isPaidOff)
                                            <x-ui.badge tone="success" size="sm">
                                                Lunas
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge tone="warning" size="sm" class="font-mono tabular-nums">
                                                Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}
                                            </x-ui.badge>
                                        @endif
                                    </div>

                                    <a
                                        href="{{ route('customer.transactions.index', ['selected' => $trx->transaction_code]) }}"
                                        class="inline-flex min-h-11 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:bg-slate-50 hover:text-brand-700"
                                    >
                                        <span>Rincian Nota</span>
                                        <x-lucide-arrow-right class="size-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Section Riwayat Transaksi Terbaru (5 Transaksi Terakhir) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-semibold tracking-tight text-slate-900">Riwayat Pesanan Terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar transaksi laundry terakhir Anda di sistem CUCI.IN</p>
                </div>
                <a
                    href="{{ route('customer.transactions.index') }}"
                    class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-1"
                >
                    <span>Lihat Semua Riwayat ({{ $stats['total_count'] }})</span>
                    <x-lucide-arrow-right class="size-4" />
                </a>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-sm text-slate-500">Belum ada data transaksi yang tercatat.</p>
                </div>
            @else
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($recentTransactions as $transaction)
                        @php
                            $statusVal = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
                            $paid = $transaction->total_paid;
                            $remaining = max(0.0, (float) $transaction->total_amount - $paid);
                            $isPaidOff = $remaining <= 0;
                        @endphp
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between py-3.5 transition hover:bg-slate-50/70 rounded-lg px-2">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 shrink-0">
                                    <x-lucide-receipt class="size-4.5" />
                                </div>
                                <div>
                                    <p class="text-sm font-mono font-semibold text-slate-900">{{ $transaction->transaction_code }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 pl-12 sm:pl-0">
                                <div class="text-right">
                                    <span class="text-sm font-semibold font-mono tabular-nums text-slate-900">Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</span>
                                    <div class="mt-1">
                                        <x-ui.payment-badge :status="$isPaidOff ? 'Lunas' : 'Belum Bayar'" size="sm" />
                                    </div>
                                </div>

                                <x-ui.status-badge :status="$transaction->status" size="sm" />

                                <a
                                    href="{{ route('customer.transactions.index', ['selected' => $transaction->transaction_code]) }}"
                                    class="inline-flex min-h-11 items-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50 hover:text-brand-800"
                                    aria-label="Lihat detail nota {{ $transaction->transaction_code }}"
                                >
                                    <span>Detail</span>
                                    <x-lucide-chevron-right class="size-4" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
