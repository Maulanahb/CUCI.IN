{{-- ============================================================
    TABEL MASTER DATA LAYANAN
============================================================= --}}
<div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 sm:text-xs">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Nama Layanan</th>
                    <th scope="col" class="px-5 py-3.5">Tarif Satuan (kg)</th>
                    <th scope="col" class="px-5 py-3.5">Estimasi Durasi</th>
                    <th scope="col" class="px-5 py-3.5">Status Ketersediaan</th>
                    <th scope="col" class="px-5 py-3.5 text-center">Riwayat Transaksi</th>
                    <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($services as $service)
                    <tr class="transition hover:bg-slate-50/70">
                        {{-- Nama Layanan & Deskripsi --}}
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $service->name }}</p>
                                    <p class="mt-0.5 line-clamp-1 text-xs text-slate-400">
                                        {{ $service->description ?: 'Tanpa keterangan khusus.' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Tarif Satuan --}}
                        <td class="px-5 py-4 font-semibold text-slate-800">
                            <span class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">
                                Rp {{ number_format($service->price, 0, ',', '.') }} <span class="ml-1 text-[10px] font-normal text-brand-600/80">/kg</span>
                            </span>
                        </td>

                        {{-- Estimasi Durasi --}}
                        <td class="px-5 py-4 text-slate-600">
                            <div class="flex items-center gap-1.5 text-xs font-medium sm:text-sm">
                                <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $service->estimated_duration }} Jam</span>
                                @if ($service->estimated_duration >= 24)
                                    <span class="text-[11px] text-slate-400">
                                        ({{ round($service->estimated_duration / 24, 1) }} hari)
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Status Ketersediaan --}}
                        <td class="px-5 py-4">
                            @if ($isAdmin)
                                <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        title="Klik untuk mengubah status ketersediaan"
                                        @class([
                                            'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold transition hover:opacity-85 cursor-pointer select-none',
                                            'border-emerald-200 bg-emerald-50 text-emerald-700' => $service->is_active,
                                            'border-slate-200 bg-slate-100 text-slate-600' => ! $service->is_active,
                                        ])
                                    >
                                        <span @class([
                                            'size-1.5 rounded-full',
                                            'bg-emerald-500' => $service->is_active,
                                            'bg-slate-400' => ! $service->is_active,
                                        ])></span>
                                        <span>{{ $service->is_active ? 'Tersedia' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            @else
                                <span @class([
                                    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold select-none',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700' => $service->is_active,
                                    'border-slate-200 bg-slate-100 text-slate-600' => ! $service->is_active,
                                ])>
                                    <span @class([
                                        'size-1.5 rounded-full',
                                        'bg-emerald-500' => $service->is_active,
                                        'bg-slate-400' => ! $service->is_active,
                                    ])></span>
                                    <span>{{ $service->is_active ? 'Tersedia' : 'Nonaktif' }}</span>
                                </span>
                            @endif
                        </td>

                        {{-- Penggunaan Transaksi --}}
                        <td class="px-5 py-4 text-center">
                            @if ($service->transaction_details_count > 0)
                                <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                    <span>{{ $service->transaction_details_count }}</span>
                                    <span class="text-[10px] font-normal">order</span>
                                </span>
                            @else
                                <span class="text-xs text-slate-400">0 order</span>
                            @endif
                        </td>

                        {{-- Aksi Kolom --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Tombol Detail / Preview --}}
                                <button
                                    type="button"
                                    @click="openDetailModal({{ json_encode($service) }})"
                                    class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
                                    title="Lihat Detail & Simulasi"
                                    aria-label="Lihat Detail & Simulasi"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>

                                @if ($isAdmin)
                                    {{-- Tombol Edit --}}
                                    <button
                                        type="button"
                                        @click="openEditModal({{ json_encode($service) }})"
                                        class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 cursor-pointer"
                                        title="Ubah Layanan"
                                        aria-label="Ubah Layanan"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <button
                                        type="button"
                                        @click="openDeleteModal({{ json_encode($service) }})"
                                        @class([
                                            'inline-flex size-8 items-center justify-center rounded-lg border transition cursor-pointer',
                                            'border-slate-200 text-slate-400 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600' => $service->transaction_details_count === 0,
                                            'border-slate-100 text-slate-300 hover:bg-slate-100 hover:text-slate-500' => $service->transaction_details_count > 0,
                                        ])
                                        title="{{ $service->transaction_details_count > 0 ? 'Layanan memiliki riwayat transaksi (Gunakan nonaktifkan)' : 'Hapus Layanan' }}"
                                        aria-label="Hapus Layanan"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                                </svg>
                            </div>
                            <h3 class="mt-3 text-sm font-semibold text-slate-900">Tidak ada layanan ditemukan</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $search !== '' ? 'Tidak ada hasil untuk kata kunci "' . $search . '".' : 'Belum ada data layanan laundry yang tersimpan.' }}
                            </p>
                            @if ($isAdmin && $search === '')
                                <div class="mt-4">
                                    <button
                                        type="button"
                                        @click="openCreateModal()"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-700 cursor-pointer"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Buat Layanan Pertama</span>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    @if ($services->hasPages())
        <div class="border-t border-slate-100 px-5 py-3.5">
            {{ $services->links() }}
        </div>
    @endif
</div>
