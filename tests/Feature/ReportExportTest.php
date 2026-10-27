<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

    protected Customer $customer;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        $this->service = Service::factory()->create([
            'name' => 'Cuci + Setrika Reguler',
            'price' => 10000,
            'unit' => 'kg',
            'service_type' => 'reguler',
            'is_express' => false,
        ]);
    }

    public function test_guest_is_redirected_from_reports_page(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_reports_page_with_data_and_kpis(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->admin->id,
            'total_amount' => 50000,
            'status' => TransactionStatus::Selesai,
            'created_at' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'weight' => 5.0,
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 50000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertViewIs('admin.reports.index');
        $response->assertSee('Laporan Operasional & Keuangan');
        $response->assertSee($transaction->transaction_code);
        $response->assertSee('Budi Santoso');
        $response->assertSee('btn-export-pdf', false);
        $response->assertSee('btn-export-excel', false);
        $response->assertSee('btn-export-csv', false);
    }

    public function test_reports_filter_includes_late_night_transactions_with_end_of_day(): void
    {
        // Transaksi dibuat pada pukul 23:45:00 di akhir hari
        $lateNightDate = now()->subDays(2)->setTime(23, 45, 0);

        $lateTransaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->admin->id,
            'total_amount' => 30000,
            'status' => TransactionStatus::Selesai,
            'created_at' => $lateNightDate,
        ]);

        // Filter dari 3 hari lalu sampai 2 hari lalu
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'start_date' => now()->subDays(3)->format('Y-m-d'),
            'end_date' => now()->subDays(2)->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertSee($lateTransaction->transaction_code);
    }

    public function test_admin_can_export_pdf_executive_report(): void
    {
        for ($i = 0; $i < 3; $i++) {
            Transaction::factory()->create([
                'customer_id' => $this->customer->id,
                'created_by' => $this->admin->id,
                'total_amount' => 25000,
                'created_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }

    public function test_admin_can_export_excel_spreadsheet_report(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->admin->id,
            'total_amount' => 45000,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.excel'));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString($transaction->transaction_code, $response->getContent());
        $this->assertStringContainsString('Budi Santoso', $response->getContent());
    }

    public function test_admin_can_export_csv_streaming_report_with_utf8_bom(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->admin->id,
            'total_amount' => 60000,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        // Cek isi stream output
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Pastikan ada UTF-8 BOM di awal berkas CSV
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('No Nota', $content);
        $this->assertStringContainsString('Tanggal Masuk', $content);
        $this->assertStringContainsString($transaction->transaction_code, $content);
    }
}
