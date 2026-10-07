<x-layouts::app title="Dashboard Admin">
    <div class="space-y-6">
        {{-- Banner Selamat Datang --}}
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 via-white to-emerald-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                        Administrator
                    </span>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="text-xs text-slate-500 sm:text-sm">
                        Pantau seluruh aktivitas operasional laundry dan master data sistem dari sini.
                    </p>
                </div>

                <div class="text-xs text-slate-400">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>

        {{-- Statistik Ringkasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Total Pengguna</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_users'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Admin, Staff & Customer</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Total Pelanggan</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_customers'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Guest & Terdaftar</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Layanan Aktif</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_services'] }}</p>
                <p class="mt-1 text-xs text-emerald-600 font-medium">Siap digunakan kasir</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Total Transaksi</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_transactions'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Keseluruhan riwayat</p>
            </div>
        </div>
    </div>
</x-layouts::app>
