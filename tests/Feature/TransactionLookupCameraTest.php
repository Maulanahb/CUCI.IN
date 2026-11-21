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

class TransactionLookupCameraTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    private Customer $customer;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role' => 'staff',
            'name' => 'Maulana Kasir',
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Ahmad Dahlan',
            'phone' => '081298765432',
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Komplit Express',
            'price' => 15000,
            'estimated_duration' => 12,
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi siap lacak.
     */
    private function createTrackableTransaction(array $attributes = []): Transaction
    {
        $transaction = Transaction::factory()->create(array_merge([
            'customer_id' => $this->customer->id,
            'created_by' => $this->cashier->id,
            'transaction_code' => 'TRK-20261102-CAM01',
            'total_amount' => 45000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addHours(12),
        ], $attributes));

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'weight' => 3.0,
            'unit_price' => 15000,
            'subtotal' => 45000,
        ]);

        return $transaction;
    }

    /**
     * Tier 1: Rute publik /lookup dapat diakses oleh pengguna tamu (guest) dengan status HTTP 200.
     */
    public function test_public_lookup_route_is_accessible_to_guests(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('Cek Status Cucian Anda');
        $response->assertSee('CUCI.IN');
    }

    /**
     * Tier 1: Halaman /lookup menyediakan tombol kontrol untuk memicu pemindai kamera (Scan QR).
     */
    public function test_lookup_page_contains_scan_qr_trigger_button(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('id="btn-scan-qr"', false);
        $response->assertSee('openQrScannerModal()', false);
        $response->assertSee('Pindai QR');
    }

    /**
     * Tier 1: Halaman /lookup memuat modal pemindai kamera dan viewport viewfinder (html5-qrcode).
     */
    public function test_lookup_page_contains_camera_viewfinder_and_modal_elements(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('id="qr-scanner-modal"', false);
        $response->assertSee('id="qr-scanner-view"', false);
        $response->assertSee('Pindai QR Nota');
    }

    /**
     * Tier 1: Halaman /lookup menyediakan input teks kode transaksi untuk fallback manual.
     */
    public function test_lookup_page_contains_transaction_code_input_and_submit_controls(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('name="code"', false);
        $response->assertSee('id="code"', false);
        $response->assertSee('Cek Transaksi');
    }

    /**
     * Tier 1: Layout tamu pada /lookup menyertakan asset Livewire/Alpine (@livewireStyles & @livewireScripts).
     */
    public function test_guest_lookup_view_includes_livewire_styles_and_scripts(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();

        // Guest view must include Livewire scripts/styles so Alpine.js functions for guest visitors
        $content = $response->getContent();
        $hasLivewire = str_contains($content, 'livewire') || str_contains($content, 'Livewire');

        $this->assertTrue(
            $hasLivewire,
            'Halaman lookup untuk tamu harus menyertakan asset @livewireStyles / @livewireScripts agar Alpine.js berfungsi.'
        );
    }

    /**
     * Tier 2: Pencarian tanpa parameter kode merender tampilan awal yang bersih.
     */
    public function test_lookup_without_query_parameter_renders_clean_initial_state(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertDontSee('Nomor Nota Transaksi');
        $response->assertDontSee('Perkiraan Selesai');
        $response->assertDontSee('Transaksi Tidak Ditemukan');
    }

    /**
     * Tier 2: Pencarian dengan kode transaksi yang tidak terdaftar menampilkan pesan error yang ramah.
     */
    public function test_lookup_with_nonexistent_transaction_code_displays_not_found_card(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRK-NONEXISTENT-999']));

        $response->assertOk();
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertSee('TRK-NONEXISTENT-999');
        $response->assertSee('Coba Nomor Lain');
    }

    /**
     * Tier 2: Input kode yang mengandung karakter khusus / tag HTML di-escape secara aman (Anti-XSS).
     */
    public function test_lookup_with_malformed_or_xss_code_is_escaped_safely(): void
    {
        $this->withoutVite();

        $xssPayload = '<script>alert("xss")</script>';
        $response = $this->get(route('transactions.lookup', ['code' => $xssPayload]));

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee(e($xssPayload), false);
    }

    /**
     * Tier 2: Modal pemindai kamera memiliki indikator loading dan penanganan penolakan izin kamera.
     */
    public function test_camera_scanner_modal_contains_permission_error_and_loading_states(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('id="qr-scanner-loading"', false);
        $response->assertSee('Menyiapkan modul kamera');
        $response->assertSee('id="qr-scanner-error"', false);
        $response->assertSee('Tidak dapat mengakses kamera');
    }

    /**
     * Tier 3: Halaman /lookup bersih total dari karakter emoji kartun default (Strict Zero Emoji).
     */
    public function test_lookup_page_is_strictly_free_of_default_cartoon_emojis(): void
    {
        $this->withoutVite();

        // Cek 1: Tampilan awal kosong
        $responseEmpty = $this->get(route('transactions.lookup'));
        $responseEmpty->assertOk();

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        $this->assertDoesNotMatchRegularExpression(
            $emojiPattern,
            $responseEmpty->getContent(),
            'Halaman lookup publik awal tidak boleh memuat karakter emoji kartun default.'
        );

        // Cek 2: Tampilan dengan data transaksi
        $transaction = $this->createTrackableTransaction();
        $responseResult = $this->get(route('transactions.lookup', ['code' => $transaction->transaction_code]));
        $responseResult->assertOk();

        $this->assertDoesNotMatchRegularExpression(
            $emojiPattern,
            $responseResult->getContent(),
            'Halaman lookup publik hasil pencarian tidak boleh memuat karakter emoji kartun default.'
        );
    }

    /**
     * Tier 3: Pencarian dengan kode valid menampilkan stepper progres, rincian item, dan nama tersamar.
     */
    public function test_lookup_with_valid_transaction_code_renders_full_progress_and_masked_customer(): void
    {
        $this->withoutVite();

        $transaction = $this->createTrackableTransaction([
            'transaction_code' => 'TRK-20261102-VALID01',
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRK-20261102-VALID01']));

        $response->assertOk();
        $response->assertSee('TRK-20261102-VALID01');
        $response->assertSee('Cuci Komplit Express');
        $response->assertSee('45.000');
        $response->assertSee('A*** D***');
        $response->assertDontSee('Ahmad Dahlan');
        $response->assertSee('Diproses');
    }

    /**
     * Tier 4: Simulasi pemindaian QR struk: membuka URL hasil scan mendarat langsung pada data nota.
     */
    public function test_customer_scans_receipt_qr_simulated_by_direct_navigation_to_encoded_url(): void
    {
        $this->withoutVite();

        $transaction = $this->createTrackableTransaction([
            'transaction_code' => 'TRK-20261102-SCANNED',
        ]);

        // URL yang terkodekan dalam QR struk thermal
        $encodedQrTargetUrl = route('transactions.lookup', ['code' => $transaction->transaction_code]);

        // Simulasi kamera smartphone atau scanner membuka URL tersebut
        $response = $this->get($encodedQrTargetUrl);

        $response->assertOk();
        $response->assertSee('TRK-20261102-SCANNED');
        $response->assertSee('Status Cucian');
        $response->assertSee('Perkiraan Selesai');
    }

    /**
     * Tier 4: Pelanggan yang belum lunas dapat mengakses opsi upload bukti transfer non-tunai.
     */
    public function test_lookup_page_renders_guest_payment_proof_upload_form_when_balance_unpaid(): void
    {
        $this->withoutVite();

        $transaction = $this->createTrackableTransaction([
            'transaction_code' => 'TRK-20261102-UNPAID',
            'total_amount' => 50000,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => $transaction->transaction_code]));

        $response->assertOk();
        $response->assertSee('Bayar Non-Tunai');
        $response->assertSee('name="proof_file"', false);
        $response->assertSee('name="phone_last_4"', false);
    }
}
