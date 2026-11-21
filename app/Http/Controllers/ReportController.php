<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman rekapitulasi dan filter laporan operasional/keuangan laundry.
     */
    public function index(Request $request): View
    {
        $filters = $this->resolveDateFilters($request);
        $query = $this->buildReportQuery($filters);

        // Agregasi Ringkasan Kinerja (Sesuai Aturan P9: Agregasi Tingkat Database)
        $summary = $this->calculateSummary($filters);

        // Data transaksi untuk pratinjau tabel di web
        $transactions = (clone $query)
            ->with(['customer', 'details.service', 'payments', 'creator'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $services = Service::query()->orderBy('name')->get();
        $statuses = TransactionStatus::cases();

        return view('admin.reports.index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'services' => $services,
            'statuses' => $statuses,
            'filters' => $filters,
        ]);
    }

    /**
     * Ekspor dokumen ringkasan eksekutif siap cetak dalam format PDF via DomPDF.
     * (Sesuai P9: Dibatasi untuk dokumen ringkas 1-2 halaman, tidak membebani memori).
     */
    public function exportPdf(Request $request): Response
    {
        $filters = $this->resolveDateFilters($request);
        $summary = $this->calculateSummary($filters);

        // Ambil data transaksi relevan (maksimal 100 data untuk menjaga stabilitas rendering DomPDF)
        $transactions = $this->buildReportQuery($filters)
            ->with(['customer', 'details.service', 'payments', 'creator'])
            ->latest('id')
            ->limit(100)
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $filters,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'generatedBy' => auth()->user()?->name ?? 'Sistem CUCI.IN',
        ])->setPaper('a4', 'portrait');

        $fileName = sprintf(
            'Laporan-Operasional-CUCIIN-%s-sd-%s.pdf',
            $filters['start_date']->format('Ymd'),
            $filters['end_date']->format('Ymd')
        );

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    /**
     * Ekspor lembar kerja spreadsheet Excel (.xls) analitis keuangan Owner dengan tabel terformat.
     */
    public function exportExcel(Request $request): Response
    {
        $filters = $this->resolveDateFilters($request);
        $summary = $this->calculateSummary($filters);

        $transactions = $this->buildReportQuery($filters)
            ->with(['customer', 'details.service', 'payments', 'creator'])
            ->latest('id')
            ->get();

        $content = view('admin.reports.excel', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $filters,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'generatedBy' => auth()->user()?->name ?? 'Sistem CUCI.IN',
        ])->render();

        $fileName = sprintf(
            'Laporan-Keuangan-CUCIIN-%s-sd-%s.xls',
            $filters['start_date']->format('Ymd'),
            $filters['end_date']->format('Ymd')
        );

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Ekspor CSV Streaming Memory-Safe untuk Dataset Besar.
     * (Sesuai P9: Memanfaatkan response()->streamDownload() + lazyById() agar RAM server tetap stabil).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveDateFilters($request);
        $query = $this->buildReportQuery($filters);

        $fileName = sprintf(
            'Arsip-Transaksi-CUCIIN-%s-sd-%s.csv',
            $filters['start_date']->format('Ymd'),
            $filters['end_date']->format('Ymd')
        );

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            // Tulis UTF-8 BOM agar Excel Windows membuka simbol karakter dan nominal Rupiah dengan tepat
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Baris CSV (Format Separator Titik Koma ';' ramah Microsoft Excel regional Indonesia)
            fputcsv($handle, [
                'No Nota',
                'Tanggal Masuk',
                'Nama Pelanggan',
                'No Telepon',
                'Rincian Layanan',
                'Total Volume (Kg/Pcs)',
                'Status Pengerjaan',
                'Status Pembayaran',
                'Total Tagihan (Rp)',
                'Uang Terbayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Metode Pembayaran',
                'Kasir Pembuat',
            ], ';');

            // lazyById(100) menjaga penggunaan memori PHP tetap konstan bahkan untuk ribuan data
            $query->with(['customer', 'details.service', 'payments', 'creator'])
                ->lazyById(100)
                ->each(function (Transaction $transaction) use ($handle): void {
                    $servicesSummary = $transaction->details->map(function ($detail): string {
                        $name = $detail->service?->name ?? 'Layanan';
                        $unit = $detail->service?->unit ?? 'kg';

                        return "{$name} ({$detail->weight} {$unit})";
                    })->implode(', ');

                    $totalWeight = $transaction->details->sum('weight');
                    $paymentMethods = $transaction->payments->pluck('method')->unique()->implode(', ') ?: '-';

                    $statusValue = $transaction->status instanceof TransactionStatus
                        ? $transaction->status->value
                        : (string) $transaction->status;

                    fputcsv($handle, [
                        $transaction->transaction_code,
                        $transaction->created_at->format('d/m/Y H:i'),
                        $transaction->customer?->name ?? 'Pelanggan Umum',
                        $transaction->customer?->phone ?? '-',
                        $servicesSummary ?: 'Kiloan Reguler',
                        $totalWeight,
                        $statusValue,
                        $transaction->payment_status,
                        (float) $transaction->total_amount,
                        (float) $transaction->total_paid,
                        (float) $transaction->remaining_amount,
                        $paymentMethods,
                        $transaction->creator?->name ?? 'Staf Kasir',
                    ], ';');
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Resolusi filter tanggal dengan pencegahan jebakan tanggal terpotong (endOfDay).
     *
     * @return array{start_date: Carbon, end_date: Carbon, status: ?string, payment_status: ?string, search: ?string}
     */
    protected function resolveDateFilters(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse((string) $request->input('start_date'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        // ATURAN P9: Wajib endOfDay() agar transaksi pada 23:59:59 tidak terpotong
        $endDate = $request->filled('end_date')
            ? Carbon::parse((string) $request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        if ($startDate->isAfter($endDate)) {
            $startDate = $endDate->copy()->startOfMonth()->startOfDay();
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $request->filled('status') ? (string) $request->input('status') : null,
            'payment_status' => $request->filled('payment_status') ? (string) $request->input('payment_status') : null,
            'search' => $request->filled('search') ? trim((string) $request->input('search')) : null,
        ];
    }

    /**
     * Bangun kueri dasar transaksi dengan seluruh filter aktif.
     *
     * @param  array{start_date: Carbon, end_date: Carbon, status: ?string, payment_status: ?string, search: ?string}  $filters
     */
    protected function buildReportQuery(array $filters): Builder
    {
        $query = Transaction::query()
            ->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status pembayaran via subquery korelasi
        if (! empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $this->applyPaymentStatusFilter($query, $filters['payment_status']);
        }

        return $query;
    }

    /**
     * Terapkan filter status pembayaran ke kueri Eloquent.
     */
    protected function applyPaymentStatusFilter(Builder $query, string $paymentStatus): void
    {
        $paidSubquery = Payment::selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('transaction_id', 'transactions.id')
            ->whereIn('verification_status', Payment::PAID_STATUSES);

        if ($paymentStatus === 'Lunas') {
            $query->whereRaw("transactions.total_amount <= ({$paidSubquery->toSql()})", $paidSubquery->getBindings());
        } elseif ($paymentStatus === 'DP') {
            $query->whereRaw("({$paidSubquery->toSql()}) > 0 AND ({$paidSubquery->toSql()}) < transactions.total_amount", [
                ...$paidSubquery->getBindings(),
                ...$paidSubquery->getBindings(),
            ]);
        } elseif ($paymentStatus === 'Belum Bayar') {
            $query->whereRaw("({$paidSubquery->toSql()}) = 0", $paidSubquery->getBindings());
        }
    }

    /**
     * Hitung ringkasan statistik dan agregasi database dalam rentang filter.
     *
     * @param  array{start_date: Carbon, end_date: Carbon, status: ?string, payment_status: ?string, search: ?string}  $filters
     * @return array<string, mixed>
     */
    protected function calculateSummary(array $filters): array
    {
        $baseQuery = $this->buildReportQuery($filters);

        // Agregasi SQL transaksi di rentang filter
        $transactionStats = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_billed')
            ->first();

        $totalTransactions = (int) ($transactionStats->total_count ?? 0);
        $totalBilled = (float) ($transactionStats->total_billed ?? 0);

        // Agregasi pembayaran yang berhasil diverifikasi pada rentang tanggal
        $paidQuery = Payment::query()
            ->whereIn('verification_status', Payment::PAID_STATUSES)
            ->whereBetween('paid_at', [$filters['start_date'], $filters['end_date']]);

        $totalPaidRevenue = (float) (clone $paidQuery)->sum('amount');

        // Rincian omset per metode bayar
        $paymentMethods = (clone $paidQuery)
            ->selectRaw('method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        // Rincian jumlah transaksi per status cucian
        $statusBreakdown = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $receivables = max(0.0, $totalBilled - $totalPaidRevenue);

        return [
            'total_transactions' => $totalTransactions,
            'total_billed' => $totalBilled,
            'total_revenue' => $totalPaidRevenue,
            'total_receivables' => $receivables,
            'average_ticket' => $totalTransactions > 0 ? $totalBilled / $totalTransactions : 0.0,
            'payment_methods' => [
                'Tunai' => (float) ($paymentMethods['Tunai'] ?? 0),
                'Transfer' => (float) ($paymentMethods['Transfer'] ?? 0),
                'QRIS' => (float) ($paymentMethods['QRIS'] ?? 0),
            ],
            'status_breakdown' => $statusBreakdown,
        ];
    }
}
