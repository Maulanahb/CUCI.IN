<x-layouts::app title="Rincian Pengaduan">
    @php
        $statusBadge = match ($complaint->status) {
            'Menunggu' => 'bg-sky-50 border-sky-200 text-sky-800',
            'Diproses' => 'bg-amber-50 border-amber-200 text-amber-800',
            'Selesai' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            default => 'bg-slate-100 border-slate-200 text-slate-700',
        };

        $isPastDeadline = $complaint->status !== 'Selesai' && now()->gt($complaint->deadline_at);
        $trx = $complaint->transaction;
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1" aria-label="Breadcrumb">
                <span class="text-brand-700 font-semibold">CUCI.IN</span>
                <span class="text-slate-300">/</span>
                <a href="{{ route('customer.complaints.index') }}" class="hover:text-slate-700 transition">Pengaduan</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-700 font-medium">{{ $complaint->complaint_code }}</span>
            </nav>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-2xl font-bold tracking-tight text-slate-900">{{ $complaint->complaint_code }}</p>
                    <span class="inline-block rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                        {{ $complaint->status }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('customer.complaints.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-slate-300 hover:bg-slate-100"
                    >
                        <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        <span>Kembali ke Daftar</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Kartu Ringkasan Batas SLA --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Waktu Pengajuan</p>
                <p class="mt-1.5 text-base font-bold text-slate-900">
                    {{ $complaint->created_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $complaint->created_at->diffForHumans() }}</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Batas Waktu Penanganan (SLA 2 Hari)</p>
                <p class="mt-1.5 text-base font-bold text-slate-900">
                    {{ $complaint->deadline_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
                @if ($complaint->status === 'Selesai')
                    <p class="text-xs text-emerald-600 font-semibold mt-0.5">Tuntas ditangani</p>
                @elseif ($isPastDeadline)
                    <p class="text-xs text-rose-600 font-semibold mt-0.5">Melewati batas SLA penanganan</p>
                @else
                    <p class="text-xs text-amber-700 font-medium mt-0.5">Target penyelesaian: {{ $complaint->deadline_at->diffForHumans() }}</p>
                @endif
            </div>
        </div>

        {{-- Kartu Isi Pengaduan --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-xs space-y-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Subjek Keluhan</p>
                <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $complaint->subject }}</h3>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rincian & Kronologi Kendala</p>
                <div class="mt-2 rounded-xl bg-slate-50/70 p-4 border border-slate-100 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $complaint->description }}
                </div>
            </div>
        </div>

        {{-- Kartu Respons Staf / Tindak Lanjut --}}
        <div class="rounded-2xl border @if($complaint->response) border-emerald-200/90 bg-emerald-50/30 @else border-slate-200/80 bg-white @endif p-6 sm:p-7 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex size-7 items-center justify-center rounded-lg @if($complaint->response) bg-emerald-100 text-emerald-700 @else bg-slate-100 text-slate-500 @endif">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Tanggapan Resmi Tim Staf CUCI.IN</h3>
                </div>

                @if ($complaint->handled_at)
                    <span class="text-xs text-slate-500">
                        Ditangani: {{ $complaint->handled_at->translatedFormat('d M Y, H:i') }} WIB
                    </span>
                @endif
            </div>

            @if ($complaint->response)
                <div class="mt-2 rounded-xl bg-white p-4 border border-emerald-100 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line shadow-2xs">
                    {{ $complaint->response }}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-1">
                    <span>Ditindaklanjuti oleh:</span>
                    <strong class="text-slate-800 font-semibold">{{ $complaint->handler?->name ?? 'Staf Operasional' }}</strong>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
                    <p class="text-xs sm:text-sm font-medium text-slate-600">
                        Pengaduan Anda sedang menunggu pemeriksaan tim operasional.
                    </p>
                    <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                        Staf kami akan memeriksa fisik pakaian atau data pengerjaan workshop dan memberikan solusi maksimal dalam 2 hari kerja.
                    </p>
                </div>
            @endif
        </div>

        {{-- Kartu Ringkasan Nota Transaksi Terkait --}}
        @if ($trx)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nota Transaksi Terkait</p>
                        <h4 class="mt-0.5 text-base font-bold text-slate-900 font-mono">{{ $trx->transaction_code }}</h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('customer.transactions.index', ['selected' => $trx->transaction_code]) }}"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800"
                        >
                            <span>Buka Riwayat Nota Ini</span>
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 text-xs">
                    <div>
                        <span class="text-slate-500">Tanggal Transaksi</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $trx->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">Status Cucian</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : $trx->status }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">Estimasi Selesai</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $trx->estimated_completed_at ? $trx->estimated_completed_at->translatedFormat('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">Total Tagihan</span>
                        <p class="font-bold text-slate-900 mt-0.5">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>

                @if ($trx->details->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Item Layanan</p>
                        <ul class="divide-y divide-slate-100 text-xs text-slate-700">
                            @foreach ($trx->details as $detail)
                                <li class="py-1.5 flex justify-between items-center">
                                    <span>{{ $detail->service?->name ?? 'Layanan' }} ({{ number_format($detail->weight, 1) }} kg)</span>
                                    <span class="font-semibold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>
