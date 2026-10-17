{{-- ============================================================
    SECTION UTAMA: CUCIAN AKTIF & STATUS PELACAKAN (LIFECYCLE STEPPER)
============================================================= --}}
<div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-slate-900">Cucian Sedang Berjalan</h3>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 tabular-nums">
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
                class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 self-start sm:self-auto cursor-pointer"
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
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition cursor-pointer"
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
                                    <span class="size-1.5 rounded-full bg-amber-600"></span>
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
                                    <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 1 ? 'bg-brand-600 text-white shadow-2xs' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                        @if ($currentStep > 1)
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            1
                                        @endif
                                    </div>
                                    <p class="mt-2 text-[11px] font-bold {{ $currentStep >= 1 ? 'text-slate-900' : 'text-slate-400' }}">Diterima</p>
                                    <p class="text-[10px] text-slate-500 hidden sm:block">Ditimbang di kasir</p>
                                </div>

                                {{-- Step 2: Diproses --}}
                                <div class="flex flex-col items-center text-center">
                                    <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 2 ? 'bg-brand-600 text-white shadow-2xs' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                        @if ($currentStep > 2)
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            2
                                        @endif
                                    </div>
                                    <p class="mt-2 text-[11px] font-bold {{ $currentStep >= 2 ? 'text-slate-900' : 'text-slate-400' }}">Diproses</p>
                                    <p class="text-[10px] text-slate-500 hidden sm:block">Dicuci & disetrika</p>
                                </div>

                                {{-- Step 3: Selesai --}}
                                <div class="flex flex-col items-center text-center">
                                    <div class="flex size-7 items-center justify-center rounded-full {{ $currentStep >= 3 ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-200 text-slate-500' }} text-xs font-bold transition">
                                        @if ($currentStep >= 3)
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            3
                                        @endif
                                    </div>
                                    <p class="mt-2 text-[11px] font-bold {{ $currentStep >= 3 ? 'text-emerald-800' : 'text-slate-400' }}">Selesai</p>
                                    <p class="text-[10px] {{ $currentStep >= 3 ? 'text-emerald-600 font-medium' : 'text-slate-500' }} hidden sm:block">Siap diambil</p>
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
                                <span class="text-slate-500">Status Pembayaran:</span>
                                @if ($isPaidOff)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 tabular-nums">
                                        Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ route('customer.transactions.index', ['selected' => $trx->transaction_code]) }}"
                                class="inline-flex items-center gap-1 rounded-lg bg-white border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition cursor-pointer"
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
