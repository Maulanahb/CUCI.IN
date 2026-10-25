<x-layouts::guest title="Lupa Password">
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
                Lupa Kata Sandi?
            </h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
            </p>
        </div>

        {{-- Status Notification --}}
        @if (session('status'))
            <x-ui.alert variant="success" class="mt-5 shadow-2xs">
                {{ session('status') }}
            </x-ui.alert>
        @endif

        {{-- Form Permintaan Link Reset --}}
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" novalidate>
            @csrf

            {{-- Input Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700">Email Terdaftar</label>
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

            {{-- Tombol Submit --}}
            <button
                type="submit"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-center text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-[0.99] cursor-pointer"
            >
                <x-lucide-send class="size-4" />
                <span>Kirim Tautan Reset Password</span>
            </button>
        </form>
    </div>

    {{-- Link Kembali ke Login --}}
    <div class="mt-6 text-center text-xs text-slate-600">
        Ingat kata sandi Anda?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 transition hover:text-brand-800 hover:underline">
            Kembali ke Halaman Masuk
        </a>
    </div>
</x-layouts::guest>
