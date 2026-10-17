{{-- ============================================================
    MODAL-MODAL LAYANAN (TAMBAH, UBAH, HAPUS, DETAIL SIMULASI)
============================================================= --}}

@if ($isAdmin)
    {{-- MODAL 1: TAMBAH LAYANAN (Hanya Admin) --}}
    <div
        x-cloak
        x-show="createModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="createModalOpen"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
            @click="createModalOpen = false"
        ></div>

        <div
            x-show="createModalOpen"
            x-transition.scale.origin.bottom
            class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all sm:p-7"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tambah Layanan Baru</h3>
                        <p class="text-xs text-slate-500">Daftarkan paket layanan laundry baru ke dalam sistem.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="createModalOpen = false"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.services.store') }}" class="mt-5 space-y-4">
                @csrf

                {{-- Nama Layanan --}}
                <div>
                    <label for="create_name" class="block text-xs font-semibold text-slate-700">
                        Nama Layanan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="create_name"
                        type="text"
                        name="name"
                        required
                        maxlength="100"
                        placeholder="Contoh: Cuci Komplit Kilat, Cuci Karpet Tebal"
                        class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                    />
                </div>

                {{-- Baris: Harga & Estimasi Durasi --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_price" class="block text-xs font-semibold text-slate-700">
                            Tarif Satuan (Rp/kg) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-xs font-semibold text-slate-400">Rp</span>
                            </div>
                            <input
                                id="create_price"
                                type="number"
                                name="price"
                                required
                                min="1"
                                step="100"
                                placeholder="8000"
                                class="w-full rounded-xl border border-slate-300 py-2.5 pr-3.5 pl-9 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="create_estimated_duration" class="block text-xs font-semibold text-slate-700">
                            Estimasi Durasi (Jam) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <input
                                id="create_estimated_duration"
                                type="number"
                                name="estimated_duration"
                                required
                                min="1"
                                max="720"
                                placeholder="24"
                                class="w-full rounded-xl border border-slate-300 py-2.5 pr-12 pl-3.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-xs font-semibold text-slate-400">Jam</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi Layanan --}}
                <div>
                    <label for="create_description" class="block text-xs font-semibold text-slate-700">
                        Deskripsi Layanan <span class="text-[11px] font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <textarea
                        id="create_description"
                        name="description"
                        rows="3"
                        maxlength="1000"
                        placeholder="Detail proses pengerjaan, perlakuan khusus, atau fasilitas layanan..."
                        class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                    ></textarea>
                </div>

                {{-- Checkbox Status Aktif --}}
                <div class="flex items-center gap-2.5 pt-1">
                    <input
                        id="create_is_active"
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked
                        class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                    />
                    <label for="create_is_active" class="text-xs font-medium text-slate-700 sm:text-sm cursor-pointer select-none">
                        Langsung aktifkan layanan (dapat langsung dipilih saat transaksi)
                    </label>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        @click="createModalOpen = false"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98 cursor-pointer"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Layanan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: UBAH LAYANAN (Hanya Admin) --}}
    <div
        x-cloak
        x-show="editModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="editModalOpen"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
            @click="editModalOpen = false"
        ></div>

        <div
            x-show="editModalOpen"
            x-transition.scale.origin.bottom
            class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all sm:p-7"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Ubah Data Layanan</h3>
                        <p class="text-xs text-slate-500">Perbarui tarif, estimasi durasi, atau keterangan paket.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="editModalOpen = false"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    aria-label="Tutup modal"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form
                method="POST"
                :action="'{{ url('admin/services') }}/' + editForm.id"
                class="mt-5 space-y-4"
            >
                @csrf
                @method('PUT')

                {{-- Nama Layanan --}}
                <div>
                    <label for="edit_name" class="block text-xs font-semibold text-slate-700">
                        Nama Layanan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="edit_name"
                        type="text"
                        name="name"
                        x-model="editForm.name"
                        required
                        maxlength="100"
                        class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                    />
                </div>

                {{-- Baris: Harga & Estimasi Durasi --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_price" class="block text-xs font-semibold text-slate-700">
                            Tarif Satuan (Rp/kg) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-xs font-semibold text-slate-400">Rp</span>
                            </div>
                            <input
                                id="edit_price"
                                type="number"
                                name="price"
                                x-model="editForm.price"
                                required
                                min="1"
                                step="100"
                                class="w-full rounded-xl border border-slate-300 py-2.5 pr-3.5 pl-9 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="edit_estimated_duration" class="block text-xs font-semibold text-slate-700">
                            Estimasi Durasi (Jam) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <input
                                id="edit_estimated_duration"
                                type="number"
                                name="estimated_duration"
                                x-model="editForm.estimated_duration"
                                required
                                min="1"
                                max="720"
                                class="w-full rounded-xl border border-slate-300 py-2.5 pr-12 pl-3.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                            />
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-xs font-semibold text-slate-400">Jam</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi Layanan --}}
                <div>
                    <label for="edit_description" class="block text-xs font-semibold text-slate-700">
                        Deskripsi Layanan <span class="text-[11px] font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <textarea
                        id="edit_description"
                        name="description"
                        x-model="editForm.description"
                        rows="3"
                        maxlength="1000"
                        class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                    ></textarea>
                </div>

                {{-- Checkbox Status Aktif --}}
                <div class="flex items-center gap-2.5 pt-1">
                    <input
                        id="edit_is_active"
                        type="checkbox"
                        name="is_active"
                        value="1"
                        x-model="editForm.is_active"
                        class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                    />
                    <label for="edit_is_active" class="text-xs font-medium text-slate-700 sm:text-sm cursor-pointer select-none">
                        Layanan aktif dan dapat dipilih di form kasir transaksi
                    </label>
                </div>

                {{-- Info Snapshot Note --}}
                <div class="rounded-xl border border-amber-200/70 bg-amber-50/60 p-3 text-xs text-amber-800">
                    <p class="font-semibold">Catatan Snapshot Harga:</p>
                    <p class="mt-0.5 text-[11px] text-amber-700">
                        Perubahan tarif ini hanya berlaku untuk transaksi baru yang akan datang. Transaksi lama tetap mempertahankan nominal riil saat transaksi dibuat.
                    </p>
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        @click="editModalOpen = false"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98 cursor-pointer"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: KONFIRMASI HAPUS / PROTEKSI TRANSAKSI --}}
    <div
        x-cloak
        x-show="deleteModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="deleteModalOpen"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
            @click="deleteModalOpen = false"
        ></div>

        <div
            x-show="deleteModalOpen"
            x-transition.scale.origin.bottom
            class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all"
        >
            {{-- Kondisi 1: Layanan sudah memiliki transaksi --}}
            <template x-if="deleteForm.transactionCount > 0">
                <div>
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Layanan Tidak Dapat Dihapus</h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-600">
                        Layanan <span class="font-semibold text-slate-900" x-text="'&ldquo;' + deleteForm.name + '&rdquo;'"></span> sudah tercatat dalam
                        <span class="font-bold text-amber-700" x-text="deleteForm.transactionCount + ' riwayat transaksi'"></span>. Sesuai aturan integritas operasional, data historis tidak boleh dihapus.
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        Solusi: Anda dapat menonaktifkan layanan ini agar tidak lagi muncul sebagai opsi transaksi baru.
                    </p>
                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            @click="deleteModalOpen = false"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </template>

            {{-- Kondisi 2: Layanan belum pernah digunakan transaksi (Aman dihapus) --}}
            <template x-if="deleteForm.transactionCount === 0">
                <div>
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Konfirmasi Hapus Layanan</h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-600">
                        Apakah Anda yakin ingin menghapus layanan <span class="font-semibold text-slate-900" x-text="'&ldquo;' + deleteForm.name + '&rdquo;'"></span>?
                        Tindakan ini bersifat permanen karena layanan belum memiliki riwayat order.
                    </p>
                    <form
                        method="POST"
                        :action="'{{ url('admin/services') }}/' + deleteForm.id"
                        class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="button"
                            @click="deleteModalOpen = false"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20 active:scale-98 cursor-pointer"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6" />
                            </svg>
                            <span>Hapus Permanen</span>
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>
@endif

{{-- MODAL 4: DETAIL LAYANAN & SIMULASI KALKULASI SUB-TOTAL (Semua User) --}}
<div
    x-cloak
    x-show="detailModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
>
    <div
        x-show="detailModalOpen"
        x-transition.opacity
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
        @click="detailModalOpen = false"
    ></div>

    <div
        x-show="detailModalOpen"
        x-transition.scale.origin.bottom
        class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all sm:p-7"
    >
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="flex size-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" x-text="detailData.name"></h3>
                    <p class="text-xs text-slate-500">Rincian spesifikasi paket dan simulasi biaya cucian.</p>
                </div>
            </div>
            <button
                type="button"
                @click="detailModalOpen = false"
                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                aria-label="Tutup modal"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mt-5 space-y-4">
            {{-- Grid Atribut --}}
            <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                <div>
                    <span class="text-[11px] text-slate-400">Tarif Satuan</span>
                    <p class="mt-0.5 text-sm font-bold text-brand-700" x-text="formatRupiah(detailData.price) + ' / kg'"></p>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400">Estimasi Durasi</span>
                    <p class="mt-0.5 text-sm font-bold text-slate-800" x-text="detailData.estimated_duration + ' Jam'"></p>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400">Status Layanan</span>
                    <p class="mt-0.5 text-xs font-semibold" :class="detailData.is_active ? 'text-emerald-600' : 'text-slate-500'" x-text="detailData.is_active ? 'Tersedia (Aktif)' : 'Nonaktif'"></p>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400">Total Digunakan</span>
                    <p class="mt-0.5 text-xs font-semibold text-slate-800" x-text="detailData.transaction_details_count + ' Transaksi'"></p>
                </div>
            </div>

            {{-- Deskripsi --}}
            <div>
                <span class="text-xs font-semibold text-slate-700">Keterangan Layanan:</span>
                <p class="mt-1 text-xs leading-relaxed text-slate-600" x-text="detailData.description"></p>
            </div>

            {{-- Simulasi Subtotal Berdasarkan Berat --}}
            <div class="rounded-xl border border-brand-200/80 bg-brand-50/50 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-brand-900">Simulasi Subtotal Kasir</span>
                    <span class="text-[11px] font-medium text-brand-600">Formula: Berat × Tarif</span>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <div class="w-1/2">
                        <label class="text-[11px] font-semibold text-slate-600">Berat Cucian (kg):</label>
                        <input
                            type="number"
                            min="0.1"
                            step="0.5"
                            x-model.number="detailData.calcWeight"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-200"
                        />
                    </div>
                    <div class="w-1/2">
                        <label class="text-[11px] font-semibold text-slate-600">Hasil Subtotal:</label>
                        <div class="mt-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-brand-700 border border-brand-100">
                            <span x-text="formatRupiah(detailData.calcWeight * detailData.price)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end border-t border-slate-100 pt-4">
            <button
                type="button"
                @click="detailModalOpen = false"
                class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
