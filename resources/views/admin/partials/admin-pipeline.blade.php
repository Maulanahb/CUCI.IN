{{-- ============================================================
    CONNECTED WASH-CYCLE PIPELINE (ADMIN OPERATIONAL STREAM)
    Jalur alur kerja pengerjaan laundry bersambung (Diterima -> Diproses -> Selesai -> Diambil)
============================================================= --}}
<div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-6">
    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3">
        <div>
            <h2 class="text-sm font-bold text-slate-900 sm:text-base">Alur Pengerjaan Laundry Aktif</h2>
            <p class="text-xs text-slate-500">Distribusi real-time cucian yang bergerak dari penerimaan hingga penyerahan.</p>
        </div>
        <a
            href="{{ route('admin.transactions.index') }}"
            class="text-xs font-semibold text-brand-600 hover:text-brand-700 cursor-pointer"
        >
            Lihat Semua Transaksi &rarr;
        </a>
    </div>

    {{-- Continuous Stepper Container --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        {{-- Tahap 1: Diterima --}}
        <a
            href="{{ route('admin.transactions.index', ['status' => 'Diterima']) }}"
            class="group relative flex flex-col justify-between rounded-xl border border-sky-100 bg-sky-50/40 p-4 transition hover:border-sky-300 hover:bg-sky-50/80 cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="flex size-5 items-center justify-center rounded-full bg-sky-600 text-[10px] font-bold text-white">1</span>
                    <span class="text-xs font-bold text-sky-950">Diterima</span>
                </div>
                <span class="size-2 rounded-full bg-sky-500 animate-pulse" title="Antrean baru"></span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-sky-900 tabular-nums">
                    {{ $statusCounts['diterima'] ?? 0 }}
                </span>
                <span class="text-xs text-sky-700 font-medium">Nota Baru &rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-sky-800/80">Antre timbang & cuci</p>
        </a>

        {{-- Tahap 2: Diproses --}}
        <a
            href="{{ route('admin.transactions.index', ['status' => 'Diproses']) }}"
            class="group relative flex flex-col justify-between rounded-xl border border-amber-100 bg-amber-50/40 p-4 transition hover:border-amber-300 hover:bg-amber-50/80 cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="flex size-5 items-center justify-center rounded-full bg-amber-600 text-[10px] font-bold text-white">2</span>
                    <span class="text-xs font-bold text-amber-950">Diproses</span>
                </div>
                <span class="size-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-amber-900 tabular-nums">
                    {{ $statusCounts['diproses'] ?? 0 }}
                </span>
                <span class="text-xs text-amber-700 font-medium">Workshop &rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-amber-800/80">Cuci, kering & setrika</p>
        </a>

        {{-- Tahap 3: Selesai --}}
        <a
            href="{{ route('admin.transactions.index', ['status' => 'Selesai']) }}"
            class="group relative flex flex-col justify-between rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/80 cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="flex size-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white">3</span>
                    <span class="text-xs font-bold text-emerald-950">Selesai</span>
                </div>
                <span class="size-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-emerald-900 tabular-nums">
                    {{ $statusCounts['selesai'] ?? 0 }}
                </span>
                <span class="text-xs text-emerald-700 font-medium">Rak Siap &rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-emerald-800/80">Siap diambil pelanggan</p>
        </a>

        {{-- Tahap 4: Diambil --}}
        <a
            href="{{ route('admin.transactions.index', ['status' => 'Diambil']) }}"
            class="group relative flex flex-col justify-between rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition hover:border-slate-300 hover:bg-slate-100/70 cursor-pointer"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="flex size-5 items-center justify-center rounded-full bg-slate-600 text-[10px] font-bold text-white">4</span>
                    <span class="text-xs font-bold text-slate-900">Diambil</span>
                </div>
                <span class="size-2 rounded-full bg-slate-400"></span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-slate-900 tabular-nums">
                    {{ $statusCounts['diambil'] ?? 0 }}
                </span>
                <span class="text-xs text-slate-600 font-medium">Arsip &rarr;</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">Diserahkan & selesai</p>
        </a>
    </div>
</div>
