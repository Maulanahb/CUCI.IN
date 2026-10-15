@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
@endphp

<x-layouts::app title="Detail Transaksi {{ $transaction->transaction_code }}">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Header & Tombol Navigasi --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        Nota: {{ $transaction->transaction_code }}
                    </h1>
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700">
                        {{ $statusValue }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Rincian nota transaksi cucian pelanggan.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    id="btn-back-to-create"
                    href="{{ route("{$currentRoutePrefix}.transactions.create") }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Transaksi Baru</span>
                </a>
            </div>
        </div>

        {{-- Flash Pesan Sukses --}}
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-emerald-900">Transaksi Berhasil Disimpan!</p>
                    <p class="mt-0.5 text-emerald-700 font-normal">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Kartu Info Transaksi & Pelanggan --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pelanggan & Nota</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Nama Pelanggan --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pelanggan</span>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $transaction->customer?->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $transaction->customer?->phone ?? '-' }}</p>
                </div>

                {{-- Tanggal Masuk --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal Dibuat</span>
                    <p class="mt-1 text-sm font-bold text-slate-900">
                        {{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '-' }}
                    </p>
                    <p class="text-xs text-slate-500">WIB</p>
                </div>

                {{-- Estimasi Selesai --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Estimasi Selesai</span>
                    <p class="mt-1 text-sm font-bold text-slate-900">
                        {{ $transaction->estimated_completed_at ? $transaction->estimated_completed_at->format('d/m/Y H:i') : '-' }}
                    </p>
                    <p class="text-xs text-slate-500">Waktu perkiraan</p>
                </div>

                {{-- Status Order --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Cucian</span>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $statusValue }}</p>
                    <p class="text-xs text-slate-500">Tahap pengerjaan</p>
                </div>
            </div>
        </div>

        {{-- Kartu Tabel Item Layanan --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
            <div class="border-b border-slate-100 p-5 sm:px-6">
                <h2 class="text-sm font-bold text-slate-900 sm:text-base">Daftar Item Cucian</h2>
                <p class="text-[11px] text-slate-500">Rincian layanan, tarif per kg, dan subtotal yang dibebankan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="py-3.5 pl-5 pr-3 sm:pl-6">#</th>
                            <th scope="col" class="px-3 py-3.5">Layanan</th>
                            <th scope="col" class="px-3 py-3.5 text-right">Berat</th>
                            <th scope="col" class="px-3 py-3.5 text-right">Harga Satuan</th>
                            <th scope="col" class="py-3.5 pl-3 pr-5 text-right sm:pr-6">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($transaction->details as $index => $detail)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 pl-5 pr-3 font-medium text-slate-400 sm:pl-6">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-3 py-3.5 font-semibold text-slate-900">
                                    {{ $detail->service?->name ?? 'Layanan' }}
                                </td>
                                <td class="px-3 py-3.5 text-right font-medium text-slate-700">
                                    {{ number_format((float) $detail->weight, 2, ',', '.') }} kg
                                </td>
                                <td class="px-3 py-3.5 text-right font-medium text-slate-600">
                                    Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 pl-3 pr-5 text-right font-bold text-slate-900 sm:pr-6">
                                    Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-slate-400">
                                    Tidak ada rincian item layanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50/60">
                        <tr>
                            <td colspan="4" class="py-4 pl-5 pr-3 text-right text-xs font-bold uppercase tracking-wider text-slate-600 sm:pl-6">
                                Total Transaksi
                            </td>
                            <td class="py-4 pl-3 pr-5 text-right text-base font-extrabold text-brand-700 sm:pr-6 sm:text-lg">
                                Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="border-t border-slate-100 p-4 sm:px-6 bg-slate-50/30 flex items-center justify-between">
                <a
                    href="{{ route("{$currentRoutePrefix}.customers.index", ['customer_id' => $transaction->customer_id]) }}"
                    class="text-xs font-semibold text-slate-600 hover:text-brand-600 transition"
                >
                    &larr; Lihat Profil Pelanggan
                </a>
                <a
                    href="{{ route("{$currentRoutePrefix}.transactions.create") }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Form Baru</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
