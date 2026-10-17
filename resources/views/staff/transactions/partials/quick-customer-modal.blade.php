{{-- ======================================================== --}}
{{-- MODAL TAMBAH PELANGGAN CEPAT (AJAX QUICK ADD)            --}}
{{-- ======================================================== --}}
<div
    id="modal-quick-customer"
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200"
    aria-labelledby="modal-quick-customer-title"
    role="dialog"
    aria-modal="true"
    onclick="if (event.target === this) closeQuickCustomerModal()"
>
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all sm:my-8">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-quick-customer-title">
                            Tambah Pelanggan Baru
                        </h3>
                        <p class="text-[11px] text-slate-500">Daftarkan pelanggan baru langsung tanpa keluar dari kasir POS.</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="closeQuickCustomerModal()"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none cursor-pointer"
                    aria-label="Tutup modal"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Container Error Validasi AJAX --}}
            <div id="quick-customer-errors" class="mt-4 hidden rounded-xl border border-rose-200 bg-rose-50/80 p-3 text-xs text-rose-800">
                <div class="flex items-center gap-1.5 font-semibold">
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Gagal menyimpan data pelanggan:</span>
                </div>
                <ul id="quick-customer-errors-list" class="mt-1 list-inside list-disc space-y-0.5 pl-2 font-normal"></ul>
            </div>

            <form id="form-quick-customer" onsubmit="submitQuickCustomer(event)" class="mt-4 space-y-4">
                <div>
                    <label for="quick_customer_name" class="block text-xs font-semibold text-slate-700">
                        Nama Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="quick_customer_name"
                            name="name"
                            required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                        />
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="quick_customer_phone" class="block text-xs font-semibold text-slate-700">
                            Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Format 08xxx</span>
                    </div>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="quick_customer_phone"
                            name="phone"
                            required
                            placeholder="081234567890"
                            class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                        />
                    </div>
                </div>

                <div>
                    <label for="quick_customer_address" class="block text-xs font-semibold text-slate-700">
                        Alamat <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute top-3 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <textarea
                            id="quick_customer_address"
                            name="address"
                            rows="2"
                            placeholder="Contoh: Jl. Merdeka No. 10, RT 02/05"
                            class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        onclick="closeQuickCustomerModal()"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="btn-save-quick-customer"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98 cursor-pointer"
                    >
                        <svg id="btn-save-quick-customer-spinner" class="hidden size-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span id="btn-save-quick-customer-text">Simpan & Pilih</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
