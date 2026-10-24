@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'staff';
    $currentStatus = request('status');
    $currentPayment = request('payment');
    $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('payment');
@endphp

<x-layouts::app title="Kelola Transaksi">

    <x-slot:actions>
        <x-ui.button :href="route($routePrefix . '.transactions.create')" size="sm" aria-label="Buka Kasir POS">
            <x-lucide-plus class="size-4" aria-hidden="true" />
            <span>Buka Kasir POS</span>
            <kbd class="hidden rounded bg-brand-700/60 px-1.5 py-0.5 text-xs font-mono text-white sm:inline">F2</kbd>
        </x-ui.button>
    </x-slot>

    <div class="space-y-5" x-data="{
        initShortcuts() {
            window.addEventListener('keydown', (e) => {
                if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    document.getElementById('search')?.focus();
                } else if (e.key === 'F2') {
                    e.preventDefault();
                    window.location.href = '{{ route($routePrefix . '.transactions.create') }}';
                }
            });
        }
    }" x-init="initShortcuts()">

        {{-- Flash message --}}
        @if (session('success'))
            <x-ui.alert type="success" :dismissible="true">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        {{-- Quick Status Filter Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none sm:gap-2">
            @php
                $statusTabs = [
                    ['label' => 'Semua Nota', 'status' => null, 'payment' => null],
                    ['label' => 'Diterima', 'status' => 'Diterima', 'payment' => null],
                    ['label' => 'Diproses', 'status' => 'Diproses', 'payment' => null],
                    ['label' => 'Selesai (Siap Ambil)', 'status' => 'Selesai', 'payment' => null],
                    ['label' => 'Sudah Diambil', 'status' => 'Diambil', 'payment' => null],
                ];
            @endphp

            @foreach ($statusTabs as $tab)
                @php
                    $isActive = ($tab['status'] === null && $currentStatus === null) || ($currentStatus === $tab['status']);
                @endphp
                <a
                    href="{{ route($routePrefix . '.transactions.index', array_filter(array_merge(request()->except(['page', 'status']), ['status' => $tab['status']]))) }}"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-semibold transition {{ $isActive ? 'border-brand-600 bg-brand-50 text-brand-700 shadow-xs ring-1 ring-brand-200' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Filter & Search Card --}}
        <x-ui.card padding="p-4">
            <form
                method="GET"
                action="{{ route($routePrefix . '.transactions.index') }}"
                class="flex flex-col gap-3 md:flex-row md:items-end"
            >
                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <label for="search" class="mb-1.5 block text-xs font-semibold text-slate-700">
                        Cari Transaksi
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-lucide-search class="size-4 text-slate-400" aria-hidden="true" />
                        </div>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Ketik nomor nota (TRK-...) atau nama pelanggan..."
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50/50 pl-9 pr-10 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-200"
                        >
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <kbd class="hidden rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-xs font-semibold text-slate-500 sm:inline" title="Tekan '/' untuk fokus">/</kbd>
                        </div>
                    </div>
                </div>

                {{-- Filter Status Cucian --}}
                <div class="md:w-52">
                    <label for="status" class="mb-1.5 block text-xs font-semibold text-slate-700">
                        Status Cucian
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 text-sm text-slate-900 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-200"
                    >
                        <option value="">Semua Status Cucian</option>
                        @foreach (['Diterima', 'Diproses', 'Selesai', 'Diambil', 'Dibatalkan'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <x-ui.button
                        :href="route($routePrefix . '.transactions.index')"
                        variant="secondary"
                        class="{{ $hasFilters ? '' : 'pointer-events-none opacity-50' }}"
                        :aria-disabled="$hasFilters ? 'false' : 'true'"
                    >
                        <x-lucide-rotate-ccw class="size-3.5" aria-hidden="true" />
                        <span>Reset</span>
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        <x-lucide-filter class="size-3.5" aria-hidden="true" />
                        <span>Terapkan</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Daftar Transaksi --}}
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="flex flex-wrap items-center justify-between border-b border-slate-200 px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-lucide-receipt class="size-5" aria-hidden="true" />
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Daftar Nota Transaksi</h2>
                        <p class="text-xs text-slate-500">
                            Menampilkan <span class="font-semibold text-slate-700">{{ $transactions->total() }}</span> nota tercatat
                        </p>
                    </div>
                </div>

                <div class="text-xs text-slate-500">
                    Halaman <span class="font-semibold text-slate-700">{{ $transactions->currentPage() }}</span> dari {{ $transactions->lastPage() ?: 1 }}
                </div>
            </div>

            {{-- Tabel Desktop --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Kode Nota & Tanggal</th>
                            <th scope="col" class="px-5 py-3.5">Pelanggan</th>
                            <th scope="col" class="px-5 py-3.5">Rincian Layanan</th>
                            <th scope="col" class="px-5 py-3.5">Total & Pembayaran</th>
                            <th scope="col" class="px-5 py-3.5">Status Cucian & Waktu</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            @php
                                $totalWeight = $transaction->details->sum('weight');
                                $firstService = $transaction->details->first()?->service?->name ?? 'Layanan Laundry';
                                $itemCount = $transaction->details->count();
                                $isOverdue = $transaction->estimated_completed_at
                                    && $transaction->estimated_completed_at->isPast()
                                    && !in_array($transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status, ['Selesai', 'Diambil', 'Dibatalkan']);
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/80">
                                {{-- Kode Nota --}}
                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route($routePrefix . '.transactions.show', $transaction) }}"
                                        class="font-mono font-bold tracking-tight text-brand-700 hover:text-brand-800 hover:underline"
                                    >
                                        {{ $transaction->transaction_code }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $transaction->created_at?->format('d M Y, H:i') }}
                                    </p>
                                </td>

                                {{-- Pelanggan --}}
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        {{ $transaction->customer?->name ?? 'Pelanggan umum' }}
                                    </p>
                                    @if ($transaction->customer?->phone)
                                        <p class="mt-0.5 font-mono text-xs text-slate-500">
                                            {{ $transaction->customer->phone }}
                                        </p>
                                    @endif
                                </td>

                                {{-- Layanan & Bobot --}}
                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-800 line-clamp-1" title="{{ $firstService }}">
                                        {{ $firstService }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ number_format((float) $totalWeight, 1, ',', '.') }} kg
                                        @if ($itemCount > 1)
                                            <span class="text-slate-400">· +{{ $itemCount - 1 }} item lainnya</span>
                                        @endif
                                    </p>
                                </td>

                                {{-- Total & Pembayaran --}}
                                <td class="px-5 py-4">
                                    <x-ui.money :value="$transaction->total_amount" class="font-bold text-slate-900" />
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <x-ui.payment-badge :status="$transaction->payment_status" size="sm" />
                                        @if ($transaction->remaining_amount > 0)
                                            <span class="text-xs font-medium text-rose-600">
                                                (Sisa: Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }})
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Cucian & Estimasi Waktu --}}
                                <td class="px-5 py-4">
                                    <x-ui.status-badge :status="$transaction->status" />
                                    @if ($transaction->estimated_completed_at)
                                        <div class="mt-1.5 flex items-center gap-1 text-xs">
                                            <x-lucide-clock class="size-3.5 {{ $isOverdue ? 'text-rose-500' : 'text-slate-400' }}" aria-hidden="true" />
                                            @if ($isOverdue)
                                                <span class="font-semibold text-rose-600">
                                                    Terlambat ({{ $transaction->estimated_completed_at->format('d/m H:i') }})
                                                </span>
                                            @else
                                                <span class="text-slate-500">
                                                    Est: {{ $transaction->estimated_completed_at->format('d M, H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <x-ui.button
                                            :href="route($routePrefix . '.transactions.show', $transaction)"
                                            variant="soft"
                                            size="xs"
                                        >
                                            <x-lucide-eye class="size-3.5" aria-hidden="true" />
                                            <span>Detail</span>
                                        </x-ui.button>
                                        <x-ui.button
                                            :href="route($routePrefix . '.transactions.print', $transaction)"
                                            variant="secondary"
                                            size="xs"
                                            target="_blank"
                                            rel="noopener"
                                            title="Cetak struk kasir"
                                        >
                                            <x-lucide-printer class="size-3.5" aria-hidden="true" />
                                            <span>Cetak</span>
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <x-lucide-receipt class="size-6" aria-hidden="true" />
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-800">Tidak ada transaksi ditemukan</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        @if ($hasFilters)
                                            Coba ubah kata kunci atau bersihkan filter pencarian.
                                        @else
                                            Transaksi baru akan muncul di sini setelah kasir menyimpan nota.
                                        @endif
                                    </p>
                                    @if ($hasFilters)
                                        <div class="mt-3">
                                            <x-ui.button :href="route($routePrefix . '.transactions.index')" variant="secondary" size="xs">
                                                Bersihkan Filter
                                            </x-ui.button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Kartu Mobile --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($transactions as $transaction)
                    @php
                        $totalWeight = $transaction->details->sum('weight');
                        $firstService = $transaction->details->first()?->service?->name ?? 'Layanan Laundry';
                        $isOverdue = $transaction->estimated_completed_at
                            && $transaction->estimated_completed_at->isPast()
                            && !in_array($transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status, ['Selesai', 'Diambil', 'Dibatalkan']);
                    @endphp
                    <div class="space-y-3 p-4 transition-colors hover:bg-slate-50/60">
                        {{-- Baris 1: Kode & Status Cucian --}}
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a
                                    href="{{ route($routePrefix . '.transactions.show', $transaction) }}"
                                    class="font-mono font-bold tracking-tight text-brand-700 hover:underline"
                                >
                                    {{ $transaction->transaction_code }}
                                </a>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $transaction->created_at?->format('d M Y, H:i') }}
                                </p>
                            </div>
                            <x-ui.status-badge :status="$transaction->status" size="sm" />
                        </div>

                        {{-- Baris 2: Pelanggan & Layanan --}}
                        <div class="rounded-lg bg-slate-50 p-2.5 text-xs">
                            <div class="flex items-center justify-between font-semibold text-slate-800">
                                <span>{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</span>
                                <span class="font-normal text-slate-500">{{ $transaction->customer?->phone ?? '-' }}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-slate-600">
                                <span class="line-clamp-1">{{ $firstService }}</span>
                                <span class="font-medium shrink-0">{{ number_format((float) $totalWeight, 1, ',', '.') }} kg</span>
                            </div>
                        </div>

                        {{-- Baris 3: Estimasi Waktu jika ada --}}
                        @if ($transaction->estimated_completed_at)
                            <div class="flex items-center gap-1 text-xs">
                                <x-lucide-clock class="size-3.5 {{ $isOverdue ? 'text-rose-500' : 'text-slate-400' }}" aria-hidden="true" />
                                @if ($isOverdue)
                                    <span class="font-semibold text-rose-600">
                                        Terlambat (Estimasi: {{ $transaction->estimated_completed_at->format('d/m H:i') }})
                                    </span>
                                @else
                                    <span class="text-slate-500">
                                        Estimasi Selesai: {{ $transaction->estimated_completed_at->format('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Baris 4: Total & Tombol Aksi --}}
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <div>
                                <x-ui.money :value="$transaction->total_amount" class="text-sm font-bold text-slate-900" />
                                <div class="mt-0.5">
                                    <x-ui.payment-badge :status="$transaction->payment_status" size="sm" />
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <x-ui.button
                                    :href="route($routePrefix . '.transactions.show', $transaction)"
                                    variant="soft"
                                    size="xs"
                                >
                                    <x-lucide-eye class="size-3.5" aria-hidden="true" />
                                    <span>Detail</span>
                                </x-ui.button>
                                <x-ui.button
                                    :href="route($routePrefix . '.transactions.print', $transaction)"
                                    variant="secondary"
                                    size="xs"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    <x-lucide-printer class="size-3.5" aria-hidden="true" />
                                    <span>Cetak</span>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <x-lucide-receipt class="size-5" aria-hidden="true" />
                        </div>
                        <p class="mt-2.5 text-sm font-semibold text-slate-800">Belum ada transaksi</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Transaksi baru akan muncul di sini setelah kasir menyimpan nota.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Paginasi --}}
            @if ($transactions->hasPages())
                <div class="border-t border-slate-200 px-5 py-3.5">
                    {{ $transactions->links() }}
                </div>
            @endif
        </x-ui.card>

    </div>

</x-layouts::app>
