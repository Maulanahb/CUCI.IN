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
    <body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center gap-8 px-4 py-12">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-2xl font-semibold tracking-tight text-brand-700">
                <x-app-logo class="size-9" />
                <span>CUCI<span class="text-brand-400">.IN</span></span>
            </a>

            <main class="w-full max-w-md">
                {{ $slot }}
            </main>

            <p class="text-xs text-slate-400">&copy; {{ date('Y') }} CUCI.IN — Sistem Manajemen Operasional Laundry</p>
        </div>

        @livewireScripts
    </body>
</html>
