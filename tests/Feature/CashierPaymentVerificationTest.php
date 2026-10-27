<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CashierPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $customerUser;

    private Transaction $transaction;

    private Payment $pendingPayment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Payment::PROOF_DISK);

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $this->customerUser->id, 'name' => 'Rina Wulandari']);

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 50000,
            'status' => TransactionStatus::Diproses,
        ]);

        $this->pendingPayment = Payment::factory()->pendingProof()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'payment_type' => 'Pembayaran Penuh',
            'created_by' => $this->customerUser->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function verifyAsStaff(array $payload, ?Payment $payment = null): TestResponse
    {
        return $this->actingAs($this->staff)
            ->from(route('staff.payments.show', $payment ?? $this->pendingPayment))
            ->patch(route('staff.payments.verify', $payment ?? $this->pendingPayment), $payload);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('staff.payments.index'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_verification_panel(): void
    {
        $this->actingAs($this->customerUser)
            ->get(route('staff.payments.index'))
            ->assertForbidden();

        $this->actingAs($this->customerUser)
            ->patch(route('staff.payments.verify', $this->pendingPayment), ['verification_status' => 'Terverifikasi'])
            ->assertForbidden();

        $this->assertSame(Payment::PENDING_STATUS, $this->pendingPayment->fresh()->verification_status);
    }

    public function test_panel_lists_pending_non_cash_payments_by_default(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 12345,
            'method' => 'Tunai',
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->get(route('staff.payments.index'))
            ->assertOk()
            ->assertSee('Antrean Bukti Pembayaran')
            ->assertSee($this->transaction->transaction_code)
            ->assertSee('Rina Wulandari')
            ->assertSee('Tinjau')
            ->assertDontSee('Rp 12.345');
    }

    public function test_rejected_tab_hides_pending_payments(): void
    {
        $this->actingAs($this->staff)
            ->get(route('staff.payments.index', ['status' => 'Ditolak']))
            ->assertOk()
            ->assertDontSee($this->transaction->transaction_code)
            ->assertSee('Data pembayaran tidak ditemukan');
    }

    public function test_admin_can_open_verification_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.payments.show', $this->pendingPayment))
            ->assertOk()
            ->assertSee(route('admin.payments.proof', $this->pendingPayment))
            ->assertSee('Keputusan Verifikasi');
    }

    public function test_staff_can_preview_proof_file(): void
    {
        Storage::disk(Payment::PROOF_DISK)->put($this->pendingPayment->proof_file, 'isi-gambar');

        $this->actingAs($this->staff)
            ->get(route('staff.payments.proof', $this->pendingPayment))
            ->assertOk();
    }

    public function test_missing_proof_file_returns_not_found(): void
    {
        $this->actingAs($this->staff)
            ->get(route('staff.payments.proof', $this->pendingPayment))
            ->assertNotFound();
    }

    public function test_staff_can_approve_pending_proof(): void
    {
        $this->verifyAsStaff(['verification_status' => 'Terverifikasi'])
            ->assertRedirect(route('staff.payments.show', $this->pendingPayment))
            ->assertSessionHas('success', 'Pembayaran Rp 50.000 berhasil diverifikasi.');

        $payment = $this->pendingPayment->fresh();
        $this->assertSame('Terverifikasi', $payment->verification_status);
        $this->assertSame($this->staff->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);

        $transaction = $this->transaction->fresh();
        $this->assertSame(50000.0, $transaction->total_paid);
        $this->assertSame('Lunas', $transaction->payment_status);
    }

    public function test_payment_type_is_recalculated_on_approval(): void
    {
        $this->pendingPayment->update(['amount' => 20000, 'payment_type' => 'Pembayaran Penuh']);
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 30000,
            'created_by' => $this->staff->id,
        ]);

        $this->verifyAsStaff(['verification_status' => 'Terverifikasi'])->assertSessionHasNoErrors();

        $this->assertSame('Pelunasan', $this->pendingPayment->fresh()->payment_type);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $this->verifyAsStaff(['verification_status' => 'Ditolak'])
            ->assertSessionHasErrors(['verification_notes' => 'Alasan penolakan wajib diisi.']);

        $this->assertSame(Payment::PENDING_STATUS, $this->pendingPayment->fresh()->verification_status);
    }

    public function test_staff_can_reject_proof_with_reason(): void
    {
        $this->verifyAsStaff([
            'verification_status' => 'Ditolak',
            'verification_notes' => 'Nominal pada bukti tidak sesuai.',
        ])->assertSessionHas('success', 'Pembayaran Rp 50.000 ditolak.');

        $payment = $this->pendingPayment->fresh();
        $this->assertSame('Ditolak', $payment->verification_status);
        $this->assertSame('Nominal pada bukti tidak sesuai.', $payment->verification_notes);
        $this->assertSame($this->staff->id, $payment->verified_by);
        $this->assertSame(0.0, $this->transaction->fresh()->total_paid);
    }

    public function test_invalid_decision_is_rejected(): void
    {
        $this->verifyAsStaff(['verification_status' => 'Tidak Diperlukan'])
            ->assertSessionHasErrors(['verification_status' => 'Keputusan verifikasi harus Terverifikasi atau Ditolak.']);
    }

    public function test_already_processed_payment_cannot_be_verified_again(): void
    {
        $this->pendingPayment->update(['verification_status' => 'Ditolak', 'verification_notes' => 'Buram']);

        $this->verifyAsStaff(['verification_status' => 'Terverifikasi'])
            ->assertSessionHasErrors(['verification_status' => 'Pembayaran ini sudah diproses sebelumnya dengan status Ditolak.']);

        $this->assertSame('Ditolak', $this->pendingPayment->fresh()->verification_status);
    }

    public function test_cannot_approve_proof_exceeding_remaining_amount(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 30000,
            'created_by' => $this->staff->id,
        ]);

        $this->verifyAsStaff(['verification_status' => 'Terverifikasi'])
            ->assertSessionHasErrors(['verification_status' => 'Nominal bukti (Rp 50.000) melebihi sisa tagihan saat ini (Rp 20.000). Tolak bukti ini atau hubungi pelanggan.']);

        $this->assertSame(Payment::PENDING_STATUS, $this->pendingPayment->fresh()->verification_status);
    }

    public function test_cannot_approve_proof_for_cancelled_transaction_but_can_reject(): void
    {
        $this->transaction->update(['status' => TransactionStatus::Dibatalkan]);

        $this->verifyAsStaff(['verification_status' => 'Terverifikasi'])
            ->assertSessionHasErrors(['verification_status' => 'Transaksi sudah dibatalkan. Tolak bukti ini dan proses pengembalian dana secara manual.']);

        $this->verifyAsStaff(['verification_status' => 'Ditolak', 'verification_notes' => 'Transaksi dibatalkan.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Ditolak', $this->pendingPayment->fresh()->verification_status);
    }

    public function test_customer_sees_rejection_reason_on_transaction_detail(): void
    {
        $this->pendingPayment->update(['verification_status' => 'Ditolak', 'verification_notes' => 'Gambar bukti buram.']);

        $this->actingAs($this->customerUser)
            ->get(route('customer.transactions.index', ['selected' => $this->transaction->transaction_code]))
            ->assertOk()
            ->assertSee('Alasan ditolak:')
            ->assertSee('Gambar bukti buram.');
    }

    public function test_transaction_detail_links_pending_proof_to_review_page(): void
    {
        $this->actingAs($this->staff)
            ->get(route('staff.transactions.show', $this->transaction))
            ->assertOk()
            ->assertSee('Tinjau bukti')
            ->assertSee(route('staff.payments.show', $this->pendingPayment));
    }
}
