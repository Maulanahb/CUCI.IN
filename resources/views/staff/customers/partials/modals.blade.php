{{-- Modal Tambah Pelanggan Baru --}}
<div
    x-cloak
    x-show="createModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
        @click.outside="createModalOpen = false"
    >
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Pelanggan Baru</h3>
            <button type="button" @click="createModalOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" action="{{ route("{$currentRoutePrefix}.customers.store") }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Nama Lengkap Pelanggan" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="phone"
                    required
                    placeholder="Contoh: 081234567890"
                    @blur="$event.target.value = formatPhone($event.target.value)"
                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                >
                <p class="mt-1 text-[11px] text-slate-400">Otomatis diformat ke standar lokal <code class="font-semibold text-slate-600">08...</code> (misal mengetik <code class="text-slate-600">+628...</code> atau <code class="text-slate-600">628...</code> otomatis disesuaikan).</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                <textarea name="address" rows="3" placeholder="Contoh: Jl. Merpati No. 12, RT 02/04" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <button type="button" @click="createModalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-700">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Data Pelanggan --}}
<div
    x-cloak
    x-show="editModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
        @click.outside="editModalOpen = false"
    >
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Edit Data Pelanggan</h3>
            <button type="button" @click="editModalOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" :action="'/' + '{{ $currentRoutePrefix }}' + '/customers/' + editForm.id" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="editForm.name" required class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="phone"
                    x-model="editForm.phone"
                    required
                    @blur="editForm.phone = formatPhone(editForm.phone)"
                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                >
                <p class="mt-1 text-[11px] text-slate-400">Otomatis diformat ke standar lokal <code class="font-semibold text-slate-600">08...</code> (misal mengetik <code class="text-slate-600">+628...</code> atau <code class="text-slate-600">628...</code> otomatis disesuaikan).</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                <textarea name="address" x-model="editForm.address" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-900 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <button type="button" @click="editModalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus Pelanggan --}}
<div
    x-cloak
    x-show="deleteModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
        @click.outside="deleteModalOpen = false"
    >
        <div class="flex size-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h3 class="mt-3 text-base font-bold text-slate-900">Hapus Data Pelanggan?</h3>
        <p class="mt-1 text-xs text-slate-500">
            Apakah Anda yakin ingin menghapus pelanggan <strong class="text-slate-800" x-text="deleteForm.name"></strong>? Data yang telah dihapus tidak dapat dikembalikan.
        </p>
        <form method="POST" :action="'/' + '{{ $currentRoutePrefix }}' + '/customers/' + deleteForm.id" class="mt-5 flex items-center justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" @click="deleteModalOpen = false" class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
            <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">Ya, Hapus Pelanggan</button>
        </form>
    </div>
</div>
