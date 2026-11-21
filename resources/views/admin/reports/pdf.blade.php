<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Operasional Laundry CUCI.IN</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat & Header */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0e7490;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .brand-title {
            font-size: 18pt;
            font-weight: bold;
            color: #0e7490;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .doc-title-cell {
            text-align: right;
            vertical-align: bottom;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
        }

        .doc-meta {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* KPI Box Summary */
        .kpi-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: left;
        }

        .kpi-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
        }

        .kpi-value.green {
            color: #047857;
        }

        .kpi-value.amber {
            color: #b45309;
        }

        /* Tabel Data Transaksi */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 15px;
        }

        .data-table th {
            background-color: #0e7490;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #0e7490;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 3px;
        }

        .badge-lunas {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-dp {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-belum {
            background-color: #ffe4e6;
            color: #9f1239;
        }

        /* Tanda Tangan & Pengesahan */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-cell {
            width: 50%;
            vertical-align: top;
            font-size: 8.5pt;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }

        .footer-note {
            margin-top: 20px;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    {{-- Header Kop Laporan --}}
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <h1 class="brand-title">CUCI.IN</h1>
                <div class="brand-subtitle">
                    Sistem Manajemen Operasional & Rekapitulasi Kasir Laundry<br>
                    Jl. Transaksi Bersih No. 12, Malang | Hotline: 0812-3456-7890
                </div>
            </td>
            <td class="doc-title-cell" style="width: 45%;">
                <h2 class="doc-title">Laporan Operasional</h2>
                <div class="doc-meta">
                    <strong>Periode:</strong> {{ $filters['start_date']->translatedFormat('d F Y') }} — {{ $filters['end_date']->translatedFormat('d F Y') }}<br>
                    <strong>Dicetak:</strong> {{ $generatedAt }} WIB oleh {{ $generatedBy }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan KPI Eksekutif --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Transaksi</div>
                <div class="kpi-value">{{ number_format($summary['total_transactions']) }} Pesanan</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Omzet Masuk (Lunas)</div>
                <div class="kpi-value green">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Nilai Tagihan Bruto</div>
                <div class="kpi-value">Rp {{ number_format($summary['total_billed'], 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Piutang Berjalan</div>
                <div class="kpi-value amber">Rp {{ number_format($summary['total_receivables'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- Rincian Cara Bayar Ringkas --}}
    <div style="font-size: 8pt; color: #475569; margin-bottom: 8px;">
        <strong>Komposisi Pembayaran:</strong>
        Tunai: Rp {{ number_format($summary['payment_methods']['Tunai'], 0, ',', '.') }} |
        QRIS: Rp {{ number_format($summary['payment_methods']['QRIS'], 0, ',', '.') }} |
        Transfer: Rp {{ number_format($summary['payment_methods']['Transfer'], 0, ',', '.') }}
    </div>

    {{-- Tabel Data Transaksi --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 15%;">No. Nota</th>
                <th style="width: 13%;">Tanggal</th>
                <th style="width: 17%;">Pelanggan</th>
                <th style="width: 20%;">Layanan</th>
                <th style="width: 10%;" class="text-center">Status</th>
                <th style="width: 9%;" class="text-center">Bayar</th>
                <th style="width: 11%;" class="text-right">Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $tx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $tx->transaction_code }}</strong>
                    </td>
                    <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ $tx->customer?->name ?? 'Pelanggan Umum' }}
                    </td>
                    <td>
                        @php
                            $servicesDesc = $tx->details->map(fn($d) => ($d->service?->name ?? 'Layanan').' ('.$d->weight.' '.($d->service?->unit ?? 'kg').')')->implode(', ');
                        @endphp
                        {{ $servicesDesc ?: 'Kiloan Reguler' }}
                    </td>
                    <td class="text-center">
                        {{ $tx->status->value ?? (string) $tx->status }}
                    </td>
                    <td class="text-center">
                        @php
                            $payBadgeClass = match ($tx->payment_status) {
                                'Lunas' => 'badge-lunas',
                                'DP' => 'badge-dp',
                                default => 'badge-belum',
                            };
                        @endphp
                        <span class="badge {{ $payBadgeClass }}">{{ $tx->payment_status }}</span>
                    </td>
                    <td class="text-right">
                        Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada transaksi pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Area Tanda Tangan & Verifikasi Dokumen --}}
    <table class="signature-table">
        <tr>
            <td class="signature-cell" style="padding-left: 20px;">
                Diverifikasi oleh,<br>
                <strong>Kasir Penanggung Jawab</strong>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">NIP / ID Petugas</div>
            </td>
            <td class="signature-cell" style="text-align: right; padding-right: 20px;">
                Mengetahui,<br>
                <strong>Manajer Outlet / Owner</strong>
                <div class="signature-space"></div>
                <div class="signature-name">( Gargarina Nanda Iswati )</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">Penanggung Jawab Operasional</div>
            </td>
        </tr>
    </table>

    {{-- Catatan Kaki --}}
    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh Sistem CUCI.IN dan merupakan bukti sah pelaporan operasional outlet.
    </div>
</body>
</html>
