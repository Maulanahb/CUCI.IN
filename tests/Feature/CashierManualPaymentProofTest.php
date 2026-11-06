<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashierManualPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Payment::PROOF_DISK);

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create();

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 100000,
        ]);
    }

    public function test_cashier_can_upload_payment_proof_and_mark_as_confirmed(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_wa.jpg', 600, 800);

        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'Transfer',
                'proof_file' => $file,
                'is_confirmed' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(50000, (float) $payment->amount);
        $this->assertEquals('Transfer', $payment->method);
        $this->assertEquals('Terverifikasi', $payment->verification_status);
        $this->assertNotNull($payment->proof_file);

        $this->assertTrue(Storage::disk(Payment::PROOF_DISK)->exists($payment->proof_file));
    }

    public function test_cashier_can_upload_payment_proof_and_leave_pending_for_owner_audit(): void
    {
        $file = UploadedFile::fake()->image('struk_qris.png', 400, 400);

        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 100000,
                'method' => 'QRIS',
                'proof_file' => $file,
                'is_confirmed' => 0,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('QRIS', $payment->method);
        $this->assertEquals('Menunggu Verifikasi', $payment->verification_status);
        $this->assertNotNull($payment->proof_file);

        $this->assertTrue(Storage::disk(Payment::PROOF_DISK)->exists($payment->proof_file));
    }

    public function test_uploading_non_image_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'Transfer',
                'proof_file' => $file,
            ]);

        $response->assertSessionHasErrors('proof_file');
        $this->assertDatabaseCount('payments', 0);
    }

    
        public function test_cashier_cannot_record_transfer_without_payment_proof(): void
        {
            $response = $this->actingAs($this->staff)
                ->from(route('staff.transactions.show', $this->transaction))
                ->post(route('staff.transactions.payments.store', $this->transaction), [
                    'amount' => 50000,
                    'method' => 'Transfer',
                ]);

            $response->assertSessionHasErrors('proof_file');

            $this->assertDatabaseCount('payments', 0);
        }

        public function test_cashier_cannot_record_qris_without_payment_proof(): void
        {
            $response = $this->actingAs($this->staff)
                ->post(route('staff.transactions.payments.store', $this->transaction), [
                    'amount' => 50000,
                    'method' => 'QRIS',
                ]);

            $response->assertSessionHasErrors('proof_file');

            $this->assertDatabaseCount('payments', 0);
        }

        public function test_cash_payment_does_not_require_payment_proof(): void
        {
            $response = $this->actingAs($this->staff)
                ->post(route('staff.transactions.payments.store', $this->transaction), [
                    'amount' => 50000,
                    'method' => 'Tunai',
                    'cash_received' => 50000,
                ]);

            $response->assertRedirect();
            $response->assertSessionHasNoErrors();

            $this->assertDatabaseHas('payments', [
                'transaction_id' => $this->transaction->id,
                'method' => 'Tunai',
                'proof_file' => null,
            ]);
        }

}
