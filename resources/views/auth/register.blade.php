<x-layouts::guest title="Daftar Akun Pelanggan" maxWidth="max-w-lg">
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
                Daftar Akun Pelanggan
            </h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Pantau progres cucian dan histori transaksi laundry Anda secara mudah.
            </p>
        </div>

        {{-- Form Registrasi --}}
        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4" novalidate>
            @csrf

            {{-- Honeypot Trap (Anti-Bot Zero-Friction) --}}
            <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
                <label for="_hp_website">Jangan isi kolom ini jika Anda manusia</label>
                <input type="text" name="_hp_website" id="_hp_website" value="" tabindex="-1" autocomplete="off" />
                <input type="hidden" name="_hp_timestamp" value="{{ time() }}" />
            </div>

            {{-- Nama Lengkap --}}
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700">Nama Lengkap</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-user class="size-4" />
                    </div>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        placeholder="Masukkan nama lengkap Anda"
                        @class([
                            'w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition focus:outline-none focus:ring-2',
                            'border-slate-300 focus:border-brand-600 focus:ring-brand-500/20' => ! $errors->has('name'),
                            'border-red-400 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-red-500/20' => $errors->has('name'),
                        ])
                    />
                </div>
                @error('name')
                    <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
                        <x-lucide-alert-circle class="size-3.5" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Nomor WhatsApp / Telepon --}}
            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-700">Nomor WhatsApp / HP</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-lucide-phone class="size-4" />
                    </div>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        required
                        placeholder="Contoh: 081234567890"
                        @class([
                            'w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm font-mono text-slate-900 placeholder-slate-400 placeholder:font-sans transition focus:outline-none focus:ring-2',
                            'border-slate-300 focus:border-brand-600 focus:ring-brand-500/20' => ! $errors->has('phone'),
                            'border-red-400 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-red-500/20' => $errors->has('phone'),
                        ])
                    />
                </div>
                @error('phone')
                    <p class="mt-1 flex items-center gap-1 text-xs text-red-600">
                        <x-lucide-alert-circle class="size-3.5" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Email --}}
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

            {{-- Grid 2 Kolom Password di Desktop --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Password --}}
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
                            placeholder="Min. 8 karakter"
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

                {{-- Konfirmasi Password --}}
                <div x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700">Konfirmasi Password</label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-lucide-shield-check class="size-4" />
                        </div>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="show ? 'text' : 'password'"
                            required
                            placeholder="Ulangi password"
                            class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 transition focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
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
                </div>
            </div>

            {{-- Tombol Submit --}}
            <button
                type="submit"
                id="register-submit-button"
                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-center text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-[0.99] cursor-pointer"
            >
                <x-lucide-user-plus class="size-4" />
                <span>Daftar Sekarang</span>
            </button>
        </form>
    </div>

    {{-- Link Masuk Bawah Card --}}
    <div class="mt-6 text-center text-xs text-slate-600">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 transition hover:text-brand-800 hover:underline">
            Masuk ke Akun
        </a>
    </div>
</x-layouts::guest>
