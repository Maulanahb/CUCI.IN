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
    <body class="min-h-screen bg-[#f8fafc] font-sans text-slate-800 antialiased flex flex-col justify-between py-10 px-4">
        <div></div>

        <main class="w-full max-w-[440px] mx-auto">
            {{ $slot }}
        </main>

        <footer class="text-center text-xs text-slate-400 mt-8">
            &copy; {{ date('Y') }} CUCI.IN
        </footer>

        @livewireScripts
    </body>
</html>
