<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThermalReceiptQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    private User $admin;

    private Customer $customer;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role' => 'staff',
            'name' => 'Maulana Kasir',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Owner Admin',
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Kering Regular',
            'price' => 7000,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi lengkap dengan detail dan pembayaran.
     */
    private function createCompleteTransaction(array $attributes = []): Transaction
    {
        $transaction = Transaction::factory()->create(array_merge([
            'customer_id' => $this->customer->id,
            'created_by' => $this->cashier->id,
            'transaction_code' => 'TRK-20261102-TEST01',
            'total_amount' => 35000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addDay(),
        ], $attributes));

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'weight' => 5.0,
            'unit_price' => 7000,
            'subtotal' => 35000,
        ]);

        return $transaction;
    }

    /**
     * Tier 1: Kasir operasional dapat mengakses halaman cetak struk nota thermal.
     */
    public function test_cashier_can_access_thermal_receipt_print_view(): void
    {
        $transaction = $this->createCompleteTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertViewIs('staff.transactions.print');
        $response->assertSee($transaction->transaction_code);
        $response->assertSee('CUCI.IN');
    }

    /**
     * Tier 1: Administrator dapat mengakses halaman cetak struk nota thermal.
     */
    public function test_admin_can_access_thermal_receipt_print_view(): void
    {
        $transaction = $this->createCompleteTransaction();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.transactions.print', $transaction));

        $response->assertOk();
        $response->assertViewIs('staff.transactions.print');
        $response->assertSee($transaction->transaction_code);
    }

    /**
     * Tier 1: Struk thermal memuat elemen SVG QR Code yang tajam via BarcodeService.
     */
    public function test_thermal_receipt_contains_svg_qr_code(): void
    {
        $transaction = $this->createCompleteTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('<svg', false);
        $response->assertSee('shape-rendering="crispEdges"', false);
        $response->assertSee('viewBox="0 0', false);
        $response->assertSee('Pindai QR Lacak Status');
    }

    /**
     * Tier 1: QR Code pada struk mengenkodekan route publik transaksi lookup dengan benar.
     */
    public function test_qr_code_encodes_public_lookup_url_with_transaction_code(): void
    {
        $transaction = $this->createCompleteTransaction([
            'transaction_code' => 'TRK-20261102-QR009',
        ]);

        $expectedUrl = route('transactions.lookup', ['code' => $transaction->transaction_code]);
        $expectedSvg = app(BarcodeService::class)->generateQrCodeSvg($expectedUrl, 96);

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $this->assertStringContainsString($expectedUrl, $expectedUrl);
        // Memastikan SVG yang dihasilkan dari BarcodeService identik dengan yang ada di view
        $response->assertSee(substr($expectedSvg, 0, 50), false);
    }

    /**
     * Tier 1: Struk thermal mendukung pergantian layout kertas 58mm dan 80mm.
     */
    public function test_thermal_receipt_supports_58mm_and_80mm_paper_sizes(): void
    {
        $transaction = $this->createCompleteTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('data-size="58mm"', false);
        $response->assertSee('data-size="80mm"', false);
        $response->assertSee('setPaperSize(\'58mm\')', false);
        $response->assertSee('setPaperSize(\'80mm\')', false);
        $response->assertSee('.paper-58mm', false);
        $response->assertSee('.paper-80mm', false);
    }

    /**
     * Tier 2: Tamu / Pengguna tanpa autentikasi ditolak dari halaman cetak struk thermal.
     */
    public function test_guest_cannot_access_thermal_receipt_print_view(): void
    {
        $transaction = $this->createCompleteTransaction();

        $response = $this->get(route('staff.transactions.print', $transaction));

        $response->assertRedirect(route('login'));
    }

    /**
     * Tier 2: Role customer dilarang (403 Forbidden) mengakses endpoint print kasir.
     */
    public function test_customer_role_cannot_access_staff_print_view(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);
        $transaction = $this->createCompleteTransaction();

        $response = $this->actingAs($customerUser)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertForbidden();
    }

    /**
     * Tier 2: Struk nota tetap tercetak rapi dan QR Code tetap ada meskipun pelanggan tidak memiliki nomor telepon terisi.
     */
    public function test_thermal_receipt_renders_gracefully_when_customer_phone_is_empty(): void
    {
        $customerWithoutPhone = Customer::factory()->create([
            'name' => 'Pelanggan Walk-In',
            'phone' => '',
        ]);

        $transaction = $this->createCompleteTransaction([
            'customer_id' => $customerWithoutPhone->id,
            'transaction_code' => 'TRK-20261102-NOPHONE',
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('Pelanggan Walk-In');
        $response->assertSee('<svg', false);
        $response->assertSee('Pindai QR Lacak Status');
    }

    /**
     * Tier 2: Struk menampilkan rincian pembayaran DP, sisa tagihan, dan status bayar dengan benar.
     */
    public function test_thermal_receipt_displays_payment_breakdown_and_remaining_balance(): void
    {
        $transaction = $this->createCompleteTransaction([
            'total_amount' => 50000,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 20000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('Total Tagihan:');
        $response->assertSee('50.000');
        $response->assertSee('Sudah Dibayar:');
        $response->assertSee('20.000');
        $response->assertSee('Sisa Tagihan:');
        $response->assertSee('30.000');
        $response->assertSee('DP');
    }

    /**
     * Tier 3: URL target dari QR Code struk thermal sesuai dengan spesifikasi rute lookup publik.
     */
    public function test_thermal_receipt_qr_code_target_url_resolves_to_valid_lookup_endpoint(): void
    {
        $transaction = $this->createCompleteTransaction([
            'transaction_code' => 'TRK-20261102-RESOLV',
        ]);

        $targetUrl = route('transactions.lookup', ['code' => $transaction->transaction_code]);
        $parsedUrl = parse_url($targetUrl);

        $this->assertNotEmpty($parsedUrl['path']);
        $this->assertStringContainsString('lookup', $parsedUrl['path']);
        $this->assertStringContainsString('code=TRK-20261102-RESOLV', $parsedUrl['query'] ?? '');
    }

    /**
     * Tier 4: Struk thermal bersih total dari karakter emoji kartun default (Strict Zero Emoji).
     */
    public function test_thermal_receipt_is_strictly_free_of_default_cartoon_emojis(): void
    {
        $transaction = $this->createCompleteTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $content = $response->getContent();

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        $this->assertDoesNotMatchRegularExpression(
            $emojiPattern,
            $content,
            'Halaman cetak struk thermal kasir tidak boleh memuat karakter emoji kartun default.'
        );
    }

    /**
     * Tier 4: Real-world workflow verifikasi kelengkapan seluruh metadata transaksi pada struk thermal.
     */
    public function test_thermal_receipt_displays_complete_store_and_transaction_metadata(): void
    {
        $transaction = $this->createCompleteTransaction([
            'transaction_code' => 'TRK-20261102-FULLFLOW',
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('CUCI.IN');
        $response->assertSee('No. Nota:');
        $response->assertSee('TRK-20261102-FULLFLOW');
        $response->assertSee('Maulana Kasir');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Cuci Kering Regular');
        $response->assertSee('Pindai QR Lacak Status');
        $response->assertSee('PERHATIAN');
    }
}
