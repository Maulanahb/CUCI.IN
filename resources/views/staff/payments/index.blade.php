@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $pendingCount = $counts['Menunggu Verifikasi'] ?? 0;
    $tabs = [
        'Menunggu Verifikasi' => ['label' => 'Menunggu', 'count' => $pendingCount],
        'Terverifikasi' => ['label' => 'Terverifikasi', 'count' => $counts['Terverifikasi'] ?? 0],
        'Ditolak' => ['label' => 'Ditolak', 'count' => $counts['Ditolak'] ?? 0],
        'Semua' => ['label' => 'Semua', 'count' => array_sum($counts)],
    ];

    $paymentsPayloadMap = $payments->mapWithKeys(function ($payment) use ($currentRoutePrefix) {
        $trx = $payment->transaction;
        $rem = (float) ($trx?->remaining_amount ?? 0);
        $amt = (float) $payment->amount;
        $isPending = $payment->isPendingVerification();
        $exceeds = $isPending && $amt > $rem;

        return [
            $payment->id => [
                'id' => $payment->id,
                'transaction_code' => $trx?->transaction_code ?? '-',
                'customer_name' => $trx?->customer?->name ?? 'Pelanggan Umum',
                'customer_phone' => $trx?->customer?->phone ?? '-',
                'method' => $payment->method ?? 'Transfer',
                'payment_type' => $payment->payment_type ?? 'Pelunasan',
                'amount' => $amt,
                'amount_formatted' => 'Rp ' . number_format($amt, 0, ',', '.'),
                'total_amount_formatted' => 'Rp ' . number_format((float) ($trx?->total_amount ?? 0), 0, ',', '.'),
                'remaining_formatted' => 'Rp ' . number_format($rem, 0, ',', '.'),
                'exceeds_remaining' => $exceeds,
                'verification_status' => $payment->verification_status,
                'verification_notes' => $payment->verification_notes ?? '',
                'is_pending' => $isPending,
                'paid_at_formatted' => $payment->paid_at?->translatedFormat('d M Y, H:i') . ' WIB',
                'proof_url' => $payment->proof_file ? route("{$currentRoutePrefix}.payments.proof", $payment) : null,
                'verify_url' => route("{$currentRoutePrefix}.payments.verify", $payment),
                'show_url' => route("{$currentRoutePrefix}.payments.show", $payment),
                'transaction_show_url' => $trx ? route("{$currentRoutePrefix}.transactions.show", $trx) : null,
            ]
        ];
    })->all();
@endphp

