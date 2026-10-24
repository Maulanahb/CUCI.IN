@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'staff';
    $currentStatus = request('status');
    $currentPayment = request('payment');
    $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('payment');
    $allTransactionIds = $transactions->pluck('id')->all();

    $transactionsPayloadMap = $transactions->getCollection()->mapWithKeys(function ($transaction) use ($routePrefix) {
        $totalWeight = (float) $transaction->details->sum('weight');
        $firstService = $transaction->details->first()?->service?->name ?? 'Layanan Laundry';
        $itemCount = $transaction->details->count();
        $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
        $paymentStatusValue = $transaction->payment_status instanceof \App\Enums\PaymentStatus ? $transaction->payment_status->value : (string) $transaction->payment_status;
        $isOverdue = $transaction->estimated_completed_at
            && $transaction->estimated_completed_at->isPast()
            && !in_array($statusValue, ['Selesai', 'Diambil', 'Dibatalkan']);
        $initials = strtoupper(substr($transaction->customer?->name ?? 'P', 0, 1));
        $isStorageOverdue = $transaction->isStorageOverdue(14);
        $daysInStorage = $isStorageOverdue && $transaction->updated_at ? (int) $transaction->updated_at->diffInDays(now()) : 0;
        $cleanPhone = preg_replace('/[^0-9]/', '', $transaction->customer?->phone ?? '');
        $remainingText = (float) $transaction->remaining_amount > 0 ? ' dengan sisa tagihan Rp ' . number_format($transaction->remaining_amount, 0, ',', '.') : ' (sudah lunas)';
        $waMsg = urlencode("Halo Kak " . ($transaction->customer?->name ?? 'Pelanggan') . ", cucian Anda di CUCI.IN (Nota: {$transaction->transaction_code}) telah selesai dan siap diambil (sudah {$daysInStorage} hari di rak{$remainingText}). Mohon dapat segera diambil di outlet ya Kak. Terima kasih! 😊");
        $waReminderUrl = $cleanPhone ? "https://wa.me/{$cleanPhone}?text={$waMsg}" : null;

        return [
            $transaction->id => [
                'id' => $transaction->id,
                'code' => $transaction->transaction_code,
                'created_at' => $transaction->created_at?->format('d M Y, H:i'),
                'customer_name' => $transaction->customer?->name ?? 'Pelanggan umum',
                'customer_phone' => $transaction->customer?->phone ?? '-',
                'customer_address' => $transaction->customer?->address ?? '-',
                'initials' => $initials,
                'status' => $statusValue,
                'payment_status' => $paymentStatusValue,
                'total_amount' => (float) $transaction->total_amount,
                'total_formatted' => 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                'paid_amount' => (float) $transaction->total_paid,
                'paid_formatted' => 'Rp ' . number_format($transaction->total_paid, 0, ',', '.'),
                'remaining_amount' => (float) $transaction->remaining_amount,
                'remaining_formatted' => 'Rp ' . number_format($transaction->remaining_amount, 0, ',', '.'),
                'total_weight' => number_format($totalWeight, 1, ',', '.'),
                'first_service' => $firstService,
                'item_count' => $itemCount,
                'estimated_at' => $transaction->estimated_completed_at?->format('d M Y, H:i'),
                'is_overdue' => $isOverdue,
                'is_storage_overdue' => $isStorageOverdue,
                'days_in_storage' => $daysInStorage,
                'wa_reminder_url' => $waReminderUrl,
                'notes' => $transaction->notes ?? '',
                'items' => $transaction->details->map(fn($d) => [
                    'service' => $d->service?->name ?? 'Layanan Laundry',
                    'weight' => (float) $d->weight,
                    'price_formatted' => 'Rp ' . number_format($d->price_per_unit, 0, ',', '.'),
                    'subtotal_formatted' => 'Rp ' . number_format($d->subtotal, 0, ',', '.'),
                    'notes' => $d->notes ?? '',
                ])->values()->all(),
                'payments' => $transaction->payments->map(fn($p) => [
                    'id' => $p->id,
                    'amount_formatted' => 'Rp ' . number_format($p->amount, 0, ',', '.'),
                    'method' => $p->method ?? 'Tunai',
                    'created_at' => $p->paid_at?->format('d M Y, H:i') ?? $p->created_at?->format('d M Y, H:i'),
                    'status' => $p->verification_status ?? 'Terverifikasi',
                ])->values()->all(),
                'show_url' => route($routePrefix . '.transactions.show', $transaction),
                'print_url' => route($routePrefix . '.transactions.print', $transaction),
                'status_url' => route($routePrefix . '.transactions.status', $transaction),
                'payment_url' => route($routePrefix . '.transactions.payments.store', $transaction),
            ],
        ];
    })->all();
@endphp

