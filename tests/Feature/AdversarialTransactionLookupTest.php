<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdversarialTransactionLookupTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customerMulti;

    private Customer $customerSingle;

    private Customer $customerThreeWords;

    private Service $service;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create([
            'name' => 'Staff Operator',
        ]);

        $this->customerMulti = Customer::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 45, Jakarta',
        ]);

        $this->customerSingle = Customer::factory()->create([
            'name' => 'Maulana',
            'phone' => '089876543210',
            'address' => 'Jl. Soekarno Hatta No. 12',
        ]);

        $this->customerThreeWords = Customer::factory()->create([
            'name' => 'Nayla Annora Nobel',
            'phone' => '085512349999',
            'address' => 'Jl. Diponegoro No. 8',
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Kering Standar',
            'price' => 10000,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'customer_id' => $this->customerMulti->id,
            'created_by' => $this->staff->id,
            'transaction_code' => 'TRX-0001',
            'total_amount' => 30000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addDay(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'weight' => 3.0,
            'unit_price' => 10000,
            'subtotal' => 30000,
        ]);
    }

    /**
     * PROBE 1: Malformed and XSS Payloads.
     * Ensure payloads are never rendered unescaped and do not trigger internal 500 server errors.
     */
    public function test_xss_script_payload_is_escaped_and_does_not_execute(): void
    {
        $this->withoutVite();

        $payload = '<script>alert(1)</script>';
        $response = $this->get(route('transactions.lookup', ['code' => $payload]));

        $response->assertOk();
        $response->assertDontSee($payload, false);
        $response->assertSee(e($payload), false);
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertDontSee('Perkiraan Selesai');
    }

    public function test_sql_injection_payload_is_treated_as_literal_string(): void
    {
        $this->withoutVite();

        $sqliPayload = "' OR '1'='1";
        $response = $this->get(route('transactions.lookup', ['code' => $sqliPayload]));

        $response->assertOk();
        $response->assertSee(e($sqliPayload), false);
        $response->assertSee('Transaksi Tidak Ditemukan');
        // Ensure SQL injection does not match any transaction card
        $response->assertDontSee('Nomor Nota Transaksi');
        $response->assertDontSee('Perkiraan Selesai');
    }

    public function test_extreme_sql_injection_union_payload_does_not_crash(): void
    {
        $this->withoutVite();

        $unionPayload = "TRX-0001' UNION SELECT 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20--";
        $response = $this->get(route('transactions.lookup', ['code' => $unionPayload]));

        $response->assertOk();
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertDontSee('Nomor Nota Transaksi');
        $response->assertDontSee('Perkiraan Selesai');
    }

    public function test_non_existent_transaction_code_returns_safe_friendly_card(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-99999999']));

        $response->assertOk();
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertSee('TRX-99999999');
        $response->assertSee('Coba Nomor Lain');
        $response->assertDontSee('Nomor Nota Transaksi');
        $response->assertDontSee('Perkiraan Selesai');
    }

    public function test_html_attribute_injection_payload_is_safely_escaped(): void
    {
        $this->withoutVite();

        $payload = '"><svg onload=alert(1)>';
        $response = $this->get(route('transactions.lookup', ['code' => $payload]));

        $response->assertOk();
        $response->assertDontSee($payload, false);
        $response->assertSee(e($payload), false);
        $response->assertDontSee('Nomor Nota Transaksi');
    }

    public function test_oversized_code_input_does_not_cause_unhandled_exception(): void
    {
        $this->withoutVite();

        $hugePayload = str_repeat('A', 5000);
        $response = $this->get(route('transactions.lookup', ['code' => $hugePayload]));

        $response->assertOk();
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertDontSee('Nomor Nota Transaksi');
    }

    /**
     * PROBE 2: Case Sensitivity and Normalization.
     */
    public function test_lookup_is_strictly_case_insensitive(): void
    {
        $this->withoutVite();

        // 1. Lowercase lookup
        $respLower = $this->get(route('transactions.lookup', ['code' => 'trx-0001']));
        $respLower->assertOk();
        $respLower->assertSee('TRX-0001');
        $respLower->assertSee('B*** S***');

        // 2. Uppercase lookup
        $respUpper = $this->get(route('transactions.lookup', ['code' => 'TRX-0001']));
        $respUpper->assertOk();
        $respUpper->assertSee('TRX-0001');
        $respUpper->assertSee('B*** S***');

        // 3. Mixed case lookup
        $respMixed = $this->get(route('transactions.lookup', ['code' => 'tRx-0001']));
        $respMixed->assertOk();
        $respMixed->assertSee('TRX-0001');
        $respMixed->assertSee('B*** S***');

        // 4. Case where database has lowercase and query has uppercase
        $trxLowerInDb = Transaction::create([
            'customer_id' => $this->customerSingle->id,
            'created_by' => $this->staff->id,
            'transaction_code' => 'trk-lowercase-99',
            'total_amount' => 10000,
            'status' => TransactionStatus::Diterima,
        ]);

        $respFromLower = $this->get(route('transactions.lookup', ['code' => 'TRK-LOWERCASE-99']));
        $respFromLower->assertOk();
        $respFromLower->assertSee('trk-lowercase-99');
        $respFromLower->assertSee('M***');
    }

    /**
     * PROBE 3: Customer Privacy and Data Leakage Prevention.
     */
    public function test_customer_name_masking_multi_word(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-0001']));

        $response->assertOk();
        $response->assertSee('B*** S***');
        $response->assertDontSee('Budi Santoso');
    }

    public function test_customer_name_masking_single_word(): void
    {
        $this->withoutVite();

        $trx = Transaction::create([
            'customer_id' => $this->customerSingle->id,
            'created_by' => $this->staff->id,
            'transaction_code' => 'TRX-SINGLE',
            'total_amount' => 10000,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-SINGLE']));

        $response->assertOk();
        $response->assertSee('M***');
        $response->assertDontSee('Maulana');
    }

    public function test_customer_name_masking_three_words(): void
    {
        $this->withoutVite();

        $trx = Transaction::create([
            'customer_id' => $this->customerThreeWords->id,
            'created_by' => $this->staff->id,
            'transaction_code' => 'TRX-THREE',
            'total_amount' => 10000,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-THREE']));

        $response->assertOk();
        $response->assertSee('N*** A*** N***');
        $response->assertDontSee('Nayla Annora Nobel');
    }

    public function test_customer_name_masking_irregular_whitespace(): void
    {
        $this->withoutVite();

        $cust = Customer::create([
            'name' => "  Dewi   \t  Sartika  ",
            'phone' => '087788990011',
        ]);

        $trx = Transaction::create([
            'customer_id' => $cust->id,
            'created_by' => $this->staff->id,
            'transaction_code' => 'TRX-WHITESPACE',
            'total_amount' => 10000,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-WHITESPACE']));

        $response->assertOk();
        $response->assertSee('D*** S***');
        $response->assertDontSee('Dewi Sartika');
    }

    public function test_customer_with_empty_name_defaults_to_pelanggan_umum(): void
    {
        $this->withoutVite();

        $cust = Customer::create([
            'name' => '',
            'phone' => '081200001111',
        ]);

        $trx = Transaction::create([
            'customer_id' => $cust->id,
            'created_by' => $this->staff->id,
            'transaction_code' => 'TRX-EMPTYNAME',
            'total_amount' => 10000,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-EMPTYNAME']));

        $response->assertOk();
        $response->assertSee('Pelanggan Umum');
    }

    public function test_customer_phone_and_address_are_never_exposed_on_public_lookup(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-0001']));

        $response->assertOk();
        // Complete phone number must NOT be in public HTML
        $response->assertDontSee('081234567890');
        // Address must NOT be in public HTML
        $response->assertDontSee('Jl. Merdeka No. 45');
        // Cashier/staff name should NOT be exposed
        $response->assertDontSee('Staff Operator');
    }

    /**
     * PROBE 4: Camera Scanner Lifecycle Elements & Wire-up Verification.
     */
    public function test_camera_modal_has_lifecycle_stop_listeners(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $content = $response->getContent();

        // 1. Esc key listener
        $this->assertStringContainsString("e.key === 'Escape'", $content);
        $this->assertStringContainsString('closeQrScannerModal()', $content);

        // 2. Backdrop click
        $this->assertStringContainsString('if (event.target === this) closeQrScannerModal()', $content);

        // 3. Close buttons (header and footer)
        $this->assertStringContainsString('onclick="closeQrScannerModal()"', $content);

        // 4. Stop scanner method
        $this->assertStringContainsString('await html5QrScannerInstance.stop()', $content);

        // 5. Beforeunload listener
        $this->assertStringContainsString("window.addEventListener('beforeunload'", $content);

        // 6. Switch camera button logic
        $this->assertStringContainsString('switchCamera()', $content);
    }
}
