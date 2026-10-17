{{-- ============================================================
    TABEL TRANSAKSI TERBARU (ADMIN OVERVIEW)
============================================================= --}}
<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 lg:col-span-2">
    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2">
            <div class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 sm:text-base">Transaksi Terbaru</h3>
                <p class="text-xs text-slate-500">5 order penerimaan laundry terakhir pada sistem.</p>
            </div>
        </div>
        <a
            href="{{ route('admin.transactions.index') }}"
            class="text-xs font-semibold text-brand-600 transition hover:text-brand-700 cursor-pointer"
        >
            Semua Transaksi &rarr;
        </a>
    </div>

    @if ($recentTransactions->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <h4 class="mt-3 text-sm font-bold text-slate-800">Belum Ada Transaksi</h4>
            <p class="mt-1 text-xs text-slate-500">Gunakan kasir POS untuk menginput penerimaan laundry baru.</p>
            <a
                href="{{ route('admin.transactions.create') }}"
                class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98 cursor-pointer"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Transaksi POS</span>
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-500">
                        <th class="pb-2.5 font-semibold">Nota</th>
                        <th class="pb-2.5 font-semibold">Pelanggan</th>
                        <th class="pb-2.5 font-semibold">Total</th>
                        <th class="pb-2.5 font-semibold">Status</th>
                        <th class="pb-2.5 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($recentTransactions as $trx)
                        @php
                            $statusStr = $trx->status instanceof \App\Enums\TransactionStatus ? $trx->status->value : (string) $trx->status;
                            $badgeClass = match ($statusStr) {
                                'Diterima' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'Diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'Diambil' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="py-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $trx->transaction_code }}
                            </td>
                            <td class="py-3">
                                <p class="font-semibold text-slate-900">{{ $trx->customer?->name ?? 'Pelanggan Umum' }}</p>
                                <p class="text-[11px] text-slate-500 tabular-nums">{{ $trx->customer?->phone ?? '-' }}</p>
                            </td>
                            <td class="py-3 font-bold text-slate-900 whitespace-nowrap tabular-nums">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $statusStr }}
                                </span>
                            </td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('admin.transactions.show', $trx) }}"
                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 cursor-pointer"
                                    >
                                        Detail
                                    </a>
                                    <a
                                        href="{{ route('admin.transactions.print', $trx) }}"
                                        target="_blank"
                                        class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-brand-700 shadow-2xs transition hover:bg-brand-50 cursor-pointer"
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
