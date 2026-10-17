@php
    $statusValue = $transaction->status instanceof \App\Enums\TransactionStatus
        ? $transaction->status->value
        : (string) $transaction->status;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Nota - {{ $transaction->transaction_code }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace, system-ui;
            font-size: 13px;
            line-height: 1.4;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .receipt-container {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            padding: 24px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            transition: max-width 0.2s ease, font-size 0.2s ease, padding 0.2s ease;
        }

        .receipt-container.size-58mm {
            max-width: 280px;
            font-size: 11px;
            padding: 16px 12px;
        }

        .receipt-container.size-58mm .header-title {
            font-size: 15px;
        }

        .receipt-container.size-58mm .header-subtitle {
            font-size: 10px;
        }

        .receipt-container.size-58mm .item-header {
            font-size: 11px;
        }

        .receipt-container.size-58mm .item-sub {
            font-size: 10px;
        }

        .receipt-container.size-58mm .total-row {
            font-size: 12px;
        }

        .receipt-container.size-58mm .footer-note {
            font-size: 9px;
        }

        .receipt-container.size-80mm {
            max-width: 380px;
            font-size: 13px;
            padding: 24px 20px;
        }

        .paper-toggle {
            display: inline-flex;
            background: #e2e8f0;
            border-radius: 6px;
            padding: 2px;
            gap: 2px;
        }

        .paper-toggle button {
            border: none;
            background: transparent;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .paper-toggle button.active {
            background: #ffffff;
            color: #0284c7;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .header-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f172a;
        }

        .header-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px dashed #94a3b8;
            margin: 12px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .meta-label {
            color: #64748b;
        }

        .meta-value {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
        }

        .item-list {
            margin: 8px 0;
        }

        .item-row {
            margin-bottom: 8px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 12px;
        }

        .item-sub {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .total-section {
            margin-top: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            background: #e2e8f0;
            color: #334155;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
            line-height: 1.4;
        }

        .actions-bar {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
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
        }

        .btn-primary {
            background: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #0369a1;
            border-color: #0369a1;
        }

        @page {
            size: auto;
            margin: 0;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                margin: 0 auto;
            }

            .receipt-container.size-58mm {
                width: 58mm !important;
                max-width: 58mm !important;
                padding: 3mm 2mm !important;
                font-size: 10px !important;
            }

            .receipt-container.size-58mm .header-title {
                font-size: 13px !important;
            }

            .receipt-container.size-58mm .total-row {
                font-size: 11px !important;
            }

            .receipt-container.size-80mm {
                width: 80mm !important;
                max-width: 80mm !important;
                padding: 6mm 4mm !important;
                font-size: 12px !important;
            }

            .receipt-container.size-80mm .header-title {
                font-size: 16px !important;
            }

            .receipt-container.size-80mm .total-row {
                font-size: 13px !important;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar no-print">
        <button onclick="window.print()" class="btn btn-primary" id="btn-print">
            <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Struk
        </button>

        <div class="paper-toggle" role="group" aria-label="Pilihan Ukuran Kertas">
            <button type="button" id="toggle-58mm" onclick="setPaperSize('58mm')">58 mm (Mini)</button>
            <button type="button" id="toggle-80mm" onclick="setPaperSize('80mm')" class="active">80 mm (Standar)</button>
        </div>

        <button onclick="window.close()" class="btn">
            Tutup
        </button>
    </div>

    <div id="receipt-container" class="receipt-container size-80mm">
        {{-- Header Kasir --}}
        <div class="text-center">
            <h1 class="header-title">CUCI.IN LAUNDRY</h1>
            <p class="header-subtitle">Layanan Laundry Bersih, Cepat & Terpercaya</p>
            <p class="header-subtitle">Telp/WA: 0812-3456-7890</p>
        </div>

        <div class="divider"></div>

        {{-- Meta Nota --}}
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
                <div class="text-center font-bold" style="font-size:11px;color:#94a3b8;padding:8px 0;">
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
            <div class="meta-row">
                <span class="meta-label">Sisa Tagihan:</span>
                <span class="meta-value font-bold">Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</span>
            </div>
            <div class="meta-row" style="margin-top: 6px;">
                <span class="meta-label">Status Bayar:</span>
                <span class="badge-status font-bold">{{ $transaction->payment_status }}</span>
            </div>
            <div class="meta-row" style="margin-top: 4px;">
                <span class="meta-label">Status Cucian:</span>
                <span class="badge-status">{{ $statusValue }}</span>
            </div>
            @if ($transaction->estimated_completed_at)
                <div class="meta-row" style="margin-top: 4px;">
                    <span class="meta-label">Estimasi Selesai:</span>
                    <span class="meta-value">{{ $transaction->estimated_completed_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Footer & Syarat Ketentuan --}}
        <div class="footer-note">
            <p class="font-bold">PERHATIAN</p>
            <p>1. Simpan nota ini sebagai bukti sah pengambilan cucian.</p>
            <p>2. Barang yang tidak diambil lebih dari 30 hari di luar tanggung jawab kami.</p>
            <p style="margin-top: 6px;">Lacak status cucian online di:</p>
            <p class="font-bold">{{ route('transactions.lookup') }}</p>
            <p style="margin-top: 8px; font-weight: 700;">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
        </div>
    </div>

    <script>
        function setPaperSize(size) {
            const container = document.getElementById('receipt-container');
            const btn58 = document.getElementById('toggle-58mm');
            const btn80 = document.getElementById('toggle-80mm');

            if (!container) return;

            if (size === '58mm') {
                container.classList.remove('size-80mm');
                container.classList.add('size-58mm');
                if (btn58) btn58.classList.add('active');
                if (btn80) btn80.classList.remove('active');
            } else {
                container.classList.remove('size-58mm');
                container.classList.add('size-80mm');
                if (btn80) btn80.classList.add('active');
                if (btn58) btn58.classList.remove('active');
            }

            try {
                localStorage.setItem('cuciin_receipt_size', size);
            } catch (e) {}
        }

        window.addEventListener('load', function() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const querySize = urlParams.get('size');
                const savedSize = localStorage.getItem('cuciin_receipt_size');
                const activeSize = querySize || savedSize;

                if (activeSize && (activeSize === '58mm' || activeSize === '80mm')) {
                    setPaperSize(activeSize);
                }
            } catch (e) {}

            window.print();
        });
    </script>
</body>
</html>
