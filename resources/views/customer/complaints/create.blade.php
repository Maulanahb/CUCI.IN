<x-layouts::app title="Ajukan Pengaduan Baru">
    <div class="mx-auto max-w-3xl space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <nav class="mb-2 flex items-center gap-1.5 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
                <span class="font-semibold text-brand-700">CUCI.IN</span>
                <span class="text-slate-300">/</span>
                <a href="{{ route('customer.complaints.index') }}" class="transition hover:text-slate-800">Pengaduan</a>
                <span class="text-slate-300">/</span>
                <span class="font-medium text-slate-700">Buat Baru</span>
            </nav>

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Formulir Pengaduan Pelanggan
                    </h1>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Laporkan kendala cucian Anda untuk ditindaklanjuti staf kami dalam batas waktu maksimal 2 hari kerja.
                    </p>
                </div>
                <div class="hidden sm:block">
                    <x-ui.button :href="route('customer.complaints.index')" variant="secondary" size="sm">
                        <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                        <span>Kembali</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Banner SLA & Edukasi Layanan --}}
        <div class="rounded-2xl border border-brand-200/90 bg-brand-50/70 p-4 sm:p-5 text-xs text-brand-900 shadow-xs">
            <div class="flex gap-3.5">
                <div class="shrink-0 text-brand-700">
                    <x-lucide-shield-alert class="size-5" aria-hidden="true" />
                </div>
                <div class="space-y-1">
                    <p class="font-bold text-brand-900">Komitmen Layanan & Batas Waktu (SLA 2 Hari Kerja)</p>
                    <p class="leading-relaxed text-brand-800/90">
                        Setiap laporan pengaduan wajib terhubung ke nomor nota transaksi yang sah dan dijamin tuntas diselesaikan maksimal dalam <strong>2 hari kerja</strong>. Mohon berikan rincian kendala secara jelas agar tim workshop kami dapat segera melakukan pemeriksaan fisik atau penanganan ulang pakaian Anda.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Pengaduan --}}
        <x-ui.card padding="p-6 sm:p-8">
            @if ($eligibleTransactions->isEmpty())
                <div class="py-6 text-center">
                    <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <x-lucide-alert-circle class="size-6" aria-hidden="true" />
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Tidak Ada Transaksi yang Dapat Dilaporkan</h2>
                    <p class="mx-auto mt-1 max-w-md text-xs text-slate-500 leading-relaxed">
                        Anda saat ini belum memiliki transaksi aktif yang selesai/diproses, atau transaksi Anda telah memiliki tiket pengaduan aktif yang sedang ditangani staf kami.
                    </p>
                    <div class="mt-5">
                        <x-ui.button :href="route('customer.complaints.index')" variant="primary" size="sm">
                            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                            <span>Lihat Pengaduan Terdaftar</span>
                        </x-ui.button>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('customer.complaints.store') }}" class="space-y-6">
                    @csrf

                    {{-- Pilih Nota Transaksi --}}
                    <div>
                        <label for="transaction_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Pilih Nota Transaksi <span class="text-rose-500">*</span>
                        </label>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Pilih nomor nota cucian yang mengalami kendala (pakaian bernoda, tertukar, atau belum rapi).
                        </p>
                        <div class="mt-2">
                            <select
                                id="transaction_id"
                                name="transaction_id"
                                required
                                class="h-11 w-full rounded-xl border @error('transaction_id') border-rose-300 ring-2 ring-rose-100 @else border-slate-300 @enderror bg-white px-3.5 text-xs sm:text-sm text-slate-900 shadow-xs transition focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-200"
                            >
                                <option value="">-- Pilih Nomor Nota Transaksi --</option>
                                @foreach ($eligibleTransactions as $trx)
                                    @php
                                        $isSelected = (string) old('transaction_id') === (string) $trx->id
                                            || ($selectedTransaction && $selectedTransaction->id === $trx->id);

                                        $serviceNames = $trx->details->map(fn($d) => $d->service?->name)->filter()->join(', ');
                                    @endphp
                                    <option value="{{ $trx->id }}" @selected($isSelected)>
                                        {{ $trx->transaction_code }} &mdash; {{ $trx->created_at->translatedFormat('d M Y') }} &bull; {{ $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : $trx->status }} ({{ $serviceNames ?: 'Layanan Laundry' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('transaction_id')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Subjek Keluhan --}}
                    <div>
                        <label for="subject" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Subjek / Judul Kendala <span class="text-rose-500">*</span>
                        </label>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Ringkasan singkat kendala yang dialami (minimal 5 karakter).
                        </p>
                        <div class="mt-2">
                            <input
                                type="text"
                                id="subject"
                                name="subject"
                                value="{{ old('subject') }}"
                                required
                                maxlength="150"
                                placeholder="Contoh: Kemeja putih tertukar / Noda di celana belum bersih"
                                class="h-11 w-full rounded-xl border @error('subject') border-rose-300 ring-2 ring-rose-100 @else border-slate-300 @enderror bg-white px-3.5 text-xs sm:text-sm text-slate-900 shadow-xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-200"
                            />
                        </div>
                        @error('subject')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi Kendala --}}
                    <div>
                        <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Rincian Kendala & Kronologi Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Jelaskan masalah secara terperinci (minimal 10 karakter, sebutkan warna atau ciri pakaian terkait).
                        </p>
                        <div class="mt-2">
                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                required
                                maxlength="2000"
                                placeholder="Tuliskan kendala secara rinci, misalnya: Ada kemeja batik warna biru lengan panjang yang tidak ada di dalam bungkusan cucian saat saya periksa..."
                                class="w-full rounded-xl border @error('description') border-rose-300 ring-2 ring-rose-100 @else border-slate-300 @enderror bg-white p-3.5 text-xs sm:text-sm text-slate-900 shadow-xs transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-hidden focus:ring-2 focus:ring-brand-200 leading-relaxed"
                            >{{ old('description') }}</textarea>
                        </div>
                        @error('description')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi Simpan --}}
                    <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 pt-5">
                        <x-ui.button :href="route('customer.complaints.index')" variant="secondary">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary">
                            <x-lucide-send class="size-4" aria-hidden="true" />
                            <span>Kirim Pengaduan</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.card>
    </div>
</x-layouts::app>
