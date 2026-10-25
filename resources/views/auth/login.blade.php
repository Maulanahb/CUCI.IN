<x-layouts::guest title="Masuk">
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
                Masuk ke CUCI.IN
            </h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Silakan masuk untuk melanjutkan ke portal operasional.
            </p>
        </div>

        {{-- Status Notification --}}
        @if (session('status'))
            <x-ui.alert variant="success" class="mt-5 shadow-2xs">
                {{ session('status') }}
            </x-ui.alert>
        @endif

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4" novalidate>
            @csrf

            {{-- Input Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700">Email</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-mail class="size-4" />
                    </div>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="Masukkan alamat email Anda"
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

            {{-- Input Password dengan Toggle Show/Hide Alpine.js --}}
            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-lock class="size-4" />
                    </div>
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        required
                        placeholder="Masukkan kata sandi akun"
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

            {{-- Link Lupa Password & Remember Me --}}
            <div class="flex items-center justify-between pt-1 text-xs">
                <label class="inline-flex cursor-pointer select-none items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-600" />
                    <span>Ingat saya</span>
                </label>

                <a href="{{ route('password.request') }}" class="font-medium text-brand-700 transition hover:text-brand-800 hover:underline">
                    Lupa Password?
                </a>
            </div>

            {{-- Tombol Submit --}}
            <button
                type="submit"
                id="login-submit-button"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-center text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-[0.99] cursor-pointer"
            >
                <x-lucide-log-in class="size-4" />
                <span>Masuk</span>
            </button>
        </form>
    </div>

    {{-- Link Registrasi & Lacak Nota Bawah Card --}}
    <div class="mt-6 space-y-2 text-center text-xs">
        <p class="text-slate-600">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-semibold text-brand-700 transition hover:text-brand-800 hover:underline">
                Daftar Pelanggan
            </a>
        </p>

        <p class="text-slate-500">
            Ingin lacak pesanan tanpa login?
            <a href="{{ route('transactions.lookup') }}" class="font-semibold text-brand-700 transition hover:text-brand-800 hover:underline">
                Cek Transaksi
            </a>
        </p>
    </div>
</x-layouts::guest>
