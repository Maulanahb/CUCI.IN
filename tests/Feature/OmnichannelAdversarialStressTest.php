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

class OmnichannelAdversarialStressTest extends TestCase
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
            'name' => 'Adversarial Cashier',
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Target Customer',
            'phone' => '081234567890',
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Kilat Express',
            'price' => 15000,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);
    }

    private function createTrx(array $attributes = []): Transaction
    {
        $transaction = Transaction::factory()->create(array_merge([
            'customer_id' => $this->customer->id,
            'created_by' => $this->cashier->id,
            'transaction_code' => 'TRX-STRESS-001',
            'total_amount' => 60000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => now()->addHours(24),
        ], $attributes));

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'weight' => 4.0,
            'unit_price' => 15000,
            'subtotal' => 60000,
        ]);

        return $transaction;
    }

    private function parseWa(string $url): array
    {
        $parsed = parse_url($url);
        $phone = trim($parsed['path'] ?? '', '/');
        parse_str($parsed['query'] ?? '', $query);

        return [
            'phone' => $phone,
            'text' => $query['text'] ?? '',
        ];
    }

    /**
     * Probe 1: Extreme Phone Input Normalization Matrix
     */
    public function test_probe_extreme_phone_inputs_normalization_matrix(): void
    {
        $trx = $this->createTrx();

        $testMatrix = [
            // [input_phone, expected_phone_in_wa_url_or_null, description]
            ['+62-812-3456-7890', '6281234567890', 'Dashes and plus prefix'],
            ['0812 3456 7890', '6281234567890', 'Spaces and leading zero'],
            ['6208123456789', '628123456789', '620 typo converted to 628'],
            ['+08123456789', '628123456789', 'Plus with leading zero'],
            ['81234567890', '6281234567890', 'Direct 8 prefix'],
            ['+6281234567890', '6281234567890', 'Canonical +628'],
            ['6281234567890', '6281234567890', 'Canonical 628'],
            ['(0812) 345-6789', '628123456789', 'Parentheses and dashes'],
            ['12345', null, 'Too short (5 digits)'],
            ['0812', null, 'Too short after 0->62 (6 digits)'],
            ['', null, 'Empty string'],
            ['   ', null, 'Whitespace only'],
            ['abc-def-ghij', null, 'Non-digits only'],
            ["' OR '1'='1", null, 'SQL injection string (cleaned to 11, length 2)'],
            ['081234567890123456', null, 'Too long (> 15 digits)'],
            ['08123456', '628123456', 'Boundary minimum: exactly 9 digits'],
            ['08123456789012', '628123456789012', 'Boundary maximum: exactly 15 digits'],
        ];

        foreach ($testMatrix as [$inputPhone, $expectedOutput, $scenario]) {
            $this->customer->update(['phone' => $inputPhone]);
            $trx->unsetRelation('customer');

            $waUrl = $trx->whatsapp_share_url;

            if ($expectedOutput === null) {
                $this->assertNull(
                    $waUrl,
                    "Failed on scenario [{$scenario}]: Input '{$inputPhone}' should produce null but got '{$waUrl}'"
                );
            } else {
                $this->assertNotNull(
                    $waUrl,
                    "Failed on scenario [{$scenario}]: Input '{$inputPhone}' produced null instead of valid URL"
                );
                $parsed = $this->parseWa($waUrl);
                $this->assertEquals(
                    $expectedOutput,
                    $parsed['phone'],
                    "Failed on scenario [{$scenario}]: Expected phone '{$expectedOutput}', got '{$parsed['phone']}'"
                );
            }
        }
    }

    /**
     * Probe 2: Null and Missing Customer Handling
     */
    public function test_probe_null_and_missing_customer(): void
    {
        $trx = $this->createTrx();
        $trx->setRelation('customer', null);

        $this->assertNull($trx->whatsapp_share_url, 'Transaction without customer relation must return null');
    }

    /**
     * Probe 3: Multibyte Customer Names, Special Characters, Quotes, Ampersands, XSS
     */
    public function test_probe_multibyte_names_and_special_character_encoding(): void
    {
        $trx = $this->createTrx();

        $multibyteNames = [
            '田中 太郎', // Japanese Kanji
            'محمد بن عبد الله', // Arabic RTL
            'René François & Söhne "L\'Étoile"', // Accented, ampersand, quotes
            '<script>alert("XSS")</script>', // HTML XSS payload
            'Maulana & Zaky / Ayin + Nora = 100%', // Math and symbols
            'John "The Boss" O\'Connor & Co.', // Mixed quotes
            'Pakaian & Sepatu (Express & Hanger)', // Ampersand in notes/names
        ];

        foreach ($multibyteNames as $name) {
            $this->customer->update(['name' => $name, 'phone' => '081234567890']);
            $trx->unsetRelation('customer');

            $waUrl = $trx->whatsapp_share_url;
            $this->assertNotNull($waUrl);

            // 1. Must be a syntactically valid URL
            $this->assertNotFalse(
                filter_var($waUrl, FILTER_VALIDATE_URL),
                "URL produced with name '{$name}' is not a valid URL: {$waUrl}"
            );

            // 2. Query string must not have unencoded ampersands breaking the 'text' param
            $parsedQuery = [];
            parse_str(parse_url($waUrl, PHP_URL_QUERY), $parsedQuery);
            $this->assertArrayHasKey('text', $parsedQuery);

            // 3. Roundtrip decode: the original customer name must match perfectly
            $decodedText = rawurldecode(explode('?text=', $waUrl)[1]);
            $this->assertStringContainsString(
                "Halo Kak {$name},",
                $decodedText,
                "Roundtrip decoding corrupted customer name '{$name}'"
            );
        }
    }

    /**
     * Probe 4: Payment Status Variations (Lunas, DP, Belum Bayar, Overpayment)
     */
    public function test_probe_payment_status_variations_and_remaining_balance_formatting(): void
    {
        $trx = $this->createTrx(['total_amount' => 100000]);

        // Scenario 1: Belum Bayar (no payment recorded)
        $this->assertEquals('Belum Bayar', $trx->payment_status);
        $this->assertEquals(100000.0, $trx->remaining_amount);
        $waUrlBelumBayar = $trx->whatsapp_share_url;
        $this->assertNotNull($waUrlBelumBayar);
        $text1 = $this->parseWa($waUrlBelumBayar)['text'];
        $this->assertStringContainsString('- Status Pembayaran: *Belum Bayar*', $text1);
        $this->assertStringContainsString('- Total Tagihan: *Rp 100.000*', $text1);
        $this->assertStringContainsString('- Sisa Tagihan: Rp 100.000', $text1);

        // Scenario 2: DP / Sebagian
        $p1 = Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 40000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);
        $trx->unsetRelation('payments');

        $this->assertEquals('DP', $trx->payment_status);
        $this->assertEquals(60000.0, $trx->remaining_amount);
        $waUrlDp = $trx->whatsapp_share_url;
        $this->assertNotNull($waUrlDp);
        $text2 = $this->parseWa($waUrlDp)['text'];
        $this->assertStringContainsString('- Status Pembayaran: *DP*', $text2);
        $this->assertStringContainsString('- Total Tagihan: *Rp 100.000*', $text2);
        $this->assertStringContainsString('- Sisa Tagihan: Rp 60.000', $text2);

        // Scenario 3: Lunas Penuh
        $p2 = Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 60000,
            'payment_type' => 'Pelunasan',
            'method' => 'QRIS',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);
        $trx->unsetRelation('payments');

        $this->assertEquals('Lunas', $trx->payment_status);
        $this->assertEquals(0.0, $trx->remaining_amount);
        $waUrlLunas = $trx->whatsapp_share_url;
        $this->assertNotNull($waUrlLunas);
        $text3 = $this->parseWa($waUrlLunas)['text'];
        $this->assertStringContainsString('- Status Pembayaran: *Lunas*', $text3);
        $this->assertStringContainsString('- Total Tagihan: *Rp 100.000*', $text3);
        // In Lunas, line Sisa Tagihan should NOT be present
        $this->assertStringNotContainsString('Sisa Tagihan:', $text3);

        // Scenario 4: Overpayment ($paid > $total_amount)
        $p3 = Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 10000, // extra 10k
            'payment_type' => 'Pelunasan',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);
        $trx->unsetRelation('payments');

        $this->assertEquals('Lunas', $trx->payment_status);
        $this->assertEquals(0.0, $trx->remaining_amount); // max(0, total - paid)
        $waUrlOver = $trx->whatsapp_share_url;
        $this->assertNotNull($waUrlOver);
        $text4 = $this->parseWa($waUrlOver)['text'];
        $this->assertStringNotContainsString('Sisa Tagihan:', $text4);
    }

    /**
     * Probe 5: Exhaustive Combinatorial Zero Emoji Assertion
     */
    public function test_probe_exhaustive_zero_emoji_on_all_status_combinations(): void
    {
        $trx = $this->createTrx();

        $allStatuses = TransactionStatus::cases();

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        foreach ($allStatuses as $status) {
            $trx->status = $status;

            // Test 1: Belum Bayar
            $trx->unsetRelation('payments');
            $url = $trx->whatsapp_share_url;
            $this->assertNotNull($url);
            $this->assertDoesNotMatchRegularExpression(
                $emojiPattern,
                $url,
                "Emoji found in status {$status->value} (Belum Bayar)"
            );

            // Test 2: With DP Payment
            Payment::create([
                'transaction_id' => $trx->id,
                'amount' => 10000,
                'payment_type' => 'DP',
                'method' => 'Tunai',
                'verification_status' => 'Tidak Diperlukan',
                'paid_at' => now(),
                'created_by' => $this->cashier->id,
            ]);
            $trx->unsetRelation('payments');

            $urlDp = $trx->whatsapp_share_url;
            $this->assertNotNull($urlDp);
            $this->assertDoesNotMatchRegularExpression(
                $emojiPattern,
                $urlDp,
                "Emoji found in status {$status->value} (DP)"
            );
        }
    }

    /**
     * Probe 6: BarcodeService QR Code Generation for Multiple Lengths & Formats
     */
    public function test_probe_barcode_service_qr_code_svg_generation(): void
    {
        $service = app(BarcodeService::class);

        $testInputs = [
            ['TRX-0001', 96, 29, 'Version 2 (25x25 + 2x2 quiet = 29x29)'],
            ['TRX-20261102-0001', 96, 29, 'Version 2 (17 chars)'],
            ['https://portal-cuciin.web.id/lookup?code=TRX-0001', 96, 33, 'Version 3 (48 chars, 29x29 + 2x2 quiet = 33x33)'],
            ['http://localhost/lookup?code=TRX-20261102-TEST999', 96, 33, 'Version 3 (50 chars)'],
            ['https://portal-cuciin.web.id/lookup?code=TRX-20261102-BRANCH01-EXTRA001', 110, 37, 'Version 4 (68 chars, 33x33 + 2x2 quiet = 37x37)'],
        ];

        foreach ($testInputs as [$input, $renderSize, $expectedViewBoxDim, $description]) {
            $svg = $service->generateQrCodeSvg($input, $renderSize);

            $this->assertStringStartsWith('<svg xmlns="http://www.w3.org/2000/svg"', $svg);
            $this->assertStringEndsWith('</svg>', $svg);
            $this->assertStringContainsString('shape-rendering="crispEdges"', $svg);
            $this->assertStringContainsString("viewBox=\"0 0 {$expectedViewBoxDim} {$expectedViewBoxDim}\"", $svg);
            $this->assertStringContainsString("width=\"{$renderSize}\"", $svg);
            $this->assertStringContainsString("height=\"{$renderSize}\"", $svg);
            $this->assertStringContainsString('<rect width="'.$expectedViewBoxDim.'" height="'.$expectedViewBoxDim.'" fill="#ffffff"/>', $svg);
            $this->assertStringContainsString('<path d="M', $svg);
        }
    }

    /**
     * Probe 6b: Exact boundary length transitions for BarcodeService QR code generator
     */
    public function test_probe_barcode_service_boundary_lengths_and_edge_cases(): void
    {
        $service = app(BarcodeService::class);

        // Boundary 0: empty string
        $svg0 = $service->generateQrCodeSvg('', 96);
        $this->assertStringContainsString('viewBox="0 0 29 29"', $svg0);

        // Boundary 1: single character
        $svg1 = $service->generateQrCodeSvg('A', 96);
        $this->assertStringContainsString('viewBox="0 0 29 29"', $svg1);

        // Boundary 32: max Version 2
        $svg32 = $service->generateQrCodeSvg(str_repeat('X', 32), 96);
        $this->assertStringContainsString('viewBox="0 0 29 29"', $svg32);

        // Boundary 33: min Version 3
        $svg33 = $service->generateQrCodeSvg(str_repeat('X', 33), 96);
        $this->assertStringContainsString('viewBox="0 0 33 33"', $svg33);

        // Boundary 53: max Version 3
        $svg53 = $service->generateQrCodeSvg(str_repeat('X', 53), 96);
        $this->assertStringContainsString('viewBox="0 0 33 33"', $svg53);

        // Boundary 54: min Version 4
        $svg54 = $service->generateQrCodeSvg(str_repeat('X', 54), 96);
        $this->assertStringContainsString('viewBox="0 0 37 37"', $svg54);

        // Boundary 78: max Version 4 safe capacity (80 bytes data cap - 2 bytes header/pad)
        $svg78 = $service->generateQrCodeSvg(str_repeat('X', 78), 96);
        $this->assertStringContainsString('viewBox="0 0 37 37"', $svg78);

        // Boundary 79+: input exceeds standard Version 4 capacity but BarcodeService gracefully generates SVG without throwing
        $svg85 = $service->generateQrCodeSvg(str_repeat('X', 85), 96);
        $this->assertStringStartsWith('<svg xmlns="http://www.w3.org/2000/svg"', $svg85);
        $this->assertStringContainsString('viewBox="0 0 37 37"', $svg85);
    }

    /**
     * Probe 7: Thermal Receipt Paper Layout Switching (58mm vs 80mm)
     */
    public function test_probe_thermal_receipt_layout_58mm_and_80mm(): void
    {
        $trx = $this->createTrx();

        $response = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $trx));

        $response->assertOk();
        $content = $response->getContent();

        // Check 58mm CSS specifications
        $this->assertStringContainsString('.receipt-container.paper-58mm', $content);
        $this->assertStringContainsString('width: 58mm;', $content);
        $this->assertStringContainsString('padding: 3mm 2.5mm 5mm;', $content);

        // Check 80mm CSS specifications
        $this->assertStringContainsString('.receipt-container.paper-80mm', $content);
        $this->assertStringContainsString('width: 80mm;', $content);
        $this->assertStringContainsString('padding: 4.5mm 4.5mm 6mm;', $content);

        // Check that max-width is constrained to prevent overflow
        $this->assertStringContainsString('max-width: 100%;', $content);

        // Check QR code container size and centering
        $this->assertStringContainsString('<div class="qr-section">', $content);
        $this->assertStringContainsString('Pindai QR Lacak Status', $content);

        // Check client-side switcher buttons
        $this->assertStringContainsString('data-size="58mm"', $content);
        $this->assertStringContainsString('data-size="80mm"', $content);
        $this->assertStringContainsString('localStorage.setItem(\'cuciin_receipt_paper_size\', size)', $content);
    }

    /**
     * Probe 8: Full E2E Flow Across Print Receipt, WhatsApp URL, and Guest /lookup Resolution
     */
    public function test_probe_e2e_cross_feature_flow_from_receipt_to_lookup_and_whatsapp(): void
    {
        $this->withoutVite();

        $trx = $this->createTrx([
            'transaction_code' => 'TRX-E2E-OMNI01',
            'total_amount' => 80000,
        ]);

        Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 30000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);

        // 1. Kasir accesses print receipt
        $printResponse = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.print', $trx));

        $printResponse->assertOk();
        $expectedLookupUrl = route('transactions.lookup', ['code' => 'TRX-E2E-OMNI01']);

        // Assert SVG QR is present and encodes the lookup URL
        $printResponse->assertSee('<svg', false);
        $printResponse->assertSee('shape-rendering="crispEdges"', false);

        // 2. Kasir accesses detail page and triggers WhatsApp share
        $showResponse = $this->actingAs($this->cashier)
            ->get(route('staff.transactions.show', $trx));

        $showResponse->assertOk();
        $waUrl = $trx->whatsapp_share_url;
        $this->assertNotNull($waUrl);
        $showResponse->assertSee($waUrl, false);

        // Message must contain lookup URL and exact balance
        $parsedWa = $this->parseWa($waUrl);
        $this->assertStringContainsString($expectedLookupUrl, $parsedWa['text']);
        $this->assertStringContainsString('Sisa Tagihan: Rp 50.000', $parsedWa['text']);

        // 3. Customer scans receipt or clicks link, arriving as guest at /lookup
        $lookupResponse = $this->get($expectedLookupUrl);

        $lookupResponse->assertOk();
        $lookupResponse->assertSee('TRX-E2E-OMNI01');
        $lookupResponse->assertSee('T*** C***'); // Masked name for Target Customer
        $lookupResponse->assertSee('DP');
        $lookupResponse->assertSee('50.000'); // Remaining amount
        $lookupResponse->assertSee('id="btn-scan-qr"', false);
        $lookupResponse->assertSee('id="qr-scanner-modal"', false);
    }

    /**
     * Probe 9: Static File Scan for Zero Default Cartoon Emojis across All Omnichannel Files
     */
    public function test_probe_static_files_zero_cartoon_emojis(): void
    {
        $files = [
            app_path('Models/Transaction.php'),
            app_path('Services/BarcodeService.php'),
            resource_path('views/staff/transactions/print.blade.php'),
            resource_path('views/staff/transactions/show.blade.php'),
            resource_path('views/staff/transactions/index.blade.php'),
            resource_path('views/lookup/index.blade.php'),
            resource_path('views/lookup/_content.blade.php'),
            resource_path('views/components/ui/qr-code.blade.php'),
        ];

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        foreach ($files as $filePath) {
            $this->assertFileExists($filePath);
            $content = file_get_contents($filePath);
            $this->assertDoesNotMatchRegularExpression(
                $emojiPattern,
                $content,
                "File [{$filePath}] contains forbidden cartoon emojis!"
            );
        }
    }
}


