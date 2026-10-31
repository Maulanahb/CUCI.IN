<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestPaymentProofLookupTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Payment::PROOF_DISK);
        Queue::fake();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create([
            'phone' => '081234567890',
        ]);

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 100000,
            'status' => TransactionStatus::Diproses,
        ]);
    }

    public function test_guest_can_submit_payment_proof_with_correct_last_4_phone_digits(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_bca.jpg', 600, 800);

        $response = $this->post(route('transactions.lookup.payment-proof'), [
            'transaction_code' => $this->transaction->transaction_code,
            'phone_last_4' => '7890',
            'method' => 'Transfer',
            'amount' => 50000,
            'proof_file' => $file,
        ]);

        $response->assertRedirect(route('transactions.lookup', ['code' => $this->transaction->transaction_code]));
        $response->assertSessionHas('payment_proof_success');

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(50000, (float) $payment->amount);
        $this->assertEquals('Transfer', $payment->method);
        $this->assertEquals('Menunggu Verifikasi', $payment->verification_status);
        $this->assertNotNull($payment->proof_file);

        Storage::disk(Payment::PROOF_DISK)->assertExists($payment->proof_file);
    }

    public function test_guest_submission_fails_when_phone_last_4_does_not_match(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_bca.jpg', 600, 800);

        $response = $this->from(route('transactions.lookup', ['code' => $this->transaction->transaction_code]))
            ->post(route('transactions.lookup.payment-proof'), [
                'transaction_code' => $this->transaction->transaction_code,
                'phone_last_4' => '1234',
                'method' => 'Transfer',
                'amount' => 50000,
                'proof_file' => $file,
            ]);

        $response->assertSessionHasErrors('phone_last_4');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_guest_submission_fails_when_amount_exceeds_remaining_balance(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_bca.jpg', 600, 800);

        $response = $this->from(route('transactions.lookup', ['code' => $this->transaction->transaction_code]))
            ->post(route('transactions.lookup.payment-proof'), [
                'transaction_code' => $this->transaction->transaction_code,
                'phone_last_4' => '7890',
                'method' => 'Transfer',
                'amount' => 200000,
                'proof_file' => $file,
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_guest_submission_fails_when_transaction_is_cancelled(): void
    {
        $this->transaction->update(['status' => TransactionStatus::Dibatalkan]);

        $file = UploadedFile::fake()->image('bukti_transfer_bca.jpg', 600, 800);

        $response = $this->from(route('transactions.lookup', ['code' => $this->transaction->transaction_code]))
            ->post(route('transactions.lookup.payment-proof'), [
                'transaction_code' => $this->transaction->transaction_code,
                'phone_last_4' => '7890',
                'method' => 'Transfer',
                'amount' => 50000,
                'proof_file' => $file,
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payments', 0);
    }
}
