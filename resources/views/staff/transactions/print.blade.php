@php
    $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus
        ? $transaction->status->value
        : (string) $transaction->status;

    $lookupUrl = route('transactions.lookup', ['code' => $transaction->transaction_code]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Nota - {{ $transaction->transaction_code }} - CUCI.IN</title>
    <style id="base-receipt-style">
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000000;
            background-color: #f1f5f9;
            padding: 24px 16px 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Action Bar (Kasir Toolbar) */
        .actions-bar {
            width: 100%;
            max-width: 640px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .bar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bar-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .bar-meta {
            font-size: 11px;
            color: #64748b;
        }

        .bar-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Segmented paper size switcher */
        .segmented-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .paper-size-btn {
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 6px;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .paper-size-btn.active {
            background: #ffffff;
            color: #0e7490;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            font-weight: 700;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            transition: all 0.15s ease;
        }

        .btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-primary {
            background: #0e7490;
            border-color: #0e7490;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #155e75;
            border-color: #155e75;
            color: #ffffff;
        }

        .btn-kbd {
            font-size: 9px;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.15);
            color: inherit;
            font-family: inherit;
        }

        /* Thermal Receipt Paper Card */
        .receipt-container {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            color: #000000;
            transition: width 0.2s ease;
        }

        /* Paper 80mm (Desktop POS) */
        .receipt-container.paper-80mm {
            width: 80mm;
            max-width: 100%;
            padding: 4.5mm 4.5mm 6mm;
            font-size: 11px;
        }

        .receipt-container.paper-80mm .header-title {
            font-size: 16px;
        }

        .receipt-container.paper-80mm .total-row {
            font-size: 13px;
        }

        /* Paper 58mm (Mini POS / Bluetooth) */
        .receipt-container.paper-58mm {
            width: 58mm;
            max-width: 100%;
            padding: 3mm 2.5mm 5mm;
            font-size: 9.5px;
            line-height: 1.35;
        }

        .receipt-container.paper-58mm .header-title {
            font-size: 13.5px;
            letter-spacing: 0.04em;
        }

        .receipt-container.paper-58mm .header-subtitle {
            font-size: 8.5px;
        }

        .receipt-container.paper-58mm .meta-row {
            font-size: 9.5px;
            margin-bottom: 2px;
        }

        .receipt-container.paper-58mm .item-header {
            font-size: 9.5px;
        }

        .receipt-container.paper-58mm .item-sub {
            font-size: 9px;
        }

        .receipt-container.paper-58mm .total-row {
            font-size: 11px;
        }

        .receipt-container.paper-58mm .footer-note {
            font-size: 8.5px;
        }

        /* Shared Receipt Elements */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .uppercase { text-transform: uppercase; }

        .header-title {
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #000000;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .header-subtitle {
            font-size: 9.5px;
            color: #000000;
            margin-top: 2px;
            line-height: 1.3;
        }

        .divider {
            border: none;
            border-top: 1px dashed #000000;
            margin: 7px 0;
            height: 0;
        }

        .divider-dotted {
            border: none;
            border-top: 1px dotted #000000;
            margin: 6px 0;
            height: 0;
        }

        .barcode-section {
            padding: 4px 0 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
            margin-bottom: 2.5px;
        }

        .meta-label {
            color: #000000;
            white-space: nowrap;
        }

        .meta-value {
            font-weight: 700;
            color: #000000;
            text-align: right;
            word-break: break-word;
        }

        .item-list {
            margin: 4px 0;
        }

        .item-row {
            margin-bottom: 6px;
        }

        .item-row:last-child {
            margin-bottom: 0;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
            font-weight: 700;
        }

        .item-sub {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #000000;
            margin-top: 1px;
        }

        .total-section {
            margin-top: 4px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px dashed #000000;
            padding-top: 5px;
            margin-top: 4px;
            font-weight: 800;
            color: #000000;
        }

        .badge-status {
            display: inline-block;
            border: 1px solid #000000;
            padding: 1px 5px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: #ffffff;
            color: #000000;
        }

        .footer-note {
            margin-top: 8px;
            font-size: 9px;
            color: #000000;
            text-align: center;
            line-height: 1.4;
        }

        .qr-section {
            margin: 8px 0 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .tear-off-line {
            text-align: center;
            font-size: 8.5px;
            color: #000000;
            letter-spacing: 0.1em;
            margin: 10px 0 4px;
            user-select: none;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .receipt-container {
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 auto !important;
            }

            .receipt-container.paper-80mm {
                width: 80mm !important;
                max-width: 80mm !important;
                padding: 3mm 4mm !important;
            }

            .receipt-container.paper-58mm {
                width: 58mm !important;
                max-width: 58mm !important;
                padding: 2mm 2.5mm !important;
            }
        }
    </style>
    <style id="dynamic-page-style">
        @page {
            size: 80mm auto;
            margin: 0;
        }
    </style>
</head>
<body>

    {{-- Kasir Action Toolbar (Layar Monitor Kasir, Disembunyikan Saat Cetak) --}}
    <div class="actions-bar no-print">
        <div class="bar-left">
            <x-lucide-receipt class="w-5 h-5 text-teal-600 inline-block" style="width:20px;height:20px;color:#0e7490;" />
            <div>
                <div class="bar-title">Struk Kasir Thermal</div>
                <div class="bar-meta">{{ $transaction->transaction_code }} · {{ $transaction->customer?->name ?? 'Guest' }}</div>
            </div>
        </div>

        <div class="bar-controls">
            {{-- Switcher Format Kertas --}}
            <div class="segmented-group" title="Pilih lebar kertas printer kasir Anda">
                <button type="button" class="paper-size-btn" data-size="58mm" onclick="setPaperSize('58mm')">
                    58mm <span class="btn-kbd">1</span>
                </button>
                <button type="button" class="paper-size-btn active" data-size="80mm" onclick="setPaperSize('80mm')">
                    80mm <span class="btn-kbd">2</span>
                </button>
            </div>

            {{-- Tombol Kembali ke Detail Transaksi --}}
            <a href="{{ route('staff.transactions.show', $transaction) }}" class="btn" title="Kembali ke halaman detail nota">
                <x-lucide-arrow-left style="width:14px;height:14px;" />
                Kembali
            </a>

            {{-- Tombol Cetak Struk --}}
            <button type="button" onclick="window.print()" class="btn btn-primary" title="Cetak struk kasir (Ctrl+P)">
                <x-lucide-printer style="width:14px;height:14px;" />
                Print Struk
                <span class="btn-kbd" style="background:rgba(255,255,255,0.25);">Ctrl+P</span>
            </button>

            {{-- Tombol Tutup --}}
            <button type="button" onclick="window.close()" class="btn" title="Tutup jendela cetak (Esc)">
                <x-lucide-x style="width:14px;height:14px;" />
                Tutup
                <span class="btn-kbd">Esc</span>
            </button>
        </div>
    </div>

    {{-- Kertas Struk Kasir Thermal --}}
    <div id="receipt" class="receipt-container paper-80mm">
        {{-- Header Outlet Laundry --}}
        <div class="text-center">
            <h1 class="header-title">CUCI.IN LAUNDRY</h1>
            <p class="header-subtitle">Layanan Laundry Bersih, Cepat & Terpercaya</p>
            <p class="header-subtitle">Telp/WA: 0812-3456-7890</p>
        </div>

        <div class="divider"></div>

        {{-- Barcode Code128 Nomor Nota (Siap Scan Barcode Gun Kasir) --}}
        <div class="barcode-section">
            <x-ui.barcode :code="$transaction->transaction_code" :height="34" />
        </div>

        <div class="divider"></div>

        {{-- Meta Nota & Pelanggan --}}
        <div>
            <div class="meta-row">
                <span class="meta-label">No. Nota:</span>
                <span class="meta-value font-bold">{{ $transaction->transaction_code }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Tanggal:</span>
                <span class="meta-value">{{ $transaction->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Kasir:</span>
                <span class="meta-value">{{ $transaction->creator?->name ?? 'Staff Kasir' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Pelanggan:</span>
                <span class="meta-value">{{ $transaction->customer?->name ?? 'Guest' }}</span>
            </div>
            @if ($transaction->customer?->phone)
                <div class="meta-row">
                    <span class="meta-label">No. Telepon:</span>
                    <span class="meta-value">{{ $transaction->customer->phone }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Daftar Item / Rincian Layanan --}}
        <div class="item-list">
            @forelse ($transaction->details as $detail)
                <div class="item-row">
                    <div class="item-header">
                        <span>{{ $detail->service?->name ?? 'Layanan' }}</span>
                        <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="item-sub">
                        <span>{{ number_format((float) $detail->weight, 2, ',', '.') }} {{ $detail->unit ?? 'kg' }} &times; Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center font-bold" style="font-size:10px;padding:6px 0;">
                    Tidak ada rincian item
                </div>
            @endforelse
        </div>

        <div class="divider"></div>

        {{-- Total & Rincian Pembayaran --}}
        <div class="total-section">
            <div class="meta-row">
                <span class="meta-label">Total Tagihan:</span>
                <span class="meta-value font-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Sudah Dibayar:</span>
                <span class="meta-value">Rp {{ number_format($transaction->total_paid, 0, ',', '.') }}</span>
            </div>

            @foreach ($transaction->payments->sortBy('id') as $payment)
                <div class="meta-row" style="margin-top: 3px;">
                    <span class="meta-label">{{ $payment->payment_type }} · {{ $payment->method }}</span>
                    <span class="meta-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                @if ($payment->cash_received !== null)
                    <div class="meta-row">
                        <span class="meta-label">Diterima / Kembali</span>
                        <span class="meta-value">
                            Rp {{ number_format($payment->cash_received, 0, ',', '.') }} /
                            Rp {{ number_format($payment->change_amount, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            @endforeach

            <div class="total-row">
                <span>Sisa Tagihan:</span>
                <span>Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</span>
            </div>

            <div class="meta-row" style="margin-top: 6px;">
                <span class="meta-label">Status Bayar:</span>
                <span class="badge-status">{{ $transaction->payment_status }}</span>
            </div>
            <div class="meta-row" style="margin-top: 3px;">
                <span class="meta-label">Status Cucian:</span>
                <span class="badge-status">{{ $statusValue }}</span>
            </div>
            @if ($transaction->estimated_completed_at)
                <div class="meta-row" style="margin-top: 3px;">
                    <span class="meta-label">Estimasi Selesai:</span>
                    <span class="meta-value">{{ $transaction->estimated_completed_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- QR Code Lacak Cucian Online --}}
        <div class="qr-section">
            <x-ui.qr-code :text="$lookupUrl" :size="96" />
            <p style="font-size:9px; font-weight:700; margin-top:4px; text-transform:uppercase;">
                Pindai QR Lacak Status
            </p>
        </div>

        {{-- Footer & Syarat Ketentuan Nota --}}
        <div class="footer-note">
            <p class="font-bold">PERHATIAN</p>
            <p>1. Simpan nota ini sebagai bukti sah pengambilan cucian.</p>
            <p>2. Pakaian tidak diambil > 30 hari di luar tanggung jawab kami.</p>
            <p style="margin-top: 4px;">Lacak status cucian online di:</p>
            <p class="font-bold" style="word-break: break-all;">{{ route('transactions.lookup') }}</p>
            <p style="margin-top: 7px; font-weight: 800; letter-spacing: 0.05em;">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
        </div>

        <div class="tear-off-line">- - - - - - - - - - - - - - - - - - - -</div>
    </div>

    {{-- Script Kontrol Ukuran Kertas & Shortcut Keyboard --}}
    <script>
        function setPaperSize(size) {
            localStorage.setItem('cuciin_receipt_paper_size', size);

            document.querySelectorAll('.paper-size-btn').forEach(function(btn) {
                if (btn.getAttribute('data-size') === size) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            var receipt = document.getElementById('receipt');
            if (receipt) {
                receipt.classList.remove('paper-58mm', 'paper-80mm');
                receipt.classList.add('paper-' + size);
            }

            var dynamicStyle = document.getElementById('dynamic-page-style');
            if (dynamicStyle) {
                dynamicStyle.innerHTML = '@page { size: ' + size + ' auto; margin: 0; }';
            }
        }

        // Muat preferensi ukuran kertas yang tersimpan
        (function() {
            var savedSize = localStorage.getItem('cuciin_receipt_paper_size') || '80mm';
            setPaperSize(savedSize);
        })();

        // Keyboard Shortcuts: Ctrl+P (Print), Esc (Close), 1 (58mm), 2 (80mm)
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P')) {
                e.preventDefault();
                window.print();
            } else if (e.key === 'Escape') {
                window.close();
            } else if (e.key === '1' && !e.ctrlKey && !e.metaKey && !e.altKey && document.activeElement.tagName !== 'INPUT') {
                setPaperSize('58mm');
            } else if (e.key === '2' && !e.ctrlKey && !e.metaKey && !e.altKey && document.activeElement.tagName !== 'INPUT') {
                setPaperSize('80mm');
            }
        });

        // Pemicu cetak otomatis saat halaman selesai dimuat
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
