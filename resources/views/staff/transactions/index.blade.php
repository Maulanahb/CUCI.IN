<x-layouts::app title="Kelola Transaksi">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('staff.dashboard') }}"
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

        </div>


        {{-- Flash Message --}}
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif


        {{-- Filter --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <form
                method="GET"
                action="{{ route('staff.transactions.index') }}"
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
                        href="{{ route('staff.transactions.index') }}"
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

            {{-- Header Table --}}
            <div class="border-b border-slate-200 px-5 py-4">

                <div class="flex items-center justify-between">

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Daftar Transaksi
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $transactions->total() }} transaksi ditemukan
                        </p>
                    </div>

                </div>

            </div>


            {{-- Table --}}
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">

                        <tr>

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

                            <tr class="transition hover:bg-slate-50">

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


                                {{-- Total --}}
                                <td class="px-5 py-4">

                                    <p class="font-semibold text-slate-900">
                                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                    </p>

                                </td>


                                {{-- Status --}}
                                <td class="px-5 py-4">

                                    @php
                                        $statusClasses = match ($transaction->status) {
                                            'Diterima' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'Selesai' => 'bg-green-50 text-green-700 border-green-200',
                                            'Diambil' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'Dibatalkan' => 'bg-red-50 text-red-700 border-red-200',
                                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                                        };
                                    @endphp

                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                        {{ $transaction->status }}
                                    </span>

                                </td>


                                {{-- Aksi --}}
                                <td class="px-5 py-4 text-right">

                                    <a
                                        href="{{ route('staff.transactions.show', $transaction) }}"
                                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600"
                                    >
                                        Detail
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
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