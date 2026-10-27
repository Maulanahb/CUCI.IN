<x-layouts::app title="Tambah Pengguna Baru">
    <div class="mx-auto max-w-2xl space-y-6">

        {{-- Navigasi Kembali --}}
        <div>
            <a
                href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 transition hover:text-slate-800"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Daftar Pengguna</span>
            </a>
        </div>

        {{-- Form Kartu Tambah Pengguna --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs">
            <div class="border-b border-slate-100 pb-5">
                <h1 class="text-lg font-bold text-slate-900 sm:text-xl">Tambah Akun Pengguna Baru</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Buat akun staf operasional, kasir, atau administrator baru untuk sistem CUCI.IN.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-5">
                @csrf

                {{-- Input Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 sm:text-sm">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="Contoh: Rian Anggara"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm @error('name') border-rose-300 bg-rose-50/20 text-rose-900 @enderror"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Alamat Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 sm:text-sm">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="nama@cuci.in"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm @error('email') border-rose-300 bg-rose-50/20 text-rose-900 @enderror"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pilihan Peran (Role) --}}
                <div>
                    <label for="role" class="block text-xs font-semibold text-slate-700 sm:text-sm">
                        Peran & Hak Akses <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="role"
                        id="role"
                        required
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm @error('role') border-rose-300 @enderror"
                    >
                        <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staf / Kasir (Akses POS & Operasional)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh Seluruh Sistem)</option>
                        <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Pelanggan (Layanan Mandiri Pelanggan)</option>
                    </select>
                    @error('role')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kata Sandi & Konfirmasi --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 sm:text-sm">
                            Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm @error('password') border-rose-300 bg-rose-50/20 text-rose-900 @enderror"
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 sm:text-sm">
                            Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            placeholder="Ulangi kata sandi"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm"
                        >
                    </div>
                </div>

                {{-- Status Keaktifan Akun Awal --}}
                <div class="pt-2">
                    <label class="relative inline-flex items-center gap-3 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="peer sr-only"
                        >
                        <div class="h-5 w-9 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></div>
                        <div class="absolute left-0.5 top-0.5 size-4 rounded-full bg-white shadow-xs transition-transform peer-checked:translate-x-4"></div>
                        <div>
                            <span class="text-xs font-semibold text-slate-800 sm:text-sm">Aktifkan Akun Langsung</span>
                            <p class="text-2xs text-slate-500">Jika dinonaktifkan, akun ini tidak dapat digunakan untuk masuk ke sistem.</p>
                        </div>
                    </label>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 sm:text-sm"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 sm:text-sm"
                    >
                        Simpan Pengguna Baru
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts::app>
