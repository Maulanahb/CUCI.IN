<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->staff = User::factory()->staff()->create();
        $this->customer = Customer::factory()->create();
    }

    public function test_staff_dashboard_prioritizes_overdue_orders_and_excludes_finished_ones(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));

        $overdue = $this->transaction(
            TransactionStatus::Diterima,
            Carbon::parse('2026-10-20 08:00:00')
        );

        $onSchedule = $this->transaction(
            TransactionStatus::Diproses,
            Carbon::parse('2026-10-21 12:00:00')
        );

        $pickedUp = $this->transaction(
            TransactionStatus::Diambil,
            Carbon::parse('2026-10-19 08:00:00')
        );

        $cancelled = $this->transaction(
            TransactionStatus::Dibatalkan,
            Carbon::parse('2026-10-19 08:00:00')
        );

        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk();

        $queue = $response->viewData('queueTransactions');
        $codes = $queue->getCollection()->pluck('transaction_code');

        $this->assertSame($overdue->transaction_code, $codes->first());
        $this->assertTrue($queue->getCollection()->first()->queue_is_overdue);
        $this->assertContains($onSchedule->transaction_code, $codes);
        $this->assertNotContains($pickedUp->transaction_code, $codes);
        $this->assertNotContains($cancelled->transaction_code, $codes);
        $this->assertSame(1, $response->viewData('stats')['overdue']);
    }

    public function test_staff_dashboard_counts_overdue_only_for_active_statuses(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));

        $this->transaction(TransactionStatus::Diterima, Carbon::parse('2026-10-20 08:00:00'));
        $this->transaction(TransactionStatus::Diproses, Carbon::parse('2026-10-20 09:00:00'));
        $this->transaction(TransactionStatus::Selesai, Carbon::parse('2026-10-20 07:00:00'));
        $this->transaction(TransactionStatus::Diambil, Carbon::parse('2026-10-20 07:00:00'));

        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk();

        $this->assertSame(2, $response->viewData('stats')['overdue']);
        $this->assertSame(1, $response->viewData('overdueByStatus')['diterima']);
        $this->assertSame(1, $response->viewData('overdueByStatus')['diproses']);
    }

    public function test_staff_dashboard_cash_summary_only_counts_valid_payments_today(): void
    {
        $transaction = $this->transaction(TransactionStatus::Diterima, now()->addDay());

        $this->payment($transaction, 30000, 'Terverifikasi', 'Tunai');
        $this->payment($transaction, 20000, 'Tidak Diperlukan', 'Transfer');
        $this->payment($transaction, 15000, 'Menunggu Verifikasi', 'QRIS');
        $this->payment($transaction, 50000, 'Terverifikasi', 'Tunai', now()->subDay());

        $paidOff = $this->transaction(TransactionStatus::Diproses, now()->addDay());
        $paidOff->update(['total_amount' => 40000]);
        $this->payment($paidOff, 40000, 'Terverifikasi', 'Tunai');

        $unpaid = $this->transaction(TransactionStatus::Diterima, now()->addDay());
        $unpaid->update(['total_amount' => 40000]);

        $this->transaction(TransactionStatus::Dibatalkan, now()->addDay());

        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk();

        $this->assertSame(90000.0, $response->viewData('cashTotal'));

        $breakdown = $response->viewData('cashBreakdown');

        $this->assertSame(70000.0, $breakdown->firstWhere('method', 'Tunai')['total']);
        $this->assertSame(20000.0, $breakdown->firstWhere('method', 'Transfer')['total']);
        $this->assertSame(0.0, $breakdown->firstWhere('method', 'QRIS')['total']);
        $this->assertSame(77.8, $breakdown->firstWhere('method', 'Tunai')['percentage']);

        $this->assertSame(2, $response->viewData('paidTodayCount'));
        $this->assertSame(1, $response->viewData('unpaidTodayCount'));
    }

    public function test_staff_dashboard_paginates_queue_six_per_page(): void
    {
        collect(range(1, 8))->each(
            fn (): Transaction => $this->transaction(TransactionStatus::Diterima, now()->addDay())
        );

        $firstPage = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $firstPage->assertOk();

        $paginator = $firstPage->viewData('queueTransactions');

        $this->assertSame(6, $paginator->count());
        $this->assertSame(6, $paginator->perPage());
        $this->assertSame(8, $paginator->total());
        $this->assertSame(2, $paginator->lastPage());

        $secondPage = $this->actingAs($this->staff)
            ->get(route('staff.dashboard', ['queue_page' => 2]));

        $secondPage->assertOk();
        $this->assertSame(2, $secondPage->viewData('queueTransactions')->count());
    }

    public function test_staff_dashboard_does_not_render_admin_only_widgets(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('Kas Hari Ini');
        $response->assertDontSee('Pemasukan Bulan Ini');
        $response->assertDontSee('Piutang Berjalan');
        $response->assertDontSee('Layanan Terlaris');
    }

    public function test_staff_dashboard_renders_empty_state_without_transactions(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('Belum Ada Antrean');
        $response->assertSee('Belum Ada Aktivitas Status');
        $response->assertSee('Belum ada pembayaran sah hari ini.');
        $this->assertSame(0.0, $response->viewData('cashTotal'));
        $this->assertSame(0, $response->viewData('stats')['overdue']);
    }

    public function test_staff_dashboard_provides_recent_activities_and_new_customers(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));

        $transaction = $this->transaction(
            TransactionStatus::Diterima,
            Carbon::parse('2026-10-21 12:00:00')
        );

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diterima,
            'changed_by' => $this->staff->id,
            'changed_at' => now()->subMinutes(20),
        ]);

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diproses,
            'notes' => 'Masuk mesin 2',
            'changed_by' => $this->staff->id,
            'changed_at' => now()->subMinutes(5),
        ]);

        $newCustomer = Customer::factory()->create([
            'name' => 'Siti Rahma',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('Aktivitas Cucian');
        $response->assertSee('Pelanggan Baru');
        $response->assertSee('Siti Rahma');
        $response->assertSee('5 menit yang lalu');
        $response->assertSee('Masuk mesin 2');

        $activities = $response->viewData('recentActivities');
        $this->assertSame(TransactionStatus::Diterima, $activities->first()->previous_status);
        $this->assertSame(TransactionStatus::Diproses, $activities->first()->status);
        $this->assertSame($transaction->transaction_code, $activities->first()->transaction->transaction_code);

        $customers = $response->viewData('recentCustomers');
        $this->assertSame('Siti Rahma', $customers->firstWhere('id', $newCustomer->id)->name);
        $this->assertSame(0, $customers->firstWhere('id', $newCustomer->id)->transactions_count);
    }

    private function transaction(
        TransactionStatus $status,
        Carbon|\DateTimeInterface $estimatedCompletedAt,
    ): Transaction {
        return Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 50000,
            'status' => $status,
            'estimated_completed_at' => $estimatedCompletedAt,
        ]);
    }

    private function payment(
        Transaction $transaction,
        float $amount,
        string $verificationStatus,
        string $method,
        ?Carbon $paidAt = null,
    ): Payment {
        return Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'payment_type' => 'Pembayaran Penuh',
            'method' => $method,
            'verification_status' => $verificationStatus,
            'paid_at' => $paidAt ?? now(),
            'created_by' => $this->staff->id,
        ]);
    }
}
