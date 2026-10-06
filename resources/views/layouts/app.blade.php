@php
    /**
     * Menu sidebar. Item hanya tampil jika route-nya sudah didaftarkan,
     * sehingga tiap PIC cukup menambah route dengan nama yang disepakati.
     *
     * @var array<int, array{label: string, route: string, active: string, icon: string}> $navigation
     */
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
        ['label' => 'Transaksi', 'route' => 'transactions.index', 'active' => 'transactions.*', 'icon' => 'M9 5h10M9 12h10M9 19h10M4 5h.01M4 12h.01M4 19h.01'],
        ['label' => 'Customer', 'route' => 'customers.index', 'active' => 'customers.*', 'icon' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.87M16 2.13a4 4 0 010 7.75'],
        ['label' => 'Layanan', 'route' => 'services.index', 'active' => 'services.*', 'icon' => 'M20 7H4a1 1 0 00-1 1v11a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
        {{-- Overlay mobile --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="flex h-16 items-center gap-2 border-b border-slate-100 px-5">
                <x-app-logo class="size-8" />
                <span class="text-lg font-semibold tracking-tight text-brand-700">CUCI<span class="text-brand-400">.IN</span></span>
            </div>

            <nav class="flex flex-1 flex-col gap-1 overflow-y-auto p-3">
                @foreach ($navigation as $item)
                    @if (Route::has($item['route']))
                        @php($isActive = request()->routeIs($item['active']))
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                'bg-brand-50 text-brand-700' => $isActive,
                                'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $isActive,
                            ])
                        >
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
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
                            <button type="submit" id="logout-button" class="mt-1 w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600">
                                Keluar
                            </button>
                        </form>
                    @endif
                </div>
            @endauth
        </aside>

        {{-- Konten utama --}}
        <div class="flex min-h-screen flex-col lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6">
                <button type="button" id="sidebar-toggle" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <h1 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $title ?? config('app.name') }}</h1>

                @isset($actions)
                    <div class="ml-auto flex items-center gap-2">{{ $actions }}</div>
                @endisset
            </header>

            <main class="flex-1 p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
