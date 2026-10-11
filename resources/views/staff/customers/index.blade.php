<x-layouts::app title="Data Pelanggan">
    <div class="space-y-5">
        {{-- Header & Sub-deskripsi Halaman --}}
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Data Pelanggan</h1>
            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                Manajemen kontak pelanggan, riwayat transaksi aktif, dan pelacakan durasi pengerjaan cucian sesuai kategori layanan.
            </p>
        </div>

        {{-- Card Ringkasan Total Pelanggan (Desain Kompak & Rapi) --}}
        <div class="rounded-xl border border-slate-200/90 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Total Pelanggan</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $totalCustomersCount }}</span>
                        <span class="text-xs font-medium text-slate-500">Akun</span>
                    </div>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Layout Kontainer: Fleksibel dan Responsif --}}
        <div class="flex flex-col items-start gap-5 lg:flex-row">

            {{-- Kolom Kiri: Search, Filter Tabs, & Daftar Pelanggan --}}
            <div @class([
                'w-full rounded-xl border border-slate-200/90 bg-white shadow-xs transition-all duration-200',
                'lg:w-full' => ! $selectedCustomer,
                'lg:w-[380px] lg:shrink-0 xl:w-[420px]' => $selectedCustomer,
            ])>
                {{-- Header List Pelanggan --}}
                <div class="border-b border-slate-100 p-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Daftar Pelanggan</h2>
                        <p class="text-[11px] text-slate-400">Urut: Transaksi Terakhir</p>
                    </div>

                    {{-- Form Pencarian --}}
                    <form method="GET" action="{{ route('staff.customers.index') }}" class="mt-3">
                        @if ($statusFilter !== 'all')
                            <input type="hidden" name="status" value="{{ $statusFilter }}">
                        @endif
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Cari nama atau telepon..."
                                class="w-full rounded-lg border border-slate-200 bg-slate-50/60 py-2 pr-8 pl-8 text-xs text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            >
                            @if ($search !== '')
                                <a
                                    href="{{ route('staff.customers.index', ['status' => $statusFilter]) }}"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                                    title="Hapus pencarian"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Tab Status Pills (Horizontal Scrollable) --}}
                    <div class="no-scrollbar mt-3 flex items-center gap-1 overflow-x-auto pb-0.5 text-[11px]">
                        <a
                            href="{{ route('staff.customers.index', array_filter(['q' => $search, 'status' => 'all'])) }}"
                            @class([
                                'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'all',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'all',
                            ])
                        >
                            Semua ({{ $counts['all'] }})
                        </a>
                        <a
                            href="{{ route('staff.customers.index', array_filter(['q' => $search, 'status' => 'processing'])) }}"
                            @class([
                                'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'processing',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'processing',
                            ])
                        >
                            Sedang Proses ({{ $counts['processing'] }})
                        </a>
                        <a
                            href="{{ route('staff.customers.index', array_filter(['q' => $search, 'status' => 'ready'])) }}"
                            @class([
                                'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'ready',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'ready',
                            ])
                        >
                            Siap Diambil ({{ $counts['ready'] }})
                        </a>
                        <a
                            href="{{ route('staff.customers.index', array_filter(['q' => $search, 'status' => 'completed'])) }}"
                            @class([
                                'shrink-0 rounded-md px-2.5 py-1 font-semibold transition',
                                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'completed',
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== 'completed',
                            ])
                        >
                            Selesai ({{ $counts['completed'] }})
                        </a>
                    </div>
                </div>

                {{-- List Item Pelanggan --}}
                <div class="divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        @php
                            $isSelected = $selectedCustomer && $selectedCustomer->id === $customer->id;
                            $initials = collect(explode(' ', $customer->name))
                                ->map(fn($part) => mb_substr($part, 0, 1))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <a
                            href="{{ route('staff.customers.index', array_filter([
                                'customer_id' => $customer->id,
                                'q' => $search,
                                'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                'page' => request('page'),
                            ])) }}"
                            @class([
                                'block p-3.5 transition',
                                'border-l-4 border-brand-600 bg-brand-50/50' => $isSelected,
                                'hover:bg-slate-50' => ! $isSelected,
                            ])
                        >
                            <div class="flex items-start gap-3">
                                <div @class([
                                    'flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold uppercase transition',
                                    'bg-brand-200 text-brand-900 ring-2 ring-brand-500/30' => $isSelected,
                                    'bg-brand-100 text-brand-800' => ! $isSelected,
                                ])>
                                    {{ $initials ?: 'CU' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-xs font-bold text-slate-900 sm:text-sm">{{ $customer->name }}</h3>
                                    <p class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-500">
                                        <svg class="size-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span>{{ $customer->phone ?: '-' }}</span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center">
                            <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-slate-900">Data Tidak Ditemukan</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">Sesuaikan kata kunci pencarian Anda.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Footer --}}
                @if ($customers->hasPages())
                    <div class="border-t border-slate-100 p-3">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Detail Pelanggan Terpilih (Hanya Tampil Jika Dipilih) --}}
            @if ($selectedCustomer)
                <div class="w-full min-w-0 flex-1 space-y-5">
                    @php
                        $selectedInitials = collect(explode(' ', $selectedCustomer->name))
                            ->map(fn($part) => mb_substr($part, 0, 1))
                            ->take(2)
                            ->implode('');
                    @endphp

                    {{-- 1. Kartu Info Profil Pelanggan --}}
                    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3.5">
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-800">
                                    {{ $selectedInitials ?: 'CU' }}
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 sm:text-lg">{{ $selectedCustomer->name }}</h2>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 text-brand-700 font-medium">
                                            <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.151.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.768-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.995-.368-1.78-.769-2.936-2.578-3.025-2.697-.089-.119-.724-.962-.724-1.835 0-.873.456-1.302.619-1.479.163-.177.355-.221.473-.221.118 0 .237.001.341.006.109.006.255-.041.398.303.148.355.503 1.226.547 1.315.044.089.074.192.015.31-.059.119-.089.192-.178.296-.089.103-.187.23-.266.31-.089.089-.182.185-.078.363.103.178.46 0.758.987 1.228.679.605 1.252.793 1.43.882.178.089.281.074.385-.044.104-.119.444-.518.563-.695.118-.178.237-.148.399-.089.163.059 1.035.488 1.213.577.177.089.296.133.34.207.045.074.045.429-.099.834z"/>
                                            </svg>
                                            WhatsApp: {{ $selectedCustomer->phone ?: '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Close (X) Detail --}}
                            <div class="flex items-center justify-end">
                                <a
                                    href="{{ route('staff.customers.index', array_filter([
                                        'q' => $search,
                                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                        'page' => request('page'),
                                    ])) }}"
                                    class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                                    title="Tutup Detail"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-3 sm:grid-cols-3">
                            <div>
                                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Terdaftar Sejak</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800">
                                    {{ $selectedCustomer->created_at ? $selectedCustomer->created_at->translatedFormat('d M Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Total Transaksi</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800">
                                    {{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }} Nota
                                    <span class="font-normal text-slate-500">(Rp {{ number_format($totalSpent, 0, ',', '.') }})</span>
                                </p>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Alamat</p>
                                <p class="mt-0.5 truncate text-xs font-semibold text-slate-700" title="{{ $selectedCustomer->address ?: '-' }}">
                                    {{ $selectedCustomer->address ?: '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Kartu Transaksi Aktif / Berjalan (Jika Ada) --}}
                    @if ($activeTransaction)
                        @php
                            $firstDetail = $activeTransaction->details->first();
                            $service = $firstDetail?->service;
                            $latestPayment = $activeTransaction->payments->last();
                            $isPaid = $latestPayment && in_array($latestPayment->verification_status, ['Tidak Diperlukan', 'Terverifikasi']);
                        @endphp
                        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs">
                            {{-- Header Transaksi --}}
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <span class="text-sm font-bold text-slate-900 sm:text-base">{{ $activeTransaction->transaction_code }}</span>
                                    <p class="text-[11px] text-slate-400">
                                        Diterima: {{ $activeTransaction->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        @if ($latestPayment && $latestPayment->creator)
                                            oleh {{ $latestPayment->creator->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span @class([
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                                        'bg-brand-50 text-brand-700 ring-1 ring-brand-600/20' => $isPaid,
                                        'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' => ! $isPaid,
                                    ])>
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ $isPaid ? 'Lunas' : 'Belum Lunas' }}
                                        @if ($latestPayment)
                                            ({{ $latestPayment->method }})
                                        @endif
                                    </span>

                                    {{-- Tombol Tutup (X) Detail Riwayat Transaksi --}}
                                    <a
                                        href="{{ route('staff.customers.index', array_filter([
                                            'customer_id' => $selectedCustomer->id,
                                            'q' => $search,
                                            'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                            'page' => request('page'),
                                        ])) }}"
                                        class="inline-flex size-7 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                        title="Tutup detail transaksi ini"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Box Detail Transaksi --}}
                            <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50/80 p-3.5">
                                <p class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Detail Transaksi</p>

                                <div class="mt-2.5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-bold text-slate-900 sm:text-sm">
                                                {{ $service?->name ?? 'Cuci Komplit Reguler' }}
                                            </h4>
                                            <span class="rounded bg-sky-100 px-1.5 py-0.2 text-[9px] font-bold text-sky-700 uppercase">
                                                Kategori Reguler
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Berat: <strong class="text-slate-700">{{ $firstDetail?->weight ?? 1 }} kg</strong>
                                            @if ($firstDetail?->unit_price)
                                                × Rp {{ number_format($firstDetail->unit_price, 0, ',', '.') }}
                                            @endif
                                            @if ($service?->description)
                                                • {{ $service->description }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-sm font-extrabold text-slate-900">
                                            Rp {{ number_format($activeTransaction->total_amount, 0, ',', '.') }}
                                        </p>
                                        <span class="mt-0.5 inline-block rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">
                                            Selesai {{ $service?->estimated_duration ?? 3 }} Hari
                                        </span>
                                    </div>
                                </div>

                                {{-- Baris Estimasi & Status Cucian --}}
                                <div class="mt-3 flex flex-col gap-2 rounded-lg bg-white p-2.5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <svg class="size-3.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>
                                            Estimasi Pengambilan:
                                            <strong class="text-slate-800">
                                                {{ $activeTransaction->estimated_completed_at ? $activeTransaction->estimated_completed_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Menunggu update' }}
                                            </strong>
                                        </span>
                                    </div>
                                    <div>
                                        <span @class([
                                            'rounded px-2.5 py-0.5 text-[11px] font-bold uppercase',
                                            'bg-sky-50 text-sky-700 border border-sky-200' => in_array($activeTransaction->status, ['Diterima', 'Diproses']),
                                            'bg-brand-50 text-brand-700 border border-brand-200' => in_array($activeTransaction->status, ['Selesai', 'Diambil']),
                                            'bg-rose-50 text-rose-700 border border-rose-200' => $activeTransaction->status === 'Dibatalkan',
                                        ])>
                                            {{ $activeTransaction->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Total Akhir --}}
                            <div class="mt-3.5 flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Akhir :</span>
                                <span class="text-lg font-extrabold text-slate-900">
                                    Rp {{ number_format($activeTransaction->total_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Tabel Riwayat Transaksi Cucian Sebelumnya --}}
                    <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold tracking-wider text-slate-900 uppercase">
                                Riwayat Transaksi Cucian Sebelumnya
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-brand-700">
                                    {{ $previousTransactions->count() }} Transaksi
                                </span>
                                {{-- Tombol Tutup (X) Seluruh Riwayat & Detail Pelanggan --}}
                                <a
                                    href="{{ route('staff.customers.index', array_filter([
                                        'q' => $search,
                                        'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                        'page' => request('page'),
                                    ])) }}"
                                    class="inline-flex size-6 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                    title="Tutup riwayat & detail pelanggan"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase">
                                        <th class="pb-2.5 font-semibold">No. Nota</th>
                                        <th class="pb-2.5 font-semibold">Tgl Masuk</th>
                                        <th class="pb-2.5 font-semibold">Kategori Layanan</th>
                                        <th class="pb-2.5 font-semibold">Durasi</th>
                                        <th class="pb-2.5 font-semibold">Total Biaya</th>
                                        <th class="pb-2.5 text-right font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse ($previousTransactions as $prevTrx)
                                        @php
                                            $detail = $prevTrx->details->first();
                                            $prevService = $detail?->service;
                                            $isRowSelected = $activeTransaction && $activeTransaction->id === $prevTrx->id;
                                        @endphp
                                        <tr
                                            onclick="window.location.href='{{ route('staff.customers.index', array_filter([
                                                'customer_id' => $selectedCustomer->id,
                                                'transaction_id' => $prevTrx->id,
                                                'q' => $search,
                                                'status' => $statusFilter !== 'all' ? $statusFilter : null,
                                                'page' => request('page'),
                                            ])) }}'"
                                            @class([
                                                'group cursor-pointer transition',
                                                'bg-brand-50/70 font-semibold ring-1 ring-inset ring-brand-500/20' => $isRowSelected,
                                                'hover:bg-slate-50/80' => ! $isRowSelected,
                                            ])
                                            title="Klik untuk melihat detail nota {{ $prevTrx->transaction_code }}"
                                        >
                                            <td class="py-2.5 font-bold text-slate-900 group-hover:text-brand-600">
                                                <div class="flex items-center gap-1.5">
                                                    @if ($isRowSelected)
                                                        <span class="size-1.5 rounded-full bg-brand-600"></span>
                                                    @endif
                                                    <span>{{ $prevTrx->transaction_code }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-slate-500">
                                                {{ $prevTrx->created_at->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="py-2.5">
                                                {{ $prevService?->name ?? 'Cuci Komplit' }}
                                            </td>
                                            <td class="py-2.5">
                                                <span class="rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">
                                                    {{ $prevService?->estimated_duration ?? 3 }} Hari
                                                </span>
                                            </td>
                                            <td class="py-2.5 font-semibold text-slate-900">
                                                Rp {{ number_format($prevTrx->total_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 text-right">
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                                    'bg-brand-100 text-brand-800' => in_array($prevTrx->status, ['Selesai', 'Diambil']),
                                                    'bg-amber-100 text-amber-800' => in_array($prevTrx->status, ['Diterima', 'Diproses']),
                                                    'bg-rose-100 text-rose-800' => $prevTrx->status === 'Dibatalkan',
                                                ])>
                                                    {{ $prevTrx->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-5 text-center text-xs text-slate-400">
                                                Belum ada riwayat transaksi lampau untuk pelanggan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 border-t border-slate-100 pt-2.5 text-[11px] text-slate-400">
                            Total riwayat tersimpan: {{ $selectedCustomer->transactions_count ?? $selectedCustomer->transactions()->count() }} transaksi
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts::app>
