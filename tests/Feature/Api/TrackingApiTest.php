<?php

namespace Tests\Feature\Api;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test API mengembalikan status 200
     * jika kode nota ditemukan.
     */
    public function test_tracking_api_returns_200_when_transaction_exists(): void
    {
        // Membuat data customer
        $customer = Customer::factory()->create();

        // Membuat data transaksi
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => null,
            'transaction_code' => 'TRK-TEST-001',
            'status' => TransactionStatus::Diproses,
        ]);

        // Mengakses endpoint API tanpa login
        $response = $this->getJson(
            '/api/v1/track/TRK-TEST-001'
        );

        // Memastikan status HTTP 200
        $response->assertStatus(200);

        // Memastikan struktur JSON
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'transaction_code',
                'status',
                'estimated_completed_at',
                'last_updated_at',
            ],
        ]);

        // Memastikan data sesuai database
        $response->assertJsonPath(
            'data.transaction_code',
            'TRK-TEST-001'
        );

        $response->assertJsonPath(
            'data.status',
            'Diproses'
        );
    }

    /**
     * Test API mengembalikan status 404
     * jika kode nota tidak ditemukan.
     */
    public function test_tracking_api_returns_404_when_transaction_not_found(): void
    {
        // Mengakses endpoint dengan kode nota yang tidak tersedia
        $response = $this->getJson(
            '/api/v1/track/TRK-NOT-FOUND'
        );

        // Memastikan status HTTP 404
        $response->assertStatus(404);

        // Memastikan pesan error
        $response->assertJson([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan.',
        ]);
    }
}
