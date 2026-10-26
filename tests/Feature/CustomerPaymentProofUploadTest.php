<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Jobs\OptimizePaymentProof;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPaymentProofUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Payment::PROOF_DISK);
        Queue::fake();

        $staff = User::factory()->create(['role' => 'staff']);
        $this->customerUser = User::factory()->create(['role' => 'customer']);
        $this->customer = Customer::factory()->create(['user_id' => $this->customerUser->id]);

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $staff->id,
            'total_amount' => 50000,
            'status' => TransactionStatus::Diproses,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'method' => 'Transfer',
            'amount' => 50000,
            'proof_file' => UploadedFile::fake()->image('bukti-transfer.jpg', 800, 1200),
            ...$overrides,
        ];
    }

    private function uploadUrl(?Transaction $transaction = null): string
    {
        return route('customer.transactions.payment-proofs.store', $transaction ?? $this->transaction);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post($this->uploadUrl(), $this->validPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_staff_cannot_use_customer_upload_endpoint(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->post($this->uploadUrl(), $this->validPayload())
            ->assertForbidden();
    }

    public function test_customer_cannot_upload_proof_for_another_customers_transaction(): void
    {
        $otherTransaction = Transaction::factory()->create([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => $this->transaction->created_by,
            'total_amount' => 30000,
        ]);

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl($otherTransaction), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_customer_can_upload_transfer_proof_which_awaits_verification(): void
    {
        $response = $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload());

        $response->assertRedirect(route('customer.transactions.index', ['selected' => $this->transaction->transaction_code]));
        $response->assertSessionHas('status');

        $payment = Payment::sole();

        $this->assertSame(Payment::PENDING_STATUS, $payment->verification_status);
        $this->assertSame('Transfer', $payment->method);
        $this->assertSame('Pembayaran Penuh', $payment->payment_type);
        $this->assertSame($this->customerUser->id, $payment->created_by);
        $this->assertNull($payment->verified_at);
        $this->assertTrue(Storage::disk(Payment::PROOF_DISK)->exists($payment->proof_file));
        $this->assertStringStartsWith(Payment::PROOF_DIRECTORY.'/', $payment->proof_file);

        Queue::assertPushed(OptimizePaymentProof::class, fn (OptimizePaymentProof $job): bool => $job->paymentId === $payment->id);

        // Bukti yang belum diverifikasi tidak boleh dihitung sebagai uang masuk.
        $this->transaction->refresh();
        $this->assertSame(0.0, $this->transaction->total_paid);
        $this->assertSame('Belum Bayar', $this->transaction->payment_status);
    }

    public function test_partial_qris_proof_is_recorded_as_dp(): void
    {
        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload(['method' => 'QRIS', 'amount' => 20000]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'method' => 'QRIS',
            'payment_type' => 'DP',
            'verification_status' => Payment::PENDING_STATUS,
        ]);
    }

    public function test_upload_requires_method_amount_and_proof_file(): void
    {
        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), [])
            ->assertSessionHasErrors([
                'method' => 'Pilih metode pembayaran yang kamu gunakan.',
                'amount' => 'Nominal yang kamu bayarkan wajib diisi.',
                'proof_file' => 'Unggah foto bukti transfer atau struk QRIS dulu, ya.',
            ]);
    }

    public function test_cash_method_is_rejected(): void
    {
        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload(['method' => 'Tunai']))
            ->assertSessionHasErrors(['method' => 'Bukti pembayaran hanya untuk metode Transfer atau QRIS.']);
    }

    public function test_non_image_proof_is_rejected(): void
    {
        $pdf = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload(['proof_file' => $pdf]))
            ->assertSessionHasErrors(['proof_file' => 'Bukti pembayaran harus berupa gambar.']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_proof_larger_than_five_megabytes_is_rejected(): void
    {
        $largeImage = UploadedFile::fake()->image('bukti.jpg', 800, 800)->size(5121);

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload(['proof_file' => $largeImage]))
            ->assertSessionHasErrors(['proof_file' => 'Ukuran bukti pembayaran maksimal 5 MB.']);
    }

    public function test_tiny_proof_image_is_rejected(): void
    {
        $tinyImage = UploadedFile::fake()->image('bukti.jpg', 100, 100);

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload(['proof_file' => $tinyImage]))
            ->assertSessionHasErrors(['proof_file' => 'Gambar bukti terlalu kecil. Gunakan gambar minimal 200×200 piksel agar terbaca jelas.']);
    }

    public function test_amount_cannot_exceed_bill_minus_pending_proofs(): void
    {
        Payment::factory()->pendingProof()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 30000,
            'created_by' => $this->customerUser->id,
        ]);

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload(['amount' => 25000]))
            ->assertSessionHasErrors(['amount' => 'Nominal pembayaran (Rp 25.000) melebihi sisa tagihan yang dapat diajukan (Rp 20.000).']);

        // Berkas unggahan yang gagal diajukan harus dibersihkan dari disk.
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame([], Storage::disk(Payment::PROOF_DISK)->allFiles());
    }

    public function test_cannot_upload_proof_for_cancelled_transaction(): void
    {
        $this->transaction->update(['status' => TransactionStatus::Dibatalkan]);

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload())
            ->assertSessionHasErrors(['amount' => 'Transaksi berstatus Dibatalkan tidak dapat menerima pembayaran.']);

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame([], Storage::disk(Payment::PROOF_DISK)->allFiles());
        Queue::assertNothingPushed();
    }

    public function test_cannot_upload_proof_for_paid_off_transaction(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'created_by' => $this->transaction->created_by,
        ]);

        $this->actingAs($this->customerUser)
            ->post($this->uploadUrl(), $this->validPayload())
            ->assertSessionHasErrors(['amount' => 'Transaksi ini sudah lunas, tidak perlu mengunggah bukti pembayaran.']);
    }

    public function test_transaction_detail_shows_upload_form_and_pending_proof(): void
    {
        Payment::factory()->pendingProof()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 20000,
            'created_by' => $this->customerUser->id,
        ]);

        $this->actingAs($this->customerUser)
            ->get(route('customer.transactions.index', ['selected' => $this->transaction->transaction_code]))
            ->assertOk()
            ->assertSee('Kirim Bukti Pembayaran')
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Maksimal yang bisa diajukan: Rp 30.000');
    }

    public function test_upload_form_is_hidden_when_transaction_is_paid_off(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'created_by' => $this->transaction->created_by,
        ]);

        $this->actingAs($this->customerUser)
            ->get(route('customer.transactions.index', ['selected' => $this->transaction->transaction_code]))
            ->assertOk()
            ->assertDontSee('Kirim Bukti Pembayaran');
    }

    public function test_customer_can_view_own_proof_file(): void
    {
        Storage::disk(Payment::PROOF_DISK)->put('payments/proofs/bukti.jpg', 'isi-gambar');
        $payment = Payment::factory()->pendingProof()->create([
            'transaction_id' => $this->transaction->id,
            'proof_file' => 'payments/proofs/bukti.jpg',
            'created_by' => $this->customerUser->id,
        ]);

        $this->actingAs($this->customerUser)
            ->get(route('customer.payments.proof', $payment))
            ->assertOk();
    }

    public function test_customer_cannot_view_another_customers_proof_file(): void
    {
        Storage::disk(Payment::PROOF_DISK)->put('payments/proofs/bukti.jpg', 'isi-gambar');
        $payment = Payment::factory()->pendingProof()->create([
            'transaction_id' => $this->transaction->id,
            'proof_file' => 'payments/proofs/bukti.jpg',
            'created_by' => $this->customerUser->id,
        ]);

        $otherUser = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($otherUser)
            ->get(route('customer.payments.proof', $payment))
            ->assertForbidden();
    }
}
