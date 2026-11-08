<?php

namespace Tests\Feature\Api;

use App\Enums\TransactionStatus;
use App\Http\Resources\TransactionDetailResource;
use App\Http\Resources\TransactionResource;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Tests\TestCase;

class TransactionResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Customer $customer;

    private Service $service;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);
        $this->service = Service::factory()->create([
            'name' => 'Cuci Kering Wangi',
            'service_type' => 'kiloan',
            'price' => 12000,
            'unit' => 'kg',
            'is_express' => false,
        ]);

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-TEST-0001',
            'total_amount' => 36000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addDays(2),
        ]);

        TransactionDetail::create([
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'weight' => 3.0,
            'unit_price' => 12000,
            'subtotal' => 36000,
        ]);
    }

    public function test_tracking_api_returns_transaction_resource_structure(): void
    {
        // Berikan pembayaran lunas
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 36000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
        ]);

        $response = $this->getJson(route('api.tracking.show', ['transaction_code' => 'TRX-TEST-0001']));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'transaction_code',
                    'status',
                    'customer' => ['name', 'phone'],
                    'total_amount',
                    'total_paid',
                    'remaining_amount',
                    'is_paid',
                    'estimated_completed_at',
                    'created_at',
                    'last_updated_at',
                    'items' => [
                        '*' => [
                            'id',
                            'service_name',
                            'service_category',
                            'is_express',
                            'weight',
                            'unit',
                            'unit_price',
                            'subtotal',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');

        // Pastikan format uang adalah integer, bukan string/float dengan desimal
        $this->assertSame(36000, $data['total_amount']);
        $this->assertSame(36000, $data['total_paid']);
        $this->assertSame(0, $data['remaining_amount']);
        $this->assertTrue($data['is_paid']);

        // Pastikan detail layanan sesuai
        $this->assertCount(1, $data['items']);
        $this->assertSame('Cuci Kering Wangi', $data['items'][0]['service_name']);
        $this->assertSame(12000, $data['items'][0]['unit_price']);
        $this->assertSame(36000, $data['items'][0]['subtotal']);
        $this->assertSame(3.0, (float) $data['items'][0]['weight']);
    }

    public function test_tracking_api_is_case_insensitive(): void
    {
        $response = $this->getJson('/api/v1/track/trx-test-0001');

        $response->assertOk()
            ->assertJsonPath('data.transaction_code', 'TRX-TEST-0001');
    }

    public function test_tracking_api_returns_404_when_code_not_found(): void
    {
        $response = $this->getJson('/api/v1/track/TRX-NOT-FOUND-999');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ]);
    }

    public function test_transaction_resource_prevents_n_plus_one_via_when_loaded(): void
    {
        // Ambil transaksi tanpa eager loading relasi
        $unloadedTrx = Transaction::query()->find($this->transaction->id);

        $resource = new TransactionResource($unloadedTrx);
        $array = $resource->toArray(Request::create('/api/v1/track/test'));

        // whenLoaded() mengembalikan MissingValue bila relasi belum di-load
        $this->assertInstanceOf(MissingValue::class, $array['customer']);
        $this->assertInstanceOf(MissingValue::class, $array['items']->resource);
    }

    public function test_transaction_detail_resource_formats_money_as_integer(): void
    {
        $detail = TransactionDetail::create([
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'weight' => 2.5,
            'unit_price' => 15000.00,
            'subtotal' => 37500.00,
        ]);

        $resource = new TransactionDetailResource($detail);
        $array = $resource->toArray(Request::create('/'));

        $this->assertIsInt($array['unit_price']);
        $this->assertSame(15000, $array['unit_price']);
        $this->assertIsInt($array['subtotal']);
        $this->assertSame(37500, $array['subtotal']);
    }
}
