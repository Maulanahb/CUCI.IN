<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name', 'CUCI.IN') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="relative flex min-h-screen flex-col justify-between overflow-x-hidden bg-gradient-to-br from-brand-50/60 via-slate-50 to-sky-50/40 px-4 py-8 font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white sm:py-12">
        {{-- Decorative Ambient Glow Circles --}}
        <div class="pointer-events-none absolute -top-24 left-1/2 -z-10 h-96 w-96 -translate-x-1/2 rounded-full bg-brand-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 right-0 -z-10 h-80 w-80 rounded-full bg-sky-300/15 blur-3xl" aria-hidden="true"></div>

        {{-- Top Navigation: Kembali ke Beranda --}}
        <div class="mx-auto mb-4 flex w-full {{ $maxWidth ?? 'max-w-md' }} items-center justify-between">
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white/80 px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-2xs backdrop-blur-xs transition hover:border-brand-300 hover:bg-white hover:text-brand-700"
            >
                <x-lucide-arrow-left class="size-3.5" />
                <span>Kembali ke Beranda</span>
            </a>
            <span class="text-2xs font-semibold uppercase tracking-wider text-slate-400">CUCI.IN Portal</span>
        </div>

        {{-- Main Container Content --}}
        <main class="mx-auto w-full {{ $maxWidth ?? 'max-w-md' }}">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="mt-8 text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} CUCI.IN &middot; Sistem Manajemen Operasional Laundry</p>
        </footer>

        @livewireScripts
    </body>
</html>
