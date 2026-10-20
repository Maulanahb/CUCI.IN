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

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11.5px;
            line-height: 1.45;
            color: #111827;
            background-color: #e2e8f0;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .receipt-container {
            width: 80mm;
            max-width: 100%;
            background: #ffffff;
            padding: 4mm 4mm 5mm;
            border: 1px solid #cbd5e1;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .header-title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #111827;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 10px;
            color: #374151;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px dashed #6b7280;
            margin: 8px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .meta-label {
            color: #374151;
        }

        .meta-value {
            font-weight: 700;
            color: #111827;
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
            gap: 8px;
            font-weight: 700;
            font-size: 11px;
        }

        .item-sub {
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: #374151;
            margin-top: 1px;
        }

        .total-section {
            margin-top: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            border-top: 1px dashed #6b7280;
            padding-top: 6px;
            font-size: 13px;
            font-weight: 800;
            color: #111827;
        }

        .badge-status {
            display: inline-block;
            border: 1px solid #111827;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .footer-note {
            margin-top: 12px;
            font-size: 9.5px;
            color: #374151;
            text-align: center;
            line-height: 1.4;
        }

        .actions-bar {
            margin-bottom: 16px;
            display: flex;
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

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .receipt-container {
                width: 80mm;
                max-width: 80mm;
                border: 0;
                padding: 3mm 4mm;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Struk
        </button>
        <button onclick="window.close()" class="btn">
            Tutup
        </button>
    </div>

    <div class="receipt-container">
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
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
