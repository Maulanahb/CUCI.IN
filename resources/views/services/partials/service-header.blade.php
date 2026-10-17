{{-- ============================================================
    HEADER & ACTIONS KATALOG LAYANAN
============================================================= --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                Katalog Layanan Laundry
            </h1>
            @if ($isAdmin)
                <span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-[11px] font-semibold text-brand-800">
                    Mode Administrator
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">
                    Katalog Kasir / Staff
                </span>
            @endif
        </div>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
            Kelola tarif pencucian, durasi estimasi pengerjaan, dan ketersediaan layanan operasional CUCI.IN.
        </p>
    </div>

    <div class="flex items-center gap-2">
        @if ($isAdmin)
            <button
                type="button"
                id="btn-open-create-service"
                @click="openCreateModal()"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 active:scale-98 cursor-pointer sm:text-sm"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Layanan</span>
            </button>
        @endif

        <a
            href="{{ route("{$currentRoutePrefix}.transactions.create") }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-medium text-slate-700 shadow-2xs transition hover:bg-slate-50 sm:text-sm"
        >
            <svg class="size-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Kasir POS</span>
        </a>
    </div>
</div>
