{{-- ============================================================
    ANTREAN CUCIAN TERKINI (KOLOM KIRI 2/3)
============================================================= --}}
<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2">
    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Antrean Cucian Terkini</h2>
            <p class="text-xs text-slate-500">Order cucian yang tercatat masuk hari ini di kasir POS.</p>
        </div>
        <a
            href="{{ route('staff.transactions.index') }}"
            class="text-xs font-semibold text-brand-600 transition hover:text-brand-700 cursor-pointer"
        >
            Lihat Semua &rarr;
        </a>
    </div>

    @if ($todayTransactions->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <h4 class="mt-3 text-sm font-bold text-slate-700">Belum Ada Transaksi</h4>
            <p class="mt-1 text-xs text-slate-500">Gunakan terminal kasir POS untuk menginput cucian pelanggan baru.</p>
            <a
                href="{{ route('staff.transactions.create') }}"
                class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Buka Kasir POS</span>
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-500 font-semibold">
                        <th class="pb-2.5">Nota</th>
                        <th class="pb-2.5">Pelanggan</th>
                        <th class="pb-2.5">Total Tagihan</th>
                        <th class="pb-2.5">Status Pengerjaan</th>
                        <th class="pb-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($todayTransactions as $trx)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="py-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $trx->transaction_code }}
                            </td>
                            <td class="py-3">
                                <p class="font-semibold text-slate-900">{{ $trx->customer?->name ?? 'Pelanggan Umum' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $trx->customer?->phone ?? '-' }}</p>
                            </td>
                            <td class="py-3 font-bold text-slate-900 tabular-nums whitespace-nowrap">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 whitespace-nowrap">
                                <x-status-badge :status="$trx->status" class="text-[10px] px-2 py-0.5" />
                            </td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('staff.transactions.show', $trx) }}"
                                        class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 cursor-pointer"
                                    >
                                        Detail
                                    </a>
                                    <a
                                        href="{{ route('staff.transactions.print', $trx) }}"
                                        target="_blank"
                                        class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-brand-700 shadow-2xs transition hover:bg-brand-50 cursor-pointer"
                                        title="Cetak Struk Thermal"
                                    >
                                        Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
