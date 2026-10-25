<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cek Transaksi — {{ config('app.name', 'CUCI.IN') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50/70 font-sans text-slate-800 antialiased flex flex-col justify-between selection:bg-brand-500 selection:text-white">
    {{-- Header / Navbar Publik --}}
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md sticky top-0 z-30 print:hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('transactions.lookup') }}" class="flex items-center gap-2.5 py-2">
                <x-app-logo class="size-8 drop-shadow-2xs" />
                <span class="text-lg font-bold tracking-tight text-brand-700">CUCI<span class="text-brand-500">.IN</span></span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <x-ui.button
                        :href="route('dashboard')"
                        variant="soft"
                        size="sm"
                    >
                        <span>Dashboard</span>
                        <x-lucide-arrow-right class="size-3.5" />
                    </x-ui.button>
                @else
                    <x-ui.button
                        :href="route('login')"
                        variant="secondary"
                        size="sm"
                    >
                        <x-lucide-log-in class="size-3.5 text-slate-500" />
                        <span>Masuk Sistem</span>
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-10 space-y-6 sm:space-y-8">
        {{-- Hero & Deskripsi --}}
        <div class="text-center max-w-xl mx-auto space-y-2 print:hidden">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Cek Status Cucian Anda
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed px-2">
                Pantau progres pengerjaan pakaianmu secara langsung. Cukup masukkan nomor nota untuk melihat perkiraan selesai dan rincian pembayaran.
            </p>
        </div>

        {{-- Form Pencarian Nota --}}
        <div class="max-w-2xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs print:hidden">
            <form method="GET" action="{{ route('transactions.lookup') }}" class="space-y-3.5">
                <label for="code" class="block text-xs font-bold text-slate-700">
                    Masukkan Kode Transaksi / Nomor Nota
                </label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-lucide-receipt class="size-4" />
                        </div>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ $code }}"
                            required
                            autofocus
                            placeholder="Contoh: TRX-0001 atau TRK-20261025-046"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-3 sm:py-2.5 pl-10 pr-4 text-xs sm:text-sm font-semibold font-mono text-slate-900 transition placeholder:text-slate-400 placeholder:font-sans placeholder:font-normal focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 uppercase tracking-wider min-h-[44px]"
                        >
                    </div>
                    <x-ui.button
                        type="submit"
                        variant="primary"
                        class="w-full sm:w-auto h-11 px-6 shadow-xs"
                    >
                        <x-lucide-search class="size-4" />
                        <span>Cek Transaksi</span>
                    </x-ui.button>
                </div>
                <p class="text-xs text-slate-500">
                    *Nomor nota tercantum di bagian atas struk fisik atau pesan WhatsApp konfirmasi saat penyerahan pakaian.
                </p>

                {{-- Riwayat Pencarian Terakhir (LocalStorage) --}}
                <div id="recent-searches-box" class="hidden pt-3 border-t border-slate-100 text-xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500 font-medium">Pencarian Terakhir:</span>
                        <button
                            type="button"
                            onclick="clearRecentSearches()"
                            class="text-xs text-slate-400 hover:text-rose-600 transition cursor-pointer"
                        >
                            Hapus Riwayat
                        </button>
                    </div>
                    <div id="recent-searches-chips" class="mt-2 flex flex-wrap gap-1.5"></div>
                </div>
            </form>
        </div>

        {{-- Hasil Pencarian: Data Tidak Ditemukan --}}
        @if ($searched && ! $transaction)
            <div class="max-w-2xl mx-auto rounded-2xl border border-rose-200 bg-rose-50/80 p-5 text-center space-y-3 print:hidden">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <x-lucide-alert-triangle class="size-6" />
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-rose-900">Transaksi Tidak Ditemukan</h3>
                    <p class="text-xs text-rose-700 max-w-md mx-auto leading-relaxed">
                        Nomor nota <strong class="font-bold font-mono">{{ $code }}</strong> belum tercatat di sistem kami. Pastikan nomor nota sudah sesuai dengan struk fisikmu, atau tanyakan langsung ke kasir jika pakaian baru saja diserahkan.
                    </p>
                </div>
                <div class="pt-1 flex items-center justify-center gap-3 text-xs">
                    <x-ui.button
                        :href="route('transactions.lookup')"
                        variant="secondary"
                        size="sm"
                    >
                        <x-lucide-search class="size-3.5" />
                        <span>Coba Nomor Lain</span>
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Hasil Pencarian: Data Ditemukan --}}
        @if ($transaction)
            @php
                $statusOrder = ['Diterima' => 1, 'Diproses' => 2, 'Selesai' => 3, 'Diambil' => 4];
                $currentStep = $statusOrder[$transaction->status->value] ?? 1;
                $isCanceled = $transaction->status->value === 'Dibatalkan';
            @endphp

            <div class="max-w-3xl mx-auto space-y-6">
                {{-- 1. Stepper Timeline Status Laundry --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Cucian</span>
                            <div class="mt-0.5 flex items-center gap-2">
                                <h2 class="text-lg font-extrabold text-slate-900">{{ $transaction->status->value }}</h2>
                                @if ($isCanceled)
                                    <span class="rounded bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700 uppercase">Dibatalkan</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                @switch($transaction->status->value)
                                    @case('Diterima')
                                        Pakaian sudah ditimbang & antre masuk mesin cuci.
                                        @break
                                    @case('Diproses')
                                        Pakaianmu sedang dicuci, dikeringkan, atau disetrika rapi.
                                        @break
                                    @case('Selesai')
                                        Cucian sudah beres & wangi! Siap diambil di outlet.
                                        @break
                                    @case('Diambil')
                                        Pakaian sudah diserahkan. Terima kasih telah mempercayakan pakaianmu di CUCI.IN!
                                        @break
                                    @case('Dibatalkan')
                                        Pesanan cucian ini telah dibatalkan.
                                        @break
                                    @default
                                        Status cucian sedang diperbarui oleh kasir.
                                @endswitch
                            </p>
                        </div>
                        <div class="text-left sm:text-right shrink-0">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Perkiraan Selesai</span>
                            <p class="text-xs sm:text-sm font-bold text-slate-800 tabular-nums">
                                {{ $transaction->estimated_completed_at ? $transaction->estimated_completed_at->translatedFormat('d F Y, H:i') . ' WIB' : 'Sedang dijadwalkan kasir' }}
                            </p>
                            @if ($transaction->estimated_completed_at)
                                @if (in_array($transaction->status->value, ['Selesai', 'Diambil']))
                                    <p class="mt-0.5 text-xs font-semibold text-emerald-600">Pakaian sudah siap diambil</p>
                                @elseif ($transaction->estimated_completed_at->isPast())
                                    <div class="mt-0.5">
                                        <x-ui.badge tone="warning" size="xs" dot pulse>
                                            Sedang dipercepat kasir (~terlewat {{ $transaction->estimated_completed_at->diffForHumans(null, true) }})
                                        </x-ui.badge>
                                    </div>
                                @else
                                    <p class="mt-0.5 text-xs text-brand-700 font-medium">
                                        {{ $transaction->estimated_completed_at->diffForHumans() }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Visual Stepper Component --}}
                    <div class="pt-5 pb-1">
                        <x-ui.stepper
                            :steps="['Diterima', 'Diproses', 'Selesai', 'Diambil']"
                            :current="$currentStep"
                            :isCanceled="$isCanceled"
                        />
                    </div>
                </div>

                {{-- 2. Detail Rincian Nota & Pelanggan --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs space-y-6">
                    {{-- Header Info Transaksi --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nomor Nota Transaksi</span>
                            <div class="mt-0.5 flex flex-wrap items-center gap-2">
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 font-mono tracking-tight tabular-nums">{{ $transaction->transaction_code }}</h3>
                                <button
                                    type="button"
                                    id="btn-copy-link"
                                    onclick="copyTrackingLink()"
                                    data-url="{{ route('transactions.lookup', ['code' => $transaction->transaction_code]) }}"
                                    class="inline-flex min-h-[32px] items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:bg-slate-100 transition active:scale-95 cursor-pointer print:hidden"
                                    title="Salin tautan pelacakan untuk dibagikan"
                                >
                                    <x-lucide-copy id="icon-copy" class="size-3.5 text-slate-500" />
                                    <x-lucide-check id="icon-copied" class="size-3.5 text-emerald-600 hidden" />
                                    <span id="text-copy">Salin Tautan</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nama Pelanggan</span>
                            <p class="mt-0.5 text-xs sm:text-sm font-bold text-slate-800">
                                {{ $maskedCustomerName }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">Nama disamarkan untuk perlindungan privasi.</p>
                        </div>
                    </div>

                    {{-- Detail Layanan & Berat (Responsif: Daftar Struk di HP, Tabel di Desktop) --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Rincian Layanan & Cucian</h4>

                        {{-- Tampilan Mobile: Kartu Item Struk Bertumpuk (<640px) --}}
                        <div class="sm:hidden divide-y divide-slate-100 rounded-xl border border-slate-100 bg-slate-50/50">
                            @forelse ($transaction->details as $detail)
                                <div class="p-3.5 space-y-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-slate-900 leading-snug">
                                                {{ $detail->service?->name ?? 'Layanan Laundry' }}
                                            </p>
                                            @if ($detail->service?->description)
                                                <p class="text-xs text-slate-500 font-normal mt-0.5 leading-tight">
                                                    {{ $detail->service->description }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="text-xs font-bold text-slate-900 shrink-0 tabular-nums">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500 pt-0.5">
                                        <span class="tabular-nums">{{ (float) $detail->weight }} kg</span>
                                        <span class="tabular-nums">@ Rp {{ number_format($detail->unit_price, 0, ',', '.') }}/kg</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-slate-500">
                                    Detail layanan belum dimasukkan.
                                </div>
                            @endforelse
                        </div>

                        {{-- Tampilan Desktop: Tabel Lengkap (≥640px) --}}
                        <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50/70 border-b border-slate-100 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="p-3">Layanan</th>
                                        <th class="p-3">Berat</th>
                                        <th class="p-3">Tarif</th>
                                        <th class="p-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse ($transaction->details as $detail)
                                        <tr>
                                            <td class="p-3 font-semibold text-slate-900">
                                                {{ $detail->service?->name ?? 'Layanan Laundry' }}
                                                @if ($detail->service?->description)
                                                    <p class="text-xs text-slate-500 font-normal">{{ $detail->service->description }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3 tabular-nums">{{ (float) $detail->weight }} kg</td>
                                            <td class="p-3 text-slate-500 tabular-nums">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                            <td class="p-3 text-right font-bold text-slate-900 tabular-nums">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-4 text-center text-xs text-slate-500">
                                                Detail layanan belum dimasukkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Ringkasan Pembayaran & Total --}}
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600">Total Tagihan</span>
                            <span class="text-base sm:text-lg font-extrabold text-slate-900 tabular-nums">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200/60 pt-2.5">
                            <span class="text-xs font-semibold text-slate-600">Status Pembayaran</span>
                            <div>
                                <x-ui.payment-badge :status="$paymentSummary['status']" size="sm" />
                            </div>
                        </div>

                        @if ($paymentSummary['status'] === 'DP')
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>Uang Muka (DP):</span>
                                <span class="font-semibold text-slate-700 tabular-nums">Rp {{ number_format($paymentSummary['total_paid'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-rose-600 font-semibold">
                                <span>Sisa Tagihan yang Harus Dibayar:</span>
                                <span class="tabular-nums">Rp {{ number_format($paymentSummary['remaining_amount'], 0, ',', '.') }}</span>
                            </div>
                        @elseif ($paymentSummary['status'] === 'Belum Bayar')
                            <div class="text-xs text-slate-500 pt-0.5">
                                Belum ada pembayaran tercatat. Pelunasan dapat diselesaikan di meja kasir saat pengambilan pakaian.
                            </div>
                        @elseif ($paymentSummary['status'] === 'Lunas')
                            <div class="text-xs text-emerald-700 font-medium pt-0.5">
                                Tagihan telah lunas. Kamu bisa langsung mengambil pakaian tanpa transaksi tambahan.
                            </div>
                        @endif

                        @if ($paymentSummary['latest_payment'])
                            <div class="text-xs text-slate-500 border-t border-slate-200/40 pt-2">
                                Metode Pembayaran: <strong class="text-slate-600">{{ $paymentSummary['latest_payment']->method }}</strong>
                                • Waktu Bayar: <span class="tabular-nums">{{ $paymentSummary['latest_payment']->paid_at ? $paymentSummary['latest_payment']->paid_at->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Aksi Bantuan Kasir & Pengaduan Kendala Cucian (Milestone 2) --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
                    <div class="space-y-1 text-center sm:text-left">
                        <p class="text-xs font-bold text-slate-900">Ada kendala atau pertanyaan seputar pakaianmu?</p>
                        <p class="text-xs text-slate-500">Pakaian ada noda khusus, butuh konfirmasi kilat, atau ingin menghubungi outlet? Beri tahu tim kami langsung.</p>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 justify-center">
                        @if (Route::has('customer.complaints.create'))
                            <x-ui.button
                                :href="route('customer.complaints.create', ['transaction_code' => $transaction->transaction_code])"
                                variant="soft-warning"
                                size="sm"
                            >
                                <x-lucide-alert-triangle class="size-3.5 text-amber-600" />
                                <span>Ajukan Pengaduan</span>
                            </x-ui.button>
                        @endif

                        <x-ui.button
                            :href="'https://wa.me/?text=' . rawurlencode('Halo Kasir CUCI.IN, saya ingin menanyakan status cucian untuk nomor nota: ' . $transaction->transaction_code)"
                            target="_blank"
                            rel="noopener noreferrer"
                            variant="secondary"
                            size="sm"
                        >
                            <x-lucide-message-circle class="size-3.5 text-emerald-600" />
                            <span>Tanya Kasir</span>
                        </x-ui.button>
                    </div>
                </div>

                {{-- Navigasi Kembali --}}
                <div class="flex items-center justify-center text-xs text-slate-500 pt-1 print:hidden">
                    <x-ui.button
                        :href="route('transactions.lookup')"
                        variant="ghost"
                        size="sm"
                    >
                        <x-lucide-arrow-left class="size-4" />
                        <span>Cek Nomor Nota Lainnya</span>
                    </x-ui.button>
                </div>
            </div>
        @endif
    </main>

    {{-- Footer Publik --}}
    <footer class="border-t border-slate-200/80 bg-white py-6 text-center text-xs text-slate-500 print:hidden">
        <div class="max-w-4xl mx-auto px-4">
            &copy; {{ date('Y') }} CUCI.IN — Sistem Manajemen Operasional Laundry.
        </div>
    </footer>

    {{-- Script Utilitas: Salin Tautan & Riwayat Pencarian Lokal --}}
    <script>
        function copyTrackingLink() {
            const btn = document.getElementById('btn-copy-link');
            if (!btn) return;
            const url = btn.getAttribute('data-url') || window.location.href;
            const iconCopy = document.getElementById('icon-copy');
            const iconCopied = document.getElementById('icon-copied');
            const textCopy = document.getElementById('text-copy');

            const onCopied = () => {
                if (iconCopy) iconCopy.classList.add('hidden');
                if (iconCopied) iconCopied.classList.remove('hidden');
                if (textCopy) textCopy.textContent = 'Tersalin!';
                setTimeout(() => {
                    if (iconCopy) iconCopy.classList.remove('hidden');
                    if (iconCopied) iconCopied.classList.add('hidden');
                    if (textCopy) textCopy.textContent = 'Salin Tautan';
                }, 2200);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(onCopied).catch(() => fallbackCopy(url, onCopied));
            } else {
                fallbackCopy(url, onCopied);
            }
        }

        function fallbackCopy(text, callback) {
            const temp = document.createElement('textarea');
            temp.value = text;
            temp.style.position = 'fixed';
            temp.style.opacity = '0';
            document.body.appendChild(temp);
            temp.focus();
            temp.select();
            try {
                document.execCommand('copy');
                if (callback) callback();
            } catch (e) {
                console.warn('Gagal menyalin tautan:', e);
            }
            document.body.removeChild(temp);
        }

        const RECENT_KEY = 'cuciin_recent_lookups';

        function getRecentSearches() {
            try {
                const data = localStorage.getItem(RECENT_KEY);
                return data ? JSON.parse(data) : [];
            } catch (e) {
                return [];
            }
        }

        function saveRecentSearch(code) {
            if (!code || typeof code !== 'string') return;
            code = code.trim().toUpperCase();
            if (!code) return;
            try {
                let recents = getRecentSearches();
                recents = recents.filter(item => item !== code);
                recents.unshift(code);
                if (recents.length > 4) recents = recents.slice(0, 4);
                localStorage.setItem(RECENT_KEY, JSON.stringify(recents));
            } catch (e) {}
        }

        function clearRecentSearches() {
            try {
                localStorage.removeItem(RECENT_KEY);
                const container = document.getElementById('recent-searches-box');
                if (container) container.classList.add('hidden');
            } catch (e) {}
        }

        function selectRecentCode(code) {
            const input = document.getElementById('code');
            if (input) {
                input.value = code;
                input.form?.submit();
            }
        }

        function renderRecentSearches() {
            const container = document.getElementById('recent-searches-box');
            const chips = document.getElementById('recent-searches-chips');
            if (!container || !chips) return;

            const recents = getRecentSearches();
            if (recents.length === 0) {
                container.classList.add('hidden');
                return;
            }

            chips.innerHTML = '';
            recents.forEach(code => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'inline-flex min-h-[32px] items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-mono font-medium text-slate-700 hover:border-brand-500 hover:bg-brand-50 hover:text-brand-800 transition active:scale-95 cursor-pointer';
                btn.innerHTML = `<span class="size-1.5 rounded-full bg-brand-500"></span><span>${code}</span>`;
                btn.onclick = () => selectRecentCode(code);
                chips.appendChild(btn);
            });

            container.classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderRecentSearches();
            @if ($transaction)
                saveRecentSearch(@js($transaction->transaction_code));
                renderRecentSearches();
            @endif
        });
    </script>
</body>
</html>
