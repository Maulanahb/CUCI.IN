<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="CUCI.IN — Layanan laundry kiloan dan satuan modern, higienis, dan terpercaya. Pantau status cucian Anda secara real-time dengan timbangan akurat.">

        <title>{{ config('app.name', 'CUCI.IN') }} — Laundry Kiloan &amp; Satuan Modern</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-slate-50/60 font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white" x-data="{ mobileMenuOpen: false }">
        {{-- Sticky Glassmorphism Header --}}
        <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/85 backdrop-blur-md transition-all">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                {{-- Logo Brand --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 transition hover:opacity-90">
                    <x-app-logo class="size-9 drop-shadow-2xs" />
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">
                        CUCI<span class="text-brand-600">.IN</span>
                    </span>
                </a>

                {{-- Desktop Navigation Links --}}
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#fitur" class="transition hover:text-brand-600">Fitur Layanan</a>
                    <a href="#estimasi" class="transition hover:text-brand-600">Kalkulator Tarif</a>
                    <a href="#keunggulan" class="transition hover:text-brand-600">Keunggulan</a>
                    <a href="#alur" class="transition hover:text-brand-600">Cara Kerja</a>
                </nav>

                {{-- Action Buttons --}}
                <div class="hidden sm:flex items-center gap-3">
                    <a
                        href="{{ route('transactions.lookup') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:border-brand-300 hover:bg-brand-50/40 hover:text-brand-700"
                    >
                        <x-lucide-search class="size-3.5 text-slate-500" />
                        <span>Lacak Cucian</span>
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                        >
                            <x-lucide-layout-dashboard class="size-3.5" />
                            <span>Buka Dashboard</span>
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            <x-lucide-log-in class="size-3.5 text-slate-500" />
                            <span>Masuk</span>
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                            >
                                <span>Daftar</span>
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Mobile Hamburger Button --}}
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    aria-label="Toggle Navigation Menu"
                >
                    <x-lucide-menu class="size-5" x-show="!mobileMenuOpen" />
                    <x-lucide-x class="size-5" x-show="mobileMenuOpen" x-cloak />
                </button>
            </div>

            {{-- Mobile Dropdown Menu --}}
            <div
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="border-b border-slate-200 bg-white px-4 py-4 md:hidden space-y-3"
                x-cloak
            >
                <a href="#fitur" @click="mobileMenuOpen = false" class="block text-sm font-medium text-slate-700 hover:text-brand-600">Fitur Layanan</a>
                <a href="#estimasi" @click="mobileMenuOpen = false" class="block text-sm font-medium text-slate-700 hover:text-brand-600">Kalkulator Tarif</a>
                <a href="#keunggulan" @click="mobileMenuOpen = false" class="block text-sm font-medium text-slate-700 hover:text-brand-600">Keunggulan</a>
                <a href="#alur" @click="mobileMenuOpen = false" class="block text-sm font-medium text-slate-700 hover:text-brand-600">Cara Kerja</a>

                <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                    <a
                        href="{{ route('transactions.lookup') }}"
                        class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-2.5 text-xs font-semibold text-slate-700"
                    >
                        <x-lucide-search class="size-4 text-slate-500" />
                        <span>Lacak Status Nota</span>
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex items-center justify-center gap-2 rounded-xl bg-brand-600 py-2.5 text-xs font-semibold text-white"
                        >
                            <x-lucide-layout-dashboard class="size-4" />
                            <span>Buka Dashboard</span>
                        </a>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <a
                                href="{{ route('login') }}"
                                class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 py-2.5 text-xs font-semibold text-slate-700"
                            >
                                <span>Masuk</span>
                            </a>
                            <a
                                href="{{ route('register') }}"
                                class="flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 py-2.5 text-xs font-semibold text-white"
                            >
                                <span>Daftar Akun</span>
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            {{-- Hero Section --}}
            <section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-slate-50/40 pt-12 pb-16 sm:pt-20 sm:pb-24">
                {{-- Decorative Glow Circles --}}
                <div class="pointer-events-none absolute -top-24 left-1/2 -z-10 h-96 w-96 -translate-x-1/2 rounded-full bg-brand-400/15 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute top-1/2 right-0 -z-10 h-72 w-72 rounded-full bg-sky-300/15 blur-3xl" aria-hidden="true"></div>

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        {{-- Main Headline --}}
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                            Laundry Bersih, Higienis &amp; <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-sky-600">Terpantau Real-Time</span>
                        </h1>

                        {{-- Subheadline --}}
                        <p class="mt-4 text-base text-slate-600 sm:text-lg leading-relaxed">
                            Nikmati kemudahan mencuci tanpa rasa cemas. Timbangan presisi, pakaian tidak pernah dicampur, dan Anda dapat memantau status cucian langsung dari ponsel menggunakan kode nota.
                        </p>

                        {{-- Quick Tracking Box Form --}}
                        <div class="mt-8 mx-auto max-w-2xl rounded-2xl border border-slate-200/90 bg-white p-2.5 shadow-lg sm:p-3">
                            <form action="{{ route('transactions.lookup') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                                <div class="relative w-full flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <x-lucide-receipt class="size-4.5" />
                                    </div>
                                    <input
                                        type="text"
                                        name="transaction_code"
                                        placeholder="Masukkan kode nota (contoh: TRX-20261024-0001)..."
                                        required
                                        class="w-full rounded-xl border border-transparent bg-slate-50/70 py-3 pl-10 pr-4 text-sm font-mono text-slate-900 placeholder:text-slate-400 placeholder:font-sans focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                    >
                                </div>
                                <button
                                    type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 shrink-0 cursor-pointer"
                                >
                                    <span>Lacak Sekarang</span>
                                    <x-lucide-arrow-right class="size-4" />
                                </button>
                            </form>
                            <p class="mt-2.5 text-center text-xs text-slate-500">
                                Tanpa perlu login. Cukup ketik kode transaksi yang tercetak pada struk thermal Anda.
                            </p>
                        </div>

                        {{-- Trust Value Badges --}}
                        <div class="mt-10 flex flex-wrap items-center justify-center gap-4 text-xs font-medium text-slate-600">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100/90 px-3 py-1">
                                <x-lucide-shield-check class="size-3.5 text-emerald-600" />
                                1 Mesin 1 Pelanggan
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100/90 px-3 py-1">
                                <x-lucide-scale class="size-3.5 text-brand-600" />
                                Timbangan Akurat Desimal
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100/90 px-3 py-1">
                                <x-lucide-clock class="size-3.5 text-sky-600" />
                                Estimasi Selesai Tepat
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100/90 px-3 py-1">
                                <x-lucide-printer class="size-3.5 text-slate-600" />
                                Struk Thermal Resmi
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Interactive Pricing & Duration Calculator --}}
            <section id="estimasi" class="border-y border-slate-200/80 bg-white py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">
                            <x-lucide-calculator class="size-3.5" />
                            <span>Simulasi Biaya &amp; Durasi</span>
                        </div>
                        <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            Kalkulator Estimasi Biaya Laundry
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Hitung perkiraan total tagihan dan waktu pengerjaan secara transparan sebelum mengantar pakaian Anda.
                        </p>
                    </div>

                    {{-- Alpine.js Calculator Card --}}
                    <div
                        class="mx-auto mt-10 max-w-3xl rounded-2xl border border-slate-200/90 bg-slate-50/40 p-5 shadow-xs sm:p-8"
                        x-data="{
                            selectedService: 'reguler',
                            weight: 3.0,
                            services: {
                                reguler: { name: 'Cuci Kering Setrika Reguler', price: 6000, duration: 48, note: 'Pakaian dicuci wangi, disetrika uap rapi, dan dikemas plastik higienis.' },
                                express: { name: 'Cuci Kilat Express 24 Jam', price: 10000, duration: 24, note: 'Prioritas pengerjaan 1 hari, cocok untuk pakaian darurat bepergian.' },
                                super: { name: 'Super Express 8 Jam (Same Day)', price: 15000, duration: 8, note: 'Masuk pagi, sore hari siap diambil dalam kondisi rapi.' },
                                setrika: { name: 'Setrika Rapi Saja', price: 4000, duration: 24, note: 'Khusus pakaian yang sudah Anda cuci sendiri di rumah.' }
                            },
                            get current() { return this.services[this.selectedService]; },
                            get total() { return Math.round(this.weight * this.current.price); },
                            formatMoney(val) { return 'Rp ' + Number(val).toLocaleString('id-ID'); }
                        }"
                    >
                        {{-- Step 1: Pilih Paket Layanan --}}
                        <div>
                            <label class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                1. Pilih Paket Layanan
                            </label>
                            <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <button
                                    type="button"
                                    class="flex items-center justify-between rounded-xl border p-3.5 text-left transition"
                                    :class="selectedService === 'reguler' ? 'border-brand-500 bg-white shadow-xs ring-2 ring-brand-500/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                                    @click="selectedService = 'reguler'"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Cuci Kering Setrika</p>
                                        <p class="text-2xs text-slate-500">Estimasi 48 Jam (2 Hari)</p>
                                    </div>
                                    <span class="font-mono text-xs font-extrabold text-brand-700">Rp 6.000 /kg</span>
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center justify-between rounded-xl border p-3.5 text-left transition"
                                    :class="selectedService === 'express' ? 'border-brand-500 bg-white shadow-xs ring-2 ring-brand-500/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                                    @click="selectedService = 'express'"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Kilat Express 24 Jam</p>
                                        <p class="text-2xs text-slate-500">Estimasi 1 Hari</p>
                                    </div>
                                    <span class="font-mono text-xs font-extrabold text-brand-700">Rp 10.000 /kg</span>
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center justify-between rounded-xl border p-3.5 text-left transition"
                                    :class="selectedService === 'super' ? 'border-brand-500 bg-white shadow-xs ring-2 ring-brand-500/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                                    @click="selectedService = 'super'"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Super Express 8 Jam</p>
                                        <p class="text-2xs text-slate-500">Selesai di Hari yang Sama</p>
                                    </div>
                                    <span class="font-mono text-xs font-extrabold text-brand-700">Rp 15.000 /kg</span>
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center justify-between rounded-xl border p-3.5 text-left transition"
                                    :class="selectedService === 'setrika' ? 'border-brand-500 bg-white shadow-xs ring-2 ring-brand-500/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                                    @click="selectedService = 'setrika'"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Setrika Rapi Saja</p>
                                        <p class="text-2xs text-slate-500">Estimasi 24 Jam</p>
                                    </div>
                                    <span class="font-mono text-xs font-extrabold text-brand-700">Rp 4.000 /kg</span>
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Slider Berat Cucian (Kg) --}}
                        <div class="mt-6 border-t border-slate-200/80 pt-6">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                                    2. Perkiraan Berat Pakaian
                                </label>
                                <span class="font-mono text-base font-extrabold text-brand-700">
                                    <span x-text="Number(weight).toFixed(1)"></span> Kg
                                </span>
                            </div>
                            <div class="mt-3">
                                <input
                                    type="range"
                                    min="1"
                                    max="20"
                                    step="0.5"
                                    x-model="weight"
                                    class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-slate-200 accent-brand-600 focus:outline-none"
                                >
                                <div class="mt-1 flex justify-between text-2xs font-mono text-slate-600">
                                    <span>1 Kg (3-4 potong)</span>
                                    <span>5 Kg (1 minggu)</span>
                                    <span>10 Kg</span>
                                    <span>20 Kg</span>
                                </div>
                            </div>
                        </div>

                        {{-- Output Panel: Estimasi Biaya & Waktu --}}
                        <div class="mt-6 rounded-xl border border-brand-200/80 bg-gradient-to-br from-brand-50 via-white to-sky-50/50 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-medium text-slate-500" x-text="current.name"></p>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        <span class="font-mono text-3xl font-extrabold text-slate-900" x-text="formatMoney(total)"></span>
                                        <span class="text-xs font-mono text-slate-500" x-text="'(' + Number(weight).toFixed(1) + ' kg × ' + formatMoney(current.price) + ')'"></span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-600" x-text="current.note"></p>
                                </div>

                                <div class="shrink-0 text-left sm:text-right border-t sm:border-t-0 border-slate-200/60 pt-3 sm:pt-0">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-sky-800">
                                        <x-lucide-clock class="size-3.5" />
                                        Estimasi Selesai:
                                    </span>
                                    <p class="font-mono text-lg font-bold text-slate-900" x-text="current.duration + ' Jam'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4 Pilar Keunggulan CUCI.IN --}}
            <section id="keunggulan" class="py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">
                            <x-lucide-award class="size-3.5" />
                            <span>Jaminan Kepuasan</span>
                        </div>
                        <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            Mengapa Memilih Layanan CUCI.IN?
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Standar operasional higienis yang dirancang khusus untuk kenyamanan dan ketenangan Anda.
                        </p>
                    </div>

                    <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- Keunggulan 1 --}}
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition hover:border-brand-300 hover:shadow-sm">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                                <x-lucide-shield-check class="size-5.5" />
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">1 Mesin 1 Pelanggan</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Pakaian Anda tidak pernah dicampur dengan pakaian orang lain untuk menjamin kebersihan maksimal dan mencegah tertukar.
                            </p>
                        </div>

                        {{-- Keunggulan 2 --}}
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition hover:border-brand-300 hover:shadow-sm">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                <x-lucide-scale class="size-5.5" />
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Timbangan Presisi Desimal</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Penimbangan digital akurat hingga desimal 0,1 kg tanpa pembulatan sepihak yang merugikan pelanggan.
                            </p>
                        </div>

                        {{-- Keunggulan 3 --}}
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition hover:border-brand-300 hover:shadow-sm">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                                <x-lucide-activity class="size-5.5" />
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Pelacakan Real-Time</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Ketahui posisi cucian Anda: Diterima, Diproses, Selesai, atau Diambil hanya dengan nomor nota tanpa harus menelepon outlet.
                            </p>
                        </div>

                        {{-- Keunggulan 4 --}}
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition hover:border-brand-300 hover:shadow-sm">
                            <div class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                                <x-lucide-printer class="size-5.5" />
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Struk Thermal &amp; QRIS</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Menerima pembayaran fleksibel (Tunai, QRIS, Bank Transfer) dengan bukti struk thermal fisik dan riwayat digital sah.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Alur Layanan Mudah (Cara Kerja) --}}
            <section id="alur" class="border-t border-slate-200/80 bg-slate-50/50 py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">
                            <x-lucide-route class="size-3.5" />
                            <span>Proses Cepat &amp; Praktis</span>
                        </div>
                        <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            3 Langkah Mudah Menggunakan CUCI.IN
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Pengalaman mencuci tanpa ribet dari awal penyerahan hingga pakaian kembali wangi ke tangan Anda.
                        </p>
                    </div>

                    <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                        {{-- Step 1 --}}
                        <div class="relative rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs text-center">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-brand-600 font-mono text-lg font-bold text-white shadow-xs">
                                1
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Antar Pakaian ke Kasir</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Kasir menimbang pakaian dengan timbangan digital, menginput rincian ke sistem POS, dan memberikan struk nota resmi.
                            </p>
                        </div>

                        {{-- Step 2 --}}
                        <div class="relative rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs text-center">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-brand-600 font-mono text-lg font-bold text-white shadow-xs">
                                2
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Pantau Progres dari Rumah</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Masukkan kode transaksi pada menu Lacak Cucian di website untuk melihat tahapan cuci dan estimasi selesai secara langsung.
                            </p>
                        </div>

                        {{-- Step 3 --}}
                        <div class="relative rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs text-center">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-brand-600 font-mono text-lg font-bold text-white shadow-xs">
                                3
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Ambil &amp; Pakaian Siap Pakai</h3>
                            <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                Begitu status berubah menjadi Selesai, ambil cucian Anda yang sudah wangi, bersih, dan rapi terbungkus plastik pelindung.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CTA Banner --}}
            <section class="py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-700 via-brand-600 to-sky-700 p-8 sm:p-12 text-white shadow-xl">
                        {{-- Background Glow --}}
                        <div class="pointer-events-none absolute -right-16 -top-16 size-72 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>

                        <div class="relative z-10 max-w-2xl">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-xs">
                                <x-lucide-sparkles class="size-3.5" />
                                Outlet Laundry Terpercaya
                            </span>
                            <h2 class="mt-4 text-2xl font-bold tracking-tight sm:text-4xl">
                                Sudah Punya Nota Cucian? Cek Status Pakaian Anda Sekarang!
                            </h2>
                            <p class="mt-3 text-sm text-brand-100 leading-relaxed sm:text-base">
                                Tidak perlu repot menanyakan ke kasir. Cukup masukkan nomor transaksi dan dapatkan pembaruan status pengerjaan secara instan.
                            </p>

                            <div class="mt-6 flex flex-wrap items-center gap-3">
                                <a
                                    href="{{ route('transactions.lookup') }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50"
                                >
                                    <x-lucide-search class="size-4" />
                                    <span>Lacak Status Nota Sekarang</span>
                                </a>
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/20"
                                >
                                    <span>Masuk ke Akun</span>
                                    <x-lucide-arrow-right class="size-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-slate-200/80 bg-white py-12 text-xs text-slate-500">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    {{-- Kolom 1: Profil Brand --}}
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-2.5">
                            <x-app-logo class="size-8 drop-shadow-2xs" />
                            <span class="text-lg font-extrabold tracking-tight text-slate-900">
                                CUCI<span class="text-brand-600">.IN</span>
                            </span>
                        </div>
                        <p class="mt-3 max-w-md text-xs leading-relaxed text-slate-500">
                            Aplikasi sistem manajemen laundry modern berbasis web. Membantu kasir mencatat penerimaan cucian, timbangan akurat, cetak struk thermal, dan memudahkan pelanggan melacak status cucian secara transparan.
                        </p>
                        <div class="mt-4 flex items-center gap-2 text-2xs font-semibold text-slate-600">
                            <span class="inline-flex size-2 rounded-full bg-emerald-500"></span>
                            <span>Buka Setiap Hari: 07.00 - 21.00 WIB</span>
                        </div>
                    </div>

                    {{-- Kolom 2: Navigasi Cepat --}}
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Navigasi</h4>
                        <ul class="mt-3 space-y-2">
                            <li><a href="#fitur" class="hover:text-brand-600 transition">Fitur Layanan</a></li>
                            <li><a href="#estimasi" class="hover:text-brand-600 transition">Kalkulator Tarif</a></li>
                            <li><a href="#keunggulan" class="hover:text-brand-600 transition">Keunggulan Kami</a></li>
                            <li><a href="#alur" class="hover:text-brand-600 transition">Cara Kerja</a></li>
                        </ul>
                    </div>

                    {{-- Kolom 3: Layanan Pelanggan --}}
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Akses Sistem</h4>
                        <ul class="mt-3 space-y-2">
                            <li><a href="{{ route('transactions.lookup') }}" class="hover:text-brand-600 transition">Lacak Status Nota</a></li>
                            <li><a href="{{ route('login') }}" class="hover:text-brand-600 transition">Masuk Akun (Login)</a></li>
                            @if (Route::has('register'))
                                <li><a href="{{ route('register') }}" class="hover:text-brand-600 transition">Pendaftaran Pelanggan</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-2xs">
                    <p>&copy; {{ date('Y') }} CUCI.IN Laundry System. Seluruh Hak Cipta Dilindungi.</p>
                    <p class="font-mono text-slate-400">D4 Sistem Informasi Bisnis — Milestone 2</p>
                </div>
            </div>
        </footer>
    </body>
</html>
