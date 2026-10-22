<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
        $this->customer = Customer::factory()->create();
    }

    public function test_admin_dashboard_sums_only_valid_payments_as_revenue(): void
    {
        $transaction = $this->transaction(100000);

        $this->payment($transaction, 40000, 'Terverifikasi');
        $this->payment($transaction, 20000, 'Tidak Diperlukan');
        $this->payment($transaction, 15000, 'Menunggu Verifikasi');
        $this->payment($transaction, 10000, 'Ditolak');

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Pemasukan Hari Ini');
        $response->assertSee('Piutang Berjalan');

        $finance = $response->viewData('finance');

        $this->assertSame(60000.0, $finance['today']);
        $this->assertSame(60000.0, $finance['month']);
        $this->assertSame(40000.0, $finance['receivables']);
    }

    public function test_weekly_revenue_always_has_seven_days_and_fills_empty_days(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));

        $transaction = $this->transaction(100000);

        $this->payment($transaction, 50000, 'Terverifikasi', Carbon::parse('2026-10-20 09:00:00'));
        $this->payment($transaction, 25000, 'Tidak Diperlukan', Carbon::parse('2026-10-17 09:00:00'));
        $this->payment($transaction, 99000, 'Ditolak', Carbon::parse('2026-10-19 09:00:00'));
        $this->payment($transaction, 88000, 'Terverifikasi', Carbon::parse('2026-10-13 09:00:00'));

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();

        $weeklyRevenue = $response->viewData('weeklyRevenue');

        $this->assertCount(7, $weeklyRevenue);
        $this->assertSame('2026-10-14', $weeklyRevenue->first()['date']);
        $this->assertSame('2026-10-20', $weeklyRevenue->last()['date']);
        $this->assertSame(50000.0, $weeklyRevenue->last()['total']);
        $this->assertSame(25000.0, $weeklyRevenue[3]['total']);
        $this->assertSame(0.0, $weeklyRevenue->first()['total']);
        $this->assertSame(75000.0, $response->viewData('weeklyTotal'));
    }

    public function test_receivables_exclude_cancelled_transactions_and_count_partial_payments(): void
    {
        $partial = $this->transaction(100000);
        $this->payment($partial, 40000, 'Terverifikasi');

        $this->transaction(80000, TransactionStatus::Dibatalkan);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();

        $finance = $response->viewData('finance');
        $paymentBreakdown = $response->viewData('paymentBreakdown');

        $this->assertSame(60000.0, $finance['receivables']);
        $this->assertSame(1, $paymentBreakdown->firstWhere('status', 'DP')['count']);
        $this->assertSame(0, $paymentBreakdown->firstWhere('status', 'Belum Bayar')['count']);
        $this->assertSame(1, $response->viewData('paymentTotal'));
    }

    public function test_payment_composition_counts_paid_off_partial_and_unpaid_transactions(): void
    {
        $paidOff = $this->transaction(50000);
        $this->payment($paidOff, 50000, 'Terverifikasi');

        $partial = $this->transaction(100000);
        $this->payment($partial, 30000, 'Tidak Diperlukan');

        $this->transaction(75000);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();

        $breakdown = $response->viewData('paymentBreakdown');

        $this->assertSame(3, $response->viewData('paymentTotal'));
        $this->assertSame(1, $breakdown->firstWhere('status', 'Lunas')['count']);
        $this->assertSame(1, $breakdown->firstWhere('status', 'DP')['count']);
        $this->assertSame(1, $breakdown->firstWhere('status', 'Belum Bayar')['count']);
        $this->assertSame(33.3, $breakdown->firstWhere('status', 'Lunas')['percentage']);
    }

    public function test_top_services_lists_five_highest_revenue_services_from_last_30_days(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));

        $regular = Service::factory()->create(['name' => 'Cuci Reguler']);
        $express = Service::factory()->create(['name' => 'Cuci Express']);
        $bedCover = Service::factory()->create(['name' => 'Bed Cover']);
        $sepatu = Service::factory()->create(['name' => 'Cuci Sepatu']);
        $gorden = Service::factory()->create(['name' => 'Cuci Gorden']);
        $karpet = Service::factory()->create(['name' => 'Cuci Karpet']);
        $serviceLama = Service::factory()->create(['name' => 'Layanan Lama']);
        $serviceBatal = Service::factory()->create(['name' => 'Layanan Batal']);

        $transaction = $this->transaction(500000);

        $this->detail($transaction, $regular, 10, 5000);
        $this->detail($transaction, $express, 8, 10000);
        $this->detail($transaction, $bedCover, 5, 20000);
        $this->detail($transaction, $sepatu, 4, 25000);
        $this->detail($transaction, $gorden, 3, 30000);
        $this->detail($transaction, $karpet, 20, 40000);

        $oldTransaction = $this->transaction(100000)->forceFill(['created_at' => now()->subDays(40)]);
        $oldTransaction->save();
        $this->detail($oldTransaction, $serviceLama, 100, 100000);

        $cancelledTransaction = $this->transaction(100000, TransactionStatus::Dibatalkan);
        $this->detail($cancelledTransaction, $serviceBatal, 100, 100000);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();

        $topServices = $response->viewData('topServices');

        $this->assertCount(5, $topServices);
        $this->assertSame('Cuci Karpet', $topServices->first()->service_name);
        $this->assertSame(800000.0, $topServices->first()->revenue);
        $this->assertNotContains('Layanan Lama', $topServices->pluck('service_name'));
        $this->assertNotContains('Layanan Batal', $topServices->pluck('service_name'));
    }

    public function test_recent_transactions_on_dashboard_are_paginated_five_per_page(): void
    {
        $transactions = collect(range(1, 7))->map(fn (): Transaction => $this->transaction(50000));

        $firstPage = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $firstPage->assertOk();

        $paginator = $firstPage->viewData('recentTransactions');

        $this->assertSame(5, $paginator->count());
        $this->assertSame(5, $paginator->perPage());
        $this->assertSame(7, $paginator->total());
        $this->assertSame(2, $paginator->lastPage());

        $secondPage = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['transactions_page' => 2]));

        $secondPage->assertOk();

        $secondPaginator = $secondPage->viewData('recentTransactions');

        $this->assertSame(2, $secondPaginator->count());
        $this->assertSame($transactions[1]->transaction_code, $secondPaginator->first()->transaction_code);
        $this->assertSame($transactions[0]->transaction_code, $secondPaginator->last()->transaction_code);
    }

    public function test_admin_dashboard_provides_recent_status_activities_and_customers(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));

        $staff = User::factory()->staff()->create(['name' => 'Budi Kasir']);
        $transaction = $this->transaction(75000);

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diterima,
            'changed_by' => $staff->id,
            'changed_at' => now()->subMinutes(30),
        ]);

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diproses,
            'notes' => 'Sedang dicuci di mesin 2',
            'changed_by' => $staff->id,
            'changed_at' => now()->subMinutes(10),
        ]);

        $newCustomer = Customer::factory()->create([
            'name' => 'Siti Rahma',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Aktivitas Cucian');
        $response->assertSee('Pelanggan Baru');
        $response->assertSee('Budi Kasir');
        $response->assertSee('Siti Rahma');
        $response->assertSee('10 menit yang lalu');
        $response->assertSee('1 transaksi');
        $response->assertSee('0 transaksi');

        $activities = $response->viewData('recentActivities');
        $customers = $response->viewData('recentCustomers');

        $this->assertNotEmpty($activities);
        $this->assertSame('Budi Kasir', $activities->first()->changedBy->name);
        $this->assertSame(TransactionStatus::Diterima, $activities->first()->previous_status);
        $this->assertSame(TransactionStatus::Diproses, $activities->first()->status);
        $this->assertSame($transaction->transaction_code, $activities->first()->transaction->transaction_code);

        $this->assertNotEmpty($customers);
        $this->assertSame('Siti Rahma', $customers->firstWhere('id', $newCustomer->id)->name);
        $this->assertSame(0, $customers->firstWhere('id', $newCustomer->id)->transactions_count);
    }

    public function test_recent_activities_and_customers_are_limited_to_five_latest(): void
    {
        $transaction = $this->transaction(75000);

        foreach (range(1, 7) as $index) {
            LaundryStatusHistory::create([
                'transaction_id' => $transaction->id,
                'status' => TransactionStatus::Diproses,
                'notes' => "Pembaruan ke-{$index}",
                'changed_by' => $this->admin->id,
                'changed_at' => now()->subMinutes(10 - $index),
            ]);
        }

        Customer::factory()->count(6)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertCount(5, $response->viewData('recentActivities'));
        $this->assertCount(5, $response->viewData('recentCustomers'));
    }

    public function test_admin_dashboard_renders_without_transactions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Belum Ada Transaksi');
        $response->assertSee('Belum ada layanan terjual');
        $response->assertSee('Belum Ada Aktivitas Status');
        $this->assertSame(0.0, $response->viewData('weeklyTotal'));
        $this->assertSame(0, $response->viewData('paymentTotal'));
    }

    private function transaction(float $totalAmount, TransactionStatus $status = TransactionStatus::Diterima): Transaction
    {
        return Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->admin->id,
            'total_amount' => $totalAmount,
            'status' => $status,
        ]);
    }

    private function payment(
        Transaction $transaction,
        float $amount,
        string $verificationStatus,
        ?Carbon $paidAt = null,
        string $method = 'Tunai',
    ): Payment {
        return Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'payment_type' => 'Pembayaran Penuh',
            'method' => $method,
            'verification_status' => $verificationStatus,
            'paid_at' => $paidAt ?? now(),
            'created_by' => $this->admin->id,
        ]);
    }

    private function detail(Transaction $transaction, Service $service, float $weight, float $unitPrice): TransactionDetail
    {
        return TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => $weight,
            'unit_price' => $unitPrice,
            'subtotal' => $weight * $unitPrice,
        ]);
    }
}
