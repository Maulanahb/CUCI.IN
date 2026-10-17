{{-- ============================================================
    PIPELINE ANTREAN & STATUS OPERASIONAL CUCIAN
============================================================= --}}
<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6">
    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
        <div>
            <h3 class="text-sm font-bold text-slate-900 sm:text-base">Antrean & Status Operasional Cucian</h3>
            <p class="text-xs text-slate-500">Pantau proses cucian laundry dan perbarui status pengerjaan.</p>
        </div>
        <a href="{{ route('staff.transactions.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 cursor-pointer">
            Kelola Seluruh Cucian &rarr;
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Diterima --}}
        <a
            href="{{ route('staff.transactions.index', ['status' => 'Diterima']) }}"
            class="group block rounded-xl border border-sky-100 bg-sky-50/50 p-4 transition hover:border-sky-300 hover:bg-sky-50 hover:shadow-xs active:scale-[0.99] cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-sky-800 transition group-hover:text-sky-900">Diterima</span>
                <span class="inline-flex size-2 rounded-full bg-sky-500 animate-pulse"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <p class="text-2xl font-extrabold text-sky-900 tabular-nums">{{ $statusCounts['diterima'] ?? 0 }}</p>
                <span class="text-xs font-semibold text-sky-600 opacity-0 transition-all duration-150 group-hover:opacity-100 group-hover:translate-x-0.5">&rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-sky-700 font-medium">Antrean masuk baru</p>
        </a>

        {{-- Diproses --}}
        <a
            href="{{ route('staff.transactions.index', ['status' => 'Diproses']) }}"
            class="group block rounded-xl border border-amber-100 bg-amber-50/50 p-4 transition hover:border-amber-300 hover:bg-amber-50 hover:shadow-xs active:scale-[0.99] cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-800 transition group-hover:text-amber-900">Diproses</span>
                <span class="inline-flex size-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <p class="text-2xl font-extrabold text-amber-900 tabular-nums">{{ $statusCounts['diproses'] ?? 0 }}</p>
                <span class="text-xs font-semibold text-amber-600 opacity-0 transition-all duration-150 group-hover:opacity-100 group-hover:translate-x-0.5">&rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-amber-700 font-medium">Sedang dicuci & disetrika</p>
        </a>

        {{-- Selesai --}}
        <a
            href="{{ route('staff.transactions.index', ['status' => 'Selesai']) }}"
            class="group block rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-xs active:scale-[0.99] cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-800 transition group-hover:text-emerald-900">Selesai</span>
                <span class="inline-flex size-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <p class="text-2xl font-extrabold text-emerald-900 tabular-nums">{{ $statusCounts['selesai'] ?? 0 }}</p>
                <span class="text-xs font-semibold text-emerald-600 opacity-0 transition-all duration-150 group-hover:opacity-100 group-hover:translate-x-0.5">&rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-emerald-700 font-medium">Siap diambil pelanggan</p>
        </a>

        {{-- Diambil --}}
        <a
            href="{{ route('staff.transactions.index', ['status' => 'Diambil']) }}"
            class="group block rounded-xl border border-slate-100 bg-slate-50/80 p-4 transition hover:border-slate-300 hover:bg-slate-100/80 hover:shadow-xs active:scale-[0.99] cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-700 transition group-hover:text-slate-900">Diambil</span>
                <span class="inline-flex size-2 rounded-full bg-slate-400"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ $statusCounts['diambil'] ?? 0 }}</p>
                <span class="text-xs font-semibold text-slate-600 opacity-0 transition-all duration-150 group-hover:opacity-100 group-hover:translate-x-0.5">&rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500 font-medium">Selesai diserahkan</p>
        </a>
    </div>
</div>
