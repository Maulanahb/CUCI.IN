{{-- ============================================================
    HEADER & ACTIONS DETAIL TRANSAKSI
============================================================= --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm">
            <a
                href="{{ route('staff.dashboard') }}"
                class="text-slate-400 transition hover:text-brand-600"
            >
                Dashboard
            </a>

            <span class="text-slate-300">/</span>

            <a
                href="{{ route('staff.transactions.index') }}"
                class="text-slate-400 transition hover:text-brand-600"
            >
                Kelola Transaksi
            </a>

            <span class="text-slate-300">/</span>

            <span class="font-medium text-slate-600">
                Detail
            </span>
        </nav>

        <div class="mt-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $transaction->transaction_code }}
                </h1>

                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $statusValue }}
                </span>
            </div>

            <p class="mt-1 text-sm text-slate-500">
                Detail transaksi dan proses operasional cucian pelanggan.
            </p>
        </div>
    </div>

    {{-- Tombol Aksi Header --}}
    <div class="flex flex-wrap items-center gap-2">
        <a
            id="btn-print-receipt"
            href="{{ route("{$currentRoutePrefix}.transactions.print", $transaction) }}"
            target="_blank"
            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Nota</span>
        </a>

        <a
            id="btn-back-to-create"
            href="{{ route("{$currentRoutePrefix}.transactions.create") }}"
            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Transaksi Baru</span>
        </a>

        <a
            href="{{ route('staff.transactions.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600"
        >
            <svg
                class="size-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                />
            </svg>
            <span>Kembali</span>
        </a>
    </div>
</div>
