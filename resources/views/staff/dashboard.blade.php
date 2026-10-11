<x-layouts::app title="Dashboard Staff">
    <div class="space-y-6">
        {{-- Banner Selamat Datang --}}
        <div class="rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                        Staff Operasional
                    </span>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="text-xs text-slate-500 sm:text-sm">
                        Kelola pelanggan, transaksi kasir POS, dan pembaruan status cucian hari ini.
                    </p>
                </div>

                <div class="text-xs text-slate-400">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>

        {{-- Statistik Ringkasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Transaksi Hari Ini</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['transactions_today'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Dibuat hari ini</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Pelanggan Terdaftar</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_customers'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Data customer laundry</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Layanan Tersedia</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active_services'] }}</p>
                <p class="mt-1 text-xs text-brand-600 font-medium">Layanan aktif</p>
            </div>
        </div>
    </div>
</x-layouts::app>
