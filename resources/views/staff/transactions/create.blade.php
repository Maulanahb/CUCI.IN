@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $transactionIndexRoute = Route::has("{$currentRoutePrefix}.transactions.index")
        ? route("{$currentRoutePrefix}.transactions.index")
        : (Route::has('staff.transactions.index') ? route('staff.transactions.index') : route('dashboard'));
    $customerDirectory = $customers->mapWithKeys(fn ($c) => [
        (string) $c->id => [
            'id' => (string) $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'address' => $c->address ?? '',
        ],
    ]);
@endphp

<x-layouts::app title="Transaksi Baru (POS)">
    <div
        class="space-y-4"
        x-data="posTransactionManager()"
    >
        {{-- Top Bar Navigasi Kasir --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">Kasir POS — Transaksi Baru</h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terminal Aktif
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                    Pilih layanan di katalog kiri, sesuaikan timbangan, dan simpan nota kasir secara instan.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ $transactionIndexRoute }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 active:scale-98"
                >
                    <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Daftar Transaksi</span>
                </a>
            </div>
        </div>

        {{-- Alert Notifikasi Status --}}
        @if (session('status'))
            <div class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <svg class="size-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Alert Error Global --}}
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-xs font-semibold text-rose-800 shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="size-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Terdapat kesalahan pada input transaksi:</span>
                </div>
                <ul class="mt-2 list-inside list-disc space-y-0.5 pl-2 font-normal">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SPLIT-SCREEN LAYOUT POS (Kiri: Katalog Cepat | Kanan: Tiket Nota) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
            {{-- Kolom Kiri: Katalog Cepat Layanan Laundry --}}
            @include('staff.transactions.partials.service-catalog')

            {{-- Kolom Kanan: Tiket Nota Kasir (Sticky Checkout) --}}
            @include('staff.transactions.partials.order-ticket')
        </div>

        {{-- Modal Tambah Pelanggan Cepat (AJAX Quick Add) --}}
        @include('staff.transactions.partials.quick-customer-modal')
    </div>

    {{-- Script Alpine POS & Interaksi DOM --}}
    @include('staff.transactions.partials.pos-scripts')
</x-layouts::app>
