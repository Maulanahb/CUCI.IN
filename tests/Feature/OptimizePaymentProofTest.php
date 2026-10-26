<?php

namespace Tests\Feature;

use App\Jobs\OptimizePaymentProof;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OptimizePaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private function createPaymentWithProof(string $proofPath): Payment
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $transaction = Transaction::factory()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $staff->id,
        ]);

        return Payment::factory()->pendingProof()->create([
            'transaction_id' => $transaction->id,
            'proof_file' => $proofPath,
            'created_by' => $staff->id,
        ]);
    }

    public function test_large_proof_image_is_resized_within_bounds(): void
    {
        Storage::fake(Payment::PROOF_DISK);
        $path = UploadedFile::fake()->image('bukti.jpg', 2400, 1800)->store(Payment::PROOF_DIRECTORY, Payment::PROOF_DISK);
        $payment = $this->createPaymentWithProof($path);

        (new OptimizePaymentProof($payment->id))->handle();

        [$width, $height] = getimagesize(Storage::disk(Payment::PROOF_DISK)->path($path));
        $this->assertSame(1280, $width);
        $this->assertSame(960, $height);
    }

    public function test_missing_proof_file_is_skipped_without_error(): void
    {
        Storage::fake(Payment::PROOF_DISK);
        $payment = $this->createPaymentWithProof('payments/proofs/tidak-ada.jpg');

        (new OptimizePaymentProof($payment->id))->handle();

        Storage::disk(Payment::PROOF_DISK)->assertMissing('payments/proofs/tidak-ada.jpg');
    }
}
