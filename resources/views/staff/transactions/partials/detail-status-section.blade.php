{{-- ============================================================
    UPDATE STATUS OPERASIONAL & TIMELINE
============================================================= --}}
@php
    $isTerminal = in_array($statusValue, ['Diambil', 'Dibatalkan']);
@endphp

<div class="space-y-6">
    {{-- Form Update Status Operasional --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">
                    Status Operasional Cucian
                </h2>

                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Perbarui status transaksi sesuai dengan proses pengerjaan laundry.
                </p>
            </div>

            {{-- Status Saat Ini --}}
            <div>
                <span class="text-xs text-slate-400">
                    Status saat ini
                </span>

                <div class="mt-1">
                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                        {{ $statusValue }}
                    </span>
                </div>
            </div>
        </div>

        @if ($isTerminal)
            {{-- Banner Final / Terminal --}}
            <div class="mt-6 rounded-xl border p-4 {{ $statusValue === 'Diambil' ? 'border-emerald-200 bg-emerald-50/60 text-emerald-900' : 'border-rose-200 bg-rose-50/60 text-rose-900' }}">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $statusValue === 'Diambil' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                        @if ($statusValue === 'Diambil')
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-xs font-bold">
                            {{ $statusValue === 'Diambil' ? 'Transaksi Telah Selesai & Cucian Diambil' : 'Transaksi Dibatalkan' }}
                        </h3>
                        <p class="mt-0.5 text-xs {{ $statusValue === 'Diambil' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $statusValue === 'Diambil' ? 'Cucian fisik telah diserahkan kepada pelanggan. Status transaksi ini telah ditutup permanen.' : 'Transaksi ini telah dibatalkan dan tidak dapat diproses lebih lanjut.' }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            {{-- Form Update dengan Alpine.js Guard --}}
            <form
                x-data="{
                    currentStatus: '{{ $statusValue }}',
                    selectedStatus: '{{ old('status', $statusValue) }}',
                    notes: '{{ old('notes', '') }}',
                    rank(s) {
                        return { 'Diterima': 1, 'Diproses': 2, 'Selesai': 3, 'Diambil': 4, 'Dibatalkan': 0 }[s] ?? 0;
                    },
                    isRollback() {
                        if (this.selectedStatus === 'Dibatalkan' || this.selectedStatus === this.currentStatus) return false;
                        return this.rank(this.selectedStatus) < this.rank(this.currentStatus);
                    },
                    needsNotes() {
                        return this.isRollback() || this.selectedStatus === 'Dibatalkan';
                    }
                }"
                method="POST"
                action="{{ route("{$currentRoutePrefix}.transactions.status", $transaction) }}"
                class="mt-6 space-y-4"
            >
                @csrf
                @method('PATCH')

                {{-- Error validation alerts --}}
                @if ($errors->has('status') || $errors->has('notes'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3.5 text-xs text-rose-700">
                        <p class="font-semibold">{{ $errors->first('status') ?: $errors->first('notes') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    {{-- Select Status --}}
                    <div class="md:col-span-2">
                        <label
                            for="status"
                            class="mb-1.5 block text-xs font-medium text-slate-600"
                        >
                            Ubah Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            x-model="selectedStatus"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                            <option value="Diterima" @selected($statusValue === 'Diterima')>
                                1. Diterima (Antrean Masuk)
                            </option>
                            <option value="Diproses" @selected($statusValue === 'Diproses')>
                                2. Diproses (Dicuci & Disetrika)
                            </option>
                            <option value="Selesai" @selected($statusValue === 'Selesai')>
                                3. Selesai (Siap Diambil)
                            </option>
                            <option value="Diambil" @selected($statusValue === 'Diambil')>
                                4. Diambil (Diserahkan ke Pelanggan)
                            </option>
                            <option value="Dibatalkan" @selected($statusValue === 'Dibatalkan')>
                                &times; Dibatalkan (Batalkan Transaksi)
                            </option>
                        </select>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 cursor-pointer"
                        >
                            Perbarui Status
                        </button>
                    </div>
                </div>

                {{-- Dynamic Warning & Mandatory Reason Field when Rolling Back or Cancelling --}}
                <div
                    x-show="needsNotes()"
                    x-cloak
                    x-transition:enter="ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-xl border p-4 transition-all"
                    :class="selectedStatus === 'Dibatalkan' ? 'border-rose-200 bg-rose-50/60' : 'border-amber-200 bg-amber-50/60'"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-7 shrink-0 items-center justify-center rounded-lg text-xs"
                            :class="selectedStatus === 'Dibatalkan' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <div class="flex-1">
                            <p
                                class="text-xs font-semibold"
                                :class="selectedStatus === 'Dibatalkan' ? 'text-rose-900' : 'text-amber-900'"
                                x-text="selectedStatus === 'Dibatalkan' ? 'Perhatian: Transaksi akan dibatalkan.' : 'Perhatian: Anda memundurkan status cucian dari ' + currentStatus + ' ke ' + selectedStatus + '.'"
                            ></p>

                            <p
                                class="mt-0.5 text-[11px]"
                                :class="selectedStatus === 'Dibatalkan' ? 'text-rose-700' : 'text-amber-700'"
                            >
                                Tuliskan alasan koreksi/pembatalan di bawah ini agar tercatat di audit trail sistem.
                            </p>

                            <div class="mt-3">
                                <label
                                    for="notes"
                                    class="mb-1 block text-xs font-semibold"
                                    :class="selectedStatus === 'Dibatalkan' ? 'text-rose-800' : 'text-amber-800'"
                                >
                                    Alasan Koreksi Status <span class="text-rose-600">*</span>
                                </label>

                                <textarea
                                    name="notes"
                                    id="notes"
                                    rows="2"
                                    x-model="notes"
                                    :required="needsNotes()"
                                    placeholder="Contoh: Salah klik status oleh kasir / pakaian perlu dicuci ulang karena noda belum tuntas"
                                    class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-xs text-slate-800 shadow-2xs outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>

    {{-- Timeline / Stepper Status --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-slate-900">
                Alur Status Cucian
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Tahapan proses transaksi laundry.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            {{-- Diterima --}}
            <div
                class="rounded-xl border p-4
                {{ in_array($statusValue, ['Diterima', 'Diproses', 'Selesai', 'Diambil'])
                    ? 'border-brand-200 bg-brand-50'
                    : 'border-slate-200 bg-slate-50' }}"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                        {{ in_array($statusValue, ['Diterima', 'Diproses', 'Selesai', 'Diambil'])
                            ? 'bg-brand-600 text-white'
                            : 'bg-white text-slate-500' }}"
                    >
                        1
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-900">
                            Diterima
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Cucian masuk
                        </p>
                    </div>
                </div>
            </div>

            {{-- Diproses --}}
            <div
                class="rounded-xl border p-4
                {{ in_array($statusValue, ['Diproses', 'Selesai', 'Diambil'])
                    ? 'border-brand-200 bg-brand-50'
                    : 'border-slate-200 bg-slate-50' }}"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                        {{ in_array($statusValue, ['Diproses', 'Selesai', 'Diambil'])
                            ? 'bg-brand-600 text-white'
                            : 'bg-white text-slate-500' }}"
                    >
                        2
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-900">
                            Diproses
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Cucian dikerjakan
                        </p>
                    </div>
                </div>
            </div>

            {{-- Selesai --}}
            <div
                class="rounded-xl border p-4
                {{ in_array($statusValue, ['Selesai', 'Diambil'])
                    ? 'border-brand-200 bg-brand-50'
                    : 'border-slate-200 bg-slate-50' }}"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                        {{ in_array($statusValue, ['Selesai', 'Diambil'])
                            ? 'bg-brand-600 text-white'
                            : 'bg-white text-slate-500' }}"
                    >
                        3
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-900">
                            Selesai
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Siap diambil
                        </p>
                    </div>
                </div>
            </div>

            {{-- Diambil --}}
            <div
                class="rounded-xl border p-4
                {{ $statusValue === 'Diambil'
                    ? 'border-brand-200 bg-brand-50'
                    : 'border-slate-200 bg-slate-50' }}"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                        {{ $statusValue === 'Diambil'
                            ? 'bg-brand-600 text-white'
                            : 'bg-white text-slate-500' }}"
                    >
                        4
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-900">
                            Diambil
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Transaksi selesai
                        </p>
                    </div>
                </div>
            </div>

            {{-- Dibatalkan --}}
            <div
                class="rounded-xl border p-4
                {{ $statusValue === 'Dibatalkan'
                    ? 'border-red-200 bg-red-50'
                    : 'border-slate-200 bg-slate-50' }}"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold
                        {{ $statusValue === 'Dibatalkan'
                            ? 'bg-red-600 text-white'
                            : 'bg-white text-slate-500' }}"
                    >
                        !
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-900">
                            Dibatalkan
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Transaksi dibatalkan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
