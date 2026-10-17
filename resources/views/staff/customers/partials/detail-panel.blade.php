{{-- Kolom Kanan (Detail Panel): Profil & Riwayat Cucian --}}
<div class="lg:col-span-7 xl:col-span-8 space-y-5">
    @php
        $selectedInitials = collect(explode(' ', $selectedCustomer->name))
            ->map(fn($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
        $hasTransactions = $selectedCustomer->transactions()->exists();
    @endphp

    {{-- 1. Kartu Info Profil Pelanggan Terpilih --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3.5">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-sm font-bold text-brand-800">
                    {{ $selectedInitials ?: 'CU' }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 sm:text-lg">{{ $selectedCustomer->name }}</h2>
                    <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        @if ($selectedCustomer->phone)
                            <a
                                href="{{ $selectedCustomer->whatsapp_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-emerald-700 font-semibold hover:text-emerald-800 transition group"
                                title="Buka obrolan WhatsApp ke {{ $selectedCustomer->name }}"
                            >
                                <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.151.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.768-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.995-.368-1.78-.769-2.936-2.578-3.025-2.697-.089-.119-.724-.962-.724-1.835 0-.873.456-1.302.619-1.479.163-.177.355-.221.473-.221.118 0 .237.001.341.006.109.006.255-.041.398.303.148.355.503 1.226.547 1.315.044.089.074.192.015.31-.059.119-.089.192-.178.296-.089.103-.187.23-.266.31-.089.089-.182.185-.078.363.103.178.46 0.758.987 1.228.679.605 1.252.793 1.43.882.178.089.281.074.385-.044.104-.119.444-.518.563-.695.118-.178.237-.148.399-.089.163.059 1.035.488 1.213.577.177.089.296.133.34.207.045.074.045.429-.099.834z"/>
                                </svg>
                                <span class="group-hover:underline underline-offset-2">WhatsApp: {{ $selectedCustomer->phone }}</span>
                            </a>
                        @else
                            <span class="text-xs text-slate-400">WhatsApp: -</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi Panel Detail --}}
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="openEditModal({{ json_encode([
                        'id' => $selectedCustomer->id,
                        'name' => $selectedCustomer->name,
                        'phone' => $selectedCustomer->phone,
                        'address' => $selectedCustomer->address,
                    ]) }})"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800"
                    title="Edit data pelanggan"
                >
                    <svg class="size-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    <span>Edit</span>
                </button>

                @if ($hasTransactions)
                    <span
                        class="inline-flex cursor-not-allowed items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-400"
                        title="Pelanggan memiliki riwayat transaksi dan tidak dapat dihapus."
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <span>Terkunci</span>
                    </span>
                @else
                    <button
                        type="button"
                        @click="openDeleteModal({{ json_encode([
                            'id' => $selectedCustomer->id,
                            'name' => $selectedCustomer->name,
                        ]) }})"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50/60 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100"
                        title="Hapus data pelanggan"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        <span>Hapus</span>
                    </button>
                @endif

                {{-- Tutup Detail (Kembali ke Tampilan Penuh) --}}
                <a
                    href="{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                        'q' => $search,
                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                        'page' => request('page'),
                    ])) }}"
                    class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                    title="Tutup Detail dan Kembali ke Tabel"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Metadata Pelanggan --}}
        <div class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-3 sm:grid-cols-3">
            <div>
                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Terdaftar Sejak</p>
                <p class="mt-0.5 text-xs font-bold text-slate-800">
                    {{ $selectedCustomer->created_at ? $selectedCustomer->created_at->translatedFormat('d M Y') : '-' }}
                </p>
            </div>
            <div>
                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Total Transaksi</p>
                <p class="mt-0.5 text-xs font-bold text-slate-800">
                    {{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }} Nota
                    <span class="font-normal text-slate-500">(Rp {{ number_format($totalSpent, 0, ',', '.') }})</span>
                </p>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Alamat</p>
                <p class="mt-0.5 truncate text-xs font-semibold text-slate-700" title="{{ $selectedCustomer->address ?: '-' }}">
                    {{ $selectedCustomer->address ?: '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- 2. Kartu Rincian Transaksi Terpilih (Muncul jika nota di riwayat diklik) --}}
    @if ($activeTransaction)
        @php
            $firstDetail = $activeTransaction->details->first();
            $service = $firstDetail?->service;
            $latestPayment = $activeTransaction->payments->last();
            $isPaid = $latestPayment && in_array($latestPayment->verification_status, ['Tidak Diperlukan', 'Terverifikasi']);
        @endphp
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="text-sm font-bold text-slate-900 sm:text-base">{{ $activeTransaction->transaction_code }}</span>
                    <p class="text-[11px] text-slate-400">
                        Diterima: {{ $activeTransaction->created_at->translatedFormat('d M Y, H:i') }} WIB
                        @if ($latestPayment && $latestPayment->creator)
                            oleh {{ $latestPayment->creator->name }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span @class([
                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                        'bg-brand-50 text-brand-700 ring-1 ring-brand-600/20' => $isPaid,
                        'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' => ! $isPaid,
                    ])>
                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $isPaid ? 'Lunas' : 'Belum Lunas' }}
                        @if ($latestPayment)
                            ({{ $latestPayment->method }})
                        @endif
                    </span>

                    {{-- Tombol Tutup Rincian Transaksi --}}
                    <a
                        href="{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                            'customer_id' => $selectedCustomer->id,
                            'q' => $search,
                            'status' => $statusFilter !== 'all' ? $statusFilter : null,
                            'page' => request('page'),
                        ])) }}"
                        class="inline-flex size-7 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        title="Tutup rincian transaksi ini"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Box Detail Transaksi --}}
            <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Detail Transaksi</p>

                <div class="mt-2.5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-slate-900 sm:text-sm">
                                {{ $service?->name ?? 'Cuci Komplit Reguler' }}
                            </h4>
                            <span class="rounded bg-sky-100 px-1.5 py-0.5 text-[9px] font-bold text-sky-700 uppercase">
                                Kategori Reguler
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Berat: <strong class="text-slate-700">{{ $firstDetail?->weight ?? 1 }} kg</strong>
                            @if ($firstDetail?->unit_price)
                                × Rp {{ number_format($firstDetail->unit_price, 0, ',', '.') }}
                            @endif
                            @if ($service?->description)
                                • {{ $service->description }}
                            @endif
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm font-extrabold text-slate-900">
                            Rp {{ number_format($activeTransaction->total_amount, 0, ',', '.') }}
                        </p>
                        <span class="mt-0.5 inline-block rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">
                            Selesai {{ $service?->estimated_duration ?? 3 }} Hari
                        </span>
                    </div>
                </div>

                {{-- Baris Estimasi & Status Cucian --}}
                <div class="mt-3 flex flex-col gap-2 rounded-lg bg-white p-2.5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <svg class="size-3.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            Estimasi Pengambilan:
                            <strong class="text-slate-800">
                                {{ $activeTransaction->estimated_completed_at ? $activeTransaction->estimated_completed_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Menunggu update' }}
                            </strong>
                        </span>
                    </div>
                    <div>
                        <span @class([
                            'rounded px-2.5 py-0.5 text-[11px] font-bold uppercase',
                            'bg-sky-50 text-sky-700 border border-sky-200' => in_array($activeTransaction->status, ['Diterima', 'Diproses']),
                            'bg-brand-50 text-brand-700 border border-brand-200' => in_array($activeTransaction->status, ['Selesai', 'Diambil']),
                            'bg-rose-50 text-rose-700 border border-rose-200' => $activeTransaction->status === 'Dibatalkan',
                        ])>
                            {{ $activeTransaction->status }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Footer Total Akhir --}}
            <div class="mt-3.5 flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Akhir :</span>
                <span class="text-lg font-extrabold text-slate-900">
                    Rp {{ number_format($activeTransaction->total_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>
    @endif

    {{-- 3. Tabel Riwayat Transaksi Cucian Sebelumnya --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold tracking-wider text-slate-900 uppercase">
                Riwayat Transaksi Cucian Sebelumnya
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-brand-700">
                    {{ $previousTransactions->count() }} Transaksi
                </span>
            </div>
        </div>

        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase">
                        <th class="pb-2.5 font-semibold">No. Nota</th>
                        <th class="pb-2.5 font-semibold">Tgl Masuk</th>
                        <th class="pb-2.5 font-semibold">Kategori Layanan</th>
                        <th class="pb-2.5 font-semibold">Durasi</th>
                        <th class="pb-2.5 font-semibold">Total Biaya</th>
                        <th class="pb-2.5 text-right font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($previousTransactions as $prevTrx)
                        @php
                            $detail = $prevTrx->details->first();
                            $prevService = $detail?->service;
                            $isRowSelected = $activeTransaction && $activeTransaction->id === $prevTrx->id;
                        @endphp
                        <tr
                            onclick="window.location.href='{{ route("{$currentRoutePrefix}.customers.index", array_filter([
                                'customer_id' => $selectedCustomer->id,
                                'transaction_id' => $prevTrx->id,
                                'q' => $search,
                                'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                'page' => request('page'),
                            ])) }}'"
                            @class([
                                'group cursor-pointer transition',
                                'bg-brand-50/70 font-semibold ring-1 ring-inset ring-brand-500/20' => $isRowSelected,
                                'hover:bg-slate-50/80' => ! $isRowSelected,
                            ])
                            title="Klik untuk melihat detail nota {{ $prevTrx->transaction_code }}"
                        >
                            <td class="py-2.5 font-bold text-slate-900 group-hover:text-brand-600">
                                <div class="flex items-center gap-1.5">
                                    @if ($isRowSelected)
                                        <span class="size-1.5 rounded-full bg-brand-600"></span>
                                    @endif
                                    <span>{{ $prevTrx->transaction_code }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 text-slate-500">
                                {{ $prevTrx->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-2.5">
                                {{ $prevService?->name ?? 'Cuci Komplit' }}
                            </td>
                            <td class="py-2.5">
                                <span class="rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">
                                    {{ $prevService?->estimated_duration ?? 3 }} Hari
                                </span>
                            </td>
                            <td class="py-2.5 font-semibold text-slate-900">
                                Rp {{ number_format($prevTrx->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 text-right">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                    'bg-brand-100 text-brand-800' => in_array($prevTrx->status, ['Selesai', 'Diambil']),
                                    'bg-amber-100 text-amber-800' => in_array($prevTrx->status, ['Diterima', 'Diproses']),
                                    'bg-rose-100 text-rose-800' => $prevTrx->status === 'Dibatalkan',
                                ])>
                                    {{ $prevTrx->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-xs text-slate-400">
                                Belum ada riwayat transaksi lampau untuk pelanggan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 border-t border-slate-100 pt-2.5 text-[11px] text-slate-400">
            Total riwayat tersimpan: {{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }} transaksi. Klik baris untuk melihat detail nota di atas.
        </div>
    </div>
</div>
