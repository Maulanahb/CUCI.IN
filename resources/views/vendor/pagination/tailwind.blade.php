@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">
            Menampilkan
            <span class="font-semibold text-slate-800">{{ $paginator->firstItem() }}</span>
            &ndash;
            <span class="font-semibold text-slate-800">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="flex flex-wrap items-center gap-1.5">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="pointer-events-none inline-flex h-8 items-center justify-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-400">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-8 cursor-pointer items-center justify-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $windowStart = max(1, $currentPage - 2);
                $windowEnd = min($lastPage, $currentPage + 2);
            @endphp

            @if ($windowStart > 1)
                <a
                    href="{{ $paginator->url(1) }}"
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-lg text-xs font-semibold text-slate-600 transition-colors hover:bg-brand-50 hover:text-brand-700"
                    aria-label="Halaman 1"
                >
                    1
                </a>
                @if ($windowStart > 2)
                    <span class="inline-flex size-8 items-center justify-center text-xs text-slate-400" aria-hidden="true">&hellip;</span>
                @endif
            @endif

            @foreach ($paginator->getUrlRange($windowStart, $windowEnd) as $page => $url)
                @if ($page === $currentPage)
                    <span aria-current="page" class="inline-flex size-8 items-center justify-center rounded-lg bg-brand-700 text-xs font-bold text-white shadow-xs">
                        {{ $page }}
                    </span>
                @else
                    <a
                        href="{{ $url }}"
                        class="inline-flex size-8 cursor-pointer items-center justify-center rounded-lg text-xs font-semibold text-slate-600 transition-colors hover:bg-brand-50 hover:text-brand-700"
                        aria-label="Halaman {{ $page }}"
                    >
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            @if ($windowEnd < $lastPage)
                @if ($windowEnd < $lastPage - 1)
                    <span class="inline-flex size-8 items-center justify-center text-xs text-slate-400" aria-hidden="true">&hellip;</span>
                @endif
                <a
                    href="{{ $paginator->url($lastPage) }}"
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-lg text-xs font-semibold text-slate-600 transition-colors hover:bg-brand-50 hover:text-brand-700"
                    aria-label="Halaman {{ $lastPage }}"
                >
                    {{ $lastPage }}
                </a>
            @endif

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-8 cursor-pointer items-center justify-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900">
                    <span>Berikutnya</span>
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" class="pointer-events-none inline-flex h-8 items-center justify-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-400">
                    <span>Berikutnya</span>
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
