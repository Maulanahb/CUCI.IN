<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan & Operasional CUCI.IN</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }
        .title { font-size: 16pt; font-weight: bold; color: #0e7490; }
        .subtitle { font-size: 10pt; color: #64748b; }
        .kpi-header { background-color: #f1f5f9; font-weight: bold; border: 1px solid #cbd5e1; padding: 6px; }
        .kpi-value { font-weight: bold; border: 1px solid #cbd5e1; padding: 6px; }
        .th-header { background-color: #0e7490; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #0e7490; padding: 8px; }
        .td-cell { border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle; }
        .td-center { border: 1px solid #cbd5e1; text-align: center; padding: 6px; vertical-align: middle; }
        .td-number { border: 1px solid #cbd5e1; text-align: right; padding: 6px; vertical-align: middle; mso-number-format: "\#\,\#\#0"; }
        .td-total { background-color: #e2e8f0; font-weight: bold; border: 1px solid #94a3b8; padding: 8px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="10" class="title">CUCI.IN — Laporan Keuangan & Rekapitulasi Operasional Laundry</td>
        </tr>
        <tr>
            <td colspan="10" class="subtitle">Periode: {{ $filters['start_date']->format('d/m/Y') }} s/d {{ $filters['end_date']->format('d/m/Y') }} | Digenerate pada: {{ $generatedAt }} WIB oleh {{ $generatedBy }}</td>
        </tr>
        <tr><td colspan="10"></td></tr>

        {{-- Ringkasan KPI --}}
        <tr>
            <td colspan="2" class="kpi-header">Total Transaksi</td>
            <td colspan="2" class="kpi-header">Omzet Masuk (Lunas)</td>
            <td colspan="2" class="kpi-header">Nilai Tagihan Bruto</td>
            <td colspan="2" class="kpi-header">Piutang Berjalan</td>
            <td colspan="2" class="kpi-header">Rerata Per Nota</td>
        </tr>
        <tr>
            <td colspan="2" class="kpi-value td-center">{{ $summary['total_transactions'] }} Pesanan</td>
            <td colspan="2" class="kpi-value td-number">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
            <td colspan="2" class="kpi-value td-number">Rp {{ number_format($summary['total_billed'], 0, ',', '.') }}</td>
            <td colspan="2" class="kpi-value td-number">Rp {{ number_format($summary['total_receivables'], 0, ',', '.') }}</td>
            <td colspan="2" class="kpi-value td-number">Rp {{ number_format($summary['average_ticket'], 0, ',', '.') }}</td>
        </tr>
        <tr><td colspan="10"></td></tr>

        {{-- Tabel Data Transaksi --}}
        <thead>
            <tr>
                <th class="th-header" style="width: 40px;">No</th>
                <th class="th-header" style="width: 140px;">No. Nota</th>
                <th class="th-header" style="width: 130px;">Tanggal Masuk</th>
                <th class="th-header" style="width: 160px;">Nama Pelanggan</th>
                <th class="th-header" style="width: 120px;">No. Telepon</th>
                <th class="th-header" style="width: 220px;">Rincian Layanan</th>
                <th class="th-header" style="width: 110px;">Status Cucian</th>
                <th class="th-header" style="width: 110px;">Status Bayar</th>
                <th class="th-header" style="width: 130px;">Total Tagihan (Rp)</th>
                <th class="th-header" style="width: 130px;">Sisa Tagihan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalBilled = 0;
                $grandTotalRemaining = 0;
            @endphp
            @forelse ($transactions as $index => $tx)
                @php
                    $grandTotalBilled += (float) $tx->total_amount;
                    $grandTotalRemaining += (float) $tx->remaining_amount;
                    $servicesStr = $tx->details->map(fn($d) => ($d->service?->name ?? 'Layanan').' ('.$d->weight.' '.($d->service?->unit ?? 'kg').')')->implode(', ');
                @endphp
                <tr>
                    <td class="td-center">{{ $index + 1 }}</td>
                    <td class="td-cell"><strong>{{ $tx->transaction_code }}</strong></td>
                    <td class="td-center">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="td-cell">{{ $tx->customer?->name ?? 'Pelanggan Umum' }}</td>
                    <td class="td-center" style="mso-number-format: '\@';">{{ $tx->customer?->phone ?? '-' }}</td>
                    <td class="td-cell">{{ $servicesStr ?: 'Kiloan Reguler' }}</td>
                    <td class="td-center">{{ $tx->status->value ?? (string) $tx->status }}</td>
                    <td class="td-center">{{ $tx->payment_status }}</td>
                    <td class="td-number">{{ (float) $tx->total_amount }}</td>
                    <td class="td-number">{{ (float) $tx->remaining_amount }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="td-center" style="padding: 20px;">Tidak ada data transaksi pada rentang filter ini.</td>
                </tr>
            @endforelse
            {{-- Baris Grand Total --}}
            @if ($transactions->isNotEmpty())
                <tr>
                    <td colspan="8" class="td-total" style="text-align: right;">GRAND TOTAL</td>
                    <td class="td-total td-number">{{ $grandTotalBilled }}</td>
                    <td class="td-total td-number">{{ $grandTotalRemaining }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
