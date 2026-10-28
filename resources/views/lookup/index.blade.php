@if (auth()->check())
    <x-layouts::app title="Lacak Nota Fisik">
        <div class="max-w-3xl mx-auto py-2 space-y-6">
            @include('lookup._content')
        </div>
    </x-layouts::app>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Cek Transaksi — {{ config('app.name', 'CUCI.IN') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50/70 font-sans text-slate-800 antialiased flex flex-col justify-between selection:bg-brand-500 selection:text-white">
        {{-- Header / Navbar Publik --}}
        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md sticky top-0 z-30 print:hidden">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <a href="{{ route('transactions.lookup') }}" class="flex items-center gap-2.5 py-2">
                    <x-app-logo class="size-8 drop-shadow-2xs" />
                    <span class="text-lg font-bold tracking-tight text-brand-700">CUCI<span class="text-brand-500">.IN</span></span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <x-ui.button
                        :href="route('login')"
                        variant="secondary"
                        size="sm"
                    >
                        <x-lucide-log-in class="size-3.5 text-slate-500" />
                        <span>Masuk Sistem</span>
                    </x-ui.button>
                </div>
            </div>
        </header>

        {{-- Konten Utama Publik --}}
        <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-10 space-y-6 sm:space-y-8">
            {{-- Hero & Deskripsi Publik --}}
            <div class="text-center max-w-xl mx-auto space-y-2 print:hidden">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    Cek Status Cucian Anda
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed px-2">
                    Pantau progres pengerjaan pakaianmu secara langsung. Cukup masukkan nomor nota untuk melihat perkiraan selesai dan rincian pembayaran.
                </p>
            </div>

            @include('lookup._content')
        </main>

        {{-- Footer Publik --}}
        <footer class="border-t border-slate-200/80 bg-white py-6 text-center text-xs text-slate-500 print:hidden">
            <div class="max-w-4xl mx-auto px-4">
                &copy; {{ date('Y') }} CUCI.IN — Sistem Manajemen Operasional Laundry.
            </div>
        </footer>
    </body>
    </html>
@endif
