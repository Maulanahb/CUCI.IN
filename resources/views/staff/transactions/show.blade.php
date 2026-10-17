@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
    $statusClasses = \App\Enums\TransactionStatus::visualStyle($statusValue)['badge'];
@endphp

<x-layouts::app title="Detail Transaksi {{ $transaction->transaction_code }}">
    <div class="space-y-6">
        {{-- Header & Aksi Transaksi --}}
        @include('staff.transactions.partials.detail-header')

        {{-- Flash Pesan Sukses --}}
        @if (session('success'))
            <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                <svg class="mt-0.5 size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- Error Validation --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Terjadi kesalahan:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Kartu Info Customer & Transaksi --}}
        @include('staff.transactions.partials.detail-customer-and-info')

        {{-- Detail Layanan Items Table --}}
        @include('staff.transactions.partials.detail-items-table')

        {{-- Informasi & Riwayat Pembayaran --}}
        @include('staff.transactions.partials.detail-payment')

        {{-- Update Status Operasional & Pipeline Stepper --}}
        @include('staff.transactions.partials.detail-status-section')

        {{-- Audit Trail / Riwayat Pembaruan Status --}}
        @include('staff.transactions.partials.detail-status-history')
    </div>
</x-layouts::app>
