<?php

namespace Tests\Feature;

use App\Services\Reports\DateRange;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    public function test_normalizes_from_to_start_of_day(): void
    {
        $range = DateRange::make('2026-10-15 14:30:00', '2026-10-20 09:00:00');

        $this->assertEquals('2026-10-15 00:00:00', $range->from->format('Y-m-d H:i:s'));
    }

    public function test_normalizes_to_to_end_of_day(): void
    {
        $range = DateRange::make('2026-10-15', '2026-10-20');

        $this->assertEquals('2026-10-20 23:59:59', $range->to->format('Y-m-d H:i:s'));
    }

    public function test_transaction_at_23_59_59_falls_within_range(): void
    {
        $range = DateRange::make('2026-10-20', '2026-10-20');

        $lateTransaction = CarbonImmutable::parse('2026-10-20 23:59:59');

        $this->assertTrue(
            $lateTransaction->greaterThanOrEqualTo($range->from)
            && $lateTransaction->lessThanOrEqualTo($range->to),
            'Transaksi pada 23:59:59 harus masuk ke dalam rentang tanggal.'
        );
    }

    public function test_rejects_reversed_date_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DateRange::make('2026-10-20', '2026-10-15');
    }

    public function test_same_day_range_is_valid(): void
    {
        $range = DateRange::make('2026-10-15', '2026-10-15');

        $this->assertEquals('2026-10-15 00:00:00', $range->from->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-10-15 23:59:59', $range->to->format('Y-m-d H:i:s'));
    }

    public function test_to_array_returns_ordered_pair(): void
    {
        $range = DateRange::make('2026-10-10', '2026-10-20');

        $arr = $range->toArray();

        $this->assertCount(2, $arr);
        $this->assertInstanceOf(CarbonImmutable::class, $arr[0]);
        $this->assertInstanceOf(CarbonImmutable::class, $arr[1]);
        $this->assertTrue($arr[0]->isBefore($arr[1]));
    }

    public function test_accepts_datetime_interface_objects(): void
    {
        $from = new \DateTimeImmutable('2026-10-15');
        $to = new \DateTimeImmutable('2026-10-20');

        $range = DateRange::make($from, $to);

        $this->assertEquals('2026-10-15', $range->from->toDateString());
        $this->assertEquals('2026-10-20', $range->to->toDateString());
    }
}
