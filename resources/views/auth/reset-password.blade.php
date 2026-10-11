<x-layouts::guest title="Atur Ulang Password">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm sm:p-10">
        {{-- Header & Logo --}}
        <div class="flex flex-col items-center text-center">
            <div class="flex items-center gap-2.5">
                <x-app-logo class="size-9 drop-shadow-sm" />
                <span class="text-xl font-bold tracking-tight text-brand-700">CUCI<span class="text-brand-500">.IN</span></span>
            </div>

            <p class="mt-2 text-xs font-medium text-slate-500">
                Sistem Manajemen Operasional Laundry
            </p>

            <h2 class="mt-6 text-xl font-bold tracking-tight text-slate-900">
                Atur Ulang Kata Sandi
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Silakan masukkan kata sandi baru yang aman untuk akun Anda.
            </p>
        </div>

        {{-- Form Reset Password --}}
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4" novalidate>
            @csrf

            {{-- Token Rahasia --}}
            <input type="hidden" name="token" value="{{ $token }}" />

            {{-- Input Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700">Email Akun</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $email) }}"
                        required
                        autofocus
                        placeholder="nama@email.com"
                        @class([
                            'w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition focus:outline-none focus:ring-2',
                            'border-slate-300 focus:border-brand-600 focus:ring-brand-500/20' => ! $errors->has('email'),
                            'border-red-400 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-red-500/20' => $errors->has('email'),
                        ])
                    />
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Password Baru --}}
            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-semibold text-slate-700">Password Baru</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        required
                        placeholder="Minimal 8 karakter"
                        @class([
                            'w-full rounded-xl border py-2.5 pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 transition focus:outline-none focus:ring-2',
                            'border-slate-300 focus:border-brand-600 focus:ring-brand-500/20' => ! $errors->has('password'),
                            'border-red-400 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-red-500/20' => $errors->has('password'),
                        ])
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none"
                        :aria-label="show ? 'Sembunyikan password' : 'Lihat password'"
                    >
                        <svg x-show="!show" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-cloak x-show="show" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Konfirmasi Password --}}
            <div x-data="{ show: false }">
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700">Ulangi Password Baru</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        required
                        placeholder="Ulangi password baru"
                        @class([
                            'w-full rounded-xl border py-2.5 pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 transition focus:outline-none focus:ring-2',
                            'border-slate-300 focus:border-brand-600 focus:ring-brand-500/20' => ! $errors->has('password_confirmation'),
                            'border-red-400 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-red-500/20' => $errors->has('password_confirmation'),
                        ])
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none"
                        :aria-label="show ? 'Sembunyikan konfirmasi password' : 'Lihat konfirmasi password'"
                    >
                        <svg x-show="!show" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-cloak x-show="show" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Submit --}}
            <button
                type="submit"
                id="reset-password-submit-button"
                class="mt-2 w-full rounded-xl bg-brand-600 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-[0.99]"
            >
                Simpan Kata Sandi Baru
            </button>
        </form>
    </div>

    {{-- Link Bawah Card --}}
    <div class="mt-6 text-center text-xs text-slate-600">
        Batal atur ulang sandi?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800 hover:underline">
            Kembali ke Halaman Masuk
        </a>
    </div>
</x-layouts::guest>
