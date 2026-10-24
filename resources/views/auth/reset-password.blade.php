<x-layouts::guest title="Atur Ulang Password">
    <div class="rounded-2xl border border-slate-200/80 bg-white/95 p-6 shadow-xs backdrop-blur-xs sm:p-8">
        {{-- Header & Logo Brand --}}
        <div class="flex flex-col items-center text-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 transition hover:opacity-90">
                <x-app-logo class="size-10 drop-shadow-xs" />
                <span class="text-xl font-bold tracking-tight text-brand-700">CUCI<span class="text-brand-500">.IN</span></span>
            </a>

            <p class="mt-2 text-xs font-medium text-slate-500">
                Sistem Manajemen Operasional Laundry
            </p>

            <h1 class="mt-5 text-xl font-bold tracking-tight text-slate-900">
                Atur Ulang Kata Sandi
            </h1>
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
                        <x-lucide-mail class="size-4" />
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
                    <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
                        <x-lucide-alert-circle class="size-3.5" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Input Password Baru --}}
            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-semibold text-slate-700">Password Baru</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-lock class="size-4" />
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
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600 focus:outline-none cursor-pointer"
                        :aria-label="show ? 'Sembunyikan password' : 'Lihat password'"
                    >
                        <x-lucide-eye x-show="!show" class="size-4" />
                        <x-lucide-eye-off x-cloak x-show="show" class="size-4" />
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
                        <x-lucide-alert-circle class="size-3.5" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Input Konfirmasi Password --}}
            <div x-data="{ show: false }">
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700">Ulangi Password Baru</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-shield-check class="size-4" />
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
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600 focus:outline-none cursor-pointer"
                        :aria-label="show ? 'Sembunyikan konfirmasi password' : 'Lihat konfirmasi password'"
                    >
                        <x-lucide-eye x-show="!show" class="size-4" />
                        <x-lucide-eye-off x-cloak x-show="show" class="size-4" />
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
                        <x-lucide-alert-circle class="size-3.5" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Tombol Submit --}}
            <button
                type="submit"
                id="reset-password-submit-button"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-center text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-[0.99] cursor-pointer"
            >
                <x-lucide-check class="size-4" />
                <span>Simpan Kata Sandi Baru</span>
            </button>
        </form>
    </div>

    {{-- Link Bawah Card --}}
    <div class="mt-6 text-center text-xs text-slate-600">
        Batal atur ulang sandi?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 transition hover:text-brand-800 hover:underline">
            Kembali ke Halaman Masuk
        </a>
    </div>
</x-layouts::guest>
