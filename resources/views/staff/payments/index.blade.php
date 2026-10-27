@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $pendingCount = $counts['Menunggu Verifikasi'] ?? 0;
    $tabs = [
        'Menunggu Verifikasi' => ['label' => 'Menunggu', 'count' => $pendingCount],
        'Terverifikasi' => ['label' => 'Terverifikasi', 'count' => $counts['Terverifikasi'] ?? 0],
        'Ditolak' => ['label' => 'Ditolak', 'count' => $counts['Ditolak'] ?? 0],
        'Semua' => ['label' => 'Semua', 'count' => array_sum($counts)],
    ];
@endphp

<x-layouts::app title="Verifikasi Pembayaran">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500 sm:text-sm">
                Tinjau bukti transfer bank dan struk QRIS dari pelanggan, lalu setujui atau tolak pembayarannya.
            </p>

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

        <div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
            <div class="border-b border-slate-100 p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Antrean Bukti Pembayaran</h2>
                        <p class="text-xs text-slate-500">Bukti yang paling lama menunggu ditampilkan paling atas.</p>
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
                                value="{{ $search }}"
                                placeholder="Cari kode nota atau nama pelanggan..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-9 text-xs text-slate-900 transition placeholder:text-slate-400 focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
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
                    <table class="w-full min-w-[720px] text-left text-xs">
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
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-mono tabular-nums text-slate-600 whitespace-nowrap">
                                        {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-bold font-mono text-brand-700">{{ $payment->transaction?->transaction_code }}</p>
                                        <p class="text-slate-600">{{ $payment->transaction?->customer?->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $payment->method }}</td>
                                    <td class="px-4 py-3 text-right font-black font-mono tabular-nums text-slate-900 whitespace-nowrap">
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
                        <a href="{{ route("{$currentRoutePrefix}.payments.show", $payment) }}" class="block p-4 transition hover:bg-slate-50">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold font-mono text-brand-700">{{ $payment->transaction?->transaction_code }}</p>
                                    <p class="truncate text-xs text-slate-600">{{ $payment->transaction?->customer?->name ?? '-' }}</p>
                                </div>
                                <x-ui.verification-badge :status="$payment->verification_status" size="sm" class="shrink-0" />
                            </div>
                            <div class="mt-2 flex items-end justify-between gap-3">
                                <p class="text-xs text-slate-500 font-mono tabular-nums">
                                    {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }} &middot; {{ $payment->method }}
                                </p>
                                <p class="text-sm font-black font-mono tabular-nums text-slate-900">
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
                    <p class="mt-1 text-xs text-slate-500">
                        Bukti transfer atau QRIS baru dari pelanggan akan muncul di sini.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
