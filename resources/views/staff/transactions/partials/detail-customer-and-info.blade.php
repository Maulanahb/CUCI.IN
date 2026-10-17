{{-- ============================================================
    INFORMASI UTAMA (CUSTOMER & TRANSAKSI)
============================================================= --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Informasi Customer --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <svg
                    class="size-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
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

            {{-- Alamat --}}
            @if ($transaction->customer?->address)
                <div>
                    <p class="text-xs text-slate-400">
                        Alamat
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-600">
                        {{ $transaction->customer->address }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Informasi Transaksi --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <svg
                    class="size-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
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
