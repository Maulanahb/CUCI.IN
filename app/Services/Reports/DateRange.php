<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Menormalkan rentang tanggal laporan ke startOfDay dan endOfDay (23:59:59)
 * dengan timezone aplikasi, serta menolak rentang terbalik.
 *
 * Sesuai rules_milestone_2.md § 4.1:
 * > Filter rentang tanggal pada laporan omzet wajib menggunakan
 * > endOfDay() (23:59:59) agar transaksi di penghujung hari tidak hilang.
 */
final class DateRange
{
    public readonly CarbonImmutable $from;

    public readonly CarbonImmutable $to;

    /**
     * @param  string|\DateTimeInterface  $from  Tanggal awal (akan dinormalisasi ke 00:00:00)
     * @param  string|\DateTimeInterface  $to  Tanggal akhir (akan dinormalisasi ke 23:59:59)
     *
     * @throws InvalidArgumentException Jika tanggal awal setelah tanggal akhir (setelah normalisasi).
     */
    public function __construct(string|\DateTimeInterface $from, string|\DateTimeInterface $to)
    {
        $tz = config('app.timezone', 'UTC');

        $this->from = CarbonImmutable::parse($from, $tz)->startOfDay();
        $this->to = CarbonImmutable::parse($to, $tz)->endOfDay();

        if ($this->from->isAfter($this->to)) {
            throw new InvalidArgumentException(
                "Tanggal awal ({$this->from->toDateString()}) tidak boleh setelah tanggal akhir ({$this->to->toDateString()})."
            );
        }
    }

    /**
     * Factory method — buat DateRange dari string tanggal.
     *
     * @throws InvalidArgumentException
     */
    public static function make(string|\DateTimeInterface $from, string|\DateTimeInterface $to): self
    {
        return new self($from, $to);
    }

    /**
     * Kembalikan array [from, to] yang siap dipakai pada whereBetween().
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function toArray(): array
    {
        return [$this->from, $this->to];
    }
}
