@php
    $indexRoute = request()->routeIs('admin.*')
        ? route('admin.complaints.index')
        : route('staff.complaints.index');

    $updateRoute = request()->routeIs('admin.*')
        ? route('admin.complaints.update', $complaint)
        : route('staff.complaints.update', $complaint);

    $isOverdue = $complaint->status !== 'Selesai' && $complaint->deadline_at->isPast();
@endphp

<x-layouts::app title="Detail Pengaduan {{ $complaint->complaint_code }}">
    <div class="space-y-7">
        {{-- Flash Message Sukses --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                <x-lucide-check-circle-2 class="size-5 shrink-0 text-emerald-600" />
                <p class="font-medium">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Navigasi Kembali & Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ $indexRoute }}"
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                    title="Kembali ke Daftar Pengaduan"
                >
                    <x-lucide-arrow-left class="size-4.5" />
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl font-mono">
                            {{ $complaint->complaint_code }}
                        </h1>
                        @if ($complaint->status === 'Menunggu')
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-sm font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                <span class="size-1.5 rounded-full bg-amber-500"></span>
                                Menunggu
                            </span>
                        @elseif ($complaint->status === 'Diproses')
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-sm font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                Sedang Diproses
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-sm font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                <x-lucide-check class="size-3.5" />
                                Selesai
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500">
                        Diajukan pada {{ $complaint->created_at->translatedFormat('l, d F Y - H:i') }} WIB
                    </p>
                </div>
            </div>
        </div>

        {{-- Konten Utama Grid --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Kolom Kiri (2 Kolom): Keluhan & Bukti --}}
            <div class="space-y-6 xl:col-span-3">
                {{-- Kartu Detail Masalah --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Rincian Pengaduan Pelanggan</h2>
                    
                    <div class="mt-4 border-b border-slate-100 pb-4">
                        <p class="text-sm font-medium text-slate-500">Subjek Keluhan</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $complaint->subject }}</p>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm font-medium text-slate-500">Uraian Masalah / Kendala</p>
                        <div class="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                            {{ $complaint->description }}
                        </div>
                    </div>

                    {{-- Bukti Foto Lampiran --}}
                    @if ($complaint->photo_path)
                        <div class="mt-6 border-t border-slate-100 pt-5">
                            <p class="text-sm font-medium text-slate-500">Bukti Foto Kendala / Pakaian</p>
                            <div class="mt-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 max-w-md">
                                <a href="{{ $complaint->photo_url }}" target="_blank" rel="noopener noreferrer" class="group relative block" title="Klik untuk memperbesar gambar">
                                    <img
                                        src="{{ $complaint->photo_url }}"
                                        alt="Foto Bukti Komplain {{ $complaint->complaint_code }}"
                                        class="max-h-96 w-full object-contain"
                                    >
                                    <div class="absolute inset-0 flex items-center justify-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/30 group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/90 px-3 py-1.5 text-sm font-semibold text-slate-800 shadow">
                                            <x-lucide-zoom-in class="size-4" />
                                            Lihat Ukuran Penuh
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <p class="mt-1.5 text-sm text-slate-500">Pilih foto untuk melihat ukuran penuh.</p>
                        </div>
                    @endif
                </div>

                {{-- Kartu Rincian Transaksi --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-semibold tracking-tight text-slate-900">Informasi Nota Transaksi Terkait</h2>
                        <a
                            href="{{ route('staff.transactions.show', $complaint->transaction_id) }}"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700"
                        >
                            <span>Buka Nota POS</span>
                            <x-lucide-external-link class="size-3.5" />
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Nomor Nota Transaksi</p>
                            <p class="mt-0.5 font-mono font-bold text-slate-900">{{ $complaint->transaction?->transaction_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Status Transaksi Cucian</p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ $complaint->transaction?->status?->value ?? (string) $complaint->transaction?->status }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Nama Pelanggan</p>
                            <p class="mt-0.5 font-semibold text-slate-900">{{ $complaint->transaction?->customer?->name ?? 'Pelanggan Umum' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Nomor Telepon / WhatsApp</p>
                            <p class="mt-0.5 font-mono text-slate-800">{{ $complaint->transaction?->customer?->phone ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Layanan Dalam Transaksi --}}
                    @if ($complaint->transaction?->details && $complaint->transaction->details->isNotEmpty())
                        <div class="mt-5 border-t border-slate-100 pt-4">
                            <p class="text-sm font-medium text-slate-400 mb-2">Item Layanan</p>
                            <div class="space-y-1.5">
                                @foreach ($complaint->transaction->details as $detail)
                                    <div class="flex items-center justify-between border-b border-slate-100 py-3 text-sm">
                                        <span class="font-medium text-slate-800">{{ $detail->service?->name ?? 'Layanan Laundry' }}</span>
                                        <span class="font-mono text-slate-600">{{ $detail->quantity }} {{ $detail->service?->unit ?? 'kg' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan: Form Tindak Lanjut & SLA --}}
            <div class="space-y-6 xl:col-span-2">
                {{-- Kartu Form Tindak Lanjut Staf --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Tindak Lanjut & Tanggapan</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Perbarui tahapan penanganan dan tulis pesan solusi untuk pelanggan.
                    </p>

                    <form method="POST" action="{{ $updateRoute }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Pilihan Status --}}
                        <div>
                            <label for="status" class="block text-sm font-semibold text-slate-700">
                                Status Penanganan <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-white px-3 py-3 text-base sm:text-sm font-medium text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                required
                            >
                                <option value="Menunggu" @selected(old('status', $complaint->status) === 'Menunggu')>
                                    Menunggu Tindak Lanjut
                                </option>
                                <option value="Diproses" @selected(old('status', $complaint->status) === 'Diproses')>
                                    Sedang Diproses
                                </option>
                                <option value="Selesai" @selected(old('status', $complaint->status) === 'Selesai')>
                                    Selesai
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Textarea Response --}}
                        <div>
                            <label for="response" class="block text-sm font-semibold text-slate-700">
                                Tanggapan / Solusi untuk Pelanggan
                            </label>
                            <textarea
                                id="response"
                                name="response"
                                rows="7"
                                placeholder="Contoh: Kami mohon maaf atas ketidaknyamanannya. Pakaian kemeja telah kami cuci ulang dan noda sudah bersih. Pakaian siap diambil kembali di outlet."
                                class="mt-1.5 block w-full rounded-lg border border-slate-200 px-3 py-3 text-base sm:text-sm text-slate-800 placeholder-slate-500 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                            >{{ old('response', $complaint->response) }}</textarea>
                            <p class="mt-1 text-sm text-slate-500">Tanggapan ini akan dapat dibaca langsung oleh pelanggan di dashboard mereka.</p>
                            @error('response')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tombol Simpan --}}
                        <div class="pt-2">
                            <x-ui.button type="submit" class="w-full">
                                <x-lucide-save class="size-4" />
                                <span>Simpan Pembaruan</span>
                            </x-ui.button>
                        </div>
                    </form>

                    {{-- Info Petugas Penangan --}}
                    @if ($complaint->handler)
                        <div class="mt-5 border-t border-slate-100 pt-4 text-sm text-slate-500">
                            <p class="font-medium text-slate-700">Ditangani Terakhir Oleh:</p>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex size-6 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">
                                    {{ str($complaint->handler->name)->substr(0, 1)->upper() }}
                                </div>
                                <span class="font-semibold text-slate-800">{{ $complaint->handler->name }}</span>
                                <span class="text-slate-500">({{ $complaint->handler->role }})</span>
                            </div>
                            @if ($complaint->handled_at)
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $complaint->handled_at->translatedFormat('d M Y, H:i') }} WIB
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Kartu Komitmen SLA --}}
                <div @class([
                    'rounded-xl border p-5 transition',
                    'border-red-200 bg-red-50/50' => $isOverdue,
                    'border-slate-200/80 bg-white' => ! $isOverdue,
                ])>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div @class([
                            'flex size-8 items-center justify-center rounded-lg',
                            'bg-red-100 text-red-600' => $isOverdue,
                            'bg-brand-50 text-brand-700' => ! $isOverdue,
                        ])>
                            <x-lucide-clock class="size-4" />
                        </div>
                        <h3 @class([
                            'text-xs font-bold uppercase tracking-wider',
                            'text-red-800' => $isOverdue,
                            'text-slate-700' => ! $isOverdue,
                        ])>
                            Target SLA (2 Hari Kerja)
                        </h3>
                    </div>

                    <div class="mt-3">
                        <p class="text-sm text-slate-500">Batas Waktu Penanganan:</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-900">
                            {{ $complaint->deadline_at->translatedFormat('l, d F Y') }}
                        </p>
                        <p @class([
                            'mt-1 text-sm font-medium',
                            'text-red-600' => $isOverdue,
                            'text-emerald-600' => ! $isOverdue,
                        ])>
                            @if ($complaint->status === 'Selesai')
                                Tiket telah terselesaikan dengan baik.
                            @elseif ($isOverdue)
                                Waktu SLA telah terlewati {{ $complaint->deadline_at->diffForHumans() }}. Harap segera diselesaikan!
                            @else
                                Sisa waktu: {{ $complaint->deadline_at->diffForHumans() }}.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
