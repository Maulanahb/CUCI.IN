<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionWhatsAppNotificationTest extends TestCase
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
            'name' => 'Nora Widyonarko',
            'phone' => '081234567890',
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Kilat 1 Hari',
            'price' => 12000,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat transaksi sampel.
     */
    private function createTransaction(array $attributes = []): Transaction
    {
        $transaction = Transaction::factory()->create(array_merge([
            'customer_id' => $this->customer->id,
            'created_by' => $this->cashier->id,
            'transaction_code' => 'TRK-20261102-WA01',
            'total_amount' => 60000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addDay(),
        ], $attributes));

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'weight' => 5.0,
            'unit_price' => 12000,
            'subtotal' => 60000,
        ]);

        return $transaction;
    }

    /**
     * Helper untuk mengekstrak nomor telepon dan parameter pesan text dari URL wa.me.
     *
     * @return array{phone: string, text: string}
     */
    private function parseWhatsAppUrl(string $url): array
    {
        $parsed = parse_url($url);
        $path = trim($parsed['path'] ?? '', '/');
        parse_str($parsed['query'] ?? '', $query);

        return [
            'phone' => $path,
            'text' => $query['text'] ?? '',
        ];
    }

    /**
     * Tier 1: Accessor model whatsapp_share_url menghasilkan tautan wa.me yang valid.
     */
    public function test_transaction_model_provides_whatsapp_share_url_accessor(): void
    {
        $transaction = $this->createTransaction();

        $url = $transaction->whatsapp_share_url;

        $this->assertNotNull($url, 'Transaction::whatsapp_share_url harus mengembalikan URL string');
        $this->assertStringStartsWith('https://wa.me/62', $url);
        $this->assertStringContainsString('?text=', $url);
    }

    /**
     * Tier 1: Normalisasi nomor telepon awalan standar '08' menjadi '628'.
     */
    public function test_phone_number_normalization_standard_08_to_628(): void
    {
        $this->customer->update(['phone' => '08123456789']);
        $transaction = $this->createTransaction();

        $url = $transaction->whatsapp_share_url;
        $this->assertNotNull($url);

        $parsed = $this->parseWhatsAppUrl($url);
        $this->assertEquals('628123456789', $parsed['phone']);
    }

    /**
     * Tier 1: Normalisasi nomor telepon awalan '+62' dan yang sudah berawalan '62'.
     */
    public function test_phone_number_normalization_plus_62_and_already_62(): void
    {
        // Kasus 1: Format '+628...'
        $this->customer->update(['phone' => '+628123456789']);
        $transaction = $this->createTransaction();

        $url1 = $transaction->whatsapp_share_url;
        $this->assertNotNull($url1);
        $this->assertEquals('628123456789', $this->parseWhatsAppUrl($url1)['phone']);

        // Kasus 2: Format '628...'
        $this->customer->update(['phone' => '628123456789']);
        $transaction->refresh();

        $url2 = $transaction->whatsapp_share_url;
        $this->assertNotNull($url2);
        $this->assertEquals('628123456789', $this->parseWhatsAppUrl($url2)['phone']);

        // Kasus 3: Format '+6208...' (edge case strip leading 0 after 62)
        $this->customer->update(['phone' => '+6208123456789']);
        $transaction->refresh();

        $url3 = $transaction->whatsapp_share_url;
        $this->assertNotNull($url3);
        $this->assertEquals('628123456789', $this->parseWhatsAppUrl($url3)['phone']);
    }

    /**
     * Tier 1: Normalisasi nomor telepon dengan karakter strip, spasi, dan tanda kurung.
     */
    public function test_phone_number_normalization_strips_dashes_spaces_and_parentheses(): void
    {
        $this->customer->update(['phone' => '+62 (812) 3456-7890']);
        $transaction = $this->createTransaction();

        $url = $transaction->whatsapp_share_url;
        $this->assertNotNull($url);

        $parsed = $this->parseWhatsAppUrl($url);
        $this->assertEquals('6281234567890', $parsed['phone']);
    }

    /**
     * Tier 1: Pesan WhatsApp memuat nomor nota, nama pelanggan, total tagihan, status, dan link /lookup.
     */
    public function test_whatsapp_message_content_contains_all_essential_transaction_details(): void
    {
        $transaction = $this->createTransaction([
            'transaction_code' => 'TRK-20261102-MSG01',
            'total_amount' => 60000,
            'status' => TransactionStatus::Diproses,
        ]);

        $url = $transaction->whatsapp_share_url;
        $this->assertNotNull($url);

        $parsed = $this->parseWhatsAppUrl($url);
        $message = $parsed['text'];

        $this->assertStringContainsString('TRK-20261102-MSG01', $message);
        $this->assertStringContainsString($this->customer->name, $message);
        $this->assertStringContainsString('60.000', $message);
        $this->assertStringContainsString('Diproses', $message);

        $expectedLookupUrl = route('transactions.lookup', ['code' => 'TRK-20261102-MSG01']);
        $this->assertStringContainsString($expectedLookupUrl, $message);
    }

    /**
     * Tier 2: Accessor mengembalikan null jika nomor telepon pelanggan kosong string.
     */
    public function test_whatsapp_share_url_returns_null_when_customer_has_empty_phone(): void
    {
        $this->customer->update(['phone' => '']);
        $transaction = $this->createTransaction();

        $this->assertNull($transaction->whatsapp_share_url);
    }

    /**
     * Tier 2: Accessor mengembalikan null jika nomor telepon tidak valid atau terlalu pendek (< 9 digit).
     */
    public function test_whatsapp_share_url_returns_null_for_too_short_or_invalid_phone_numbers(): void
    {
        // Format terlalu pendek
        $this->customer->update(['phone' => '0812']);
        $transaction = $this->createTransaction();
        $this->assertNull($transaction->whatsapp_share_url);

        // Karakter non-digit saja
        $this->customer->update(['phone' => 'ABCD-EFGH']);
        $transaction->refresh();
        $this->assertNull($transaction->whatsapp_share_url);
    }

    /**
     * Tier 2: Format pesan beradaptasi dengan benar antara status Lunas vs Sisa Tagihan (DP).
     */
    public function test_whatsapp_message_reflects_payment_status_correctly_for_lunas_and_unpaid(): void
    {
        $transaction = $this->createTransaction(['total_amount' => 50000]);

        // Skenario 1: Belum lunas / DP
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 20000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);

        $urlDp = $transaction->whatsapp_share_url;
        $this->assertNotNull($urlDp);
        $msgDp = $this->parseWhatsAppUrl($urlDp)['text'];
        $this->assertStringContainsString('Sisa Tagihan: Rp 30.000', $msgDp);

        // Skenario 2: Lunas penuh
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 30000,
            'payment_type' => 'Pelunasan',
            'method' => 'QRIS',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);

        $transaction->unsetRelation('payments');
        $urlLunas = $transaction->whatsapp_share_url;
        $this->assertNotNull($urlLunas);
        $msgLunas = $this->parseWhatsAppUrl($urlLunas)['text'];
        $this->assertStringContainsString('Lunas', $msgLunas);
    }

    /**
     * Tier 3: Tautan WhatsApp dan isi pesan bebas total dari karakter emoji kartun default (Strict Zero Emoji).
     */
    public function test_whatsapp_share_url_and_message_strictly_free_of_default_cartoon_emojis(): void
    {
        $transaction = $this->createTransaction();

        $url = $transaction->whatsapp_share_url;
        $this->assertNotNull($url);

        $rawMessage = $this->parseWhatsAppUrl($url)['text'];

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        $this->assertDoesNotMatchRegularExpression(
            $emojiPattern,
            $url,
            'URL WhatsApp wa.me tidak boleh memuat karakter emoji kartun default.'
        );

        $this->assertDoesNotMatchRegularExpression(
            $emojiPattern,
            $rawMessage,
            'Teks pesan WhatsApp notifikasi kasir tidak boleh memuat karakter emoji kartun default.'
        );
    }

    /**
     * Tier 3: Link pelacakan di dalam pesan WhatsApp sama persis dengan link tujuan QR Code struk thermal.
     */
    public function test_whatsapp_share_url_tracking_link_matches_receipt_qr_code_destination(): void
    {
        $transaction = $this->createTransaction([
            'transaction_code' => 'TRK-20261102-MATCH',
        ]);

        $url = $transaction->whatsapp_share_url;
        $this->assertNotNull($url);

        $message = $this->parseWhatsAppUrl($url)['text'];
        $expectedLookupUrl = route('transactions.lookup', ['code' => 'TRK-20261102-MATCH']);

        $this->assertStringContainsString($expectedLookupUrl, $message);
    }

    /**
     * Tier 4: Halaman detail transaksi kasir (staff.transactions.show) menampilkan tombol aksi WhatsApp.
     */
    public function test_cashier_transaction_show_page_displays_whatsapp_share_action(): void
    {
        $this->withoutVite();

        $transaction = $this->createTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('wa.me/62', false);
        $response->assertSee('WhatsApp');
    }

    /**
     * Tier 4: Halaman cetak struk kasir (staff.transactions.print) menyediakan akses langsung ke WhatsApp.
     */
    public function test_cashier_transaction_print_page_displays_whatsapp_share_action(): void
    {
        $transaction = $this->createTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('wa.me/62', false);
    }

    /**
     * Tier 4: UI Kasir menangani ketiadaan nomor telepon pelanggan secara aman tanpa link rusak.
     */
    public function test_cashier_ui_handles_missing_customer_phone_without_rendering_broken_wa_link(): void
    {
        $this->withoutVite();

        $this->customer->update(['phone' => '']);
        $transaction = $this->createTransaction();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.show', $transaction));

        $response->assertOk();
        $response->assertDontSee('wa.me/?text=', false);
        $response->assertDontSee('wa.me/null', false);
    }
}