<x-layouts::app title="Kelola Transaksi">

    <x-slot:actions>
        <x-ui.button
            :href="route($routePrefix . '.transactions.create')"
            size="sm"
            aria-label="Buka Kasir POS"
            class="shadow-2xs active:scale-98 cursor-pointer gap-1.5"
        >
            <x-lucide-plus class="size-4" aria-hidden="true" />
            <span>Buka Kasir POS</span>
            <kbd class="hidden rounded-md bg-brand-800/80 px-1.5 py-0.5 text-[10px] font-mono font-bold text-white sm:inline border border-brand-600/40">F2</kbd>
        </x-ui.button>
    </x-slot>

    <div class="space-y-5" x-data="{
        trxMap: {{ Js::from($transactionsPayloadMap) }},
        selectedTrx: null,
        isDrawerOpen: false,
        detailTab: 'items',
        isSubmitting: false,
        showCustomPay: false,
        showAdvancedStatus: false,

        selectedIds: [],
        selectAll: false,
        allIds: {{ Js::from($allTransactionIds) }},

        // Edit status model
        newStatus: '',
        statusNotes: '',

        // Payment form model
        payAmount: 0,
        payMethod: 'Tunai',
        cashReceived: 0,
        get changeAmount() {
            if (this.payMethod !== 'Tunai') return 0;
            const received = Number(this.cashReceived) || 0;
            const pay = Number(this.payAmount) || 0;
            return Math.max(0, received - pay);
        },

        // Toast notification model
        toast: {
            show: false,
            message: '',
            type: 'success'
        },
        toastTimeout: null,
        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        },

        rank(s) {
            return { 'Diterima': 1, 'Diproses': 2, 'Selesai': 3, 'Diambil': 4, 'Dibatalkan': 0 }[s] ?? 0;
        },
        isTerminal() {
            return this.selectedTrx?.status === 'Diambil' || this.selectedTrx?.status === 'Dibatalkan';
        },
        isRollback(targetStatus) {
            if (!this.selectedTrx || !targetStatus || targetStatus === 'Dibatalkan' || this.isTerminal()) return false;
            return this.rank(targetStatus) < this.rank(this.selectedTrx.status);
        },
        canSelectStatus(stOption) {
            if (this.isTerminal()) return false;
            if (stOption === 'Diambil' && (this.selectedTrx?.remaining_amount || 0) > 0) return false;
            return true;
        },
        canSubmitStatus() {
            if (!this.selectedTrx || this.isTerminal()) return false;
            const statusChanged = this.newStatus !== this.selectedTrx.status;
            const notesChanged = (this.statusNotes || '').trim() !== (this.selectedTrx.notes || '').trim();

            if (!statusChanged && !notesChanged) return false;

            if (this.isRollback(this.newStatus) && (!this.statusNotes || this.statusNotes.trim().length === 0)) {
                return false;
            }

            if (this.newStatus === 'Dibatalkan' && (!this.statusNotes || this.statusNotes.trim().length === 0)) {
                return false;
            }

            if (this.newStatus === 'Diambil' && (this.selectedTrx?.remaining_amount || 0) > 0) {
                return false;
            }

            return true;
        },

        copied: false,
        copyCode(code) {
            if (!code) return;
            navigator.clipboard.writeText(code);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },

        openDrawer(param) {
            const id = typeof param === 'object' && param !== null ? param.id : param;
            this.selectedTrx = this.trxMap[id] || param;
            if (!this.selectedTrx) return;

            this.isDrawerOpen = true;
            this.newStatus = this.selectedTrx.status;
            this.statusNotes = this.selectedTrx.notes || '';
            this.payAmount = this.selectedTrx.remaining_amount || 0;
            this.cashReceived = this.selectedTrx.remaining_amount || 0;
            this.payMethod = 'Tunai';
            this.showCustomPay = false;
            this.showAdvancedStatus = false;
            this.detailTab = 'items';
        },
        closeDrawer() {
            this.isDrawerOpen = false;
        },

        // Asynchronous Status Advance (1-Click)
        async advanceStatus(targetStatus) {
            if (!this.selectedTrx || this.isSubmitting) return;

            if (targetStatus === 'Diambil' && (this.selectedTrx.remaining_amount || 0) > 0) {
                this.showToast('Cucian belum dapat diserahkan karena pembayaran belum lunas.', 'error');
                return;
            }

            if ((this.isRollback(targetStatus) || targetStatus === 'Dibatalkan') && (!this.statusNotes || this.statusNotes.trim().length === 0)) {
                this.showToast('Wajib mengisi memo/alasan sebelum memundurkan atau membatalkan status.', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const res = await fetch(this.selectedTrx.status_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        _method: 'PATCH',
                        status: targetStatus,
                        notes: this.statusNotes || this.selectedTrx.notes || ''
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    const updated = data.transaction;
                    this.selectedTrx.status = updated.status;
                    this.selectedTrx.notes = updated.notes;
                    this.newStatus = updated.status;
                    this.statusNotes = updated.notes || '';

                    if (this.trxMap[this.selectedTrx.id]) {
                        this.trxMap[this.selectedTrx.id].status = updated.status;
                        this.trxMap[this.selectedTrx.id].notes = updated.notes;
                    }

                    this.showAdvancedStatus = false;
                    this.showToast(data.message || 'Status berhasil diperbarui.', 'success');
                } else {
                    let errorMsg = data?.message || 'Gagal memperbarui status transaksi.';
                    if (data?.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey]?.[0]) {
                            errorMsg = data.errors[firstKey][0];
                        }
                    }
                    this.showToast(errorMsg, 'error');
                }
            } catch (err) {
                this.showToast('Terjadi gangguan koneksi jaringan.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        // Asynchronous Memo Save
        async saveNotes() {
            if (!this.selectedTrx || this.isSubmitting) return;

            this.isSubmitting = true;

            try {
                const res = await fetch(this.selectedTrx.status_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        _method: 'PATCH',
                        status: this.selectedTrx.status,
                        notes: this.statusNotes
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    const updated = data.transaction;
                    this.selectedTrx.notes = updated.notes;

                    if (this.trxMap[this.selectedTrx.id]) {
                        this.trxMap[this.selectedTrx.id].notes = updated.notes;
                    }

                    this.showToast('Memo catatan cucian berhasil disimpan.', 'success');
                } else {
                    let errorMsg = data?.message || 'Gagal menyimpan catatan.';
                    if (data?.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey]?.[0]) {
                            errorMsg = data.errors[firstKey][0];
                        }
                    }
                    this.showToast(errorMsg, 'error');
                }
            } catch (err) {
                this.showToast('Terjadi gangguan koneksi jaringan.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        // Asynchronous 1-Click Exact Cash Payment
        async quickPayExact() {
            if (!this.selectedTrx || this.isSubmitting || (this.selectedTrx.remaining_amount || 0) <= 0) return;

            this.isSubmitting = true;

            try {
                const res = await fetch(this.selectedTrx.payment_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        method: 'Tunai',
                        amount: this.selectedTrx.remaining_amount,
                        cash_received: this.selectedTrx.remaining_amount
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    this.applyPaymentResult(data);
                    this.showToast(data.message || 'Pelunasan tagihan berhasil dicatat.', 'success');
                } else {
                    let errorMsg = data?.message || 'Gagal mencatat pelunasan.';
                    if (data?.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey]?.[0]) {
                            errorMsg = data.errors[firstKey][0];
                        }
                    }
                    this.showToast(errorMsg, 'error');
                }
            } catch (err) {
                this.showToast('Terjadi gangguan koneksi jaringan.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        // Asynchronous Custom Payment Submission
        async submitCustomPayment() {
            if (!this.selectedTrx || this.isSubmitting) return;

            const amount = Number(this.payAmount) || 0;
            if (amount <= 0) {
                this.showToast('Nominal bayar harus lebih dari 0.', 'error');
                return;
            }

            if (this.payMethod === 'Tunai' && (Number(this.cashReceived) || 0) < amount) {
                this.showToast('Uang diterima kurang dari nominal pembayaran.', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const res = await fetch(this.selectedTrx.payment_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        method: this.payMethod,
                        amount: amount,
                        cash_received: this.payMethod === 'Tunai' ? Number(this.cashReceived) : null
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    this.applyPaymentResult(data);
                    this.showCustomPay = false;
                    this.showToast(data.message || 'Pembayaran berhasil disimpan.', 'success');
                } else {
                    let errorMsg = data?.message || 'Gagal menyimpan pembayaran.';
                    if (data?.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey]?.[0]) {
                            errorMsg = data.errors[firstKey][0];
                        }
                    }
                    this.showToast(errorMsg, 'error');
                }
            } catch (err) {
                this.showToast('Terjadi gangguan koneksi jaringan.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        applyPaymentResult(data) {
            if (!data.transaction) return;
            const t = data.transaction;

            this.selectedTrx.paid_amount = t.paid_amount;
            this.selectedTrx.paid_formatted = t.paid_formatted;
            this.selectedTrx.remaining_amount = t.remaining_amount;
            this.selectedTrx.remaining_formatted = t.remaining_formatted;
            this.selectedTrx.payment_status = t.payment_status;
            this.selectedTrx.payments = t.payments;

            this.payAmount = t.remaining_amount;
            this.cashReceived = t.remaining_amount;

            if (this.trxMap[this.selectedTrx.id]) {
                this.trxMap[this.selectedTrx.id].paid_amount = t.paid_amount;
                this.trxMap[this.selectedTrx.id].paid_formatted = t.paid_formatted;
                this.trxMap[this.selectedTrx.id].remaining_amount = t.remaining_amount;
                this.trxMap[this.selectedTrx.id].remaining_formatted = t.remaining_formatted;
                this.trxMap[this.selectedTrx.id].payment_status = t.payment_status;
                this.trxMap[this.selectedTrx.id].payments = t.payments;
            }
        },

        toggleSelectAll() {
            this.selectAll = !this.selectAll;
            this.selectedIds = this.selectAll ? [...this.allIds] : [];
        },
        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
                this.selectAll = false;
            } else {
                this.selectedIds.push(id);
                if (this.selectedIds.length === this.allIds.length) {
                    this.selectAll = true;
                }
            }
        },

        initShortcuts() {
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isDrawerOpen) {
                    this.closeDrawer();
                } else if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    document.getElementById('search')?.focus();
                } else if (e.key === 'F2') {
                    e.preventDefault();
                    window.location.href = '{{ route($routePrefix . '.transactions.create') }}';
                }
            });
        }
    }" x-init="initShortcuts()">

        {{-- Flash message --}}
        @if (session('success'))
            <x-ui.alert variant="success" dismissible>
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="destructive" dismissible>
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive" dismissible>
                <div class="space-y-1">
                    <p class="font-bold text-xs">Perhatian:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </x-ui.alert>
        @endif

        {{-- Filter & Search Card --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <form
                method="GET"
                action="{{ route($routePrefix . '.transactions.index') }}"
                class="flex flex-col gap-3 md:flex-row md:items-end"
            >
                @if (request()->filled('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <label for="search" class="mb-1.5 block text-xs font-bold text-slate-700">
                        Cari Transaksi
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-lucide-search class="size-4" aria-hidden="true" />
                        </div>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Ketik nomor nota (TRK-...) atau nama pelanggan..."
                            class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50/50 pl-10 pr-10 text-xs sm:text-sm text-slate-800 transition placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                        >
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <kbd class="hidden rounded-md border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-[10px] font-mono font-bold text-slate-500 sm:inline" title="Tekan '/' untuk fokus">/</kbd>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <x-ui.button
                        :href="route($routePrefix . '.transactions.index')"
                        variant="secondary"
                        size="sm"
                        class="cursor-pointer shadow-2xs {{ $hasFilters ? '' : 'pointer-events-none opacity-50' }}"
                        :aria-disabled="$hasFilters ? 'false' : 'true'"
                    >
                        <x-lucide-rotate-ccw class="size-3.5 text-slate-500" aria-hidden="true" />
                        <span>Reset</span>
                    </x-ui.button>
                    <x-ui.button
                        type="submit"
                        variant="primary"
                        size="sm"
                        class="cursor-pointer shadow-2xs active:scale-98"
                    >
                        <x-lucide-search class="size-3.5" aria-hidden="true" />
                        <span>Cari</span>
                    </x-ui.button>
                </div>
            </form>
        </div>

        {{-- Quick Status Filter Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none sm:gap-2">
            @php
                $statusTabs = [
                    ['label' => 'Semua Nota', 'status' => null],
                    ['label' => 'Diterima', 'status' => 'Diterima'],
                    ['label' => 'Diproses', 'status' => 'Diproses'],
                    ['label' => 'Selesai (Siap Ambil)', 'status' => 'Selesai'],
                    ['label' => 'Lewat Simpan (>14 Hari)', 'status' => 'unclaimed', 'count' => $unclaimedCount ?? 0],
                    ['label' => 'Sudah Diambil', 'status' => 'Diambil'],
                    ['label' => 'Dibatalkan', 'status' => 'Dibatalkan'],
                ];
            @endphp

            @foreach ($statusTabs as $tab)
                @php
                    $isActive = ($tab['status'] === null && $currentStatus === null) || ($currentStatus === $tab['status']);
                @endphp
                <a
                    href="{{ route($routePrefix . '.transactions.index', array_filter(array_merge(request()->except(['page', 'status']), ['status' => $tab['status']]))) }}"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-semibold transition active:scale-95 {{ $isActive ? 'border-brand-600 bg-brand-50 text-brand-700 shadow-2xs ring-1 ring-brand-300 font-bold' : 'border-slate-200/90 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 shadow-2xs' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    @if (!empty($tab['count']))
                        <span class="rounded-full bg-rose-600 px-1.5 py-0.2 text-[10px] font-bold text-white font-mono">{{ $tab['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Daftar Transaksi --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xs">
            <div class="flex flex-wrap items-center justify-between border-b border-slate-100 bg-slate-50/60 px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 shadow-2xs">
                        <x-lucide-receipt class="size-5" aria-hidden="true" />
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Daftar Nota Transaksi</h2>
                        <p class="text-xs text-slate-500">
                            Menampilkan <span class="font-bold text-slate-800 font-mono tabular-nums">{{ $transactions->total() }}</span> nota tercatat
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tabel Desktop --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="border-b border-slate-200/80 bg-slate-50/75 text-2xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="w-10 px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="selectAll"
                                    @change="toggleSelectAll()"
                                    class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                >
                            </th>
                            <th class="px-5 py-3">Kode Nota</th>
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Layanan & Bobot</th>
                            <th class="px-5 py-3">Total & Pembayaran</th>
                            <th class="px-5 py-3">Status Operasional</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            @php
                                $totalWeight = (float) $transaction->details->sum('weight');
                                $firstService = $transaction->details->first()?->service?->name ?? 'Layanan Laundry';
                                $itemCount = $transaction->details->count();
                                $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
                                $paymentStatusValue = $transaction->payment_status instanceof \App\Enums\PaymentStatus ? $transaction->payment_status->value : (string) $transaction->payment_status;
                                $isOverdue = $transaction->estimated_completed_at
                                    && $transaction->estimated_completed_at->isPast()
                                    && !in_array($statusValue, ['Selesai', 'Diambil', 'Dibatalkan']);
                                $initials = strtoupper(substr($transaction->customer?->name ?? 'P', 0, 1));
                            @endphp
                            <tr
                                class="group cursor-pointer transition-colors hover:bg-brand-50/30"
                                :class="{ 'bg-brand-50/70 ring-1 ring-inset ring-brand-300': selectedTrx?.id === {{ $transaction->id }} }"
                                @click="openDrawer({{ $transaction->id }})"
                            >
                                {{-- Checkbox --}}
                                <td class="w-10 px-4 py-4 text-center" @click.stop>
                                    <input
                                        type="checkbox"
                                        :checked="selectedIds.includes({{ $transaction->id }})"
                                        @change="toggleSelect({{ $transaction->id }})"
                                        class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                    >
                                </td>

                                {{-- Kode Nota --}}
                                <td class="px-5 py-4">
                                    <span class="font-mono font-bold tracking-tight text-brand-700 hover:text-brand-800 text-sm">
                                        {{ $transaction->transaction_code }}
                                    </span>
                                    <p class="mt-0.5 text-xs text-slate-500 font-mono tabular-nums">
                                        {{ $transaction->created_at?->format('d M Y, H:i') }}
                                    </p>
                                </td>

                                {{-- Pelanggan --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 font-bold text-xs text-slate-700 border border-slate-200/90 shadow-2xs">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 truncate">
                                                {{ $transaction->customer?->name ?? 'Pelanggan umum' }}
                                            </p>
                                            @if ($transaction->customer?->phone)
                                                <p class="mt-0.5 font-mono text-xs text-slate-500 tabular-nums">
                                                    {{ $transaction->customer->phone }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Layanan & Bobot --}}
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800 line-clamp-1 text-xs sm:text-sm" title="{{ $firstService }}">
                                        {{ $firstService }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500 font-mono tabular-nums">
                                        {{ number_format((float) $totalWeight, 1, ',', '.') }} kg
                                        @if ($itemCount > 1)
                                            <span class="text-slate-400 font-sans">· +{{ $itemCount - 1 }} item lainnya</span>
                                        @endif
                                    </p>
                                </td>

                                {{-- Total & Pembayaran Dinamis --}}
                                <td class="px-5 py-4">
                                    <x-ui.money :value="$transaction->total_amount" class="font-black text-slate-900 font-mono tabular-nums text-sm" />
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset transition-colors duration-200"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20': (trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}') === 'Lunas',
                                                'bg-amber-50 text-amber-800 ring-amber-600/25': (trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}') === 'DP',
                                                'bg-rose-50 text-rose-700 ring-rose-600/20': (trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}') === 'Belum Bayar'
                                            }"
                                            x-text="trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}'"
                                        >{{ $paymentStatusValue }}</span>

                                        <template x-if="(trxMap[{{ $transaction->id }}]?.remaining_amount ?? {{ (float) $transaction->remaining_amount }}) > 0">
                                            <span class="text-xs font-bold text-rose-600 font-mono tabular-nums">
                                                (Sisa: <span x-text="trxMap[{{ $transaction->id }}]?.remaining_formatted ?? 'Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}'">Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</span>)
                                            </span>
                                        </template>
                                    </div>
                                </td>

                                {{-- Status Operasional Dinamis --}}
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset transition-colors duration-200"
                                        :class="{
                                            'bg-sky-50 text-sky-700 ring-sky-600/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diterima',
                                            'bg-amber-50 text-amber-800 ring-amber-600/25': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diproses',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Selesai',
                                            'bg-slate-100 text-slate-700 ring-slate-400/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diambil',
                                            'bg-rose-50 text-rose-700 ring-rose-600/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Dibatalkan'
                                        }"
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="{
                                                'bg-sky-500': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diterima',
                                                'bg-amber-500 animate-pulse': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diproses',
                                                'bg-emerald-500': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Selesai',
                                                'bg-slate-400': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diambil',
                                                'bg-rose-500': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Dibatalkan'
                                            }"
                                        ></span>
                                        <span x-text="trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}'">{{ $statusValue }}</span>
                                    </span>

                                    @if ($transaction->estimated_completed_at)
                                        <div class="mt-1.5 flex items-center gap-1 text-xs">
                                            <x-lucide-clock class="size-3.5 {{ $isOverdue ? 'text-rose-500' : 'text-slate-400' }}" aria-hidden="true" />
                                            @if ($isOverdue)
                                                <span class="font-bold text-rose-600 font-mono tabular-nums">
                                                    Terlambat ({{ $transaction->estimated_completed_at->format('d/m H:i') }})
                                                </span>
                                            @else
                                                <span class="text-slate-500 font-mono tabular-nums">
                                                    Est: {{ $transaction->estimated_completed_at->format('d M, H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($transaction->isStorageOverdue(14))
                                        <div class="mt-1">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 border border-amber-200/90 px-1.5 py-0.5 text-[10px] font-bold text-amber-800" title="Cucian sudah selesai lebih dari 14 hari di rak penyimpanan">
                                                <x-lucide-clock class="size-2.5 text-amber-600" />
                                                <span>Lewat Simpan ({{ (int) $transaction->updated_at?->diffInDays(now()) }} hr)</span>
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Edit & Aksi --}}
                                <td class="px-5 py-4 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openDrawer({{ $transaction->id }})"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200/90 bg-white text-slate-600 shadow-2xs hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 transition active:scale-95 cursor-pointer"
                                            title="Buka panel edit cepat"
                                        >
                                            <x-lucide-sliders-horizontal class="size-4" aria-hidden="true" />
                                        </button>
                                        <x-ui.button
                                            :href="route($routePrefix . '.transactions.print', $transaction)"
                                            variant="secondary"
                                            size="xs"
                                            target="_blank"
                                            rel="noopener"
                                            title="Cetak struk kasir"
                                            class="cursor-pointer shadow-2xs active:scale-95"
                                        >
                                            <x-lucide-printer class="size-3.5 text-slate-500" aria-hidden="true" />
                                            <span class="sr-only">Cetak</span>
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 shadow-2xs">
                                        <x-lucide-receipt class="size-6" aria-hidden="true" />
                                    </div>
                                    <p class="mt-3 text-sm font-bold text-slate-800">Tidak ada transaksi ditemukan</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        @if ($hasFilters)
                                            Coba ubah kata kunci atau bersihkan filter pencarian.
                                        @else
                                            Transaksi baru akan muncul di sini setelah kasir menyimpan nota.
                                        @endif
                                    </p>
                                    @if ($hasFilters)
                                        <div class="mt-3">
                                            <x-ui.button :href="route($routePrefix . '.transactions.index')" variant="secondary" size="xs">
                                                Bersihkan Filter
                                            </x-ui.button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Kartu Mobile --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($transactions as $transaction)
                    @php
                        $totalWeight = $transaction->details->sum('weight');
                        $firstService = $transaction->details->first()?->service?->name ?? 'Layanan Laundry';
                        $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus ? $transaction->status->value : (string) $transaction->status;
                        $paymentStatusValue = $transaction->payment_status instanceof \App\Enums\PaymentStatus ? $transaction->payment_status->value : (string) $transaction->payment_status;
                        $isOverdue = $transaction->estimated_completed_at
                            && $transaction->estimated_completed_at->isPast()
                            && !in_array($statusValue, ['Selesai', 'Diambil', 'Dibatalkan']);
                        $initials = strtoupper(substr($transaction->customer?->name ?? 'P', 0, 1));
                    @endphp
                    <div
                        class="space-y-3 p-4 transition-colors hover:bg-brand-50/30 cursor-pointer"
                        @click="openDrawer({{ $transaction->id }})"
                    >
                        {{-- Baris 1: Kode & Status Cucian Dinamis --}}
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="font-mono font-bold tracking-tight text-brand-700 text-sm">
                                    {{ $transaction->transaction_code }}
                                </span>
                                <p class="mt-0.5 text-xs text-slate-500 font-mono tabular-nums">
                                    {{ $transaction->created_at?->format('d M Y, H:i') }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset"
                                :class="{
                                    'bg-sky-50 text-sky-700 ring-sky-600/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diterima',
                                    'bg-amber-50 text-amber-800 ring-amber-600/25': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diproses',
                                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Selesai',
                                    'bg-slate-100 text-slate-700 ring-slate-400/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Diambil',
                                    'bg-rose-50 text-rose-700 ring-rose-600/20': (trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}') === 'Dibatalkan'
                                }"
                                x-text="trxMap[{{ $transaction->id }}]?.status ?? '{{ $statusValue }}'"
                            >{{ $statusValue }}</span>
                        </div>

                        {{-- Baris 2: Pelanggan & Layanan --}}
                        <div class="rounded-xl bg-slate-50/80 border border-slate-200/80 p-3 text-xs shadow-2xs">
                            <div class="flex items-center justify-between font-bold text-slate-800">
                                <span>{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</span>
                                <span class="font-normal font-mono text-slate-500 tabular-nums">{{ $transaction->customer?->phone ?? '-' }}</span>
                            </div>
                            <div class="mt-1.5 flex items-center justify-between text-slate-600">
                                <span class="line-clamp-1 font-medium">{{ $firstService }}</span>
                                <span class="font-bold font-mono text-slate-800 shrink-0 tabular-nums">{{ number_format((float) $totalWeight, 1, ',', '.') }} kg</span>
                            </div>
                        </div>

                        {{-- Baris 3: Estimasi Waktu jika ada --}}
                        @if ($transaction->estimated_completed_at)
                            <div class="flex items-center gap-1.5 text-xs">
                                <x-lucide-clock class="size-3.5 {{ $isOverdue ? 'text-rose-500' : 'text-slate-400' }}" aria-hidden="true" />
                                @if ($isOverdue)
                                    <span class="font-bold text-rose-600 font-mono tabular-nums">
                                        Terlambat ({{ $transaction->estimated_completed_at->format('d/m H:i') }})
                                    </span>
                                @else
                                    <span class="text-slate-500 font-mono tabular-nums">
                                        Est: {{ $transaction->estimated_completed_at->format('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Baris 4: Total & Tombol Aksi --}}
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3" @click.stop>
                            <div>
                                <x-ui.money :value="$transaction->total_amount" class="text-sm font-black text-slate-900 font-mono tabular-nums" />
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-2xs font-semibold ring-1 ring-inset"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20': (trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}') === 'Lunas',
                                            'bg-amber-50 text-amber-800 ring-amber-600/25': (trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}') === 'DP',
                                            'bg-rose-50 text-rose-700 ring-rose-600/20': (trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}') === 'Belum Bayar'
                                        }"
                                        x-text="trxMap[{{ $transaction->id }}]?.payment_status ?? '{{ $paymentStatusValue }}'"
                                    >{{ $paymentStatusValue }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="openDrawer({{ $transaction->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-brand-50 hover:text-brand-700 transition"
                                >
                                    <x-lucide-sliders-horizontal class="size-3.5" />
                                    <span>Panel</span>
                                </button>
                                <x-ui.button
                                    :href="route($routePrefix . '.transactions.print', $transaction)"
                                    variant="secondary"
                                    size="xs"
                                    target="_blank"
                                    rel="noopener"
                                    class="cursor-pointer shadow-2xs active:scale-95"
                                >
                                    <x-lucide-printer class="size-3.5 text-slate-500" aria-hidden="true" />
                                    <span>Cetak</span>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 shadow-2xs">
                            <x-lucide-receipt class="size-5" aria-hidden="true" />
                        </div>
                        <p class="mt-2.5 text-sm font-bold text-slate-800">Belum ada transaksi</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Transaksi baru akan muncul di sini setelah kasir menyimpan nota.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Paginasi --}}
            @if ($transactions->hasPages())
                <div class="border-t border-slate-100 px-5 py-3.5 bg-slate-50/50">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
        {{-- Slide-Over Sheet Drawer: Impeccable Operate Inspector --}}
        <x-ui.sheet show="isDrawerOpen" name="transaction-detail" maxWidth="xl">
            <x-slot:header>
                <div class="flex items-center justify-between gap-4 w-full pr-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-base font-bold tracking-tight text-slate-900" x-text="selectedTrx?.code"></span>
                            <button
                                type="button"
                                @click="copyCode(selectedTrx?.code)"
                                class="rounded-md p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                :title="copied ? 'Tersalin' : 'Salin kode nota'"
                                aria-label="Salin kode nota"
                            >
                                <x-lucide-copy x-show="!copied" class="size-3.5" />
                                <x-lucide-check x-show="copied" x-cloak class="size-3.5 text-emerald-600" />
                            </button>
                        </div>
                        <p class="mt-0.5 text-2xs text-slate-500 font-mono" x-text="'Dibuat: ' + (selectedTrx?.created_at || '-')"></p>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="font-mono text-base font-bold tracking-tight text-slate-900 tabular-nums" x-text="selectedTrx?.total_formatted"></p>
                        <div class="mt-0.5 flex items-center justify-end">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-2xs font-semibold ring-1 ring-inset"
                                :class="{
                                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20': selectedTrx?.payment_status === 'Lunas',
                                    'bg-amber-50 text-amber-800 ring-amber-600/25': selectedTrx?.payment_status === 'DP',
                                    'bg-rose-50 text-rose-700 ring-rose-600/20': selectedTrx?.payment_status === 'Belum Bayar'
                                }"
                                x-text="selectedTrx?.payment_status"
                            ></span>
                        </div>
                    </div>
                </div>
            </x-slot:header>

            <div class="space-y-5">

                {{-- 1. ACTION & LIFECYCLE STEPPER --}}
                <template x-if="selectedTrx?.status !== 'Dibatalkan'">
                    <div class="rounded-2xl border border-slate-200/90 bg-gradient-to-b from-slate-50/70 via-white to-white p-4 shadow-2xs space-y-4">
                        {{-- Visual Mini Progress Stepper --}}
                        <div class="relative px-2 pt-1">
                            {{-- Connecting track --}}
                            <div class="absolute top-4.5 left-8 right-8 h-1 bg-slate-200/90 rounded-full -z-0">
                                <div
                                    class="h-full bg-brand-600 rounded-full transition-all duration-500 ease-out"
                                    :style="'width: ' + (Math.max(0, Math.min(3, rank(selectedTrx?.status) - 1)) / 3 * 100) + '%'"
                                ></div>
                            </div>

                            {{-- 4 Step Nodes --}}
                            <div class="relative z-10 flex items-center justify-between">
                                @foreach([
                                    ['key' => 'Diterima', 'num' => 1, 'label' => 'Diterima'],
                                    ['key' => 'Diproses', 'num' => 2, 'label' => 'Diproses'],
                                    ['key' => 'Selesai', 'num' => 3, 'label' => 'Siap Ambil'],
                                    ['key' => 'Diambil', 'num' => 4, 'label' => 'Diambil'],
                                ] as $st)
                                    <div class="flex flex-col items-center text-center">
                                        <div
                                            class="size-7 sm:size-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                            :class="{
                                                'bg-brand-600 text-white shadow-xs ring-4 ring-brand-100 scale-105': selectedTrx?.status === '{{ $st['key'] }}',
                                                'bg-brand-700 text-white': rank(selectedTrx?.status) > {{ $st['num'] }},
                                                'bg-white text-slate-400 border-2 border-slate-200': rank(selectedTrx?.status) < {{ $st['num'] }}
                                            }"
                                        >
                                            <template x-if="rank(selectedTrx?.status) > {{ $st['num'] }}">
                                                <x-lucide-check class="size-3.5 stroke-[2.5]" />
                                            </template>
                                            <template x-if="rank(selectedTrx?.status) <= {{ $st['num'] }}">
                                                <span>{{ $st['num'] }}</span>
                                            </template>
                                        </div>
                                        <span
                                            class="mt-1.5 text-[11px] font-semibold leading-tight tracking-tight transition-colors"
                                            :class="{
                                                'text-brand-900 font-extrabold': selectedTrx?.status === '{{ $st['key'] }}',
                                                'text-slate-700 font-medium': rank(selectedTrx?.status) > {{ $st['num'] }},
                                                'text-slate-400 font-normal': rank(selectedTrx?.status) < {{ $st['num'] }}
                                            }"
                                        >{{ $st['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Peringatan Cucian Lewat Batas Simpan 14 Hari --}}
                        <template x-if="selectedTrx?.is_storage_overdue">
                            <div class="rounded-xl border border-amber-200/90 bg-amber-50/90 p-3 text-xs text-amber-950 flex items-start justify-between gap-3 shadow-2xs">
                                <div class="flex items-start gap-2 min-w-0">
                                    <x-lucide-clock class="size-4 text-amber-600 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-bold text-xs text-amber-950">Lewat Batas Simpan (<span x-text="selectedTrx?.days_in_storage"></span> hari di rak)</p>
                                        <p class="text-2xs text-amber-800 mt-0.5">Cucian sudah selesai lebih dari 14 hari namun belum diambil pelanggan.</p>
                                    </div>
                                </div>
                                <template x-if="selectedTrx?.wa_reminder_url">
                                    <a
                                        :href="selectedTrx?.wa_reminder_url"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-2.5 py-1 text-2xs font-bold text-white hover:bg-emerald-700 transition shadow-2xs shrink-0 cursor-pointer"
                                        title="Kirim pesan pengingat WhatsApp ke pelanggan"
                                    >
                                        <x-lucide-message-circle class="size-3" />
                                        <span>Kirim WA</span>
                                    </a>
                                </template>
                            </div>
                        </template>

                        {{-- Primary 1-Click Operational Action --}}
                        <div class="pt-1">
                            {{-- Diterima -> Mulai Cuci --}}
                            <template x-if="selectedTrx?.status === 'Diterima'">
                                <button
                                    type="button"
                                    @click="advanceStatus('Diproses')"
                                    :disabled="isSubmitting"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-brand-700 transition active:scale-98 disabled:opacity-50 cursor-pointer"
                                >
                                    <template x-if="isSubmitting">
                                        <x-lucide-loader-2 class="size-4 animate-spin text-white" />
                                    </template>
                                    <template x-if="!isSubmitting">
                                        <x-lucide-play class="size-4 text-white fill-white" />
                                    </template>
                                    <span>Mulai Proses Cuci (Pindahkan ke Diproses)</span>
                                </button>
                            </template>

                            {{-- Diproses -> Selesaikan Cucian --}}
                            <template x-if="selectedTrx?.status === 'Diproses'">
                                <button
                                    type="button"
                                    @click="advanceStatus('Selesai')"
                                    :disabled="isSubmitting"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-brand-700 transition active:scale-98 disabled:opacity-50 cursor-pointer"
                                >
                                    <template x-if="isSubmitting">
                                        <x-lucide-loader-2 class="size-4 animate-spin text-white" />
                                    </template>
                                    <template x-if="!isSubmitting">
                                        <x-lucide-check-circle-2 class="size-4 text-white" />
                                    </template>
                                    <span>Selesaikan Cucian (Tandai Siap Diambil)</span>
                                </button>
                            </template>

                            {{-- Selesai -> Serahkan ke Pelanggan --}}
                            <template x-if="selectedTrx?.status === 'Selesai'">
                                <div>
                                    <template x-if="(selectedTrx?.remaining_amount || 0) <= 0">
                                        <button
                                            type="button"
                                            @click="advanceStatus('Diambil')"
                                            :disabled="isSubmitting"
                                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 transition active:scale-98 disabled:opacity-50 cursor-pointer"
                                        >
                                            <template x-if="isSubmitting">
                                                <x-lucide-loader-2 class="size-4 animate-spin text-white" />
                                            </template>
                                            <template x-if="!isSubmitting">
                                                <x-lucide-package-check class="size-4 text-white" />
                                            </template>
                                            <span>Serahkan Cucian ke Pelanggan (Lunas)</span>
                                        </button>
                                    </template>

                                    <template x-if="(selectedTrx?.remaining_amount || 0) > 0">
                                        <div class="rounded-xl border border-amber-200/90 bg-amber-50/90 p-3 text-xs text-amber-900 flex items-start gap-2.5">
                                            <x-lucide-alert-triangle class="size-4 text-amber-600 shrink-0 mt-0.5" />
                                            <div class="flex-1 min-w-0">
                                                <p class="font-bold text-xs text-amber-950">Menunggu Pelunasan Tagihan</p>
                                                <p class="text-2xs text-amber-800 mt-0.5">Cucian telah selesai, namun belum dapat diserahkan sebelum sisa tagihan lunas.</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Diambil (Final) --}}
                            <template x-if="selectedTrx?.status === 'Diambil'">
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-3 text-xs text-emerald-900 flex items-center gap-2.5">
                                    <x-lucide-check-circle-2 class="size-4 text-emerald-600 shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-xs text-emerald-950">Cucian Telah Diambil & Selesai</p>
                                        <p class="text-2xs text-emerald-800 mt-0.5">Status transaksi sudah tuntas dan terkunci permanen.</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Koreksi Mundur / Batal (Progressive disclosure) --}}
                        <template x-if="!isTerminal()">
                            <div class="pt-1 border-t border-slate-100">
                                <button
                                    type="button"
                                    @click="showAdvancedStatus = !showAdvancedStatus"
                                    class="inline-flex items-center gap-1.5 text-2xs font-medium text-slate-500 hover:text-slate-800 transition cursor-pointer"
                                >
                                    <x-lucide-history class="size-3" />
                                    <span x-text="showAdvancedStatus ? 'Tutup koreksi status manual' : 'Koreksi status mundur atau batalkan nota...'"></span>
                                </button>

                                <div x-show="showAdvancedStatus" x-cloak class="mt-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                                    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
                                        @foreach (['Diterima', 'Diproses', 'Selesai', 'Diambil', 'Dibatalkan'] as $stOption)
                                            <label class="relative" :class="canSelectStatus('{{ $stOption }}') ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'">
                                                <input
                                                    type="radio"
                                                    name="advanced_status"
                                                    value="{{ $stOption }}"
                                                    x-model="newStatus"
                                                    :disabled="!canSelectStatus('{{ $stOption }}')"
                                                    class="sr-only"
                                                >
                                                <div
                                                    class="rounded-lg border p-1.5 text-center text-xs font-semibold transition"
                                                    :class="{
                                                        'border-brand-600 bg-brand-50 text-brand-700 ring-1 ring-brand-400': newStatus === '{{ $stOption }}' && '{{ $stOption }}' !== 'Dibatalkan',
                                                        'border-rose-600 bg-rose-50 text-rose-700 ring-rose-400': newStatus === '{{ $stOption }}' && '{{ $stOption }}' === 'Dibatalkan',
                                                        'border-slate-200 bg-white text-slate-600 hover:bg-slate-50': newStatus !== '{{ $stOption }}' && canSelectStatus('{{ $stOption }}'),
                                                        'border-slate-200 bg-slate-100 text-slate-400': !canSelectStatus('{{ $stOption }}')
                                                    }"
                                                >
                                                    <span>{{ $stOption }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div x-show="isRollback(newStatus)" x-cloak class="text-2xs text-amber-800 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                                        Wajib isi memo di bawah sebagai alasan koreksi status mundur.
                                    </div>

                                    <div x-show="newStatus === 'Dibatalkan'" x-cloak class="text-2xs text-rose-800 bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                                        Wajib isi memo di bawah sebagai alasan pembatalan permanen.
                                    </div>

                                    <button
                                        type="button"
                                        @click="advanceStatus(newStatus)"
                                        :disabled="!canSubmitStatus() || isSubmitting"
                                        class="w-full rounded-lg py-2 text-xs font-semibold shadow-xs transition active:scale-98 disabled:opacity-50"
                                        :class="canSubmitStatus()
                                            ? (newStatus === 'Dibatalkan' ? 'bg-rose-600 hover:bg-rose-700 text-white cursor-pointer' : 'bg-slate-900 hover:bg-slate-800 text-white cursor-pointer')
                                            : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                    >
                                        <span x-show="newStatus === 'Dibatalkan'">Konfirmasi Batalkan Nota</span>
                                        <span x-show="newStatus !== 'Dibatalkan'">Terapkan Status Terpilih</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Banner Khusus Transaksi Dibatalkan --}}
                <template x-if="selectedTrx?.status === 'Dibatalkan'">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4 text-xs text-rose-900 flex items-start gap-3 shadow-2xs">
                        <x-lucide-ban class="size-5 text-rose-600 shrink-0 mt-0.5" />
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="font-bold text-sm text-rose-950">Transaksi Dibatalkan</p>
                            <p class="text-xs text-rose-800">Nota transaksi ini telah dibatalkan permanen dan tidak dapat diproses lagi.</p>
                            <template x-if="selectedTrx?.notes">
                                <p class="text-2xs text-rose-700 bg-white/70 p-2 rounded-lg border border-rose-200/60 font-mono mt-1.5" x-text="'Alasan: ' + selectedTrx?.notes"></p>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- 2. CUSTOMER & ORDER QUICK OVERVIEW --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 space-y-3.5 shadow-2xs">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="size-9 rounded-xl bg-brand-50 border border-brand-200/60 text-brand-700 font-bold text-xs flex items-center justify-center font-mono shrink-0 shadow-2xs" x-text="selectedTrx?.initials || 'P'"></div>
                            <div class="min-w-0">
                                <p class="font-bold text-xs text-slate-900 truncate" x-text="selectedTrx?.customer_name"></p>
                                <p class="text-2xs text-slate-500 truncate mt-0.5" x-text="selectedTrx?.customer_address !== '-' ? selectedTrx?.customer_address : 'Alamat belum diatur'"></p>
                            </div>
                        </div>

                        <template x-if="selectedTrx?.customer_phone && selectedTrx?.customer_phone !== '-'">
                            <a
                                :href="'https://wa.me/' + selectedTrx?.customer_phone?.replace(/[^0-9]/g, '')"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/70 px-2.5 py-1 text-2xs font-semibold text-emerald-700 hover:bg-emerald-100 transition shadow-2xs shrink-0 cursor-pointer"
                                title="Kirim pesan WhatsApp"
                            >
                                <x-lucide-message-circle class="size-3 text-emerald-600" />
                                <span x-text="selectedTrx?.customer_phone"></span>
                            </a>
                        </template>
                    </div>

                    {{-- Quick Operational Specs --}}
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                            <span class="text-2xs font-medium text-slate-500 block">Total Bobot Cucian</span>
                            <span class="font-mono text-xs font-bold text-slate-900 mt-0.5 block" x-text="selectedTrx?.total_weight + ' kg'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                            <span class="text-2xs font-medium text-slate-500 block">Estimasi Selesai</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-2xs font-bold text-slate-900" x-text="selectedTrx?.estimated_at || '-'"></span>
                                <template x-if="selectedTrx?.is_overdue">
                                    <span class="inline-flex items-center rounded bg-rose-100 px-1 py-0.2 text-[9px] font-bold text-rose-700">Lewat</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. FINANCIAL & PAYMENT PROCESSING HUB --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 space-y-3.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 tracking-tight">Status Pembayaran</span>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-2xs font-semibold ring-1 ring-inset"
                            :class="{
                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20': selectedTrx?.payment_status === 'Lunas',
                                'bg-amber-50 text-amber-800 ring-amber-600/25': selectedTrx?.payment_status === 'DP',
                                'bg-rose-50 text-rose-700 ring-rose-600/20': selectedTrx?.payment_status === 'Belum Bayar'
                            }"
                            x-text="selectedTrx?.payment_status"
                        ></span>
                    </div>

                    {{-- Financial Breakdown Snapshot --}}
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2.5 rounded-xl bg-slate-50/80 border border-slate-100">
                            <span class="text-[10px] text-slate-500 font-medium block">Total Biaya</span>
                            <span class="font-mono text-xs font-bold text-slate-900 tabular-nums mt-0.5 block" x-text="selectedTrx?.total_formatted"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                            <span class="text-[10px] text-emerald-700 font-medium block">Terbayar</span>
                            <span class="font-mono text-xs font-bold text-emerald-700 tabular-nums mt-0.5 block" x-text="selectedTrx?.paid_formatted"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border"
                             :class="(selectedTrx?.remaining_amount || 0) > 0 ? 'bg-rose-50/60 border-rose-100' : 'bg-slate-50/80 border-slate-100'">
                            <span class="text-[10px] font-medium block" :class="(selectedTrx?.remaining_amount || 0) > 0 ? 'text-rose-700 font-bold' : 'text-slate-500'">Sisa Tagihan</span>
                            <span class="font-mono text-xs font-bold tabular-nums mt-0.5 block"
                                  :class="(selectedTrx?.remaining_amount || 0) > 0 ? 'text-rose-700' : 'text-slate-900'"
                                  x-text="selectedTrx?.remaining_formatted"></span>
                        </div>
                    </div>

                    {{-- 1-Click Pelunasan Tagihan (Jika masih ada sisa) --}}
                    <template x-if="(selectedTrx?.remaining_amount || 0) > 0 && !isTerminal()">
                        <div class="space-y-2 pt-1 border-t border-slate-100">
                            <button
                                type="button"
                                @click="quickPayExact()"
                                :disabled="isSubmitting"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-slate-800 transition active:scale-98 disabled:opacity-50 cursor-pointer"
                            >
                                <template x-if="isSubmitting">
                                    <x-lucide-loader-2 class="size-4 animate-spin text-white" />
                                </template>
                                <template x-if="!isSubmitting">
                                    <x-lucide-banknote class="size-4 text-emerald-400" />
                                </template>
                                <span>Pelunasan Tunai Cepat: <span class="font-mono font-bold" x-text="selectedTrx?.remaining_formatted"></span></span>
                            </button>

                            <div class="text-center">
                                <button
                                    type="button"
                                    @click="showCustomPay = !showCustomPay"
                                    class="inline-flex items-center gap-1 text-2xs font-medium text-slate-500 hover:text-slate-800 transition cursor-pointer"
                                >
                                    <x-lucide-sliders-horizontal class="size-3" />
                                    <span x-text="showCustomPay ? 'Tutup opsi pembayaran lain' : 'Opsi bayar lain (Transfer, QRIS, Cicil)...'"></span>
                                </button>
                            </div>

                            {{-- Form Bayar Kustom --}}
                            <div x-show="showCustomPay" x-cloak class="pt-2 border-t border-slate-100 space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Metode Bayar</label>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="custom_pay_method" value="Tunai" x-model="payMethod" class="sr-only">
                                            <div class="rounded-lg border text-center py-1.5 text-xs font-semibold transition"
                                                :class="payMethod === 'Tunai' ? 'border-brand-600 bg-brand-600 text-white shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                                Tunai
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="custom_pay_method" value="Transfer" x-model="payMethod" class="sr-only">
                                            <div class="rounded-lg border text-center py-1.5 text-xs font-semibold transition"
                                                :class="payMethod === 'Transfer' ? 'border-brand-600 bg-brand-600 text-white shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                                Transfer
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="custom_pay_method" value="QRIS" x-model="payMethod" class="sr-only">
                                            <div class="rounded-lg border text-center py-1.5 text-xs font-semibold transition"
                                                :class="payMethod === 'QRIS' ? 'border-brand-600 bg-brand-600 text-white shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                                                QRIS
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Nominal (Rp)</label>
                                    <input
                                        type="number"
                                        x-model="payAmount"
                                        min="1"
                                        :max="selectedTrx?.remaining_amount"
                                        class="h-8 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs font-semibold text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                    >
                                </div>

                                <template x-if="payMethod === 'Tunai'">
                                    <div class="space-y-1.5 pt-1">
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Uang Diterima (Rp)</label>
                                        <input
                                            type="number"
                                            x-model="cashReceived"
                                            class="h-8 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs font-semibold text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                        >
                                        <div class="flex items-center justify-between text-xs font-medium text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-200/80">
                                            <span>Kembalian:</span>
                                            <span class="font-mono font-bold text-emerald-700" x-text="'Rp ' + Number(changeAmount).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </template>

                                <button
                                    type="button"
                                    @click="submitCustomPayment()"
                                    :disabled="isSubmitting"
                                    class="w-full rounded-lg bg-brand-600 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 transition active:scale-98 cursor-pointer disabled:opacity-50"
                                >
                                    <span x-show="isSubmitting" class="inline-flex items-center gap-1.5">
                                        <x-lucide-loader-2 class="size-3.5 animate-spin" />
                                        <span>Menyimpan...</span>
                                    </span>
                                    <span x-show="!isSubmitting">Simpan Pembayaran</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 4. MEMO / CATATAN KHUSUS CUCIAN --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 space-y-2.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <x-lucide-sticky-note class="size-3.5 text-slate-400" />
                            <span class="text-xs font-bold text-slate-900 tracking-tight">Memo Catatan Cucian</span>
                        </div>
                        <button
                            type="button"
                            @click="saveNotes()"
                            :disabled="isSubmitting"
                            class="inline-flex items-center gap-1 rounded-lg bg-brand-50 border border-brand-200/80 px-2.5 py-1 text-2xs font-semibold text-brand-700 hover:bg-brand-100 transition active:scale-98 cursor-pointer disabled:opacity-50"
                        >
                            <x-lucide-check class="size-3" />
                            <span>Simpan Memo</span>
                            <span class="text-[10px] text-brand-600 font-mono hidden sm:inline">(Ctrl+Enter)</span>
                        </button>
                    </div>
                    <textarea
                        x-model="statusNotes"
                        @keydown.ctrl.enter.prevent="saveNotes()"
                        @keydown.meta.enter.prevent="saveNotes()"
                        rows="2"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition resize-none"
                        placeholder="Contoh: Pakaian luntur dipisah, rak B-04, noda minyak di kerah..."
                    ></textarea>
                </div>

                {{-- 5. SEGMENTED TABS (Item Layanan vs Riwayat Pembayaran) --}}
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 space-y-3.5 shadow-2xs">
                    <x-ui.tabs variant="segmented" class="grid grid-cols-2 w-full">
                        <x-ui.tab name="items" model="detailTab" icon="package" class="justify-center">
                            <span>Item Layanan (<span x-text="selectedTrx?.items?.length || 0"></span>)</span>
                        </x-ui.tab>
                        <x-ui.tab name="payments" model="detailTab" icon="credit-card" class="justify-center">
                            <span>Riwayat Bayar (<span x-text="selectedTrx?.payments?.length || 0"></span>)</span>
                        </x-ui.tab>
                    </x-ui.tabs>

                    {{-- Item list --}}
                    <div x-show="detailTab === 'items'" class="divide-y divide-slate-100">
                        <template x-for="(item, idx) in selectedTrx?.items" :key="idx">
                            <div class="py-2.5 text-xs first:pt-1 last:pb-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-slate-900" x-text="item.service"></p>
                                    <p class="font-mono font-bold text-slate-900 tabular-nums" x-text="item.subtotal_formatted"></p>
                                </div>
                                <div class="flex items-center justify-between mt-0.5">
                                    <p class="text-2xs text-slate-500 font-mono" x-text="item.weight + ' kg' + (item.price_formatted ? ' · ' + item.price_formatted + '/kg' : '')"></p>
                                    <template x-if="item.notes">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200/60 font-medium">
                                            <x-lucide-tag class="size-2.5" />
                                            <span x-text="item.notes"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedTrx?.items || selectedTrx?.items.length === 0">
                            <p class="text-xs text-slate-400 italic py-3 text-center">Tidak ada item layanan yang tercatat.</p>
                        </template>
                    </div>

                    {{-- Payment list --}}
                    <div x-show="detailTab === 'payments'" class="divide-y divide-slate-100">
                        <template x-for="(pay, pIdx) in selectedTrx?.payments" :key="pIdx">
                            <div class="flex items-center justify-between py-2.5 text-xs first:pt-1 last:pb-1">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block rounded-md bg-slate-100 border border-slate-200/80 px-1.5 py-0.5 font-mono text-[10px] font-bold text-slate-700" x-text="pay.method"></span>
                                        <span class="text-2xs text-slate-400 font-mono" x-text="pay.created_at"></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-mono font-bold text-emerald-700 tabular-nums" x-text="pay.amount_formatted"></p>
                                    <p class="text-[10px] text-slate-400 font-medium" x-text="pay.status"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedTrx?.payments || selectedTrx?.payments.length === 0">
                            <div class="py-4 text-center">
                                <x-lucide-receipt class="size-5 text-slate-300 mx-auto mb-1" />
                                <p class="text-xs text-slate-400 italic">Belum ada riwayat pembayaran yang tercatat.</p>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <x-slot:footer>
                <div class="flex items-center justify-between w-full">
                    <a
                        :href="selectedTrx?.show_url"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition"
                    >
                        <x-lucide-external-link class="size-3.5 text-slate-400" />
                        <span>Buka Detail Lengkap</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="closeDrawer()"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer"
                        >
                            <span>Tutup</span>
                            <kbd class="font-mono text-[10px] bg-slate-200/70 text-slate-600 px-1 py-0.2 rounded border border-slate-300/60">ESC</kbd>
                        </button>
                        <a
                            :href="selectedTrx?.print_url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-slate-800 transition active:scale-95"
                        >
                            <x-lucide-printer class="size-3.5" />
                            <span>Cetak Struk</span>
                        </a>
                    </div>
                </div>
            </x-slot:footer>
        </x-ui.sheet>

        {{-- Toast Notifikasi Mengambang --}}
        <div
            x-cloak
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-3 scale-95"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 rounded-xl border bg-white px-4 py-3 shadow-lg ring-1 ring-slate-900/5 max-w-sm"
            :class="toast.type === 'error' ? 'border-rose-200 text-rose-900' : 'border-slate-200 text-slate-900'"
        >
            <template x-if="toast.type === 'success'">
                <x-lucide-check-circle-2 class="size-4 text-emerald-600 shrink-0" />
            </template>
            <template x-if="toast.type === 'error'">
                <x-lucide-alert-circle class="size-4 text-rose-600 shrink-0" />
            </template>
            <span class="text-xs font-semibold" x-text="toast.message"></span>
        </div>

        {{-- Floating Bulk Action Bar --}}
        <div
            x-cloak
            x-show="selectedIds.length > 0"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 inset-x-0 mx-auto z-40 w-fit max-w-lg rounded-2xl border border-slate-200/90 bg-white/95 px-4 py-3 shadow-2xl backdrop-blur-md flex items-center gap-3 sm:gap-4 ring-1 ring-slate-900/5"
        >
            <div class="flex items-center gap-2">
                <span class="flex size-6 items-center justify-center rounded-lg bg-brand-600 font-mono text-xs font-bold text-white" x-text="selectedIds.length"></span>
                <span class="text-xs font-semibold text-slate-700">nota dipilih</span>
            </div>
            <div class="h-4 w-px bg-slate-200"></div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="selectedIds = []; selectAll = false;"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition active:scale-95 cursor-pointer"
                >
                    <x-lucide-printer class="size-3.5" />
                    <span>Cetak Terpilih</span>
                </button>
            </div>
        </div>

    </div>

</x-layouts::app>
