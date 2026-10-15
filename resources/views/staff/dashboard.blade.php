<x-layouts::app title="Dashboard Staff">
    <div class="space-y-6">
        <div class="rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                        Staff Operasional
                    </span>

                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>

                    <p class="text-xs text-slate-500 sm:text-sm">
                        Kelola pelanggan, transaksi, pembayaran, dan operasional status cucian dari sini.
                    </p>
                </div>

                <div class="text-xs text-slate-400">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>

            </div>
        </div>


        {{-- ============================================================
            STATISTIK RINGKASAN
        ============================================================= --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            {{-- Transaksi Hari Ini --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">

                    <p class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">
                        Transaksi Hari Ini
                    </p>

                    <svg
                        class="size-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-brand-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>

                </div>

                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ $stats['transactions_today'] }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Dibuat hari ini
                </p>
            </a>


            {{-- Pelanggan --}}
            <a
                href="{{ route('staff.customers.index') }}"
                class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">

                    <p class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">
                        Pelanggan Terdaftar
                    </p>

                    <svg
                        class="size-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-brand-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>

                </div>

                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ $stats['total_customers'] }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Data customer laundry →
                </p>
            </a>


            {{-- Layanan --}}
            <a
                href="{{ route('staff.services.index') }}"
                class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md"
            >
                <div class="flex items-center justify-between">

                    <p class="text-xs font-medium text-slate-500 transition group-hover:text-brand-600">
                        Layanan Tersedia
                    </p>

                    <svg
                        class="size-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-brand-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>

                </div>

                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ $stats['active_services'] }}
                </p>

                <p class="mt-1 text-xs font-medium text-brand-600">
                    Layanan aktif
                </p>
            </a>

        </div>


        {{-- ============================================================
            MENU OPERASIONAL
        ============================================================= --}}
        <div>

            {{-- Judul --}}
            <div class="mb-4">
                <h3 class="text-base font-semibold text-slate-900">
                    Operasional Laundry
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola transaksi, pembayaran, dan status cucian.
                </p>
            </div>


            {{-- Card Menu --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">


                {{-- ====================================================
                    KELOLA TRANSAKSI
                ===================================================== --}}
                <a
                    href="{{ route('staff.transactions.index') }}"
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md"
                >

                    <div class="flex items-start justify-between">

                        {{-- Icon --}}
                        <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">

                            <svg
                                class="size-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>

                        </div>


                        {{-- Arrow --}}
                        <svg
                            class="size-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-brand-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </div>


                    <h4 class="mt-4 text-sm font-semibold text-slate-900 group-hover:text-brand-600">
                        Kelola Transaksi
                    </h4>

                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        Lihat daftar transaksi, detail cucian, pelanggan,
                        total pembayaran, dan proses pengerjaan laundry.
                    </p>

                    <div class="mt-4 flex items-center gap-1 text-xs font-medium text-brand-600">
                        Lihat transaksi
                        <span class="transition group-hover:translate-x-1">
                            →
                        </span>
                    </div>

                </a>



                {{-- ====================================================
                    PEMBAYARAN
                ===================================================== --}}
                <a
                    href="{{ route('staff.payments.index') }}"
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md"
                >

                    <div class="flex items-start justify-between">

                        {{-- Icon --}}
                        <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">

                            <svg
                                class="size-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2 7h20M5 11h4m-4 4h2m13-8v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h13a2 2 0 012 2z"
                                />
                            </svg>

                        </div>


                        {{-- Arrow --}}
                        <svg
                            class="size-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-brand-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </div>


                    <h4 class="mt-4 text-sm font-semibold text-slate-900 group-hover:text-brand-600">
                        Pembayaran
                    </h4>

                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        Periksa pembayaran customer, lihat bukti pembayaran,
                        dan lakukan verifikasi pembayaran secara manual.
                    </p>

                    <div class="mt-4 flex items-center gap-1 text-xs font-medium text-brand-600">
                        Kelola pembayaran
                        <span class="transition group-hover:translate-x-1">
                            →
                        </span>
                    </div>

                </a>



                {{-- ====================================================
                    STATUS OPERASIONAL
                ===================================================== --}}
                <a
                    href="{{ route('staff.transactions.index') }}"
                    class="group rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md"
                >

                    <div class="flex items-start justify-between">

                        {{-- Icon --}}
                        <div class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">

                            <svg
                                class="size-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                        </div>


                        {{-- Arrow --}}
                        <svg
                            class="size-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-brand-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </div>


                    <h4 class="mt-4 text-sm font-semibold text-slate-900 group-hover:text-brand-600">
                        Status Operasional
                    </h4>

                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        Pantau proses cucian dan perbarui status mulai dari
                        diterima, diproses, selesai, hingga diambil.
                    </p>

                    <div class="mt-4 flex items-center gap-1 text-xs font-medium text-brand-600">
                        Kelola status
                        <span class="transition group-hover:translate-x-1">
                            →
                        </span>
                    </div>

                </a>

            </div>
        </div>


        {{-- ============================================================
            ALUR OPERASIONAL
        ============================================================= --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">

            <div class="mb-5">
                <h3 class="text-base font-semibold text-slate-900">
                    Alur Operasional Cucian
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Tahapan proses transaksi laundry yang dikelola Staff.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

                {{-- Diterima --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <div class="flex items-center gap-3">

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-700 shadow-sm">
                            1
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Diterima
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Cucian masuk
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Diproses --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <div class="flex items-center gap-3">

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-700 shadow-sm">
                            2
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Diproses
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Cucian dikerjakan
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Selesai --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <div class="flex items-center gap-3">

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-700 shadow-sm">
                            3
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Selesai
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Siap diambil
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Diambil --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <div class="flex items-center gap-3">

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-700 shadow-sm">
                            4
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-900">
                                Diambil
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Transaksi selesai
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Dibatalkan --}}
                <div class="rounded-xl border border-red-100 bg-red-50 p-4">

                    <div class="flex items-center gap-3">

                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-red-600 shadow-sm">
                            !
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-red-700">
                                Dibatalkan
                            </p>

                            <p class="mt-0.5 text-[11px] text-red-500">
                                Transaksi dibatalkan
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</x-layouts::app>