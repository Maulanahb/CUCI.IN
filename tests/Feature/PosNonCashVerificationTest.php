<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * BUG-11: pembayaran Transfer/QRIS dari Kasir POS tidak boleh otomatis "Terverifikasi".
 */
class PosNonCashVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Cuci Kilat',
            'price' => 15000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);
    }

    private function postPos(array $extra = []): void
    {
        Storage::fake(Payment::PROOF_DISK);

        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = $this->createService();

        $this->actingAs($staff)
            ->post(route('staff.transactions.store'), array_merge([
                'customer_id' => $customer->id,
                'items' => [['service_id' => $service->id, 'weight' => 2]],
                'pay_now' => true,
                'payment_choice' => 'full',
                'payment_method' => 'QRIS',
                'payment_proof' => UploadedFile::fake()->image('bukti_pos.jpg'),
            ], $extra))
            ->assertRedirect();
    }

    public function test_pos_non_cash_payment_without_confirmation_waits_for_verification(): void
    {
        $this->postPos();

        $payment = Payment::query()->firstOrFail();

        $this->assertSame(Payment::PENDING_STATUS, $payment->verification_status);
        $this->assertSame('Belum Bayar', Transaction::query()->latest('id')->firstOrFail()->payment_status);
    }

    public function test_pos_non_cash_payment_with_confirmation_is_verified_immediately(): void
    {
        $this->postPos(['is_confirmed' => true]);

        $payment = Payment::query()->firstOrFail();

        $this->assertSame('Terverifikasi', $payment->verification_status);
        $this->assertSame('Lunas', Transaction::query()->latest('id')->firstOrFail()->payment_status);
    }

    public function test_pos_cash_payment_never_needs_verification(): void
    {
        $this->postPos([
            'payment_method' => 'Tunai',
            'payment_proof' => null,
        ]);

        $payment = Payment::query()->firstOrFail();

        $this->assertSame('Tidak Diperlukan', $payment->verification_status);
    }
}