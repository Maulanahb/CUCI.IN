@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'staff';
    $txMeta = $transactions->mapWithKeys(fn($t) => [
        $t->id => [
            'id' => $t->id,
            'code' => $t->transaction_code,
            'status' => $t->status instanceof \BackedEnum ? $t->status->value : (string) $t->status,
            'is_paid' => $t->payment_status === 'Lunas',
            'is_terminal' => in_array($t->status instanceof \BackedEnum ? $t->status->value : (string) $t->status, ['Diambil', 'Dibatalkan']),
        ]
    ]);
@endphp

<x-layouts::app title="Kelola Transaksi">

    <div
        class="space-y-6"
        x-data="{
            selectedIds: [],
            txMeta: {{ json_encode($txMeta) }},
            targetStatus: 'Diproses',
            get selectedTransactions() {
                return this.selectedIds.map(id => this.txMeta[id]).filter(Boolean);
            },
            get unpaidCount() {
                return this.selectedTransactions.filter(t => !t.is_paid).length;
            },
            get terminalCount() {
                return this.selectedTransactions.filter(t => t.is_terminal).length;
            },
            get sameStatusCount() {
                return this.selectedTransactions.filter(t => t.status === this.targetStatus).length;
            },
            get validCount() {
                return this.selectedTransactions.filter(t => {
                    if (t.is_terminal) return false;
                    if (t.status === this.targetStatus) return false;
                    if (this.targetStatus === 'Diambil' && !t.is_paid) return false;
                    return true;
                }).length;
            },
            toggleAll(event) {
                if (event.target.checked) {
                    this.selectedIds = Object.keys(this.txMeta).map(Number);
                } else {
                    this.selectedIds = [];
                }
            },
            isAllSelected() {
                const total = Object.keys(this.txMeta).length;
                return total > 0 && this.selectedIds.length === total;
            }
        }"
    >

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route($routePrefix . '.dashboard') }}"
                        class="text-sm text-slate-400 hover:text-brand-600"
                    >
                        Dashboard
                    </a>

                    <span class="text-slate-300">/</span>

                    <span class="text-sm font-medium text-slate-600">
                        Kelola Transaksi
                    </span>
                </div>

                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    Kelola Transaksi
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola transaksi laundry dan pantau status pengerjaan cucian.
                </p>
            </div>

            <div>
                <a
                    href="{{ route($routePrefix . '.transactions.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 active:scale-98"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buka Kasir POS Baru</span>
                </a>
            </div>

        </div>


        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs sm:text-sm font-medium text-emerald-800">
                <svg class="size-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs sm:text-sm font-medium text-rose-800">
                <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs sm:text-sm text-rose-800">
                <p class="font-bold flex items-center gap-1.5">
                    <svg class="size-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Perhatian Validasi:</span>
                </p>
                <ul class="mt-1.5 list-disc pl-5 space-y-0.5 text-xs text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- Filter --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <form
                method="GET"
                action="{{ route($routePrefix . '.transactions.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-3"
            >

                {{-- Search --}}
                <div class="md:col-span-2">

                    <label
                        for="search"
                        class="mb-1.5 block text-xs font-medium text-slate-600"
                    >
                        Cari Transaksi
                    </label>

                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Kode transaksi atau nama pelanggan..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >

                </div>


                {{-- Status --}}
                <div>

                    <label
                        for="status"
                        class="mb-1.5 block text-xs font-medium text-slate-600"
                    >
                        Status Cucian
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >

                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="Diterima"
                            @selected(request('status') === 'Diterima')
                        >
                            Diterima
                        </option>

                        <option
                            value="Diproses"
                            @selected(request('status') === 'Diproses')
                        >
                            Diproses
                        </option>

                        <option
                            value="Selesai"
                            @selected(request('status') === 'Selesai')
                        >
                            Selesai
                        </option>

                        <option
                            value="Diambil"
                            @selected(request('status') === 'Diambil')
                        >
                            Diambil
                        </option>

                        <option
                            value="Dibatalkan"
                            @selected(request('status') === 'Dibatalkan')
                        >
                            Dibatalkan
                        </option>

                    </select>

                </div>


                {{-- Button --}}
                <div class="md:col-span-3 flex justify-end gap-2">

                    <a
                        href="{{ route($routePrefix . '.transactions.index') }}"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Reset
                    </a>

                    <button
                        type="submit"
                        class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                    >
                        Cari Transaksi
                    </button>

                </div>

            </form>

        </div>


        {{-- Daftar Transaksi --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Header Table / Integrated Bulk Toolbar --}}
            <div class="border-b border-slate-200">
                {{-- State Default: Info Jumlah Transaksi --}}
                <div x-show="selectedIds.length === 0" class="flex items-center justify-between px-5 py-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Daftar Transaksi
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $transactions->total() }} transaksi ditemukan
                        </p>
                    </div>
                </div>

                {{-- State Aktif: Integrated Bulk Action Toolbar dengan Validasi Real-Time --}}
                <div
                    x-cloak
                    x-show="selectedIds.length > 0"
                    x-transition:enter="ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="space-y-3 bg-brand-50/80 p-4 sm:px-5 border-b border-brand-200 transition"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex size-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white tabular-nums" x-text="selectedIds.length"></span>
                            <span class="text-xs font-bold text-brand-950">Transaksi Terpilih</span>
                            <span class="text-xs text-brand-700 hidden lg:inline">• Pembaruan status massal kasir</span>
                        </div>

                        <form
                            method="POST"
                            action="{{ route($routePrefix . '.transactions.bulk-status') }}"
                            class="flex flex-wrap items-center gap-2"
                        >
                            @csrf
                            @method('PATCH')

                            <template x-for="id in selectedIds" :key="id">
                                <input type="hidden" name="transaction_ids[]" :value="id" />
                            </template>

                            <div class="flex items-center gap-1.5">
                                <label for="bulk_status_select" class="text-xs font-medium text-brand-900 hidden sm:inline">Ubah ke:</label>
                                <select
                                    id="bulk_status_select"
                                    name="status"
                                    x-model="targetStatus"
                                    required
                                    class="rounded-xl border border-brand-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-2xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 cursor-pointer"
                                >
                                    <option value="Diproses">Diproses (Workshop)</option>
                                    <option value="Selesai">Selesai (Siap Diambil)</option>
                                    <option value="Diambil">Diambil (Diserahkan)</option>
                                </select>
                            </div>

                            <input
                                type="text"
                                name="notes"
                                placeholder="Catatan massal (opsional)..."
                                class="rounded-xl border border-brand-200 bg-white px-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 sm:w-48"
                            />

                            <button
                                type="submit"
                                :disabled="validCount === 0"
                                :class="validCount === 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-brand-600 hover:bg-brand-700 cursor-pointer'"
                                class="inline-flex items-center gap-1.5 rounded-xl px-4 py-1.5 text-xs font-bold text-white shadow-xs transition active:scale-98"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Terapkan (<span x-text="validCount"></span>)</span>
                            </button>

                            <button
                                type="button"
                                @click="selectedIds = []"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-2xs transition hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
                            >
                                Batal
                            </button>
                        </form>
                    </div>

                    {{-- Feedback & Validasi Real-time --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-brand-100 text-[11px]">
                        {{-- Kasus 1: Peringatan Pelunasan untuk status Diambil --}}
                        <template x-if="targetStatus === 'Diambil' && unpaidCount > 0">
                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 font-medium text-amber-900">
                                <svg class="size-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span><strong x-text="unpaidCount"></strong> nota belum lunas & akan otomatis dilewati sesuai proteksi SOP penyerahan.</span>
                            </span>
                        </template>

                        {{-- Kasus 2: Transaksi sudah berstatus terminal (Diambil / Dibatalkan) --}}
                        <template x-if="terminalCount > 0">
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-700">
                                <span><strong x-text="terminalCount"></strong> nota sudah final (Diambil/Dibatalkan) & tidak dapat diubah kembali.</span>
                            </span>
                        </template>

                        {{-- Kasus 3: Transaksi statusnya sudah sama dengan target --}}
                        <template x-if="sameStatusCount > 0">
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-600">
                                <span><strong x-text="sameStatusCount"></strong> nota sudah berstatus <span x-text="targetStatus"></span>.</span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>


            {{-- Table --}}
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">

                        <tr>
                            <th class="w-10 px-4 py-3">
                                <input
                                    type="checkbox"
                                    @change="toggleAll($event)"
                                    :checked="isAllSelected()"
                                    class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                    aria-label="Pilih semua transaksi"
                                />
                            </th>
                            <th class="px-5 py-3 font-semibold">
                                Kode Transaksi
                            </th>

                            <th class="px-5 py-3 font-semibold">
                                Pelanggan
                            </th>

                            <th class="px-5 py-3 font-semibold">
                                Total
                            </th>

                            <th class="px-5 py-3 font-semibold">
                                Status
                            </th>

                            <th class="px-5 py-3 text-right font-semibold">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse ($transactions as $transaction)

                            <tr class="transition hover:bg-slate-50" :class="selectedIds.includes({{ $transaction->id }}) ? 'bg-brand-50/40' : ''">
                                <td class="w-10 px-4 py-4">
                                    <input
                                        type="checkbox"
                                        :value="{{ $transaction->id }}"
                                        x-model.number="selectedIds"
                                        class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                        aria-label="Pilih transaksi {{ $transaction->transaction_code }}"
                                    />
                                </td>
                                {{-- Kode --}}
                                <td class="px-5 py-4">

                                    <p class="font-semibold text-slate-900">
                                        {{ $transaction->transaction_code }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        {{ $transaction->created_at?->format('d M Y H:i') }}
                                    </p>

                                </td>


                                {{-- Customer --}}
                                <td class="px-5 py-4">

                                    <p class="font-medium text-slate-800">
                                        {{ $transaction->customer?->name ?? 'Guest' }}
                                    </p>

                                    @if ($transaction->customer?->phone)
                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ $transaction->customer->phone }}
                                        </p>
                                    @endif

                                </td>


                                {{-- Total & Status Bayar --}}
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                    </p>

                                    @php
                                        $payStatus = $transaction->payment_status;
                                        $payClasses = match ($payStatus) {
                                            'Lunas' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'DP' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                                        };
                                    @endphp
                                    <div class="mt-1">
                                        <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $payClasses }}">
                                            {{ $payStatus }}
                                        </span>
                                    </div>
                                </td>


                                {{-- Status --}}
                                    <x-status-badge :status="$transaction->status" />
                                </td>


                                {{-- Aksi --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a
                                            href="{{ route($routePrefix . '.transactions.show', $transaction) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900"
                                        >
                                            Detail
                                        </a>
                                        <a
                                            href="{{ route($routePrefix . '.transactions.print', $transaction) }}"
                                            target="_blank"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-brand-700 shadow-2xs transition hover:bg-brand-50"
                                            title="Cetak Struk"
                                        >
                                            Cetak
                                        </a>
                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center"
                                >

                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100">

                                        <svg
                                            class="size-6 text-slate-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>

                                    </div>

                                    <p class="mt-3 text-sm font-medium text-slate-700">
                                        Belum ada transaksi
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Data transaksi yang tersedia akan muncul di sini.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if ($transactions->hasPages())

                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $transactions->links() }}
                </div>

            @endif

        </div>

    </div>

</x-layouts::app>