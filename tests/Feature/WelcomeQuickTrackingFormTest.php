<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomeQuickTrackingFormTest extends TestCase
{
    /**
     * BUG-09: kotak pencarian cepat di landing page harus mengirim parameter "code"
     * karena TransactionLookupController hanya membaca $request->input('code').
     */
    public function test_landing_quick_tracking_input_uses_code_parameter(): void
    {
        $this->withoutVite();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('name="code"', false);
        $response->assertDontSee('name="transaction_code"', false);
    }

    public function test_lookup_page_prefills_input_when_code_query_is_sent_from_landing(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-20261024-0001']));

        $response->assertOk();
        $response->assertSee('TRX-20261024-0001');
    }
}