<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Services\TransactionCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test kalkulasi untuk satu item transaksi.
     */
    public function test_calculate_single_item(): void
    {
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 10000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $referenceTime = Carbon::parse('2026-10-15 10:00:00');

        $result = TransactionCalculator::calculate([
            [
                'service_id' => $service->id,
                'weight' => 3,
            ],
        ], $referenceTime);

        $this->assertCount(1, $result['items']);
        $this->assertEquals($service->id, $result['items'][0]['service_id']);
        $this->assertEquals('3.00', $result['items'][0]['weight']);
        $this->assertEquals('10000.00', $result['items'][0]['unit_price']);
        $this->assertEquals('30000.00', $result['items'][0]['subtotal']);

        $this->assertEquals('30000.00', $result['total_amount']);
        $this->assertInstanceOf(CarbonInterface::class, $result['estimated_completed_at']);
        $this->assertTrue($result['estimated_completed_at']->equalTo(Carbon::parse('2026-10-16 10:00:00')));
        $this->assertEquals(24, $result['estimated_duration']);
    }

    /**
     * Test kalkulasi untuk banyak item transaksi.
     */
    public function test_calculate_multiple_items(): void
    {
        $serviceA = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $serviceB = Service::create([
            'name' => 'Setrika',
            'price' => 5000.00,
            'estimated_duration' => 12,
            'is_active' => true,
        ]);

        $result = TransactionCalculator::calculate([
            ['service_id' => $serviceA->id, 'weight' => 2],
            ['service_id' => $serviceB->id, 'weight' => 3],
        ]);

        $this->assertCount(2, $result['items']);
        $this->assertEquals('14000.00', $result['items'][0]['subtotal']);
        $this->assertEquals('15000.00', $result['items'][1]['subtotal']);
        $this->assertEquals('29000.00', $result['total_amount']);
    }

    /**
     * Test kalkulasi dengan berat desimal dan pembulatan konsisten.
     */
    public function test_calculate_decimal_weight_with_consistent_rounding(): void
    {
        $serviceA = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 48,
            'is_active' => true,
        ]);

        $serviceB = Service::create([
            'name' => 'Cuci Express',
            'price' => 9999.00,
            'estimated_duration' => 6,
            'is_active' => true,
        ]);

        $result = TransactionCalculator::calculate([
            ['service_id' => $serviceA->id, 'weight' => 2.35],
            ['service_id' => $serviceB->id, 'weight' => 1.33],
        ]);

        // 7000 * 2.35 = 16450.00
        $this->assertEquals('16450.00', $result['items'][0]['subtotal']);

        // 9999 * 1.33 = 13298.67
        $this->assertEquals('13298.67', $result['items'][1]['subtotal']);

        // 16450.00 + 13298.67 = 29748.67
        $this->assertEquals('29748.67', $result['total_amount']);
    }

    /**
     * Test durasi terlama yang digunakan untuk menentukan estimated_completed_at.
     */
    public function test_calculate_uses_longest_service_duration_for_estimated_completed_at(): void
    {
        $short = Service::create([
            'name' => 'Express',
            'price' => 15000.00,
            'estimated_duration' => 6,
            'is_active' => true,
        ]);

        $longest = Service::create([
            'name' => 'Bedcover',
            'price' => 25000.00,
            'estimated_duration' => 72,
            'is_active' => true,
        ]);

        $medium = Service::create([
            'name' => 'Reguler',
            'price' => 10000.00,
            'estimated_duration' => 48,
            'is_active' => true,
        ]);

        $baseTime = Carbon::parse('2026-10-15 08:00:00');

        $result = TransactionCalculator::calculate([
            ['service_id' => $short->id, 'weight' => 1],
            ['service_id' => $longest->id, 'weight' => 4],
            ['service_id' => $medium->id, 'weight' => 2],
        ], $baseTime);

        $this->assertEquals(72, $result['estimated_duration']);
        $this->assertTrue(
            $result['estimated_completed_at']->equalTo(Carbon::parse('2026-10-18 08:00:00')),
            'Estimasi selesai harus menggunakan durasi terlama (72 jam) dari layanan yang dipilih.'
        );
    }

    /**
     * Test service nonaktif ditolak dan melempar InvalidArgumentException.
     */
    public function test_inactive_service_is_rejected(): void
    {
        $inactiveService = Service::create([
            'name' => 'Layanan Lama',
            'price' => 8000.00,
            'estimated_duration' => 24,
            'is_active' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak aktif/i');

        TransactionCalculator::calculate([
            ['service_id' => $inactiveService->id, 'weight' => 2],
        ]);
    }

    /**
     * Test service yang tidak ditemukan ditolak.
     */
    public function test_nonexistent_service_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak ditemukan/i');

        TransactionCalculator::calculate([
            ['service_id' => 999999, 'weight' => 2],
        ]);
    }

    /**
     * Test daftar item kosong ditolak.
     */
    public function test_empty_items_list_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak boleh kosong/i');

        TransactionCalculator::calculate([]);
    }

    /**
     * Test berat 0 dan negatif ditolak.
     */
    public function test_zero_and_negative_weight_is_rejected(): void
    {
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        // Uji berat 0
        try {
            TransactionCalculator::calculate([
                ['service_id' => $service->id, 'weight' => 0],
            ]);
            $this->fail('Seharusnya melempar InvalidArgumentException saat weight = 0');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('lebih besar dari 0', $e->getMessage());
        }

        // Uji berat negatif
        try {
            TransactionCalculator::calculate([
                ['service_id' => $service->id, 'weight' => -1.5],
            ]);
            $this->fail('Seharusnya melempar InvalidArgumentException saat weight negatif');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('lebih besar dari 0', $e->getMessage());
        }
    }

    /**
     * Test harga atau subtotal dari input luar diabaikan (mengambil dari database).
     */
    public function test_external_price_input_is_ignored(): void
    {
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 12000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        // Manipulasi input dengan harga murah palsu dan durasi palsu
        $result = TransactionCalculator::calculate([
            [
                'service_id' => $service->id,
                'weight' => 2,
                'unit_price' => 100, // Harus diabaikan
                'price' => 50,       // Harus diabaikan
                'subtotal' => 200,   // Harus diabaikan
                'estimated_duration' => 1, // Harus diabaikan
            ],
        ]);

        // Harus menggunakan harga resmi database (12000.00 * 2 = 24000.00)
        $this->assertEquals('12000.00', $result['items'][0]['unit_price']);
        $this->assertEquals('24000.00', $result['items'][0]['subtotal']);
        $this->assertEquals('24000.00', $result['total_amount']);
        $this->assertEquals(24, $result['estimated_duration']);
    }

    /**
     * Test kalkulator dapat dipanggil sebagai instance object maupun invokable.
     */
    public function test_calculator_can_be_called_via_instance_and_invoke(): void
    {
        $service = Service::create([
            'name' => 'Setrika Cepat',
            'price' => 6000.00,
            'estimated_duration' => 12,
            'is_active' => true,
        ]);

        $calculator = new TransactionCalculator;

        $result1 = $calculator->calculate([
            ['service_id' => $service->id, 'weight' => 2],
        ]);

        $result2 = $calculator([
            ['service_id' => $service->id, 'weight' => 2],
        ]);

        $this->assertEquals('12000.00', $result1['total_amount']);
        $this->assertEquals('12000.00', $result2['total_amount']);
    }

    /**
     * Test kalkulator menyertakan metadata unit dan mendeteksi paket kilat (express).
     */
    public function test_calculate_with_advanced_service_catalog_and_express_metadata(): void
    {
        $serviceReguler = Service::create([
            'name' => 'Cuci Kering Standar',
            'price' => 7000.00,
            'unit' => 'kg',
            'service_type' => 'reguler',
            'is_express' => false,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $serviceExpress = Service::create([
            'name' => 'Cuci Kilat Express 4 Jam',
            'price' => 18000.00,
            'unit' => 'kg',
            'service_type' => 'express',
            'is_express' => true,
            'estimated_duration' => 4,
            'is_active' => true,
        ]);

        $servicePcs = Service::create([
            'name' => 'Cuci Sepatu Sneaker',
            'price' => 25000.00,
            'unit' => 'pcs',
            'service_type' => 'khusus',
            'is_express' => false,
            'estimated_duration' => 48,
            'is_active' => true,
        ]);

        $result = TransactionCalculator::calculate([
            ['service_id' => $serviceExpress->id, 'weight' => 2],
            ['service_id' => $servicePcs->id, 'weight' => 1],
        ]);

        $this->assertTrue($result['has_express']);
        $this->assertEquals('61000.00', $result['total_amount']);
        $this->assertEquals(48, $result['estimated_duration']);

        $this->assertTrue($result['items'][0]['is_express']);
        $this->assertEquals('Kg', $result['items'][0]['unit_label']);
        $this->assertFalse($result['items'][1]['is_express']);
        $this->assertEquals('Pcs', $result['items'][1]['unit_label']);
    }
}
