@php
    $currentRoutePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'staff');
    $transaction = $payment->transaction;
    $isPending = $payment->isPendingVerification();
    $remainingAmount = (float) $transaction->remaining_amount;
    $exceedsRemaining = $isPending && (float) $payment->amount > $remainingAmount;
    $proofUrl = $payment->proof_file ? route("{$currentRoutePrefix}.payments.proof", $payment) : null;
@endphp

<x-layouts::app title="Tinjau Bukti Pembayaran">
    <div class="space-y-7" x-data="{ showReject: {{ $errors->has('verification_notes') ? 'true' : 'false' }} }">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tinjau pembayaran</h1>
                <p class="mt-2 font-mono text-base font-semibold text-brand-700">{{ $transaction->transaction_code }}</p>
                <p class="text-sm text-slate-600">
                    Bukti {{ $payment->method }} dari {{ $transaction->customer?->name ?? 'pelanggan' }} &middot; diunggah {{ $payment->paid_at?->format('d M Y, H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button :href="route($currentRoutePrefix . '.transactions.show', $transaction)" variant="secondary" size="md">
                    <x-lucide-receipt class="size-4" />
                    <span>Lihat Nota</span>
                </x-ui.button>
                <x-ui.button :href="route($currentRoutePrefix . '.payments.index')" variant="ghost" size="md">
                    <x-lucide-arrow-left class="size-4" />
                    <span>Kembali</span>
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert variant="success" dismissible>
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->has('verification_status'))
            <x-ui.alert variant="destructive" title="Verifikasi tidak dapat diproses">
                {{ $errors->first('verification_status') }}
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Pratinjau bukti --}}
            <x-ui.card class="xl:col-span-3">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Pratinjau Bukti Pembayaran</h2>
                    @if ($proofUrl)
                        <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">
                            <x-lucide-zoom-in class="size-3.5" />
                            <span>Buka ukuran penuh</span>
                        </a>
                    @endif
                </div>

                @if ($proofUrl)
                    <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                        <img
                            src="{{ $proofUrl }}"
                            alt="Bukti {{ $payment->method }} untuk nota {{ $transaction->transaction_code }}"
                            class="mx-auto max-h-[70vh] w-full object-contain"
                        />
                    </a>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                        Pembayaran ini tidak memiliki berkas bukti.
                    </div>
                @endif
            </x-ui.card>

            <div class="space-y-6 xl:col-span-2">
                {{-- Ringkasan pembayaran & tagihan --}}
                <x-ui.card>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Nominal Diajukan</p>
                            <p class="mt-1 text-3xl font-semibold tracking-tight tabular-nums text-slate-900">
                                <x-ui.money :value="$payment->amount" />
                            </p>
                            <p class="text-sm text-slate-500">{{ $payment->method }} &middot; {{ $payment->payment_type }}</p>
                        </div>
                        <x-ui.verification-badge :status="$payment->verification_status" />
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-slate-200 pt-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-2 text-slate-600">
                            <dt>Total Tagihan</dt>
                            <dd class="font-bold tabular-nums text-slate-900"><x-ui.money :value="$transaction->total_amount" /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-2 text-slate-600">
                            <dt>Sudah Dibayar (valid)</dt>
                            <dd class="font-semibold tabular-nums text-brand-700"><x-ui.money :value="$transaction->total_paid" /></dd>
                        </div>
                        <div class="flex flex-wrap justify-between gap-2 border-t border-slate-100 pt-1.5 text-sm">
                            <dt class="font-bold text-slate-800">Sisa Tagihan</dt>
                            <dd class="font-semibold tabular-nums {{ $remainingAmount > 0 ? 'text-amber-800' : 'text-emerald-700' }}"><x-ui.money :value="$remainingAmount" /></dd>
                        </div>
                    </dl>

                    @if ($payment->verified_at)
                        <div class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">
                            Diproses oleh <span class="font-semibold text-slate-800">{{ $payment->verifier?->name ?? '-' }}</span>
                            pada {{ $payment->verified_at->format('d M Y, H:i') }}.
                            @if ($payment->verification_notes)
                                <p class="mt-1"><span class="font-semibold text-slate-800">Catatan:</span> {{ $payment->verification_notes }}</p>
                            @endif
                        </div>
                    @endif
                </x-ui.card>

                {{-- Keputusan verifikasi --}}
                @if ($isPending)
                    <x-ui.card class="space-y-3">
                        <div>
                            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Keputusan Verifikasi</h2>
                            <p class="mt-0.5 text-sm text-slate-500">
                                Cocokkan nominal, tanggal, dan rekening/merchant tujuan pada bukti sebelum menyetujui.
                            </p>
                        </div>

                        @if ($exceedsRemaining)
                            <x-ui.alert variant="warning">
                                Nominal bukti melebihi sisa tagihan saat ini. Pembayaran ini tidak dapat disetujui. Tolak dan hubungi pelanggan.
                            </x-ui.alert>
                        @endif

                        <form method="POST" action="{{ route("{$currentRoutePrefix}.payments.verify", $payment) }}" x-show="! showReject">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="verification_status" value="Terverifikasi">
                            <div class="grid grid-cols-2 gap-2">
                                <x-ui.button type="submit" size="md" :disabled="$exceedsRemaining" class="w-full">
                                    <x-lucide-check class="size-4" />
                                    <span>Setujui</span>
                                </x-ui.button>
                                <x-ui.button type="button" variant="danger-outline" size="md" class="w-full" @click="showReject = true">
                                    <x-lucide-x class="size-4" />
                                    <span>Tolak</span>
                                </x-ui.button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route("{$currentRoutePrefix}.payments.verify", $payment) }}" x-show="showReject" x-cloak class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="verification_status" value="Ditolak">
                            <label for="verification-notes" class="block text-sm font-semibold text-slate-700">
                                Alasan Penolakan <span class="text-rose-600">*</span>
                            </label>
                            <textarea
                                id="verification-notes"
                                name="verification_notes"
                                rows="5"
                                maxlength="500"
                                required
                                placeholder="Contoh: Nominal pada bukti tidak sesuai, gambar buram, atau dana belum masuk ke rekening."
                                class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base sm:text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            >{{ old('verification_notes') }}</textarea>
                            @error('verification_notes')
                                <p class="text-sm font-semibold text-rose-700" role="alert">{{ $message }}</p>
                            @enderror
                            <div class="grid grid-cols-2 gap-2">
                                <x-ui.button type="button" variant="secondary" size="md" class="w-full" @click="showReject = false">
                                    Batal
                                </x-ui.button>
                                <x-ui.button type="submit" variant="danger" size="md" class="w-full">
                                    Konfirmasi Tolak
                                </x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>
                @endif

                {{-- Riwayat pembayaran nota --}}
                <x-ui.card>
                    <h2 class="mb-3 text-lg font-semibold tracking-tight text-slate-900">Pembayaran Lain pada Nota Ini</h2>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($transaction->payments->sortByDesc('id') as $otherPayment)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3 {{ $otherPayment->is($payment) ? 'font-semibold' : '' }}">
                                <div>
                                    <p class="tabular-nums text-slate-900"><x-ui.money :value="$otherPayment->amount" /></p>
                                    <p class="text-slate-500">{{ $otherPayment->paid_at?->format('d M Y, H:i') }} &middot; {{ $otherPayment->method }}</p>
                                </div>
                                <x-ui.verification-badge :status="$otherPayment->verification_status" size="md" />
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts::app>
