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
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Formulir Pengaduan Pelanggan
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Laporkan kendala cucian Anda untuk ditindaklanjuti staf kami dalam batas waktu maksimal 2 hari kerja.
                    </p>
                </div>
                <div class="hidden sm:block">
                    <x-ui.button :href="route('customer.complaints.index')" variant="secondary" size="md">
                        <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                        <span>Kembali</span>
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Banner SLA & Edukasi Layanan --}}
        <div class="rounded-2xl border border-brand-200 bg-brand-50/70 p-5 text-sm text-brand-900">
            <div class="flex gap-3.5">
                <div class="shrink-0 text-brand-700">
                    <x-lucide-shield-alert class="size-5" aria-hidden="true" />
                </div>
                <div class="space-y-1">
                    <p class="font-bold text-brand-900">Komitmen Layanan & Batas Waktu (SLA 2 Hari Kerja)</p>
                    <p class="leading-relaxed text-brand-800">
                        Setiap laporan pengaduan wajib terhubung ke nomor nota transaksi yang sah dan dijamin tuntas diselesaikan maksimal dalam <strong>2 hari kerja</strong>. Mohon berikan rincian kendala secara jelas agar tim workshop kami dapat segera melakukan pemeriksaan fisik atau penanganan ulang pakaian Anda.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Pengaduan --}}
        <x-ui.card padding="p-6 sm:p-8">
            @if ($eligibleTransactions->isEmpty())
                <div class="py-8 text-center">
                    <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <x-lucide-alert-circle class="size-6" aria-hidden="true" />
                    </div>
                    <h2 class="text-base font-bold text-slate-900">Tidak Ada Transaksi yang Dapat Dilaporkan</h2>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500 leading-relaxed">
                        Anda saat ini belum memiliki transaksi aktif yang selesai/diproses, atau transaksi Anda telah memiliki tiket pengaduan aktif yang sedang ditangani staf kami.
                    </p>
                    <div class="mt-6">
                        <x-ui.button :href="route('customer.complaints.index')" variant="primary" size="md">
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
                        <label for="transaction_id" class="block text-sm font-bold text-slate-700">
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
                                class="min-h-11 w-full rounded-xl border @error('transaction_id') border-rose-300 ring-2 ring-rose-100 @else border-slate-300 @enderror bg-white px-3.5 text-sm text-slate-900 transition focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
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
                        <label for="subject" class="block text-sm font-bold text-slate-700">
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
                                class="min-h-11 w-full rounded-xl border @error('subject') border-rose-300 ring-2 ring-rose-100 @else border-slate-300 @enderror bg-white px-3.5 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            />
                        </div>
                        @error('subject')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi Kendala --}}
                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-700">
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
                                class="w-full rounded-xl border @error('description') border-rose-300 ring-2 ring-rose-100 @else border-slate-300 @enderror bg-white p-3.5 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20 leading-relaxed"
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
                        <label class="block text-sm font-bold text-slate-700">
                            Foto Bukti Pakaian Kendala / Noda <span class="text-xs font-normal text-slate-500 lowercase">(opsional)</span>
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
                                class="cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 p-6 text-center transition hover:border-brand-600 hover:bg-brand-50/20"
                            >
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-white text-slate-500 border border-slate-200">
                                    <x-lucide-camera class="size-6 text-slate-600" aria-hidden="true" />
                                </div>
                                <p class="mt-3 text-sm font-semibold text-slate-800">
                                    Klik untuk memilih foto bukti pakaian
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    PNG, JPG, atau WEBP hingga 5MB. Otomatis dikompresi oleh sistem.
                                </p>
                            </div>

                            {{-- Live Preview Berkas --}}
                            <div
                                x-show="previewUrl"
                                x-cloak
                                class="relative rounded-2xl border border-slate-200 bg-white p-3.5"
                            >
                                <div class="flex items-center gap-4">
                                    <img
                                        :src="previewUrl"
                                        alt="Preview Bukti Foto"
                                        class="size-20 rounded-xl object-cover border border-slate-200"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate" x-text="fileName"></p>
                                        <p class="text-xs text-slate-500 mt-0.5" x-text="fileSize"></p>
                                        <span class="inline-flex items-center gap-1 mt-1 text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md">
                                            <x-lucide-check class="size-3.5" aria-hidden="true" />
                                            Foto Siap Diunggah
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile()"
                                        class="flex size-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                        title="Hapus foto"
                                        aria-label="Hapus foto"
                                    >
                                        <x-lucide-trash-2 class="size-5" aria-hidden="true" />
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
                        <x-ui.button :href="route('customer.complaints.index')" variant="secondary" size="md">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary" size="md">
                            <x-lucide-send class="size-4" aria-hidden="true" />
                            <span>Kirim Pengaduan</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.card>
    </div>
</x-layouts::app>
