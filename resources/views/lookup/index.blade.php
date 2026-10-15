<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cek Transaksi — {{ config('app.name', 'CUCI.IN') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50/70 font-sans text-slate-800 antialiased flex flex-col justify-between">
    {{-- Header / Navbar Publik --}}
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('transactions.lookup') }}" class="flex items-center gap-2.5">
                <x-app-logo class="size-8 drop-shadow-2xs" />
                <span class="text-lg font-bold tracking-tight text-brand-700">CUCI<span class="text-brand-500">.IN</span></span>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-50 px-3.5 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-100/80"
                    >
                        <span>Dashboard</span>
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <span>Masuk Sistem</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-8">
        {{-- Hero & Deskripsi --}}
        <div class="text-center max-w-xl mx-auto space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100/70 px-3 py-1 text-xs font-semibold text-brand-800">
                <svg class="size-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Pelacakan Cucian Publik
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Cek Status Cucian Anda
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Masukkan kode transaksi laundry untuk mengetahui progres pengerjaan, estimasi selesai, dan status pembayaran nota Anda.
            </p>
        </div>

        {{-- Form Pencarian Nota --}}
        <div class="max-w-2xl mx-auto rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-sm">
            <form method="GET" action="{{ route('transactions.lookup') }}" class="space-y-3">
                <label for="code" class="block text-xs font-semibold text-slate-700">
                    Masukkan Kode Transaksi
                </label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ $code }}"
                            required
                            autofocus
                            placeholder="Contoh: TRX-0001 atau TRK-20261025-046"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-10 pr-4 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 uppercase tracking-wider focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cek Transaksi</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400">
                    *Kode transaksi tercantum pada struk fisik atau pesan konfirmasi yang diberikan saat penyerahan pakaian.
                </p>
            </form>
        </div>

        {{-- Hasil Pencarian: Data Tidak Ditemukan --}}
        @if ($searched && ! $transaction)
            <div class="max-w-2xl mx-auto rounded-2xl border border-rose-200 bg-rose-50/80 p-5 text-center space-y-2">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-rose-900">Transaksi Tidak Ditemukan</h3>
                <p class="text-xs text-rose-700 max-w-md mx-auto">
                    Tidak ditemukan data transaksi dengan kode <strong class="font-bold">{{ $code }}</strong>. Silakan periksa kembali ketikan nomor nota Anda atau tanyakan ke kasir CUCI.IN.
                </p>
            </div>
        @endif

        {{-- Hasil Pencarian: Data Ditemukan --}}
        @if ($transaction)
            @php
                $statusOrder = ['Diterima' => 1, 'Diproses' => 2, 'Selesai' => 3, 'Diambil' => 4];
                $currentStep = $statusOrder[$transaction->status->value] ?? 1;
                $isCanceled = $transaction->status->value === 'Dibatalkan';
            @endphp

            <div class="max-w-3xl mx-auto space-y-6">
                {{-- 1. Stepper Timeline Status Laundry --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Cucian</span>
                            <div class="mt-0.5 flex items-center gap-2">
                                <h2 class="text-lg font-extrabold text-slate-900">{{ $transaction->status->value }}</h2>
                                @if ($isCanceled)
                                    <span class="rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 uppercase">Dibatalkan</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Estimasi Selesai</span>
                            <p class="text-xs sm:text-sm font-bold text-slate-800">
                                {{ $transaction->estimated_completed_at ? $transaction->estimated_completed_at->translatedFormat('d F Y, H:i') . ' WIB' : 'Menunggu update kasir' }}
                            </p>
                        </div>
                    </div>

                    {{-- Visual Stepper --}}
                    @if (! $isCanceled)
                        <div class="pt-6 pb-2">
                            <div class="grid grid-cols-4 relative">
                                {{-- Connecting Line --}}
                                <div class="absolute top-4 left-[12.5%] right-[12.5%] h-0.5 bg-slate-200 -z-0">
                                    <div
                                        class="h-full bg-brand-600 transition-all duration-500"
                                        style="width: {{ match($currentStep) { 1 => '0%', 2 => '33.33%', 3 => '66.66%', 4 => '100%', default => '0%' } }};"
                                    ></div>
                                </div>

                                {{-- Step 1: Diterima --}}
                                <div class="flex flex-col items-center text-center relative z-10">
                                    <div @class([
                                        'size-8 rounded-full flex items-center justify-center text-xs font-bold transition',
                                        'bg-brand-600 text-white ring-4 ring-brand-100' => $currentStep >= 1,
                                        'bg-slate-200 text-slate-500' => $currentStep < 1,
                                    ])>
                                        1
                                    </div>
                                    <span @class([
                                        'mt-2 text-xs font-semibold',
                                        'text-brand-900 font-bold' => $currentStep === 1,
                                        'text-slate-700' => $currentStep > 1,
                                        'text-slate-400' => $currentStep < 1,
                                    ])>
                                        Diterima
                                    </span>
                                </div>

                                {{-- Step 2: Diproses --}}
                                <div class="flex flex-col items-center text-center relative z-10">
                                    <div @class([
                                        'size-8 rounded-full flex items-center justify-center text-xs font-bold transition',
                                        'bg-brand-600 text-white ring-4 ring-brand-100' => $currentStep >= 2,
                                        'bg-slate-200 text-slate-500' => $currentStep < 2,
                                    ])>
                                        2
                                    </div>
                                    <span @class([
                                        'mt-2 text-xs font-semibold',
                                        'text-brand-900 font-bold' => $currentStep === 2,
                                        'text-slate-700' => $currentStep > 2,
                                        'text-slate-400' => $currentStep < 2,
                                    ])>
                                        Diproses
                                    </span>
                                </div>

                                {{-- Step 3: Selesai --}}
                                <div class="flex flex-col items-center text-center relative z-10">
                                    <div @class([
                                        'size-8 rounded-full flex items-center justify-center text-xs font-bold transition',
                                        'bg-brand-600 text-white ring-4 ring-brand-100' => $currentStep >= 3,
                                        'bg-slate-200 text-slate-500' => $currentStep < 3,
                                    ])>
                                        3
                                    </div>
                                    <span @class([
                                        'mt-2 text-xs font-semibold',
                                        'text-brand-900 font-bold' => $currentStep === 3,
                                        'text-slate-700' => $currentStep > 3,
                                        'text-slate-400' => $currentStep < 3,
                                    ])>
                                        Selesai
                                    </span>
                                </div>

                                {{-- Step 4: Diambil --}}
                                <div class="flex flex-col items-center text-center relative z-10">
                                    <div @class([
                                        'size-8 rounded-full flex items-center justify-center text-xs font-bold transition',
                                        'bg-brand-600 text-white ring-4 ring-brand-100' => $currentStep >= 4,
                                        'bg-slate-200 text-slate-500' => $currentStep < 4,
                                    ])>
                                        4
                                    </div>
                                    <span @class([
                                        'mt-2 text-xs font-semibold',
                                        'text-brand-900 font-bold' => $currentStep === 4,
                                        'text-slate-400' => $currentStep < 4,
                                    ])>
                                        Diambil
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 2. Detail Rincian Nota & Pelanggan --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs space-y-6">
                    {{-- Header Info Transaksi --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode Transaksi</span>
                            <div class="mt-0.5 flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 font-mono">{{ $transaction->transaction_code }}</h3>
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-700">Terdaftar</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Customer</span>
                            <p class="mt-0.5 text-xs sm:text-sm font-bold text-slate-800">
                                {{ $transaction->customer?->name ?? 'Pelanggan Umum' }}
                            </p>
                        </div>
                    </div>

                    {{-- Tabel Layanan & Berat --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Detail Layanan & Berat</h4>
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50/70 border-b border-slate-100 text-[10px] font-bold uppercase text-slate-400">
                                    <tr>
                                        <th class="p-3">Layanan</th>
                                        <th class="p-3">Berat</th>
                                        <th class="p-3">Tarif</th>
                                        <th class="p-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse ($transaction->details as $detail)
                                        <tr>
                                            <td class="p-3 font-semibold text-slate-900">
                                                {{ $detail->service?->name ?? 'Layanan Laundry' }}
                                                @if ($detail->service?->description)
                                                    <p class="text-[10px] text-slate-400 font-normal">{{ $detail->service->description }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3">{{ (float) $detail->weight }} kg</td>
                                            <td class="p-3 text-slate-500">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                            <td class="p-3 text-right font-bold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-4 text-center text-xs text-slate-400">
                                                Detail layanan belum dimasukkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Ringkasan Pembayaran & Total --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600">Total Transaksi</span>
                            <span class="text-base sm:text-lg font-extrabold text-slate-900">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200/60 pt-2.5">
                            <span class="text-xs font-semibold text-slate-600">Status Pembayaran</span>
                            <div>
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-bold uppercase',
                                    'bg-emerald-100 text-emerald-800' => $paymentSummary['status'] === 'Lunas',
                                    'bg-amber-100 text-amber-800' => $paymentSummary['status'] === 'DP',
                                    'bg-rose-100 text-rose-800' => $paymentSummary['status'] === 'Belum Bayar',
                                ])>
                                    {{ $paymentSummary['status'] }}
                                </span>
                            </div>
                        </div>

                        @if ($paymentSummary['status'] === 'DP')
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>Sudah Dibayar:</span>
                                <span class="font-semibold text-slate-700">Rp {{ number_format($paymentSummary['total_paid'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-rose-600 font-semibold">
                                <span>Sisa Pembayaran:</span>
                                <span>Rp {{ number_format($paymentSummary['remaining_amount'], 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if ($paymentSummary['latest_payment'])
                            <div class="text-[11px] text-slate-400 border-t border-slate-200/40 pt-2">
                                Metode: <strong class="text-slate-600">{{ $paymentSummary['latest_payment']->method }}</strong>
                                • Dibayar: {{ $paymentSummary['latest_payment']->paid_at ? $paymentSummary['latest_payment']->paid_at->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tombol Bantuan / Cari Lagi --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                    <a
                        href="{{ route('transactions.lookup') }}"
                        class="inline-flex items-center gap-1.5 font-semibold text-brand-700 hover:text-brand-800 transition"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Cari Transaksi Lain</span>
                    </a>
                    <span>Ada kendala cucian? Hubungi outlet laundry CUCI.IN terdekat.</span>
                </div>
            </div>
        @endif
    </main>

    {{-- Footer Publik --}}
    <footer class="border-t border-slate-200/80 bg-white py-6 text-center text-xs text-slate-400">
        <div class="max-w-4xl mx-auto px-4">
            &copy; {{ date('Y') }} CUCI.IN — Sistem Manajemen Operasional Laundry.
        </div>
    </footer>
</body>
</html>
