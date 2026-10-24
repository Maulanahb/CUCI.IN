<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Jobs\CompressComplaintPhoto;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ComplaintCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_complaints(): void
    {
        $response = $this->get(route('customer.complaints.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_complaints_index_page(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('customer.complaints.index'));
        $response->assertOk();
        $response->assertSee('Daftar Pengaduan');
        $response->assertSee('Ajukan Pengaduan');
    }

    public function test_customer_can_view_create_complaint_page(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($user)->get(route('customer.complaints.create'));
        $response->assertOk();
        $response->assertSee($transaction->transaction_code);
    }

    public function test_customer_can_store_complaint_successfully(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($user)->post(route('customer.complaints.store'), [
            'transaction_id' => $transaction->id,
            'subject' => 'Baju kemeja putih tertukar',
            'description' => 'Ada kemeja putih yang bukan milik saya di dalam paket laundry yang saya terima.',
        ]);

        $complaint = Complaint::where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($complaint);
        $this->assertSame('Baju kemeja putih tertukar', $complaint->subject);
        $this->assertSame('Menunggu', $complaint->status);
        $this->assertStringStartsWith('CMP-', $complaint->complaint_code);

        // Validasi SLA 2 hari
        $this->assertTrue($complaint->deadline_at->greaterThan(now()->addDay()));

        $response->assertRedirect(route('customer.complaints.show', $complaint));
    }

    public function test_customer_cannot_complaint_other_customer_transaction(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create(['role' => 'customer']);
        $customerB = Customer::factory()->create(['user_id' => $userB->id]);
        $transactionB = Transaction::factory()->create([
            'customer_id' => $customerB->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $response = $this->actingAs($userA)->post(route('customer.complaints.store'), [
            'transaction_id' => $transactionB->id,
            'subject' => 'Mencoba komplain nota orang lain',
            'description' => 'Deskripsi keluhan yang tidak sah secara otorisasi sistem.',
        ]);

        $response->assertSessionHasErrors('transaction_id');
        $this->assertDatabaseMissing('complaints', [
            'subject' => 'Mencoba komplain nota orang lain',
        ]);
    }

    public function test_customer_cannot_complaint_canceled_transaction(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Dibatalkan,
        ]);

        $response = $this->actingAs($user)->post(route('customer.complaints.store'), [
            'transaction_id' => $transaction->id,
            'subject' => 'Komplain nota dibatalkan',
            'description' => 'Mencoba mengajukan komplain untuk nota transaksi yang sudah dibatalkan.',
        ]);

        $response->assertSessionHasErrors('transaction_id');
    }

    public function test_customer_cannot_submit_duplicate_active_complaint(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Komplain awal yang masih menunggu',
            'description' => 'Rincian keluhan pertama yang masih berstatus menunggu tindak lanjut.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->post(route('customer.complaints.store'), [
            'transaction_id' => $transaction->id,
            'subject' => 'Komplain kedua yang mencoba duplikasi',
            'description' => 'Mencoba mengajukan komplain kedua pada nota yang sama sebelum selesai.',
        ]);

        $response->assertSessionHasErrors('transaction_id');
    }

    public function test_customer_can_view_their_own_complaint_detail(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaint = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transaction->id,
            'subject' => 'Pakaian bernoda setelah dicuci',
            'description' => 'Ada noda oli di bagian kerah yang masih tertinggal saat diterima.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('customer.complaints.show', $complaint));
        $response->assertOk();
        $response->assertSee($complaint->complaint_code);
        $response->assertSee('Pakaian bernoda setelah dicuci');
    }

    public function test_customer_cannot_view_other_customer_complaint(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create(['role' => 'customer']);
        $customerB = Customer::factory()->create(['user_id' => $userB->id]);
        $transactionB = Transaction::factory()->create([
            'customer_id' => $customerB->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $complaintB = Complaint::create([
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => $transactionB->id,
            'subject' => 'Keluhan rahasia customer B',
            'description' => 'Rincian masalah pelanggan lain yang tidak boleh diakses pelanggan A.',
            'status' => 'Menunggu',
            'deadline_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($userA)->get(route('customer.complaints.show', $complaintB));
        $response->assertForbidden();
    }

    public function test_sidebar_renders_pengaduan_navigation_for_customer(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));
        $response->assertOk();
        $response->assertSee(route('customer.complaints.index'));
        $response->assertSee('Pengaduan');
    }

    public function test_customer_can_upload_complaint_photo_and_dispatches_compress_job(): void
    {
        Storage::fake('public');
        Queue::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        // Buat file gambar fake dengan header JPEG valid tanpa tergantung ekstensi PHP-GD
        $fakeJpgContent = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00\xFF\xDB\x00C\x00\xFF\xC0\x00\x11\x08\x00\x10\x00\x10\x03\x01\x22\x00\x02\x11\x01\x03\x11\x01\xFF\xC4\x00\x1F\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\xFF\xDA\x00\x0C\x03\x01\x00\x02\x11\x03\x11\x00?\x00\xBF\x00\xFF\xD9";
        $fakePhoto = UploadedFile::fake()->createWithContent('baju_noda.jpg', $fakeJpgContent);

        $response = $this->actingAs($user)->post(route('customer.complaints.store'), [
            'transaction_id' => $transaction->id,
            'subject' => 'Noda oli di kemeja putih',
            'description' => 'Ada noda hitam pekat di bagian dada baju setelah dicuci.',
            'photo' => $fakePhoto,
        ]);

        $complaint = Complaint::where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($complaint);
        $this->assertNotNull($complaint->photo_path);

        Storage::disk('public')->assertExists($complaint->photo_path);
        Queue::assertPushed(CompressComplaintPhoto::class);

        $response->assertRedirect(route('customer.complaints.show', $complaint));
    }

    public function test_customer_cannot_upload_invalid_file_for_complaint_photo(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'status' => TransactionStatus::Diproses,
        ]);

        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($user)->post(route('customer.complaints.store'), [
            'transaction_id' => $transaction->id,
            'subject' => 'Noda oli di kemeja putih',
            'description' => 'Ada noda hitam pekat di bagian dada baju setelah dicuci.',
            'photo' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('photo');
    }
}
