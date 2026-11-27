<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Jobs\CompressComplaintPhoto;
use App\Jobs\OptimizePaymentProof;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ComplaintCodeGenerator;
use App\Services\ImageOptimizer;
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

        $this->assertFalse(Storage::disk(Payment::PROOF_DISK)->exists('payments/proofs/tidak-ada.jpg'));
    }

    private function syntheticPngHeader(int $width, int $height): string
    {
        $ihdrData = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);

        return "\x89PNG\r\n\x1a\n"
            .pack('N', 13)
            .'IHDR'
            .$ihdrData
            .pack('N', crc32('IHDR'.$ihdrData));
    }

    public function test_oversized_pixel_image_is_skipped_before_gd_decompression(): void
    {
        Storage::fake(Payment::PROOF_DISK);

        $bombPath = Payment::PROOF_DIRECTORY.'/pixel-bomb.png';
        Storage::disk(Payment::PROOF_DISK)->put($bombPath, $this->syntheticPngHeader(6000, 6000));
        $payment = $this->createPaymentWithProof($bombPath);

        $this->assertSame(16_777_216, ImageOptimizer::MAX_IMAGE_PIXELS);
        $this->assertNull(ImageOptimizer::optimize(Payment::PROOF_DISK, $bombPath));

        (new OptimizePaymentProof($payment->id))->handle();

        [$width, $height] = getimagesize(Storage::disk(Payment::PROOF_DISK)->path($bombPath));
        $this->assertSame(6000, $width);
        $this->assertSame(6000, $height);
    }

    public function test_compress_complaint_photo_resizes_valid_image_and_skips_pixel_flood(): void
    {
        Storage::fake(Complaint::PHOTO_DISK);

        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $validPath = UploadedFile::fake()->image('keluhan.jpg', 2400, 1800)->store(Complaint::PHOTO_DIRECTORY, Complaint::PHOTO_DISK);
        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Baju luntur setelah dicuci',
            'description' => 'Warna kemeja putih berubah kemerahan di bagian kerah.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
            'photo_path' => $validPath,
        ]);

        (new CompressComplaintPhoto($complaint->id))->handle();

        [$width, $height] = getimagesize(Storage::disk(Complaint::PHOTO_DISK)->path($validPath));
        $this->assertSame(1280, $width);
        $this->assertSame(960, $height);

        $bombPath = Complaint::PHOTO_DIRECTORY.'/bomb-keluhan.png';
        Storage::disk(Complaint::PHOTO_DISK)->put($bombPath, $this->syntheticPngHeader(6000, 6000));
        $complaint->update(['photo_path' => $bombPath]);

        (new CompressComplaintPhoto($complaint->id))->handle();

        [$bombWidth, $bombHeight] = getimagesize(Storage::disk(Complaint::PHOTO_DISK)->path($bombPath));
        $this->assertSame(6000, $bombWidth);
        $this->assertSame(6000, $bombHeight);
    }
}
