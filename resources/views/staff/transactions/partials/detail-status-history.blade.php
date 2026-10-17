{{-- ============================================================
    AUDIT TRAIL / RIWAYAT PEMBARUAN STATUS
============================================================= --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">
                Riwayat Pembaruan Status
            </h2>
            <p class="mt-0.5 text-xs text-slate-500">
                Catatan audit trail seluruh perubahan status pengerjaan cucian.
            </p>
        </div>
        <span class="text-xs font-medium text-slate-400">
            Total: {{ $transaction->statusHistories->count() }} catatan
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Waktu Perubahan</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Petugas (Diubah Oleh)</th>
                    <th class="px-5 py-3 font-semibold">Catatan / Alasan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transaction->statusHistories->sortByDesc('id') as $history)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 text-xs text-slate-600">
                            <div class="font-medium text-slate-800">
                                {{ $history->changed_at?->translatedFormat('d F Y, H:i:s') ?? '-' }}
                            </div>
                            <div class="mt-0.5 text-[11px] text-slate-400">
                                {{ $history->changed_at?->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <x-status-badge :status="$history->status" />
                        </td>
                        <td class="px-5 py-3.5 text-xs font-medium text-slate-700">
                            <div class="flex items-center gap-2">
                                <div class="flex size-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">
                                    {{ strtoupper(substr($history->changedBy?->name ?? 'S', 0, 1)) }}
                                </div>
                                <span>{{ $history->changedBy?->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-600">
                            @if ($history->notes)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-800 border border-amber-200">
                                    <svg class="size-3.5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $history->notes }}</span>
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-xs text-slate-400">
                            Belum ada catatan riwayat status untuk transaksi ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
