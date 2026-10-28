<?php

namespace App\Services\Reports;

/**
 * Agregator utama keuangan dan pendapatan laundry (Revenue Summary).
 *
 * Menggabungkan seluruh modul pelaporan:
 * 1. DateRange: Pembatas rentang tanggal dengan normalisasi 00:00:00 s.d. 23:59:59.
 * 2. TransactionSummary: Perhitungan omset kotor (total tagihan non-dibatalkan) dan rincian transaksi.
 * 3. ValidPayments: Perhitungan penerimaan kas riil dari pembayaran yang sah (Tunai & Transfer/QRIS Terverifikasi).
 * 4. Piutang / Pending: Selisih antara omset kotor dengan penerimaan riil (max(0, omset - penerimaan)).
 * 5. Breakdown Metode Bayar: Rincian penerimaan per metode (Tunai, Transfer, QRIS) dengan zero-filling.
 *
 * Memenuhi standar teknis rules_milestone_2.md § 4.1 (Agregasi tingkat database & O(1) memory).
 */
final class RevenueSummary
{
    /**
     * Daftar metode pembayaran resmi CUCI.IN yang dipantau pada laporan keuangan.
     *
     * @var list<string>
     */
    public const SUPPORTED_METHODS = ['Tunai', 'Transfer', 'QRIS'];

    private TransactionSummary $transactionSummary;

    /**
     * Cache hasil agregasi metode pembayaran database.
     *
     * @var array{
     *     total_real: float,
     *     methods: array<string, float>,
     *     methods_detail: array<string, array{count: int, total: float}>
     * }|null
     */
    private ?array $paymentData = null;

    public function __construct(public readonly DateRange $range)
    {
        $this->transactionSummary = new TransactionSummary($this->range);
    }

    /**
     * Factory method untuk membuat instans dari DateRange.
     */
    public static function forRange(DateRange $range): self
    {
        return new self($range);
    }

    /**
     * Factory method untuk membuat instans dari dua tanggal string/DateTimeInterface.
     */
    public static function fromDates(string|\DateTimeInterface $from, string|\DateTimeInterface $to): self
    {
        return new self(new DateRange($from, $to));
    }

    /**
     * Instans TransactionSummary terikat pada rentang tanggal ini.
     */
    public function transactionSummary(): TransactionSummary
    {
        return $this->transactionSummary;
    }

    /**
     * Instans PeriodBreakdown terikat pada rentang tanggal ini.
     */
    public function periodBreakdown(): PeriodBreakdown
    {
        return new PeriodBreakdown($this->range);
    }

    /**
     * Agregasi database untuk pembayaran valid (dieksekusi sekali, di-cache).
     *
     * @return array{
     *     total_real: float,
     *     methods: array<string, float>,
     *     methods_detail: array<string, array{count: int, total: float}>
     * }
     */
    private function aggregatePayments(): array
    {
        if ($this->paymentData !== null) {
            return $this->paymentData;
        }

        // Siapkan kerangka default (zero-filling untuk seluruh metode)
        $methods = [];
        $methodsDetail = [];

        foreach (self::SUPPORTED_METHODS as $method) {
            $methods[$method] = 0.0;
            $methodsDetail[$method] = [
                'count' => 0,
                'total' => 0.0,
            ];
        }

        // Query agregasi pembayaran valid tingkat SQL
        $rows = ValidPayments::query($this->range)
            ->selectRaw('method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('method')
            ->get();

        $totalReal = 0.0;

        foreach ($rows as $row) {
            $methodName = (string) $row->method;
            $totalAmount = (float) $row->total;
            $count = (int) $row->count;

            $methods[$methodName] = $totalAmount;
            $methodsDetail[$methodName] = [
                'count' => $count,
                'total' => $totalAmount,
            ];

            $totalReal += $totalAmount;
        }

        return $this->paymentData = [
            'total_real' => $totalReal,
            'methods' => $methods,
            'methods_detail' => $methodsDetail,
        ];
    }

    /**
     * Omset kotor (total nilai transaksi non-dibatalkan yang dibuat dalam rentang tanggal).
     */
    public function omsetKotor(): float
    {
        return $this->transactionSummary->totalOmset();
    }

    /**
     * Alias bahasa Inggris untuk omset kotor.
     */
    public function grossRevenue(): float
    {
        return $this->omsetKotor();
    }

    /**
     * Penerimaan riil (total uang masuk dari pembayaran sah: Tunai dan Transfer/QRIS Terverifikasi).
     */
    public function penerimaanRiil(): float
    {
        return $this->aggregatePayments()['total_real'];
    }

    /**
     * Alias bahasa Inggris untuk penerimaan riil.
     */
    public function realRevenue(): float
    {
        return $this->penerimaanRiil();
    }

    /**
     * Piutang / tagihan pending (omset kotor dikurangi uang riil yang sudah diterima).
     */
    public function piutang(): float
    {
        return max(0.0, $this->omsetKotor() - $this->penerimaanRiil());
    }

    /**
     * Alias bahasa Inggris untuk piutang.
     */
    public function receivables(): float
    {
        return $this->piutang();
    }

    /**
     * Rincian penerimaan uang riil per metode pembayaran (hanya nominal).
     *
     * @return array<string, float> [Tunai => 0.0, Transfer => 0.0, QRIS => 0.0]
     */
    public function paymentMethods(): array
    {
        return $this->aggregatePayments()['methods'];
    }

    /**
     * Rincian penerimaan uang riil per metode bayar lengkap beserta frekuensi transaksi.
     *
     * @return array<string, array{count: int, total: float}>
     */
    public function paymentMethodsDetail(): array
    {
        return $this->aggregatePayments()['methods_detail'];
    }

    /**
     * Ekspor seluruh ringkasan pendapatan dalam bentuk array terstruktur.
     *
     * @return array{
     *     omset_kotor: float,
     *     penerimaan_riil: float,
     *     piutang: float,
     *     payment_methods: array<string, float>,
     *     payment_methods_detail: array<string, array{count: int, total: float}>,
     *     total_transactions: int,
     *     active_transactions: int,
     *     cancelled_transactions: int,
     *     status_counts: array<string, int>
     * }
     */
    public function toArray(): array
    {
        $txSummary = $this->transactionSummary->toArray();

        return [
            'omset_kotor' => $this->omsetKotor(),
            'penerimaan_riil' => $this->penerimaanRiil(),
            'piutang' => $this->piutang(),
            'payment_methods' => $this->paymentMethods(),
            'payment_methods_detail' => $this->paymentMethodsDetail(),
            'total_transactions' => $txSummary['total_count'],
            'active_transactions' => $txSummary['active_count'],
            'cancelled_transactions' => $txSummary['cancelled_count'],
            'status_counts' => $txSummary['status_counts'],
        ];
    }
}
