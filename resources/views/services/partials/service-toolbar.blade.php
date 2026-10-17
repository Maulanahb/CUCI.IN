{{-- ============================================================
    FILTER & SEARCH TOOLBAR
============================================================= --}}
<div class="flex flex-col gap-3 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs sm:flex-row sm:items-center sm:justify-between">
    {{-- Tab Filter Status --}}
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 sm:pb-0">
        <a
            href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
            @class([
                'rounded-xl px-3 py-1.5 text-xs font-semibold transition select-none',
                'bg-brand-600 text-white shadow-2xs' => $statusFilter === 'all',
                'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900' => $statusFilter !== 'all',
            ])
        >
            Semua ({{ $stats['total'] }})
        </a>

        <a
            href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
            @class([
                'rounded-xl px-3 py-1.5 text-xs font-semibold transition select-none',
                'bg-emerald-600 text-white shadow-2xs' => $statusFilter === 'active',
                'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900' => $statusFilter !== 'active',
            ])
        >
            Aktif ({{ $stats['active'] }})
        </a>

        <a
            href="{{ route("{$currentRoutePrefix}.services.index", array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
            @class([
                'rounded-xl px-3 py-1.5 text-xs font-semibold transition select-none',
                'bg-slate-700 text-white shadow-2xs' => $statusFilter === 'inactive',
                'bg-slate-100 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900' => $statusFilter !== 'inactive',
            ])
        >
            Nonaktif ({{ $stats['inactive'] }})
        </a>
    </div>

    {{-- Form Pencarian --}}
    <form method="GET" action="{{ route("{$currentRoutePrefix}.services.index") }}" class="relative w-full sm:w-72">
        @if ($statusFilter !== 'all')
            <input type="hidden" name="status" value="{{ $statusFilter }}">
        @endif
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input
            id="service-search-input"
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Cari nama layanan..."
            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-9 text-xs text-slate-800 transition placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100 sm:text-sm"
        />
        @if ($search !== '')
            <a
                href="{{ route("{$currentRoutePrefix}.services.index", ['status' => $statusFilter]) }}"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                title="Hapus pencarian"
                aria-label="Hapus pencarian"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        @endif
    </form>
</div>
