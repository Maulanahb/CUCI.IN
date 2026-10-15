<x-layouts::app title="Detail Transaksi">

    <div class="space-y-6">

        {{-- ============================================================
            HEADER
        ============================================================= --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>

                {{-- Breadcrumb --}}
                <div class="flex items-center gap-2 text-sm">

                    <a
                        href="{{ route('staff.dashboard') }}"
                        class="text-slate-400 transition hover:text-brand-600"
                    >
                        Dashboard
                    </a>

                    <span class="text-slate-300">
                        /
                    </span>

                    <a
                        href="{{ route('staff.transactions.index') }}"
                        class="text-slate-400 transition hover:text-brand-600"
                    >
                        Kelola Transaksi
                    </a>

                    <span class="text-slate-300">
                        /
                    </span>

                    <span class="font-medium text-slate-600">
                        Detail
                    </span>

                </div>


                <div class="mt-2">

                    <div class="flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            {{ $transaction->transaction_code }}
                        </h1>

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

                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ $transaction->status }}
                        </span>

                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Detail transaksi dan proses operasional cucian.
                    </p>

                </div>

            </div>


            {{-- Kembali --}}
            <a
                href="{{ route('staff.transactions.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600"
            >

                <svg
                    class="size-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>

                Kembali

            </a>

        </div>


        {{-- ============================================================
            SUCCESS MESSAGE
        ============================================================= --}}
        @if (session('success'))

            <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">

                <svg
                    class="mt-0.5 size-5 shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <p>
                    {{ session('success') }}
                </p>

            </div>

        @endif


        {{-- ============================================================
            ERROR VALIDATION
        ============================================================= --}}
        @if ($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">

                <p class="font-semibold">
                    Terjadi kesalahan:
                </p>

                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        {{-- ============================================================
            INFORMASI UTAMA
        ============================================================= --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">


            {{-- ========================================================
                INFORMASI CUSTOMER
            ========================================================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">

                <div class="flex items-center gap-3">

                    <div class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">

                        <svg
                            class="size-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>

                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Informasi Pelanggan
                        </h2>

                        <p class="text-xs text-slate-400">
                            Data customer transaksi
                        </p>
                    </div>

                </div>


                <div class="mt-6 space-y-4">

                    {{-- Nama --}}
                    <div>
                        <p class="text-xs text-slate-400">
                            Nama Pelanggan
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $transaction->customer?->name ?? 'Guest' }}
                        </p>
                    </div>


                    {{-- Telepon --}}
                    @if ($transaction->customer?->phone)

                        <div>
                            <p class="text-xs text-slate-400">
                                Nomor Telepon
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ $transaction->customer->phone }}
                            </p>
                        </div>

                    @endif


                    {{-- Email --}}
                    @if ($transaction->customer?->email)

                        <div>
                            <p class="text-xs text-slate-400">
                                Email
                            </p>

                            <p class="mt-1 break-all text-sm font-medium text-slate-700">
                                {{ $transaction->customer->email }}
                            </p>
                        </div>

                    @endif

                </div>

            </div>


            {{-- ========================================================
                INFORMASI TRANSAKSI
            ========================================================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">

                <div class="flex items-center gap-3">

                    <div class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">

                        <svg
                            class="size-5"
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

                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            Informasi Transaksi
                        </h2>

                        <p class="text-xs text-slate-400">
                            Informasi utama transaksi laundry
                        </p>
                    </div>

                </div>


                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">

                    {{-- Kode Transaksi --}}
                    <div>
                        <p class="text-xs text-slate-400">
                            Kode Transaksi
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $transaction->transaction_code }}
                        </p>
                    </div>


                    {{-- Tanggal --}}
                    <div>
                        <p class="text-xs text-slate-400">
                            Tanggal Transaksi
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $transaction->created_at?->format('d F Y, H:i') ?? '-' }}
                        </p>
                    </div>


                    {{-- Estimasi --}}
                    <div>
                        <p class="text-xs text-slate-400">
                            Estimasi Selesai
                        </p>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            {{ $transaction->estimated_completed_at?->format('d F Y, H:i') ?? '-' }}
                        </p>
                    </div>


                    {{-- Total --}}
                    <div>
                        <p class="text-xs text-slate-400">
                            Total Transaksi
                        </p>

                        <p class="mt-1 text-lg font-bold text-brand-600">
                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                        </p>
                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            DETAIL LAYANAN
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

                            <tr class="hover:bg-slate-50">

                                {{-- Service --}}
                                <td class="px-6 py-4">

                                    <p class="font-medium text-slate-900">
                                        {{ $detail->service?->name ?? 'Layanan' }}
                                    </p>

                                </td>


                                {{-- Weight --}}
                                <td class="px-6 py-4 text-slate-600">

                                    {{ $detail->weight }}

                                    @if ($detail->unit)
                                        {{ $detail->unit }}
                                    @else
                                        kg
                                    @endif

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


        {{-- ============================================================
            UPDATE STATUS OPERASIONAL
        ============================================================= --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <h2 class="text-sm font-semibold text-slate-900">
                        Status Operasional Cucian
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        Perbarui status transaksi sesuai dengan proses pengerjaan laundry.
                    </p>

                </div>


                {{-- Status Saat Ini --}}
                <div>

                    <span class="text-xs text-slate-400">
                        Status saat ini
                    </span>

                    <div class="mt-1">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ $transaction->status }}
                        </span>
                    </div>

                </div>

            </div>


            {{-- Form Update --}}
            <form
                method="POST"
                action="{{ route('staff.transactions.status', $transaction) }}"
                class="mt-6"
            >

                @csrf
                @method('PATCH')


                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                    {{-- Status --}}
                    <div class="md:col-span-2">

                        <label
                            for="status"
                            class="mb-1.5 block text-xs font-medium text-slate-600"
                        >
                            Ubah Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >

                            <option value="Diterima" @selected($transaction->status === 'Diterima')}>
                                Diterima
                            </option>

                            <option value="Diproses" @selected($transaction->status === 'Diproses')}>
                                Diproses
                            </option>

                            <option value="Selesai" @selected($transaction->status === 'Selesai')}>
                                Selesai
                            </option>

                            <option value="Diambil" @selected($transaction->status === 'Diambil')}>
                                Diambil
                            </option>

                            <option value="Dibatalkan" @selected($transaction->status === 'Dibatalkan')}>
                                Dibatalkan
                            </option>

                        </select>

                    </div>


                    {{-- Submit --}}
                    <div class="flex items-end">

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                        >
                            Perbarui Status
                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- ============================================================
            TIMELINE STATUS
        ============================================================= --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-6">

                <h2 class="text-sm font-semibold text-slate-900">
                    Alur Status Cucian
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Tahapan proses transaksi laundry.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

                {{-- Diterima --}}
                <div
                    class="rounded-xl border p-4
                    {{ in_array($transaction->status, ['Diterima', 'Diproses', 'Selesai', 'Diambil'])
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ in_array($transaction->status, ['Diterima', 'Diproses', 'Selesai', 'Diambil'])
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            1
                        </div>

                        <div>

                            <p class="text-xs font-semibold text-slate-900">
                                Diterima
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Cucian masuk
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Diproses --}}
                <div
                    class="rounded-xl border p-4
                    {{ in_array($transaction->status, ['Diproses', 'Selesai', 'Diambil'])
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ in_array($transaction->status, ['Diproses', 'Selesai', 'Diambil'])
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            2
                        </div>

                        <div>

                            <p class="text-xs font-semibold text-slate-900">
                                Diproses
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Cucian dikerjakan
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Selesai --}}
                <div
                    class="rounded-xl border p-4
                    {{ in_array($transaction->status, ['Selesai', 'Diambil'])
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ in_array($transaction->status, ['Selesai', 'Diambil'])
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            3
                        </div>

                        <div>

                            <p class="text-xs font-semibold text-slate-900">
                                Selesai
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Siap diambil
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Diambil --}}
                <div
                    class="rounded-xl border p-4
                    {{ $transaction->status === 'Diambil'
                        ? 'border-brand-200 bg-brand-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ $transaction->status === 'Diambil'
                                ? 'bg-brand-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            4
                        </div>

                        <div>

                            <p class="text-xs font-semibold text-slate-900">
                                Diambil
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Transaksi selesai
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Dibatalkan --}}
                <div
                    class="rounded-xl border p-4
                    {{ $transaction->status === 'Dibatalkan'
                        ? 'border-red-200 bg-red-50'
                        : 'border-slate-200 bg-slate-50' }}"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ $transaction->status === 'Dibatalkan'
                                ? 'bg-red-600 text-white'
                                : 'bg-white text-slate-500' }}"
                        >
                            !
                        </div>

                        <div>

                            <p class="text-xs font-semibold text-slate-900">
                                Dibatalkan
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                Transaksi dibatalkan
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layouts::app>