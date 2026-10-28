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

        Storage::disk(Payment::PROOF_DISK)->assertExists($payment->proof_file);
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

        Storage::disk(Payment::PROOF_DISK)->assertExists($payment->proof_file);
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
}