<x-layouts::app title="Verifikasi Pembayaran">
    <div
        class="space-y-7"
        x-data="{
            paymentsMap: {{ Js::from($paymentsPayloadMap) }},
            selectedPayment: null,
            sheetOpen: false,
            showReject: false,
            rejectionNotes: '',
            isSubmitting: false,

            openPaymentSheet(id) {
                this.selectedPayment = this.paymentsMap[id] || null;
                if (!this.selectedPayment) return;
                this.showReject = false;
                this.rejectionNotes = '';
                this.sheetOpen = true;
            },
            closePaymentSheet() {
                this.sheetOpen = false;
            }
        }"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Verifikasi pembayaran</h1>
                <p class="mt-2 text-sm text-slate-600">Cocokkan bukti transfer dan QRIS sebelum menyetujui pembayaran.</p>
            </div>

            @if ($pendingCount > 0)
                <x-ui.badge tone="warning" dot pulse>
                    {{ $pendingCount }} bukti menunggu verifikasi
                </x-ui.badge>
            @endif
        </div>

        @if (session('success'))
            <x-ui.alert variant="success" dismissible>
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <div class="rounded-2xl border border-slate-200/90 bg-white overflow-hidden">
            <div class="border-b border-slate-100 p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-slate-900">Antrean Bukti Pembayaran</h2>
                        <p class="text-sm text-slate-500">Bukti yang paling lama menunggu ditampilkan paling atas.</p>
                    </div>

                    <form method="GET" action="{{ route("{$currentRoutePrefix}.payments.index") }}" class="w-full sm:w-72">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <x-lucide-search class="size-4 text-slate-400" />
                            </div>
                            <input
                                type="text"
                                name="search"
                                aria-label="Cari kode nota atau nama pelanggan"
                                value="{{ $search }}"
                                placeholder="Cari kode nota atau nama pelanggan..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 min-h-11 py-2.5 pr-9 pl-9 text-base sm:text-sm text-slate-900 transition placeholder:text-slate-500 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            >
                            @if ($search !== '')
                                <a
                                    href="{{ route("{$currentRoutePrefix}.payments.index", ['status' => $statusFilter]) }}"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition"
                                    title="Hapus pencarian"
                                >
                                    <x-lucide-x class="size-4" />
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <x-ui.tabs variant="segmented" class="mt-4">
                    @foreach ($tabs as $value => $tab)
                        <x-ui.tab
                            :href="route($currentRoutePrefix . '.payments.index', array_filter(['status' => $value, 'search' => $search]))"
                            :active="$statusFilter === $value"
                            :badge="$tab['count']"
                        >
                            {{ $tab['label'] }}
                        </x-ui.tab>
                    @endforeach
                </x-ui.tabs>
            </div>

            @if ($payments->isNotEmpty())
                {{-- Tabel (desktop) --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-slate-50/80 text-xs uppercase tracking-wider font-bold text-slate-500 border-b border-slate-100">
                            <tr>
                                <th scope="col" class="px-4 py-3">Diunggah</th>
                                <th scope="col" class="px-4 py-3">Nota &amp; Pelanggan</th>
                                <th scope="col" class="px-4 py-3">Metode</th>
                                <th scope="col" class="px-4 py-3 text-right">Nominal</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($payments as $payment)
                                <tr class="hover:bg-slate-50/60 transition-colors cursor-pointer" @click="openPaymentSheet({{ $payment->id }})">
                                    <td class="px-4 py-4 tabular-nums text-slate-600 whitespace-nowrap">
                                        {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-bold font-mono text-brand-700">{{ $payment->transaction?->transaction_code }}</p>
                                        <p class="text-slate-600">{{ $payment->transaction?->customer?->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $payment->method }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-900 whitespace-nowrap">
                                        <x-ui.money :value="$payment->amount" />
                                        <span class="block text-xs font-normal text-slate-500">{{ $payment->payment_type }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-ui.verification-badge :status="$payment->verification_status" size="sm" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <x-ui.button
                                            :href="route($currentRoutePrefix . '.payments.show', $payment)"
                                            :variant="$payment->isPendingVerification() ? 'primary' : 'soft'"
                                            size="row"
                                            @click.stop.prevent="openPaymentSheet({{ $payment->id }})"
                                            class="cursor-pointer"
                                        >
                                            <x-lucide-eye class="size-3.5" />
                                            <span>{{ $payment->isPendingVerification() ? 'Tinjau' : 'Detail' }}</span>
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Kartu (mobile) --}}
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($payments as $payment)
                        <a href="{{ route("{$currentRoutePrefix}.payments.show", $payment) }}" @click.prevent="openPaymentSheet({{ $payment->id }})" class="block p-4 transition hover:bg-slate-50 cursor-pointer">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold font-mono text-brand-700">{{ $payment->transaction?->transaction_code }}</p>
                                    <p class="truncate text-sm text-slate-600">{{ $payment->transaction?->customer?->name ?? '-' }}</p>
                                </div>
                                <x-ui.verification-badge :status="$payment->verification_status" size="sm" class="shrink-0" />
                            </div>
                            <div class="mt-2 flex items-end justify-between gap-3">
                                <p class="text-sm text-slate-500 font-mono tabular-nums">
                                    {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }} &middot; {{ $payment->method }}
                                </p>
                                <p class="text-sm font-semibold tabular-nums text-slate-900">
                                    <x-ui.money :value="$payment->amount" />
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="border-t border-slate-100 px-4 py-3 sm:px-6">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="p-8 text-center sm:p-12">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                        <x-lucide-shield-check class="size-6" />
                    </div>
                    <h3 class="mt-3 text-sm font-bold text-slate-900">
                        {{ $statusFilter === 'Menunggu Verifikasi' ? 'Tidak ada bukti yang menunggu verifikasi' : 'Data pembayaran tidak ditemukan' }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Bukti transfer atau QRIS baru dari pelanggan akan muncul di sini.
                    </p>
                </div>
            @endif
        </div>

        {{-- Slide-Over Sheet Drawer: Verifikasi Pembayaran --}}
        <x-ui.sheet show="sheetOpen" maxWidth="xl">
            <x-slot:header>
                <div class="flex items-center justify-between gap-4 w-full pr-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-base font-bold tracking-tight text-slate-900" x-text="selectedPayment?.transaction_code"></span>
                            <template x-if="selectedPayment?.verification_status === 'Menunggu Verifikasi'">
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    Menunggu Verifikasi
                                </span>
                            </template>
                            <template x-if="selectedPayment?.verification_status === 'Terverifikasi'">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    Terverifikasi
                                </span>
                            </template>
                            <template x-if="selectedPayment?.verification_status === 'Ditolak'">
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                    Ditolak
                                </span>
                            </template>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500" x-text="'Diunggah: ' + (selectedPayment?.paid_at_formatted || '-')"></p>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="font-mono text-base font-bold text-slate-900" x-text="selectedPayment?.amount_formatted"></span>
                        <span class="block text-xs text-slate-500" x-text="selectedPayment?.method + ' (' + selectedPayment?.payment_type + ')'"></span>
                    </div>
                </div>
            </x-slot:header>

            <div class="space-y-6 text-sm">
                {{-- 1. Pratinjau Foto Bukti Pembayaran --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4.5 space-y-3.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Bukti Transfer / Struk QRIS</span>
                        <template x-if="selectedPayment?.proof_url">
                            <a
                                :href="selectedPayment?.proof_url"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800"
                            >
                                <x-lucide-zoom-in class="size-3.5" />
                                <span>Buka Ukuran Penuh</span>
                            </a>
                        </template>
                    </div>

                    <template x-if="selectedPayment?.proof_url">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                            <a :href="selectedPayment?.proof_url" target="_blank" rel="noopener">
                                <img
                                    :src="selectedPayment?.proof_url"
                                    alt="Foto Bukti Pembayaran"
                                    class="max-h-80 w-full object-contain mx-auto"
                                />
                            </a>
                        </div>
                    </template>
                    <template x-if="!selectedPayment?.proof_url">
                        <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-500">
                            Pembayaran ini tidak memiliki berkas bukti digital.
                        </div>
                    </template>
                </div>

                {{-- 2. Ringkasan Tagihan & Pelanggan --}}
                <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4 space-y-3 shadow-2xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block">Rincian Finansial Nota</span>

                    <div class="grid grid-cols-2 gap-2.5 text-center text-xs">
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80">
                            <span class="text-slate-400 block font-medium">Total Tagihan</span>
                            <span class="font-mono font-bold text-slate-900 mt-0.5 block text-sm" x-text="selectedPayment?.total_amount_formatted"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80">
                            <span class="text-slate-400 block font-medium">Sisa Tagihan</span>
                            <span class="font-mono font-bold text-amber-700 mt-0.5 block text-sm" x-text="selectedPayment?.remaining_formatted"></span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200/80 pt-2.5 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-500">Nama Pelanggan:</span>
                            <span class="font-semibold text-slate-900 ml-1" x-text="selectedPayment?.customer_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-500">WhatsApp:</span>
                            <span class="font-mono font-semibold text-slate-900 ml-1" x-text="selectedPayment?.customer_phone"></span>
                        </div>
                    </div>

                    <template x-if="selectedPayment?.verification_status === 'Ditolak' && selectedPayment?.verification_notes">
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                            <span class="font-bold">Alasan Penolakan:</span>
                            <p class="mt-0.5" x-text="selectedPayment?.verification_notes"></p>
                        </div>
                    </template>
                </div>

                {{-- 3. Keputusan Verifikasi --}}
                <template x-if="selectedPayment?.is_pending">
                    <div class="rounded-2xl border border-brand-200/80 bg-brand-50/30 p-4 space-y-3.5 shadow-2xs">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 tracking-tight">Keputusan Verifikasi Kasir</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Cocokkan nominal dan mutasi rekening/QRIS sebelum menyetujui.</p>
                        </div>

                        <template x-if="selectedPayment?.exceeds_remaining">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                                Nominal bukti melebihi sisa tagihan saat ini. Pembayaran ini tidak dapat disetujui. Tolak dan hubungi pelanggan.
                            </div>
                        </template>

                        {{-- Tombol Setujui atau Tolak --}}
                        <div x-show="!showReject" class="grid grid-cols-2 gap-2.5">
                            <form :action="selectedPayment?.verify_url" method="POST" class="w-full" @submit="isSubmitting = true">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="verification_status" value="Terverifikasi">
                                <button
                                    type="submit"
                                    :disabled="selectedPayment?.exceeds_remaining || isSubmitting"
                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs transition hover:bg-brand-800 disabled:opacity-50 cursor-pointer"
                                >
                                    <x-lucide-check class="size-4" />
                                    <span>Setujui Pembayaran</span>
                                </button>
                            </form>

                            <button
                                type="button"
                                @click="showReject = true"
                                :disabled="isSubmitting"
                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-xs sm:text-sm font-bold text-rose-700 shadow-2xs transition hover:bg-rose-100 disabled:opacity-50 cursor-pointer"
                            >
                                <x-lucide-x class="size-4" />
                                <span>Tolak Bukti</span>
                            </button>
                        </div>

                        {{-- Form Alasan Tolak --}}
                        <form
                            x-show="showReject"
                            x-cloak
                            :action="selectedPayment?.verify_url"
                            method="POST"
                            class="space-y-3"
                            @submit="isSubmitting = true"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="verification_status" value="Ditolak">

                            <div>
                                <label for="drawer_notes" class="block text-xs font-bold text-slate-700 mb-1">
                                    Alasan Penolakan <span class="text-rose-600">*</span>
                                </label>
                                <textarea
                                    id="drawer_notes"
                                    name="verification_notes"
                                    x-model="rejectionNotes"
                                    rows="3"
                                    required
                                    placeholder="Contoh: Nominal transfer kurang, foto buram, atau dana belum masuk mutasi."
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="showReject = false"
                                    class="w-full rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full rounded-xl bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700 transition cursor-pointer"
                                >
                                    Konfirmasi Tolak
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>

            <x-slot:footer>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <a
                            :href="selectedPayment?.show_url"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition"
                        >
                            <x-lucide-external-link class="size-3.5 text-slate-400" />
                            <span>Halaman Penuh</span>
                        </a>
                        <template x-if="selectedPayment?.transaction_show_url">
                            <a
                                :href="selectedPayment?.transaction_show_url"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800"
                            >
                                <x-lucide-receipt class="size-3.5" />
                                <span>Lihat Nota</span>
                            </a>
                        </template>
                    </div>

                    <button
                        type="button"
                        @click="closePaymentSheet()"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-200 focus:outline-none cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </x-slot:footer>
        </x-ui.sheet>
    </div>
</x-layouts::app>
