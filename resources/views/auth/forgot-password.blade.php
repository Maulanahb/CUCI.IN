<x-layouts::guest title="Lupa Password">
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
                Lupa Kata Sandi?
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm max-w-sm">
                Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
            </p>
        </div>

        {{-- Status Notification --}}
        @if (session('status'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        {{-- Form Permintaan Link Reset --}}
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" novalidate>
            @csrf

            {{-- Input Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700">Email Terdaftar</label>
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
                        value="{{ old('email') }}"
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

            {{-- Tombol Submit --}}
            <button
                type="submit"
                id="forgot-password-submit-button"
                class="mt-2 w-full rounded-xl bg-brand-600 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-[0.99]"
            >
                Kirim Tautan Reset Password
            </button>
        </form>
    </div>

    {{-- Link Kembali ke Login --}}
    <div class="mt-6 text-center text-xs text-slate-600">
        Ingat kata sandi Anda?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800 hover:underline">
            Masuk ke Akun
        </a>
    </div>
</x-layouts::guest>
