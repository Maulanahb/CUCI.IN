<x-layouts::app title="Dashboard Pelanggan">
    <div class="space-y-6">
        {{-- Banner Selamat Datang Pelanggan --}}
        <div class="rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50 via-white to-brand-50/50 p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-800">
                        Pelanggan CUCI.IN
                    </span>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="text-xs text-slate-500 sm:text-sm">
                        Pantau status pengerjaan cucian dan riwayat transaksi laundry Anda secara real-time.
                    </p>
                </div>

                <div class="text-xs text-slate-400">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>

        {{-- Kartu Info Ringkas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Nomor Telepon / WhatsApp</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $customer->phone ?? 'Belum diatur' }}</p>
                <p class="mt-1 text-xs text-slate-400">Untuk notifikasi status cucian</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Email Terdaftar</p>
                <p class="mt-2 text-lg font-bold text-slate-900 truncate">{{ auth()->user()->email }}</p>
                <p class="mt-1 text-xs text-brand-600 font-medium">Akun aktif</p>
            </div>

            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Total Transaksi</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $customer ? $customer->transactions()->count() : 0 }}</p>
                <p class="mt-1 text-xs text-slate-400">Riwayat transaksi laundry</p>
            </div>
        </div>

        {{-- Riwayat Cucian & Transaksi Terbaru --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-semibold text-slate-900">Riwayat Pesanan Terbaru</h3>
                <span class="text-xs text-slate-400">Update berkala</span>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="py-12 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h4 class="mt-3 text-sm font-semibold text-slate-900">Belum Ada Transaksi Cucian</h4>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Anda belum memiliki cucian aktif. Kunjungi gerai CUCI.IN terdekat untuk mencuci pakaian Anda dengan layanan prima!
                    </p>
                </div>
            @else
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($recentTransactions as $transaction)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $transaction->invoice_number ?? 'Invoice #'.$transaction->id }}</p>
                                <p class="text-xs text-slate-400">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {{ $transaction->status ?? 'Diproses' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
