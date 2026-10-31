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
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('customer.complaints.index') }}"
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                        title="Kembali ke Daftar Pengaduan"
                    >
                        <x-lucide-arrow-left class="size-4.5" />
                    </a>
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="font-mono text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                                {{ $complaint->complaint_code }}
                            </h1>
                            @if ($complaint->status === 'Menunggu')
                                <x-ui.badge tone="warning" size="md" dot>
                                    Menunggu Penanganan
                                </x-ui.badge>
                            @elseif ($complaint->status === 'Diproses')
                                <x-ui.badge tone="info" size="md" dot>
                                    Sedang Diproses
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
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                            Diajukan pada {{ $complaint->created_at->translatedFormat('l, d F Y - H:i') }} WIB
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.button :href="route('customer.complaints.index')" variant="secondary" size="md">
                        <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                        <span>Kembali ke Daftar</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Signature Complaint Progress Stepper (Dengan Jalur Garis Terhubung & Brand Cohesion) --}}
        <x-ui.card padding="p-5 sm:p-6">
            <h2 class="mb-6 text-xs font-bold uppercase tracking-wider text-slate-500">
                Tahapan Penanganan Tiket Keluhan
            </h2>

            <div class="relative max-w-2xl mx-auto">
                {{-- Track Garis Horizontal Stepper --}}
                <div class="absolute top-5 left-[16.666%] right-[16.666%] -translate-y-1/2 h-1 bg-slate-200" aria-hidden="true">
                    <div
                        class="h-full bg-brand-700 transition-all duration-300 {{ $currentStep === 1 ? 'w-0' : ($currentStep === 2 ? 'w-1/2' : 'w-full') }}"
                    ></div>
                </div>

                <div class="relative grid grid-cols-3 gap-2">
                    {{-- Langkah 1: Diajukan --}}
                    <div class="flex flex-col items-center text-center">
                        @if ($currentStep > 1)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-brand-700 text-white font-bold shadow-xs">
                                <x-lucide-check class="size-5" aria-hidden="true" />
                            </div>
                        @else
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-brand-700 text-white font-bold shadow-xs ring-4 ring-brand-100">
                                <x-lucide-file-text class="size-5" aria-hidden="true" />
                            </div>
                        @endif
                        <p class="mt-2 text-xs font-bold text-slate-900">Laporan Diajukan</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $complaint->created_at->translatedFormat('d M, H:i') }}</p>
                    </div>

                    {{-- Langkah 2: Pemeriksaan Staf --}}
                    <div class="flex flex-col items-center text-center">
                        @if ($currentStep > 2)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-brand-700 text-white font-bold shadow-xs">
                                <x-lucide-check class="size-5" aria-hidden="true" />
                            </div>
                        @elseif ($currentStep === 2)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-brand-700 text-white font-bold shadow-xs ring-4 ring-brand-100">
                                <x-lucide-clock class="size-5" aria-hidden="true" />
                            </div>
                        @else
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-white border-2 border-slate-200 text-slate-400 font-bold">
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
                        @if ($currentStep === 3)
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-emerald-600 text-white font-bold shadow-xs ring-4 ring-emerald-100">
                                <x-lucide-check-circle-2 class="size-5" aria-hidden="true" />
                            </div>
                        @else
                            <div class="relative flex size-10 items-center justify-center rounded-full bg-white border-2 border-slate-200 text-slate-400 font-bold">
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

        {{-- Konten Utama Grid (2 Kolom Seimbang Konsisten dengan Tampilan Staf) --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Kolom Kiri (3 Kolom): Keluhan, Uraian, Bukti Foto, & Tanggapan Staf --}}
            <div class="space-y-6 xl:col-span-3">
                {{-- Kartu Detail Masalah --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-base sm:text-lg font-semibold tracking-tight text-slate-900">Rincian Pengaduan Pelanggan</h2>

                    <div class="mt-4 border-b border-slate-100 pb-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Subjek Keluhan</p>
                        <p class="mt-1 text-base font-semibold text-slate-900 break-words">{{ $complaint->subject }}</p>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Uraian Masalah / Kendala</p>
                        <div class="mt-2 rounded-xl bg-slate-50 p-4 border border-slate-200/80 text-sm leading-relaxed text-slate-800 whitespace-pre-line break-words break-all">
                            {{ $complaint->description }}
                        </div>
                    </div>

                    {{-- Bukti Foto Lampiran --}}
                    @if ($complaint->photo_path)
                        <div class="mt-6 border-t border-slate-100 pt-5">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Bukti Foto Kendala / Pakaian</p>
                            <div class="inline-block rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-slate-50 max-w-md">
                                <a href="{{ $complaint->photo_url }}" target="_blank" rel="noopener noreferrer" class="group relative block" title="Klik untuk memperbesar gambar">
                                    <img
                                        src="{{ $complaint->photo_url }}"
                                        alt="Foto Bukti Komplain {{ $complaint->complaint_code }}"
                                        class="max-h-80 w-full object-contain transition duration-200 group-hover:scale-102"
                                    >
                                    <div class="absolute inset-0 flex items-center justify-center bg-slate-900/40 opacity-0 transition group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-1.5 text-xs sm:text-sm font-semibold text-slate-800 shadow-sm">
                                            <x-lucide-zoom-in class="size-4" />
                                            <span>Lihat Ukuran Penuh</span>
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Kartu Respons Staf / Tindak Lanjut --}}
                <div class="rounded-2xl border @if($complaint->response) border-emerald-200 bg-emerald-50/20 @else border-slate-200 bg-white @endif p-6 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
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
                        <div class="mt-2 rounded-xl bg-white p-4 border border-emerald-100 text-sm text-slate-800 leading-relaxed whitespace-pre-line break-words break-all shadow-xs">
                            {{ $complaint->response }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-1">
                            <span>Ditindaklanjuti oleh:</span>
                            <strong class="font-semibold text-slate-800">{{ $complaint->handler?->name ?? 'Staf Operasional' }}</strong>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center">
                            <x-lucide-clock class="mx-auto size-7 text-slate-400 mb-2" />
                            <p class="text-sm font-semibold text-slate-800">
                                Pengaduan Anda sedang dalam antrean pemeriksaan operasional workshop.
                            </p>
                            <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                                Staf kami akan memeriksa fisik pakaian atau data pengerjaan cucian Anda dan memberikan solusi maksimal dalam 2 hari kerja.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan (2 Kolom): Komitmen Waktu & SLA, Serta Nota Transaksi Terkait --}}
            <div class="space-y-6 xl:col-span-2">
                {{-- Kartu Komitmen Waktu & Batas SLA --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Waktu Pengajuan</span>
                            <x-lucide-calendar class="size-4 text-slate-400" aria-hidden="true" />
                        </div>
                        <p class="mt-1.5 text-sm font-bold text-slate-900">
                            {{ $complaint->created_at->translatedFormat('d F Y, H:i') }} WIB
                        </p>
                        <p class="text-xs text-slate-500">{{ $complaint->created_at->diffForHumans() }}</p>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Target SLA (Maks 2 Hari)</span>
                            <x-lucide-clock class="size-4 text-slate-400" aria-hidden="true" />
                        </div>
                        <p class="mt-1.5 text-sm font-bold text-slate-900">
                            {{ $complaint->deadline_at->translatedFormat('d F Y, H:i') }} WIB
                        </p>

                        <div class="mt-2">
                            @if ($complaint->status === 'Selesai')
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <x-lucide-check-circle-2 class="size-3.5 text-emerald-600" aria-hidden="true" />
                                    <span>Pengaduan tuntas tepat waktu</span>
                                </span>
                            @elseif ($isPastDeadline)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">
                                    <x-lucide-alert-circle class="size-3.5 text-rose-500" aria-hidden="true" />
                                    <span>Melewati batas SLA 2 hari</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    <x-lucide-clock class="size-3.5 text-amber-600" aria-hidden="true" />
                                    <span>Target: {{ $complaint->deadline_at->diffForHumans() }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Kartu Ringkasan Nota Transaksi Terkait --}}
                @if ($trx)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nota Transaksi Terkait</p>
                                <h3 class="mt-0.5 font-mono text-base font-bold text-brand-700">{{ $trx->transaction_code }}</h3>
                            </div>
                            <a
                                href="{{ route('customer.transactions.index', ['selected' => $trx->transaction_code]) }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800 hover:underline"
                            >
                                <span>Buka Nota</span>
                                <x-lucide-arrow-right class="size-3.5" aria-hidden="true" />
                            </a>
                        </div>

                        <div class="mt-3 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Tanggal Transaksi</span>
                                <span class="font-medium text-slate-900">{{ $trx->created_at->translatedFormat('d M Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Status Cucian</span>
                                <x-ui.status-badge :status="$trx->status" size="sm" />
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Estimasi Selesai</span>
                                <span class="font-medium text-slate-900">{{ $trx->estimated_completed_at ? $trx->estimated_completed_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-100 pt-2 font-bold">
                                <span class="text-slate-700">Total Tagihan</span>
                                <span class="text-slate-900"><x-ui.money :value="$trx->total_amount" /></span>
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
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
