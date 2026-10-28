<?php

namespace App\Services\Reports;

use App\Enums\TransactionStatus;
use App\Models\Transaction;

/**
 * Agregator ringkasan transaksi laundry dalam rentang tanggal tertentu.
 *
 * ASUMSI BISNIS & TEKNIS:
 * 1. Total omset dihitung dari SUM(total_amount) untuk transaksi yang statusnya
 *    BUKAN 'Dibatalkan' (TransactionStatus::Dibatalkan). Transaksi yang dibatalkan
 *    tidak dianggap menghasilkan omset karena layanan dibatalkan/tidak diselesaikan.
 * 2. Jumlah transaksi total (total_count) mencakup SELURUH transaksi yang dibuat
 *    pada rentang tanggal (termasuk status Dibatalkan), agar rasio pembatalan
 *    dan throughput kasir tetap dapat dianalisis secara akurat.
 * 3. Rincian status (status_counts) menyajikan seluruh status operasional cucian
 *    yang valid (TransactionStatus::cases()) dengan nilai awal 0 (zero-filled),
 *    sehingga status yang belum memiliki transaksi tetap tampil pada laporan.
 * 4. Filter tanggal menggunakan kolom `created_at` dengan batas [00:00:00, 23:59:59]
 *    sesuai standar rules_milestone_2.md § 4.1 (mencegah transaksi di penghujung hari terpotong).
 * 5. Agregasi dilakukan langsung di level database (SQL GROUP BY & COUNT/SUM)
 *    untuk menjaga penggunaan memori tetap minimal dan konstan (O(1)).
 */
final class TransactionSummary
{
    /**
     * Cache hasil agregasi agar query hanya dieksekusi satu kali per instans.
     *
     * @var array{total_count: int, total_omset: float, status_counts: array<string, int>}|null
     */
    private ?array $data = null;

    public function __construct(public readonly DateRange $range) {}

    /**
     * Factory method untuk membuat instans dari DateRange yang sudah ada.
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
     * Eksekusi agregasi SQL tingkat database secara efisien.
     *
     * @return array{total_count: int, total_omset: float, status_counts: array<string, int>}
     */
    private function aggregate(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        // Siapkan kerangka status_counts dengan seluruh status diisi 0 (zero-filling)
        $statusCounts = [];
        foreach (TransactionStatus::cases() as $case) {
            $statusCounts[$case->value] = 0;
        }

        $rows = Transaction::query()
            ->whereBetween('created_at', $this->range->toArray())
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total_amount')
            ->groupBy('status')
            ->get();

        $totalCount = 0;
        $totalOmset = 0.0;

        foreach ($rows as $row) {
            $statusValue = $row->status instanceof TransactionStatus
                ? $row->status->value
                : (string) $row->status;

            $count = (int) $row->count;
            $amount = (float) $row->total_amount;

            if (array_key_exists($statusValue, $statusCounts)) {
                $statusCounts[$statusValue] = $count;
            }

            $totalCount += $count;

            // ASUMSI: Status Dibatalkan tidak dihitung ke dalam omset
            if ($statusValue !== TransactionStatus::Dibatalkan->value) {
                $totalOmset += $amount;
            }
        }

        return $this->data = [
            'total_count' => $totalCount,
            'total_omset' => $totalOmset,
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * Total transaksi yang dibuat dalam rentang tanggal (termasuk Dibatalkan).
     */
    public function totalCount(): int
    {
        return $this->aggregate()['total_count'];
    }

    /**
     * Total omset transaksi (SUM total_amount tanpa status Dibatalkan).
     */
    public function totalOmset(): float
    {
        return $this->aggregate()['total_omset'];
    }

    /**
     * Jumlah transaksi per status cucian (seluruh status terisi, default 0).
     *
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        return $this->aggregate()['status_counts'];
    }

    /**
     * Jumlah transaksi aktif (seluruh transaksi selain status Dibatalkan).
     */
    public function activeCount(): int
    {
        $dibatalkan = $this->statusCounts()[TransactionStatus::Dibatalkan->value] ?? 0;

        return $this->totalCount() - $dibatalkan;
    }

    /**
     * Jumlah transaksi dengan status Dibatalkan.
     */
    public function cancelledCount(): int
    {
        return $this->statusCounts()[TransactionStatus::Dibatalkan->value] ?? 0;
    }

    /**
     * Konversi ringkasan menjadi array terstruktur.
     *
     * @return array{
     *     total_count: int,
     *     total_omset: float,
     *     active_count: int,
     *     cancelled_count: int,
     *     status_counts: array<string, int>
     * }
     */
    public function toArray(): array
    {
        return [
            'total_count' => $this->totalCount(),
            'total_omset' => $this->totalOmset(),
            'active_count' => $this->activeCount(),
            'cancelled_count' => $this->cancelledCount(),
            'status_counts' => $this->statusCounts(),
        ];
    }
}
