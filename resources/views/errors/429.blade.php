@php
    $retryAfter = (isset($exception) && method_exists($exception, 'getHeaders'))
        ? ($exception->getHeaders()['Retry-After'] ?? null)
        : null;
@endphp

<x-layouts::guest title="429 — Terlalu Banyak Percobaan">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-xs sm:p-8">
        {{-- Header & Logo Brand --}}
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 transition hover:opacity-90">
                <x-app-logo class="size-10 drop-shadow-xs" />
                <span class="text-xl font-bold tracking-tight text-brand-700">CUCI<span class="text-brand-500">.IN</span></span>
            </a>

            <div class="mt-6 flex size-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-200">
                <x-lucide-clock class="size-7" />
            </div>

            <span class="mt-4 inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                HTTP 429 &middot; Rate Limit Exceeded
            </span>

            <h1 class="mt-3 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Terlalu Banyak Permintaan
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                Terlalu banyak percobaan, coba lagi beberapa saat.
            </p>

            @if ($retryAfter)
                <div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50/60 px-4 py-3 text-xs font-medium text-amber-800">
                    Silakan tunggu sekitar <span class="font-bold text-amber-900">{{ $retryAfter }}</span> detik sebelum mencoba kembali.
                </div>
            @endif

            <div class="mt-6 flex w-full flex-col gap-2.5 sm:flex-row sm:justify-center">
                <a
                    href="{{ url()->previous() ?: route('home') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700"
                >
                    <x-lucide-refresh-cw class="size-3.5" />
                    <span>Coba Muat Ulang</span>
                </a>

                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50"
                >
                    <x-lucide-home class="size-3.5" />
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts::guest>

