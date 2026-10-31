<x-layouts::app title="Edit Pengguna">
    @php
        $isSelf = $user->id === auth()->id();
    @endphp

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

        {{-- Peringatan Akun Sendiri --}}
        @if ($isSelf)
            <div class="rounded-xl border border-brand-200 bg-brand-50/60 p-4 text-xs text-brand-800 sm:text-sm">
                <div class="flex items-start gap-3">
                    <svg class="size-5 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Anda sedang mengedit akun Anda sendiri</p>
                        <p class="mt-0.5 text-xs text-brand-700">Untuk menjaga keamanan operasional, hak akses peran Administrator dan status aktif tidak dapat dicabut dari akun Anda sendiri.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form Kartu Edit Pengguna --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs">
            <div class="border-b border-slate-100 pb-5">
                <h1 class="text-lg font-bold text-slate-900 sm:text-xl">Edit Data Pengguna</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Perbarui profil, hak akses peran, atau setel ulang kata sandi pengguna <span class="font-semibold text-slate-800">{{ $user->name }}</span>.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                {{-- Input Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 sm:text-sm">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
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
                        value="{{ old('email', $user->email) }}"
                        required
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
                    @if ($isSelf)
                        <input type="hidden" name="role" value="admin">
                        <div class="mt-1.5 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-medium text-slate-600 sm:text-sm">
                            <span class="inline-flex items-center rounded-md border border-purple-200 bg-purple-50 px-2 py-0.5 text-2xs font-semibold text-purple-700">
                                Administrator
                            </span>
                            <span class="text-2xs text-slate-500">(Terkunci untuk akun sendiri)</span>
                        </div>
                    @else
                        <select
                            name="role"
                            id="role"
                            required
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm @error('role') border-rose-300 @enderror"
                        >
                            <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staf / Kasir (Akses POS & Operasional)</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh Seluruh Sistem)</option>
                            <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Pelanggan (Layanan Mandiri Pelanggan)</option>
                        </select>
                    @endif
                    @error('role')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Keaktifan Akun --}}
                <div class="pt-2">
                    @if ($isSelf)
                        <input type="hidden" name="is_active" value="1">
                        <div class="flex items-center gap-2 text-xs text-slate-500 sm:text-sm">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <span class="font-medium text-emerald-700">Akun Anda berstatus Aktif</span>
                        </div>
                    @else
                        <label class="relative inline-flex items-center gap-3 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                                class="peer sr-only"
                            >
                            <div class="h-5 w-9 rounded-full bg-slate-200 transition-colors peer-checked:bg-emerald-500"></div>
                            <div class="absolute left-0.5 top-0.5 size-4 rounded-full bg-white shadow-xs transition-transform peer-checked:translate-x-4"></div>
                            <div>
                                <span class="text-xs font-semibold text-slate-800 sm:text-sm">Status Akun Aktif</span>
                                <p class="text-2xs text-slate-500">Jika dimatikan, pengguna ini tidak akan diizinkan masuk ke dalam sistem.</p>
                            </div>
                        </label>
                    @endif
                    @error('is_active')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bagian Ganti Kata Sandi (Opsional) --}}
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 sm:p-5 space-y-4">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 sm:text-sm">Ubah Kata Sandi (Opsional)</h2>
                        <p class="mt-0.5 text-2xs text-slate-500">Biarkan formulir kata sandi kosong jika tidak ingin mengubah password lama pengguna.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-xs font-medium text-slate-700">
                                Kata Sandi Baru
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm @error('password') border-rose-300 @enderror"
                            >
                            @error('password')
                                <p class="mt-1 text-2xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium text-slate-700">
                                Konfirmasi Kata Sandi Baru
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                placeholder="Ulangi kata sandi baru"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 sm:text-sm"
                            >
                        </div>
                    </div>
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts::app>
