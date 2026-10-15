<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionStatusRollbackTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create();
    }

    public function test_cannot_update_status_if_already_terminal_diambil(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Diambil,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Selesai',
                'notes' => 'Mencoba rollback cucian diambil',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(TransactionStatus::Diambil, $transaction->fresh()->status);
    }

    public function test_cannot_update_status_if_already_terminal_dibatalkan(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Dibatalkan,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Diproses',
                'notes' => 'Mencoba mengaktifkan kembali',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(TransactionStatus::Dibatalkan, $transaction->fresh()->status);
    }

    public function test_cannot_update_to_same_status(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Diproses',
            ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_rollback_requires_reason_notes(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Selesai,
        ]);

        // Rollback dari Selesai ke Diproses tanpa notes
        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Diproses',
                'notes' => '',
            ]);

        $response->assertSessionHasErrors('notes');
        $this->assertSame(TransactionStatus::Selesai, $transaction->fresh()->status);
    }

    public function test_rollback_with_valid_reason_succeeds_and_saves_notes_in_history(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Selesai,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Diproses',
                'notes' => 'Pakaian ada noda minyak perlu dicuci ulang',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(TransactionStatus::Diproses, $transaction->fresh()->status);

        $this->assertDatabaseHas('laundry_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => 'Diproses',
            'notes' => 'Pakaian ada noda minyak perlu dicuci ulang',
            'changed_by' => $this->staff->id,
        ]);
    }

    public function test_forward_progression_does_not_require_notes(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Selesai',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(TransactionStatus::Selesai, $transaction->fresh()->status);

        $this->assertDatabaseHas('laundry_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => 'Selesai',
            'notes' => null,
            'changed_by' => $this->staff->id,
        ]);
    }

    public function test_cancelling_requires_cancellation_reason(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Diterima,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $transaction), [
                'status' => 'Dibatalkan',
                'notes' => '',
            ]);

        $response->assertSessionHasErrors('notes');
        $this->assertSame(TransactionStatus::Diterima, $transaction->fresh()->status);
    }

    public function test_admin_can_view_transaction_and_update_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'status' => TransactionStatus::Diterima,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.transactions.show', $transaction));

        $response->assertOk();

        $patchResponse = $this->actingAs($admin)
            ->patch(route('admin.transactions.status', $transaction), [
                'status' => 'Diproses',
            ]);

        $patchResponse->assertSessionHasNoErrors();
        $this->assertSame(TransactionStatus::Diproses, $transaction->fresh()->status);
    }
}
