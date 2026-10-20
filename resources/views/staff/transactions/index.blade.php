@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'staff';
    $hasFilters = request()->filled('search') || request()->filled('status');
@endphp

<x-layouts::app title="Kelola Transaksi">

    <x-slot:actions>
        <x-ui.button :href="route($routePrefix . '.transactions.create')" size="sm" aria-label="Buka Kasir POS">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden sm:inline">Buka Kasir POS</span>
        </x-ui.button>
    </x-slot>

    <div class="space-y-5">

        {{-- Flash message --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter --}}
        <x-ui.card>
            <form
                method="GET"
                action="{{ route($routePrefix . '.transactions.index') }}"
                class="flex flex-col gap-3 md:flex-row md:items-end"
            >
                <div class="flex-1">
                    <label for="search" class="mb-1.5 block text-xs font-medium text-slate-600">
                        Cari transaksi
                    </label>
                    <input
                        type="search"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Kode nota atau nama pelanggan..."
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 transition placeholder:text-slate-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                </div>

                <div class="md:w-56">
                    <label for="status" class="mb-1.5 block text-xs font-medium text-slate-600">
                        Status cucian
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    >
                        <option value="">Semua status</option>
                        @foreach (['Diterima', 'Diproses', 'Selesai', 'Diambil', 'Dibatalkan'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <x-ui.button
                        :href="route($routePrefix . '.transactions.index')"
                        variant="ghost"
                        class="{{ $hasFilters ? '' : 'pointer-events-none opacity-50' }}"
                        :aria-disabled="$hasFilters ? 'false' : 'true'"
                    >
                        Reset
                    </x-ui.button>
                    <x-ui.button type="submit">
                        Terapkan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Daftar transaksi --}}
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Daftar Transaksi</h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $transactions->total() }} nota ditemukan
                    </p>
                </div>
            </div>

            {{-- Tabel (desktop) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Kode Nota</th>
                            <th class="px-5 py-3 font-semibold">Pelanggan</th>
                            <th class="px-5 py-3 font-semibold">Total</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr class="transition-colors hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $transaction->transaction_code }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $transaction->created_at?->format('d M Y, H:i') }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-800">
                                        {{ $transaction->customer?->name ?? 'Pelanggan umum' }}
                                    </p>
                                    @if ($transaction->customer?->phone)
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $transaction->customer->phone }}</p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.money :value="$transaction->total_amount" class="font-semibold text-slate-900" />
                                    <div class="mt-1">
                                        <x-ui.payment-badge :status="$transaction->payment_status" size="sm" />
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <x-ui.status-badge :status="$transaction->status" />
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <x-ui.button
                                            :href="route($routePrefix . '.transactions.show', $transaction)"
                                            variant="secondary"
                                            size="sm"
                                        >
                                            Detail
                                        </x-ui.button>
                                        <x-ui.button
                                            :href="route($routePrefix . '.transactions.print', $transaction)"
                                            variant="ghost"
                                            size="sm"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Cetak
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-slate-100">
                                        <svg class="size-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-slate-700">Belum ada transaksi</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Transaksi baru akan muncul di sini setelah kasir menyimpan nota.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Kartu (mobile) --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($transactions as $transaction)
                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $transaction->transaction_code }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $transaction->created_at?->format('d M Y, H:i') }}
                                </p>
                            </div>
                            <x-ui.status-badge :status="$transaction->status" size="sm" />
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-600">
                                {{ $transaction->customer?->name ?? 'Pelanggan umum' }}
                            </span>
                            <x-ui.payment-badge :status="$transaction->payment_status" size="sm" />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <x-ui.money :value="$transaction->total_amount" class="text-sm font-semibold text-slate-900" />

                            <div class="flex items-center gap-1.5">
                                <x-ui.button
                                    :href="route($routePrefix . '.transactions.show', $transaction)"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Detail
                                </x-ui.button>
                                <x-ui.button
                                    :href="route($routePrefix . '.transactions.print', $transaction)"
                                    variant="ghost"
                                    size="sm"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Cetak
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-700">Belum ada transaksi</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Transaksi baru akan muncul di sini setelah kasir menyimpan nota.
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($transactions->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </x-ui.card>

    </div>

</x-layouts::app>
