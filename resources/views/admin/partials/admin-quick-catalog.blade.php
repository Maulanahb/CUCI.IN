{{-- ============================================================
    KATALOG LAYANAN AKTIF & PANDUAN OPERASIONAL ADMIN
============================================================= --}}
<div class="space-y-6 lg:col-span-1">
    {{-- Card Master Layanan Aktif --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Katalog Layanan</h3>
                <p class="text-xs text-slate-500">Tarif aktif per kilogram laundry.</p>
            </div>
            <a href="{{ route('admin.services.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 cursor-pointer">
                Kelola &rarr;
            </a>
        </div>

        <div class="space-y-3">
            @forelse ($activeServices as $service)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition hover:border-slate-200">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">{{ $service->name }}</h4>
                        <p class="text-[11px] text-slate-500 tabular-nums">Estimasi {{ $service->estimated_duration }} Jam</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-extrabold text-brand-700 tabular-nums">
                            Rp {{ number_format($service->price, 0, ',', '.') }}
                        </p>
                        <span class="text-[10px] text-slate-500">/ kg</span>
                    </div>
                </div>
            @empty
                <p class="py-4 text-center text-xs text-slate-500">Belum ada layanan aktif.</p>
            @endforelse
        </div>

        <div class="mt-4 border-t border-slate-100 pt-3">
            <a
                href="{{ route('admin.services.index') }}"
                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50/70 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-100 active:scale-98 cursor-pointer"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Tambah / Kelola Master Layanan</span>
            </a>
        </div>
    </div>

    {{-- Card Bantuan & Panduan Cepat Operasional --}}
    <div class="rounded-2xl border border-brand-200/80 bg-gradient-to-br from-brand-50/70 via-white to-brand-50/30 p-5 shadow-xs">
        <div class="flex items-center gap-2">
            <div class="flex size-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h4 class="text-xs font-bold text-slate-900">Operasional Laundry</h4>
        </div>
        <p class="mt-2 text-xs leading-relaxed text-slate-600">
            Admin memiliki akses penuh untuk mengelola master layanan, memantau daftar transaksi, mencetak struk kasir thermal, serta mengelola data pelanggan dan peran pengguna.
        </p>
    </div>
</div>
