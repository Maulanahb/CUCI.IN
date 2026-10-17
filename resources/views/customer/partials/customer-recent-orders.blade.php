{{-- ============================================================
    SECTION RIWAYAT TRANSAKSI TERBARU (5 TRANSAKSI TERAKHIR)
============================================================= --}}
<div class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Riwayat Pesanan Terbaru</h3>
            <p class="text-xs text-slate-500">Daftar transaksi laundry terakhir Anda di sistem CUCI.IN</p>
        </div>
        <a
            href="{{ route('customer.transactions.index') }}"
            class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 cursor-pointer"
        >
            <span>Lihat Semua Riwayat ({{ $stats['total_count'] }})</span>
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    @if ($recentTransactions->isEmpty())
        <div class="py-10 text-center">
            <p class="text-xs text-slate-500">Belum ada data transaksi yang tercatat.</p>
        </div>
    @else
        <div class="mt-4 divide-y divide-slate-100">
            @foreach ($recentTransactions as $transaction)
                @php
                    $statusVal = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
                    $paid = (float) $transaction->payments->where('verification_status', '!=', 'Ditolak')->sum('amount');
                    $remaining = max(0.0, (float) $transaction->total_amount - $paid);
                    $isPaidOff = $remaining <= 0;
                @endphp
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between py-3.5 transition hover:bg-slate-50/70 rounded-lg px-2">
                    <div>
                        <p class="text-xs font-bold font-mono text-slate-900">{{ $transaction->transaction_code }}</p>
                        <p class="text-[11px] text-slate-500">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3">
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-900 tabular-nums">Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</p>
                            <span class="text-[10px] {{ $isPaidOff ? 'text-emerald-600 font-semibold' : 'text-amber-600 font-medium' }}">
                                {{ $isPaidOff ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </div>

                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ match ($statusVal) {
                            'Diterima' => 'bg-sky-50 text-sky-700 border border-sky-200/60',
                            'Diproses' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                            'Selesai' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                            'Diambil' => 'bg-slate-100 text-slate-700 border border-slate-200/60',
                            'Dibatalkan' => 'bg-rose-50 text-rose-700 border border-rose-200/60',
                            default => 'bg-slate-100 text-slate-700',
                        } }}">
                            {{ $statusVal }}
                        </span>

                        <a
                            href="{{ route('customer.transactions.index', ['selected' => $transaction->transaction_code]) }}"
                            class="text-xs font-semibold text-brand-600 hover:text-brand-700 cursor-pointer"
                        >
                            Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
