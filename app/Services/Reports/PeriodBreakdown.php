<?php

namespace App\Services\Reports;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Pengelompokan metrik transaksi per periode waktu (harian, mingguan, bulanan)
 * dengan garansi seluruh slot waktu terisi nol (zero-filling) meski tidak ada transaksi.
 *
 * Sesuai rules_milestone_2.md § 4.1:
 * - Agregasi dilakukan di level database (SQL GROUP BY) untuk efisiensi memori O(1).
 * - Rentang tanggal dijamin hingga 23:59:59 via DateRange.
 * - Transaksi berstatus 'Dibatalkan' dikecualikan dari omset.
 */
final class PeriodBreakdown
{
    /**
     * Cache hasil query agregasi harian tingkat database.
     *
     * @var Collection<string, object>|null
     */
    private ?Collection $dailyRows = null;

    public function __construct(public readonly DateRange $range) {}

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
     * Dapatkan ekspresi SQL pemformatan tanggal sesuai driver database aktif.
     */
    private static function dateKeyExpression(string $column = 'created_at'): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => "to_char({$column}, 'YYYY-MM-DD')",
            'sqlite' => "strftime('%Y-%m-%d', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }

    /**
     * Ambil data agregat harian dari database (dieksekusi sekali, di-cache).
     *
     * @return Collection<string, object>
     */
    private function getDailyRows(): Collection
    {
        if ($this->dailyRows !== null) {
            return $this->dailyRows;
        }

        $dateExpr = self::dateKeyExpression('created_at');
        $cancelledStatus = TransactionStatus::Dibatalkan->value;

        return $this->dailyRows = Transaction::query()
            ->whereBetween('created_at', $this->range->toArray())
            ->selectRaw("
                {$dateExpr} as date_key,
                COUNT(*) as total_count,
                SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_count,
                COALESCE(SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END), 0) as omset
            ", [$cancelledStatus, $cancelledStatus, $cancelledStatus])
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->get()
            ->keyBy('date_key');
    }

    /**
     * Pengelompokan harian dengan zero-filling untuk hari tanpa transaksi.
     *
     * @return list<array{
     *     period: string,
     *     date: string,
     *     label: string,
     *     count: int,
     *     total_count: int,
     *     cancelled_count: int,
     *     omset: float
     * }>
     */
    public function daily(): array
    {
        $rows = $this->getDailyRows();
        $result = [];

        $cursor = $this->range->from->startOfDay();
        $end = $this->range->to->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $row = $rows->get($key);

            $result[] = [
                'period' => $key,
                'date' => $key,
                'label' => $cursor->format('d M Y'),
                'count' => (int) ($row?->active_count ?? 0),
                'total_count' => (int) ($row?->total_count ?? 0),
                'cancelled_count' => (int) ($row?->cancelled_count ?? 0),
                'omset' => (float) ($row?->omset ?? 0.0),
            ];

            $cursor = $cursor->addDay();
        }

        return $result;
    }

    /**
     * Pengelompokan mingguan (Senin–Minggu) dengan zero-filling untuk minggu tanpa transaksi.
     *
     * @return list<array{
     *     period: string,
     *     start_date: string,
     *     end_date: string,
     *     label: string,
     *     count: int,
     *     total_count: int,
     *     cancelled_count: int,
     *     omset: float
     * }>
     */
    public function weekly(): array
    {
        $rows = $this->getDailyRows();
        $weeks = [];

        $cursor = $this->range->from->startOfWeek();
        $end = $this->range->to->endOfWeek();

        while ($cursor->lte($end)) {
            $weekEnd = $cursor->endOfWeek();
            $key = $cursor->format('o-\WW');
            $label = sprintf('%s - %s', $cursor->format('d M'), $weekEnd->format('d M Y'));

            $weeks[$key] = [
                'period' => $key,
                'start_date' => $cursor->format('Y-m-d'),
                'end_date' => $weekEnd->format('Y-m-d'),
                'label' => $label,
                'count' => 0,
                'total_count' => 0,
                'cancelled_count' => 0,
                'omset' => 0.0,
            ];

            $cursor = $cursor->addWeek();
        }

        foreach ($rows as $dateKey => $row) {
            $date = CarbonImmutable::parse($dateKey);
            $weekKey = $date->format('o-\WW');

            if (isset($weeks[$weekKey])) {
                $weeks[$weekKey]['count'] += (int) $row->active_count;
                $weeks[$weekKey]['total_count'] += (int) $row->total_count;
                $weeks[$weekKey]['cancelled_count'] += (int) $row->cancelled_count;
                $weeks[$weekKey]['omset'] += (float) $row->omset;
            }
        }

        return array_values($weeks);
    }

    /**
     * Pengelompokan bulanan dengan zero-filling untuk bulan tanpa transaksi.
     *
     * @return list<array{
     *     period: string,
     *     label: string,
     *     count: int,
     *     total_count: int,
     *     cancelled_count: int,
     *     omset: float
     * }>
     */
    public function monthly(): array
    {
        $rows = $this->getDailyRows();
        $months = [];

        $cursor = $this->range->from->startOfMonth();
        $end = $this->range->to->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $label = $cursor->format('F Y');

            $months[$key] = [
                'period' => $key,
                'label' => $label,
                'count' => 0,
                'total_count' => 0,
                'cancelled_count' => 0,
                'omset' => 0.0,
            ];

            $cursor = $cursor->addMonth();
        }

        foreach ($rows as $dateKey => $row) {
            $monthKey = substr((string) $dateKey, 0, 7);

            if (isset($months[$monthKey])) {
                $months[$monthKey]['count'] += (int) $row->active_count;
                $months[$monthKey]['total_count'] += (int) $row->total_count;
                $months[$monthKey]['cancelled_count'] += (int) $row->cancelled_count;
                $months[$monthKey]['omset'] += (float) $row->omset;
            }
        }

        return array_values($months);
    }

    /**
     * Ambil rincian data berdasarkan interval pilihan ('day'|'week'|'month').
     *
     * @param  'day'|'daily'|'week'|'weekly'|'month'|'monthly'  $interval
     * @return list<array<string, mixed>>
     *
     * @throws InvalidArgumentException
     */
    public function get(string $interval = 'day'): array
    {
        return match (strtolower($interval)) {
            'day', 'daily' => $this->daily(),
            'week', 'weekly' => $this->weekly(),
            'month', 'monthly' => $this->monthly(),
            default => throw new InvalidArgumentException("Interval period '{$interval}' tidak valid. Gunakan 'day', 'week', atau 'month'."),
        };
    }
}
