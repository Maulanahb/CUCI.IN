<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\Reports\ServiceTurnaroundEvaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ServiceTurnaroundEvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create();
    }

    public function test_service_turnaround_evaluation_aggregates_duration_and_on_time_rate(): void
    {
        Carbon::setTestNow('2026-11-20 08:00:00');

        $reguler = Service::factory()->create([
            'name' => 'Cuci Reguler 2 Hari',
            'estimated_duration' => 48,
            'is_express' => false,
        ]);

        $express = Service::factory()->create([
            'name' => 'Cuci Kilat 6 Jam',
            'estimated_duration' => 6,
            'is_express' => true,
        ]);

        // Transaksi 1: Reguler, selesai dalam 24 jam (On Time karena target 48 jam)
        $trx1 = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Selesai,
            'created_at' => Carbon::parse('2026-11-20 08:00:00'),
        ]);
        TransactionDetail::create([
            'transaction_id' => $trx1->id,
            'service_id' => $reguler->id,
            'weight' => 2,
            'unit_price' => 10000,
            'subtotal' => 20000,
        ]);
        LaundryStatusHistory::create([
            'transaction_id' => $trx1->id,
            'status' => TransactionStatus::Selesai,
            'changed_by' => $this->staff->id,
            'changed_at' => Carbon::parse('2026-11-21 08:00:00'), // 24 jam
        ]);

        // Transaksi 2: Express, selesai dalam 8 jam (Terlambat karena target 6 jam)
        $trx2 = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Diambil,
            'created_at' => Carbon::parse('2026-11-20 09:00:00'),
        ]);
        TransactionDetail::create([
            'transaction_id' => $trx2->id,
            'service_id' => $express->id,
            'weight' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
        ]);
        LaundryStatusHistory::create([
            'transaction_id' => $trx2->id,
            'status' => TransactionStatus::Selesai,
            'changed_by' => $this->staff->id,
            'changed_at' => Carbon::parse('2026-11-20 17:00:00'), // 8 jam
        ]);

        $evaluator = new ServiceTurnaroundEvaluation;
        $result = $evaluator->evaluate(
            Carbon::parse('2026-11-01')->startOfDay(),
            Carbon::parse('2026-11-30')->endOfDay()
        );

        $this->assertCount(2, $result['evaluations']);

        $regulerEval = collect($result['evaluations'])->firstWhere('service_id', $reguler->id);
        $this->assertNotNull($regulerEval);
        $this->assertSame('Cuci Reguler 2 Hari', $regulerEval['service_name']);
        $this->assertFalse($regulerEval['is_express']);
        $this->assertEquals(24.0, $regulerEval['avg_duration_hours']);
        $this->assertEquals(100.0, $regulerEval['on_time_percentage']);

        $expressEval = collect($result['evaluations'])->firstWhere('service_id', $express->id);
        $this->assertNotNull($expressEval);
        $this->assertSame('Cuci Kilat 6 Jam', $expressEval['service_name']);
        $this->assertTrue($expressEval['is_express']);
        $this->assertEquals(8.0, $expressEval['avg_duration_hours']);
        $this->assertEquals(0.0, $expressEval['on_time_percentage']);

        $this->assertEquals(16.0, $result['overall_avg_hours']);
        $this->assertEquals(50.0, $result['overall_on_time_percentage']);
    }
}
