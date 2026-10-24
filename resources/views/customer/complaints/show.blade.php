@php
    $isPastDeadline = $complaint->status !== 'Selesai' && now()->gt($complaint->deadline_at);
    $trx = $complaint->transaction;

    // Hitung step aktif stepper (1: Menunggu, 2: Diproses, 3: Selesai)
    $currentStep = match ($complaint->status) {
        'Diproses' => 2,
        'Selesai' => 3,
        default => 1,
    };
@endphp

<x-layouts::app title="Rincian Pengaduan - {{ $complaint->complaint_code }}">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Breadcrumb & Header Halaman --}}
        <div>
            <nav class="mb-2 flex items-center gap-1.5 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
                <span class="font-semibold text-brand-700">CUCI.IN</span>
                <span class="text-slate-300">/</span>
                <a href="{{ route('customer.complaints.index') }}" class="transition hover:text-slate-800">Pengaduan</a>
                <span class="text-slate-300">/</span>
                <span class="font-mono font-medium text-slate-700">{{ $complaint->complaint_code }}</span>
            </nav>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        {{ $complaint->complaint_code }}
                    </h1>

                    @if ($complaint->status === 'Menunggu')
                        <x-ui.badge tone="info" size="md" dot>
                            Menunggu Penanganan
                        </x-ui.badge>
                    @elseif ($complaint->status === 'Diproses')
                        <x-ui.badge tone="warning" size="md" dot pulse>
                            Sedang Diproses Staf
                        </x-ui.badge>
                    @elseif ($complaint->status === 'Selesai')
                        <x-ui.badge tone="success" size="md" dot>
                            Tuntas Selesai
                        </x-ui.badge>
                    @else
                        <x-ui.badge tone="neutral" size="md">
                            {{ $complaint->status }}
                        </x-ui.badge>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.button :href="route('customer.complaints.index')" variant="secondary" size="sm">
                        <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                        <span>Kembali ke Daftar</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Signature Complaint Progress Stepper --}}
        <x-ui.card padding="p-5 sm:p-6">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                Tahapan Penanganan Tiket Keluhan
            </h2>

            <div class="relative">
                <div class="grid grid-cols-3 gap-2">
                    {{-- Langkah 1: Diajukan --}}
                    <div class="flex flex-col items-center text-center">
                        @if ($currentStep >= 1)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-brand-600 text-white font-bold shadow-xs ring-4 ring-brand-100">
                                <x-lucide-file-text class="size-5" aria-hidden="true" />
                            </div>
                        @else
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-slate-100 text-slate-400 font-bold">
                                <x-lucide-file-text class="size-5" aria-hidden="true" />
                            </div>
                        @endif
                        <p class="mt-2 text-xs font-bold text-slate-900">Laporan Diajukan</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $complaint->created_at->translatedFormat('d M, H:i') }}</p>
                    </div>

                    {{-- Langkah 2: Pemeriksaan Staf --}}
                    <div class="flex flex-col items-center text-center">
                        @if ($currentStep >= 2)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-amber-600 text-white font-bold shadow-xs ring-4 ring-amber-100">
                                <x-lucide-clock class="size-5" aria-hidden="true" />
                            </div>
                        @else
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-slate-100 text-slate-400 font-bold">
                                <x-lucide-clock class="size-5" aria-hidden="true" />
                            </div>
                        @endif
                        <p class="mt-2 text-xs font-bold text-slate-900">Pemeriksaan Tim</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            @if ($currentStep >= 2)
                                Sedang ditangani
                            @else
                                Dalam antrean
                            @endif
                        </p>
                    </div>

                    {{-- Langkah 3: Selesai & Solusi --}}
                    <div class="flex flex-col items-center text-center">
                        @if ($currentStep >= 3)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-emerald-600 text-white font-bold shadow-xs ring-4 ring-emerald-100">
                                <x-lucide-check-circle-2 class="size-5" aria-hidden="true" />
                            </div>
                        @else
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-slate-100 text-slate-400 font-bold">
                                <x-lucide-check-circle-2 class="size-5" aria-hidden="true" />
                            </div>
                        @endif
                        <p class="mt-2 text-xs font-bold text-slate-900">Tuntas & Solusi</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            @if ($currentStep >= 3)
                                {{ $complaint->handled_at?->translatedFormat('d M, H:i') ?? 'Selesai' }}
                            @else
                                Menunggu tanggapan
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Kartu Ringkasan Batas SLA --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-ui.card padding="p-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Waktu Pengajuan</p>
                    <x-lucide-calendar class="size-4 text-slate-400" aria-hidden="true" />
                </div>
                <p class="mt-2 text-base font-bold text-slate-900">
                    {{ $complaint->created_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
                <p class="mt-1 text-xs text-slate-500">{{ $complaint->created_at->diffForHumans() }}</p>
            </x-ui.card>

            <x-ui.card padding="p-5">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Batas Waktu Penanganan (SLA 2 Hari)</p>
                    <x-lucide-clock class="size-4 text-slate-400" aria-hidden="true" />
                </div>
                <p class="mt-2 text-base font-bold text-slate-900">
                    {{ $complaint->deadline_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
                @if ($complaint->status === 'Selesai')
                    <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-emerald-700">
                        <x-lucide-check-circle-2 class="size-3.5 text-emerald-600" aria-hidden="true" />
                        <span>Pengaduan tuntas ditangani tepat waktu</span>
                    </p>
                @elseif ($isPastDeadline)
                    <p class="mt-1 flex items-center gap-1 text-xs font-bold text-rose-600">
                        <x-lucide-alert-circle class="size-3.5 text-rose-500" aria-hidden="true" />
                        <span>Melewati komitmen batas SLA 2 hari</span>
                    </p>
                @else
                    <p class="mt-1 flex items-center gap-1 text-xs font-medium text-amber-700">
                        <x-lucide-clock class="size-3.5 text-amber-600" aria-hidden="true" />
                        <span>Target penyelesaian: {{ $complaint->deadline_at->diffForHumans() }}</span>
                    </p>
                @endif
            </x-ui.card>
        </div>

        {{-- Kartu Isi Pengaduan Pelanggan --}}
        <x-ui.card padding="p-6 sm:p-7" class="space-y-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Subjek Keluhan</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $complaint->subject }}</h2>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rincian & Kronologi Kendala</p>
                <div class="mt-2 rounded-xl bg-slate-50 p-4 border border-slate-200/70 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line font-normal">
                    {{ $complaint->description }}
                </div>
            </div>
        </x-ui.card>

        {{-- Kartu Respons Staf / Tindak Lanjut --}}
        <div class="rounded-2xl border @if($complaint->response) border-emerald-200 bg-emerald-50/20 @else border-slate-200 bg-white @endif p-6 sm:p-7 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-xl @if($complaint->response) bg-emerald-100 text-emerald-700 @else bg-slate-100 text-slate-500 @endif shrink-0">
                        <x-lucide-message-square class="size-4" aria-hidden="true" />
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
                <div class="mt-2 rounded-xl bg-white p-4 border border-emerald-100 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line shadow-xs">
                    {{ $complaint->response }}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-1">
                    <span>Ditindaklanjuti oleh:</span>
                    <strong class="font-semibold text-slate-800">{{ $complaint->handler?->name ?? 'Staf Operasional' }}</strong>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center">
                    <p class="text-xs sm:text-sm font-semibold text-slate-700">
                        Pengaduan Anda sedang dalam antrean pemeriksaan tim operasional workshop.
                    </p>
                    <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Staf kami akan memeriksa fisik pakaian atau data pengerjaan cucian Anda dan memberikan solusi maksimal dalam 2 hari kerja.
                    </p>
                </div>
            @endif
        </div>

        {{-- Kartu Ringkasan Nota Transaksi Terkait --}}
        @if ($trx)
            <x-ui.card padding="p-6">
                <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nota Transaksi Terkait</p>
                        <h3 class="mt-0.5 font-mono text-base font-bold text-brand-700">{{ $trx->transaction_code }}</h3>
                    </div>
                    <div>
                        <a
                            href="{{ route('customer.transactions.index', ['selected' => $trx->transaction_code]) }}"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline"
                        >
                            <span>Buka Riwayat Nota Ini</span>
                            <x-lucide-arrow-right class="size-3.5" aria-hidden="true" />
                        </a>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 text-xs">
                    <div>
                        <span class="text-slate-500">Tanggal Transaksi</span>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $trx->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">Status Cucian</span>
                        <p class="mt-0.5">
                            <x-ui.status-badge :status="$trx->status" size="sm" />
                        </p>
                    </div>
                    <div>
                        <span class="text-slate-500">Estimasi Selesai</span>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $trx->estimated_completed_at ? $trx->estimated_completed_at->translatedFormat('d M Y, H:i') : '-' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">Total Tagihan</span>
                        <p class="mt-0.5 font-bold text-slate-900">
                            <x-ui.money :value="$trx->total_amount" />
                        </p>
                    </div>
                </div>

                @if ($trx->details->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Item Layanan</p>
                        <ul class="divide-y divide-slate-100 text-xs text-slate-700">
                            @foreach ($trx->details as $detail)
                                <li class="flex items-center justify-between py-1.5">
                                    <span>{{ $detail->service?->name ?? 'Layanan' }} ({{ number_format((float) $detail->weight, 1) }} kg)</span>
                                    <span class="font-semibold text-slate-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-ui.card>
        @endif
    </div>
</x-layouts::app>
