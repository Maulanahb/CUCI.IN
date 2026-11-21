@php
    /**
     * Menu navigasi utama dan navigasi bawah mobile/tablet.
     * Item hanya tampil jika route-nya sudah didaftarkan.
     *
     * @var array<int, array{label: string, short_label: string, bottom: bool, route: string, active: string|array<int, string>, icon: string}> $navigation
     */
    $role = auth()->user()?->role;

    $navigation = match ($role) {
        'customer' => [
            ['label' => 'Beranda', 'short_label' => 'Beranda', 'bottom' => true, 'route' => 'customer.dashboard', 'active' => 'customer.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['label' => 'Riwayat Transaksi', 'short_label' => 'Riwayat', 'bottom' => true, 'route' => 'customer.transactions.index', 'active' => 'customer.transactions.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['label' => 'Pengaduan', 'short_label' => 'Pengaduan', 'bottom' => true, 'route' => 'customer.complaints.index', 'active' => 'customer.complaints.*', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
            ['label' => 'Lacak Nota Fisik', 'short_label' => 'Lacak Nota', 'bottom' => true, 'route' => 'transactions.lookup', 'active' => 'transactions.lookup', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
        ],
        'staff' => [
            ['label' => 'Dashboard', 'short_label' => 'Beranda', 'bottom' => true, 'route' => 'staff.dashboard', 'active' => 'staff.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['label' => 'Kasir POS', 'short_label' => 'Kasir POS', 'bottom' => true, 'route' => 'staff.transactions.create', 'active' => 'staff.transactions.create', 'icon' => 'M12 4v16m8-8H4'],
            ['label' => 'Kelola Transaksi', 'short_label' => 'Transaksi', 'bottom' => true, 'route' => 'staff.transactions.index', 'active' => ['staff.transactions.index', 'staff.transactions.show', 'staff.transactions.print'], 'icon' => 'M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01'],
            ['label' => 'Verifikasi Pembayaran', 'short_label' => 'Verifikasi', 'bottom' => true, 'route' => 'staff.payments.index', 'active' => 'staff.payments.*', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['label' => 'Data Pelanggan', 'short_label' => 'Pelanggan', 'bottom' => false, 'route' => 'staff.customers.index', 'active' => 'staff.customers.*', 'icon' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.87M16 2.13a4 4 0 010 7.75'],
            ['label' => 'Katalog Layanan', 'short_label' => 'Layanan', 'bottom' => false, 'route' => 'staff.services.index', 'active' => 'staff.services.*', 'icon' => 'M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2'],
            ['label' => 'Pengaduan', 'short_label' => 'Pengaduan', 'bottom' => false, 'route' => 'staff.complaints.index', 'active' => 'staff.complaints.*', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ],
        'admin' => [
            ['label' => 'Dashboard', 'short_label' => 'Beranda', 'bottom' => true, 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['label' => 'Kasir POS', 'short_label' => 'Kasir POS', 'bottom' => true, 'route' => 'admin.transactions.create', 'active' => 'admin.transactions.create', 'icon' => 'M12 4v16m8-8H4'],
            ['label' => 'Kelola Transaksi', 'short_label' => 'Transaksi', 'bottom' => true, 'route' => 'admin.transactions.index', 'active' => ['admin.transactions.index', 'admin.transactions.show', 'admin.transactions.print'], 'icon' => 'M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01'],
            ['label' => 'Verifikasi Pembayaran', 'short_label' => 'Verifikasi', 'bottom' => false, 'route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['label' => 'Data Pelanggan', 'short_label' => 'Pelanggan', 'bottom' => false, 'route' => 'admin.customers.index', 'active' => 'admin.customers.*', 'icon' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.87M16 2.13a4 4 0 010 7.75'],
            ['label' => 'Master Layanan', 'short_label' => 'Layanan', 'bottom' => false, 'route' => 'admin.services.index', 'active' => 'admin.services.*', 'icon' => 'M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2'],
            ['label' => 'Pengaduan', 'short_label' => 'Pengaduan', 'bottom' => false, 'route' => 'admin.complaints.index', 'active' => 'admin.complaints.*', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
            ['label' => 'Laporan', 'short_label' => 'Laporan', 'bottom' => true, 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['label' => 'Kelola Pengguna', 'short_label' => 'Pengguna', 'bottom' => false, 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ],
        default => [
            ['label' => 'Dashboard', 'short_label' => 'Beranda', 'bottom' => true, 'route' => 'dashboard', 'active' => '*dashboard*', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
        ],
    };

    $navItems = collect($navigation)
        ->filter(fn (array $item): bool => Route::has($item['route']))
        ->map(function (array $item): array {
            $isActive = is_array($item['active'])
                ? request()->routeIs(...$item['active'])
                : request()->routeIs($item['active']);

            return [
                ...$item,
                'url' => route($item['route']),
                'is_active' => $isActive,
            ];
        })
        ->values();

    $bottomNavItems = $navItems->where('bottom', true)->values();
    $isOverflowActive = $navItems->where('bottom', false)->contains('is_active', true);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false, isDesktop: window.matchMedia('(min-width: 64rem)').matches }" x-init="window.matchMedia('(min-width: 64rem)').addEventListener('change', (event) => { isDesktop = event.matches; if (event.matches) { sidebarOpen = false; } }); $watch('sidebarOpen', (open) => { if (! isDesktop) { $nextTick(() => document.getElementById(open ? 'sidebar-close' : 'sidebar-toggle').focus()); } })" x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)" @keydown.escape.window="sidebarOpen = false">
        {{-- Skip to main content link (Aksesibilitas Keyboard WCAG 2.2 AA) --}}
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-xl focus:bg-brand-700 focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
        >
            Lewati ke konten utama
        </a>

        {{-- Overlay mobile --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
            :inert="! sidebarOpen && ! isDesktop"
        >
            <div class="flex h-16 items-center gap-2 border-b border-slate-100 px-5">
                <x-app-logo class="size-8" />
                <span class="text-lg font-semibold tracking-tight text-brand-700">CUCI<span class="text-brand-600">.IN</span></span>
                <button type="button" id="sidebar-close" class="ml-auto flex size-11 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                    <x-lucide-x class="size-5" aria-hidden="true" />
                </button>
            </div>

            <nav aria-label="Navigasi utama" class="flex flex-1 flex-col gap-1.5 overflow-y-auto p-3 pt-5">
                @foreach ($navItems as $item)
                    <a
                        href="{{ $item['url'] }}"
                        @if ($item['is_active']) aria-current="page" @endif
                        wire:navigate
                        @class([
                            'flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                            'bg-brand-700 text-white' => $item['is_active'],
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $item['is_active'],
                        ])
                    >
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            @auth
                <div class="border-t border-slate-100 p-3">
                    <div class="flex items-center gap-3 rounded-lg px-3 py-2">
                        <div class="flex size-9 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">
                            {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p>
                        </div>
                    </div>

                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" id="logout-button" class="mt-1 w-full rounded-lg px-3 py-2 pointer-coarse:min-h-11 text-left text-sm font-medium text-slate-600 transition-colors hover:bg-rose-50 hover:text-rose-600">
                                Keluar
                            </button>
                        </form>
                    @endif
                </div>
            @endauth
        </aside>

        {{-- Konten utama --}}
        <div class="flex min-h-screen flex-col lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/80 backdrop-blur">
                <div class="mx-auto flex h-16 w-full max-w-7xl items-center gap-3 px-4 sm:px-6">
                    <x-app-logo class="size-8 shrink-0 lg:hidden" />

                    <h1 class="min-w-0 truncate text-base font-semibold text-slate-900 sm:text-lg">{{ $title ?? config('app.name') }}</h1>

                    @isset($actions)
                        <div class="ml-auto flex items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>
            </header>

            <main id="main-content" class="mx-auto w-full max-w-7xl flex-1 p-4 pb-[calc(5.5rem+env(safe-area-inset-bottom))] sm:p-6 sm:pb-[calc(5.5rem+env(safe-area-inset-bottom))] lg:pb-6">
                @if (session('status'))
                    <x-ui.alert variant="success" dismissible class="mb-5 shadow-2xs">
                        {{ session('status') }}
                    </x-ui.alert>
                @endif

                {{ $slot }}
            </main>
        </div>

        {{-- Navigasi bawah mobile & tablet --}}
        <nav aria-label="Navigasi bawah" class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden print:hidden">
            <div class="mx-auto grid h-16 max-w-2xl grid-flow-col auto-cols-fr items-stretch px-1 sm:px-3">
                @foreach ($bottomNavItems as $item)
                    <a
                        href="{{ $item['url'] }}"
                        @if ($item['is_active']) aria-current="page" @endif
                        wire:navigate
                        @class([
                            'my-1.5 mx-0.5 flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-xl px-1 text-center transition-colors',
                            'bg-brand-50 text-brand-700 font-semibold' => $item['is_active'],
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $item['is_active'],
                        ])
                    >
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="{{ $item['icon'] }}" />
                        </svg>
                        <span class="max-w-full truncate text-xs leading-tight">{{ $item['short_label'] }}</span>
                    </a>
                @endforeach

                <button
                    type="button"
                    id="sidebar-toggle"
                    @click="sidebarOpen = true"
                    aria-label="Buka menu lainnya"
                    aria-controls="sidebar"
                    :aria-expanded="sidebarOpen"
                    data-bottom-overflow-active="{{ $isOverflowActive ? 'true' : 'false' }}"
                    @class([
                        'my-1.5 mx-0.5 flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-xl px-1 text-center transition-colors',
                        'bg-brand-50 text-brand-700 font-semibold' => $isOverflowActive,
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $isOverflowActive,
                    ])
                >
                    <span class="relative inline-flex">
                        <x-lucide-menu class="size-5 shrink-0" aria-hidden="true" />
                        @if ($isOverflowActive)
                            <span class="absolute -top-0.5 -right-1.5 size-1.5 rounded-full bg-brand-600" aria-hidden="true"></span>
                        @endif
                    </span>
                    <span class="max-w-full truncate text-xs leading-tight">Lainnya</span>
                </button>
            </div>
        </nav>

        @livewireScripts
    </body>
</html>
