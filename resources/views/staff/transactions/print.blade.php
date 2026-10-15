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
                box-shadow: none;
                border-radius: 0;
                padding: 10px 0;
                width: 100%;
                max-width: 100%;
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
