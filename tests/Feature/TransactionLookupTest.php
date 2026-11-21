<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionLookupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tamu/guest dapat membuka halaman pelacakan transaksi tanpa login.
     */
    public function test_guest_can_access_lookup_page(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('Cek Status Cucian Anda');
        $response->assertSee('Masukkan Kode Transaksi');
        $response->assertSee('Cek Transaksi');
    }

    /**
     * Test tamu dapat mencari dan melihat rincian transaksi berdasarkan kode nota.
     */
    public function test_guest_can_search_and_find_transaction_details(): void
    {
        $this->withoutVite();

        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000,
            'estimated_duration' => 2,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-0001',
            'total_amount' => 21000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => Carbon::create(2026, 10, 6, 15, 0),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 3,
            'unit_price' => 7000,
            'subtotal' => 21000,
        ]);

        $staff = User::factory()->staff()->create();
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 10000,
            'payment_type' => 'DP',
            'method' => 'Transfer',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-0001']));

        $response->assertOk();
        $response->assertSee('TRX-0001');
        $response->assertSee('B*** S***');
        $response->assertDontSee('Budi Santoso');
        $response->assertSee('Cuci Kering');
        $response->assertSee('3 kg');
        $response->assertSee('21.000');
        $response->assertSee('DP');
        $response->assertSee('Diproses');
        $response->assertSee('2026');

        $responseLowercase = $this->get(route('transactions.lookup', ['code' => 'trx-0001']));
        $responseLowercase->assertOk();
        $responseLowercase->assertSee('TRX-0001');
        $responseLowercase->assertSee('B*** S***');
    }

    /**
     * Test pesan notifikasi ketika kode transaksi yang dicari tidak ditemukan.
     */
    public function test_guest_sees_not_found_message_when_transaction_code_is_invalid(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-INVALID-999']));

        $response->assertOk();
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertSee('TRX-INVALID-999');
    }

    /**
     * Test status pembayaran Lunas ditampilkan ketika pembayaran sudah penuh.
     */
    public function test_guest_sees_paid_full_status_when_transaction_is_lunas(): void
    {
        $this->withoutVite();

        $customer = Customer::create([
            'name' => 'Siti Aminah',
            'phone' => '08987654321',
        ]);

        $service = Service::create([
            'name' => 'Cuci + Setrika',
            'price' => 10000,
            'estimated_duration' => 2,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-0002',
            'total_amount' => 30000,
            'status' => TransactionStatus::Selesai,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 3,
            'unit_price' => 10000,
            'subtotal' => 30000,
        ]);

        $staff = User::factory()->staff()->create();
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 30000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'QRIS',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-0002']));

        $response->assertOk();
        $response->assertSee('TRX-0002');
        $response->assertSee('S*** A***');
        $response->assertDontSee('Siti Aminah');
        $response->assertSee('Lunas');
        $response->assertSee('Selesai');
    }

    /**
     * Test halaman login memiliki tautan cepat menuju cek transaksi publik.
     */
    public function test_login_page_contains_link_to_transaction_lookup(): void
    {
        $this->withoutVite();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee(route('transactions.lookup'));
        $response->assertSee('Cek Transaksi');
    }

    /**
     * Test pelanggan terautentikasi melihat halaman pelacakan di dalam layout aplikasi bersidebar.
     */
    public function test_authenticated_customer_sees_lookup_page_inside_app_layout(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('Lacak Nota Fisik');
        $response->assertSee('sidebar');
    }

    /**
     * Test halaman pelacakan memuat tombol pemindai kamera QR Code dan modal scanner.
     */
    public function test_lookup_page_renders_camera_qr_scanner_button_and_modal(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('id="btn-scan-qr"', false);
        $response->assertSee('Pindai QR');
        $response->assertSee('id="qr-scanner-modal"', false);
        $response->assertSee('id="qr-scanner-view"', false);
        $response->assertSee('id="qr-scanner-loading"', false);
        $response->assertSee('btn-switch-camera');
        $response->assertSee('openQrScannerModal');
        $response->assertSee('closeQrScannerModal');
    }

    /**
     * Test halaman pelacakan memuat kontainer riwayat pencarian cepat nota (LocalStorage chips).
     */
    public function test_lookup_page_renders_recent_searches_section(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('id="recent-searches-box"', false);
        $response->assertSee('id="recent-searches-chips"', false);
        $response->assertSee('Pencarian Terakhir:');
        $response->assertSee('Hapus Riwayat');
    }

    /**
     * Test pembukaan URL langsung dari hasil pindai QR struk thermal otomatis menampilkan status transaksi.
     */
    public function test_direct_access_via_scanned_receipt_qr_url_loads_transaction_details(): void
    {
        $this->withoutVite();

        $customer = Customer::create([
            'name' => 'Gargarina Nanda',
            'phone' => '087711223344',
        ]);

        $service = Service::create([
            'name' => 'Cuci Kilat Express',
            'price' => 15000,
            'estimated_duration' => 6,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRK-20261102-SCANNED',
            'total_amount' => 45000,
            'status' => TransactionStatus::Diproses,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 3.0,
            'unit_price' => 15000,
            'subtotal' => 45000,
        ]);

        $scannedUrl = route('transactions.lookup', ['code' => $transaction->transaction_code]);

        $response = $this->get($scannedUrl);

        $response->assertOk();
        $response->assertSee('TRK-20261102-SCANNED');
        $response->assertSee('G*** N***');
        $response->assertDontSee('Gargarina Nanda');
        $response->assertSee('Cuci Kilat Express');
        $response->assertSee('Diproses');
        $response->assertSee('45.000');
    }

    /**
     * Test halaman pelacakan publik bebas total dari karakter emoji kartun default (Strict Zero Emoji).
     */
    public function test_lookup_page_is_strictly_free_of_default_cartoon_emojis(): void
    {
        $this->withoutVite();

        $customer = Customer::create([
            'name' => 'Ahmad Kasir',
            'phone' => '089988776655',
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRK-EMOJI-CHECK',
            'total_amount' => 20000,
            'status' => TransactionStatus::Diterima,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => $transaction->transaction_code]));

        $response->assertOk();
        $content = $response->getContent();

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        $this->assertDoesNotMatchRegularExpression(
            $emojiPattern,
            $content,
            'Halaman pelacakan status transaksi tidak boleh memuat karakter emoji kartun default.'
        );
    }

    /**
     * Test halaman cetak struk thermal kasir memuat QR Code SVG tajam yang mengarah ke endpoint lookup publik.
     */
    public function test_thermal_receipt_print_view_renders_qr_code_svg_pointing_to_lookup_url(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::create([
            'name' => 'Maulana Kasir',
            'phone' => '081234567890',
        ]);

        $service = Service::create([
            'name' => 'Cuci Kering Regular',
            'price' => 7000,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261102-QR01',
            'total_amount' => 35000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addDay(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 5.0,
            'unit_price' => 7000,
            'subtotal' => 35000,
        ]);

        $expectedUrl = route('transactions.lookup', ['code' => $transaction->transaction_code]);

        $response = $this->actingAs($staff)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('<svg', false);
        $response->assertSee('shape-rendering="crispEdges"', false);
        $response->assertSee('Pindai QR Lacak Status');
        $response->assertSee(route('transactions.lookup'));
    }

    /**
     * Test halaman cetak struk thermal kasir memuat tombol WhatsApp jika nomor telepon pelanggan tersedia.
     */
    public function test_thermal_receipt_print_view_renders_whatsapp_share_button_when_phone_available(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::create([
            'name' => 'Nora Widyonarko',
            'phone' => '081298765432',
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261102-WA-PRINT',
            'total_amount' => 25000,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('Kirim WhatsApp');
        $response->assertSee('wa.me/6281298765432', false);
    }

    /**
     * Test accessor whatsapp_share_url pada model Transaction menghasilkan tautan wa.me valid dengan normalisasi nomor dan tanpa emoji.
     */
    public function test_transaction_whatsapp_share_url_normalizes_phone_and_contains_tracking_link(): void
    {
        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '+62 (812) 3456-7890',
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRK-20261102-WATEST',
            'total_amount' => 50000,
            'status' => TransactionStatus::Diproses,
        ]);

        $url = $transaction->whatsapp_share_url;

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6281234567890', $url);

        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $query);
        $message = $query['text'] ?? '';

        $expectedLookupUrl = route('transactions.lookup', ['code' => 'TRK-20261102-WATEST']);
        $this->assertStringContainsString($expectedLookupUrl, $message);
        $this->assertStringContainsString('TRK-20261102-WATEST', $message);
        $this->assertStringContainsString('Budi Santoso', $message);

        // Strict zero cartoon emoji check on WhatsApp URL & message
        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';
        $this->assertDoesNotMatchRegularExpression($emojiPattern, $url);
        $this->assertDoesNotMatchRegularExpression($emojiPattern, $message);

        // Edge case: test phone with +620 prefix normalization
        $customer->update(['phone' => '+62081234567890']);
        $transaction->refresh();
        $this->assertStringStartsWith('https://wa.me/6281234567890', $transaction->whatsapp_share_url);
    }

    /**
     * Test halaman detail transaksi kasir (staff.transactions.show) menampilkan tombol aksi WhatsApp.
     */
    public function test_cashier_transaction_show_page_displays_whatsapp_share_action(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customer = Customer::create([
            'name' => 'Dewi Sartika',
            'phone' => '087812345678',
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261102-SHOW-WA',
            'total_amount' => 30000,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('staff.transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('wa.me/6287812345678', false);
        $response->assertSee('Kirim WhatsApp');
    }
}
