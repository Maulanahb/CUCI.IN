{{-- ============================================================
    DETAIL LAYANAN (ITEMS TABLE)
============================================================= --}}
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="text-sm font-semibold text-slate-900">
            Detail Layanan
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            Layanan yang digunakan dalam transaksi ini.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-semibold">
                        Layanan
                    </th>
                    <th class="px-6 py-3 font-semibold">
                        Berat
                    </th>
                    <th class="px-6 py-3 font-semibold">
                        Harga / Unit
                    </th>
                    <th class="px-6 py-3 text-right font-semibold">
                        Subtotal
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($transaction->details as $detail)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        {{-- Service --}}
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">
                                {{ $detail->service?->name ?? 'Layanan' }}
                            </p>
                        </td>

                        {{-- Weight --}}
                        <td class="px-6 py-4 text-slate-600">
                            {{ number_format((float) $detail->weight, 2, ',', '.') }} {{ $detail->unit ?? 'kg' }}
                        </td>

                        {{-- Unit Price --}}
                        <td class="px-6 py-4 text-slate-600">
                            Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                        </td>

                        {{-- Subtotal --}}
                        <td class="px-6 py-4 text-right font-semibold text-slate-900">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="4"
                            class="px-6 py-10 text-center"
                        >
                            <p class="text-sm font-medium text-slate-600">
                                Belum ada detail layanan.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
