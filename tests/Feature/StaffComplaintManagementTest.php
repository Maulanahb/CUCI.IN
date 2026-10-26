<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ComplaintCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffComplaintManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_staff_complaints(): void
    {
        $response = $this->get(route('staff.complaints.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_staff_complaints(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customerUser)->get(route('staff.complaints.index'));
        $response->assertForbidden();
    }

    public function test_staff_can_view_complaints_index_page_with_stats(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Baju kelunturan warna merah',
            'description' => 'Ada bagian kerah baju yang kelunturan.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.complaints.index'));
        $response->assertOk();
        $response->assertSee('Kelola Pengaduan Pelanggan');
        $response->assertSee($complaint->complaint_code);
        $response->assertSee($customer->name);
        $response->assertSee('Baju kelunturan warna merah');
    }

    public function test_staff_can_filter_complaints_by_status(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $pendingComplaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Komplain status menunggu',
            'description' => 'Masih menunggu respon staf.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $resolvedComplaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Komplain status selesai',
            'description' => 'Sudah selesai ditangani.',
            'status' => 'Selesai',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.complaints.index', ['status' => 'Menunggu']));
        $response->assertOk();
        $response->assertSee($pendingComplaint->subject);
        $response->assertDontSee($resolvedComplaint->subject);
    }

    public function test_staff_can_search_complaints_by_code_or_subject(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaintA = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Jaket kulit tertinggal di mesin cuci',
            'description' => 'Mohon dicek di keranjang outlet.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $complaintB = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Sprei kasur wangi kurang kuat',
            'description' => 'Minta parfum lavender lebih pekat.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.complaints.index', ['search' => 'Jaket kulit']));
        $response->assertOk();
        $response->assertSee($complaintA->subject);
        $response->assertDontSee($complaintB->subject);
    }

    public function test_staff_can_view_complaint_detail(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Pakaian tertukar dengan pelanggan lain',
            'description' => 'Kemeja flanel warna biru bukan punya saya.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.complaints.show', $complaint));
        $response->assertOk();
        $response->assertSee($complaint->complaint_code);
        $response->assertSee('Pakaian tertukar dengan pelanggan lain');
        $response->assertSee($transaction->transaction_code);
        $response->assertSee('Tindak Lanjut');
    }

    public function test_staff_can_update_complaint_status_and_response_successfully(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Siti Kasir']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Kancing baju lepas',
            'description' => 'Kancing jas bagian depan lepas saat dicuci.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->patch(route('staff.complaints.update', $complaint), [
            'status' => 'Diproses',
            'response' => 'Kami sedang mencarikan kancing cadangan yang sesuai dan akan segera kami pasang kembali.',
        ]);

        $response->assertRedirect(route('staff.complaints.show', $complaint));
        $response->assertSessionHas('status');

        $complaint->refresh();
        $this->assertSame('Diproses', $complaint->status);
        $this->assertSame('Kami sedang mencarikan kancing cadangan yang sesuai dan akan segera kami pasang kembali.', $complaint->response);
        $this->assertSame($staff->id, $complaint->handled_by);
        $this->assertNotNull($complaint->handled_at);
    }

    public function test_staff_can_resolve_complaint_successfully(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Noda oli pada celana jeans',
            'description' => 'Noda oli belum hilang sepenuhnya.',
            'status' => 'Diproses',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->patch(route('staff.complaints.update', $complaint), [
            'status' => 'Selesai',
            'response' => 'Celana telah dicuci ulang dengan formula anti-oli khusus dan sekarang sudah bersih sempurna. Siap diambil di kasir.',
        ]);

        $response->assertRedirect(route('staff.complaints.show', $complaint));

        $complaint->refresh();
        $this->assertSame('Selesai', $complaint->status);
        $this->assertSame($staff->id, $complaint->handled_by);
    }

    public function test_staff_update_fails_with_invalid_status(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Uji validasi status tidak valid',
            'description' => 'Deskripsi keluhan.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($staff)->patch(route('staff.complaints.update', $complaint), [
            'status' => 'StatusTidakValid',
            'response' => 'Tanggapan.',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_admin_also_can_access_and_manage_complaints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Komplain dicek oleh admin',
            'description' => 'Deskripsi keluhan yang dipantau administrator.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.complaints.index'));
        $response->assertOk();
        $response->assertSee($complaint->complaint_code);

        $updateResponse = $this->actingAs($admin)->patch(route('admin.complaints.update', $complaint), [
            'status' => 'Selesai',
            'response' => 'Diselesaikan langsung oleh admin toko.',
        ]);

        $updateResponse->assertRedirect(route('staff.complaints.show', $complaint));
        $complaint->refresh();
        $this->assertSame('Selesai', $complaint->status);
    }
}
