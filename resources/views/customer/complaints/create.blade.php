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
                <form method="POST" action="{{ route('customer.complaints.store') }}" enctype="multipart/form-data" class="space-y-6">
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

                    {{-- Unggah Bukti Foto Pakaian (Opsional / Dianjurkan) --}}
                    <div
                        x-data="{
                            previewUrl: null,
                            fileName: '',
                            fileSize: '',
                            handleFile(event) {
                                const file = event.target.files[0];
                                if (!file) {
                                    this.previewUrl = null;
                                    this.fileName = '';
                                    this.fileSize = '';
                                    return;
                                }
                                this.fileName = file.name;
                                this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    this.previewUrl = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            },
                            removeFile() {
                                this.previewUrl = null;
                                this.fileName = '';
                                this.fileSize = '';
                                $refs.photoInput.value = '';
                            }
                        }"
                    >
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Foto Bukti Pakaian Kendala / Noda <span class="text-xs font-normal text-slate-400 lowercase">(opsional)</span>
                        </label>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Unggah foto jelas bagian noda, kancing hilang, atau pakaian yang rusak (JPG, PNG, WEBP maks. 5 MB).
                        </p>

                        <div class="mt-2.5">
                            <input
                                type="file"
                                id="photo"
                                name="photo"
                                x-ref="photoInput"
                                accept="image/jpeg,image/png,image/webp,image/jpg"
                                @change="handleFile($event)"
                                class="hidden"
                            />

                            {{-- Area Drop / Pilih Berkas --}}
                            <div
                                x-show="!previewUrl"
                                @click="$refs.photoInput.click()"
                                class="cursor-pointer rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 p-6 text-center transition hover:border-brand-400 hover:bg-brand-50/20"
                            >
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-2xs border border-slate-100">
                                    <svg class="size-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                </div>
                                <p class="mt-3 text-xs font-semibold text-slate-700">
                                    Klik untuk memilih foto bukti pakaian
                                </p>
                                <p class="mt-1 text-2xs text-slate-400">
                                    PNG, JPG, atau WEBP hingga 5MB. Otomatis dikompresi oleh sistem.
                                </p>
                            </div>

                            {{-- Live Preview Berkas --}}
                            <div
                                x-show="previewUrl"
                                x-cloak
                                class="relative rounded-2xl border border-slate-200 bg-white p-3 shadow-2xs"
                            >
                                <div class="flex items-center gap-4">
                                    <img
                                        :src="previewUrl"
                                        alt="Preview Bukti Foto"
                                        class="size-20 rounded-xl object-cover border border-slate-100"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate" x-text="fileName"></p>
                                        <p class="text-2xs text-slate-500 mt-0.5" x-text="fileSize"></p>
                                        <span class="inline-flex items-center gap-1 mt-1 text-2xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md">
                                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Foto Siap Diunggah
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile()"
                                        class="rounded-xl border border-rose-200 bg-rose-50 p-2 text-rose-600 hover:bg-rose-100 transition"
                                        title="Hapus foto"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @error('photo')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
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
