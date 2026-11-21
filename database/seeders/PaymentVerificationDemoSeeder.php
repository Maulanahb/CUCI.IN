<?php

namespace Database\Seeders;

use App\Enums\TransactionStatus;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ComplaintCodeGenerator;
use App\Services\CreateTransaction;
use App\Services\RecordPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PaymentVerificationDemoSeeder extends Seeder
{
    /**
     * Prefix berkas bukti demo, sekaligus penanda agar seeder tidak dijalankan dua kali.
     */
    public const DEMO_PROOF_PREFIX = Payment::PROOF_DIRECTORY.'/demo-';

    /**
     * Seed skenario demo M2: bukti transfer/QRIS (menunggu, disetujui, ditolak), DP, lunas, dan pengaduan.
     *
     * Jalankan: php artisan db:seed --class=PaymentVerificationDemoSeeder
     */
    public function run(): void
    {
        $this->call([UserSeeder::class, ServiceSeeder::class, CustomerSeeder::class]);

        if (Payment::query()->where('proof_file', 'like', self::DEMO_PROOF_PREFIX.'%')->exists()) {
            $this->command?->warn('Data demo verifikasi pembayaran sudah ada, seeder dilewati.');

            return;
        }

        $staff = User::query()->where('email', 'staff@cuci.in')->firstOrFail();
        $registered = Customer::query()->where('phone', '081234567804')->firstOrFail();
        $guests = Customer::query()->whereNull('user_id')->orderBy('id')->get();
        $services = Service::query()->where('is_active', true)->orderBy('id')->get();

        $scenarios = [
            // [customer, layanan (index => berat), status cucian, skenario pembayaran, hari lalu]
            [$registered, [1 => 3.5], TransactionStatus::Diproses, 'pending_full_transfer', 1],
            [$registered, [0 => 4.0, 2 => 2.0], TransactionStatus::Selesai, 'dp_cash_pending_qris', 2],
            [$registered, [3 => 2.0], TransactionStatus::Diterima, 'rejected_transfer', 0],
            [$registered, [1 => 5.0], TransactionStatus::Diambil, 'verified_transfer', 6],
            [$guests[0], [1 => 6.0], TransactionStatus::Diproses, 'pending_full_qris', 1],
            [$guests[1], [0 => 3.0], TransactionStatus::Diambil, 'full_cash', 9],
            [$guests[2], [2 => 4.0], TransactionStatus::Selesai, 'dp_cash', 3],
            [$guests[0], [1 => 2.5], TransactionStatus::Dibatalkan, 'unpaid', 4],
            [$guests[1], [3 => 1.5], TransactionStatus::Diterima, 'unpaid', 0],
        ];

        $transactions = [];

        foreach ($scenarios as $index => [$customer, $items, $status, $paymentScenario, $daysAgo]) {
            $transaction = CreateTransaction::execute([
                'customer_id' => $customer->id,
                'created_by' => $staff->id,
                'items' => collect($items)->map(fn (float $weight, int $serviceIndex): array => [
                    'service_id' => $services[$serviceIndex]->id,
                    'weight' => $weight,
                ])->values()->all(),
            ]);

            $this->seedPayments($transaction, $paymentScenario, $staff, $customer, $index);
            $this->advanceStatus($transaction, $status, $staff);
            $this->backdate($transaction, $daysAgo);

            $transactions[] = $transaction;
        }

        $this->seedComplaints($transactions, $staff);

        $this->command?->info('Data demo verifikasi pembayaran & pengaduan berhasil dibuat.');
    }

    /**
     * Buat pembayaran sesuai skenario demo.
     */
    private function seedPayments(Transaction $transaction, string $scenario, User $staff, Customer $customer, int $index): void
    {
        $total = (float) $transaction->total_amount;
        $uploaderId = $customer->user_id ?? $staff->id;
        $halfAmount = round($total / 2, -3);

        match ($scenario) {
            'full_cash' => RecordPayment::execute($transaction, $total, 'Tunai', $staff->id),
            'dp_cash' => RecordPayment::execute($transaction, $halfAmount, 'Tunai', $staff->id),
            'pending_full_transfer' => $this->createProofPayment($transaction, $total, 'Transfer', Payment::PENDING_STATUS, $uploaderId, $index),
            'pending_full_qris' => $this->createProofPayment($transaction, $total, 'QRIS', Payment::PENDING_STATUS, $uploaderId, $index),
            'dp_cash_pending_qris' => $this->createDpThenPendingProof($transaction, $halfAmount, $staff, $uploaderId, $index),
            'rejected_transfer' => $this->createProofPayment($transaction, $total, 'Transfer', 'Ditolak', $uploaderId, $index, $staff, 'Nominal pada bukti tidak sesuai dengan tagihan.'),
            'verified_transfer' => $this->createProofPayment($transaction, $total, 'Transfer', 'Terverifikasi', $uploaderId, $index, $staff),
            default => null,
        };
    }

    /**
     * DP tunai di kasir, lalu pelunasan via QRIS yang menunggu verifikasi.
     */
    private function createDpThenPendingProof(Transaction $transaction, float $dpAmount, User $staff, int $uploaderId, int $index): void
    {
        RecordPayment::execute($transaction, $dpAmount, 'Tunai', $staff->id);

        $this->createProofPayment($transaction, (float) $transaction->total_amount - $dpAmount, 'QRIS', Payment::PENDING_STATUS, $uploaderId, $index);
    }

    /**
     * Simpan pembayaran non-tunai beserta gambar bukti demo.
     */
    private function createProofPayment(
        Transaction $transaction,
        float $amount,
        string $method,
        string $verificationStatus,
        int $uploaderId,
        int $index,
        ?User $verifier = null,
        ?string $notes = null
    ): Payment {
        $proofPath = self::DEMO_PROOF_PREFIX.$transaction->transaction_code.'-'.$index.'.png';
        self::writeSampleProof($proofPath, $method, $amount);

        $isProcessed = $verificationStatus !== Payment::PENDING_STATUS;

        return Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'payment_type' => RecordPayment::determinePaymentType($transaction->fresh(), $amount),
            'method' => $method,
            'proof_file' => $proofPath,
            'verification_status' => $verificationStatus,
            'verification_notes' => $notes,
            'paid_at' => now()->subHours($index + 1),
            'created_by' => $uploaderId,
            'verified_by' => $isProcessed ? $verifier?->id : null,
            'verified_at' => $isProcessed ? now() : null,
        ]);
    }

    /**
     * Majukan status cucian sesuai urutan operasional sambil mencatat riwayatnya.
     */
    private function advanceStatus(Transaction $transaction, TransactionStatus $target, User $staff): void
    {
        $path = match ($target) {
            TransactionStatus::Diterima => [],
            TransactionStatus::Diproses => [TransactionStatus::Diproses],
            TransactionStatus::Selesai => [TransactionStatus::Diproses, TransactionStatus::Selesai],
            TransactionStatus::Diambil => [TransactionStatus::Diproses, TransactionStatus::Selesai, TransactionStatus::Diambil],
            TransactionStatus::Dibatalkan => [TransactionStatus::Dibatalkan],
        };

        foreach ($path as $status) {
            $transaction->update(['status' => $status]);

            LaundryStatusHistory::create([
                'transaction_id' => $transaction->id,
                'status' => $status,
                'notes' => $status === TransactionStatus::Dibatalkan ? 'Pelanggan membatalkan pesanan (data demo).' : null,
                'changed_by' => $staff->id,
                'changed_at' => now(),
            ]);
        }
    }

    /**
     * Mundurkan tanggal transaksi agar dashboard & laporan demo terlihat realistis.
     */
    private function backdate(Transaction $transaction, int $daysAgo): void
    {
        if ($daysAgo === 0) {
            return;
        }

        $createdAt = now()->subDays($daysAgo);

        Transaction::query()->whereKey($transaction->id)->update(['created_at' => $createdAt]);
        LaundryStatusHistory::query()->where('transaction_id', $transaction->id)->update(['changed_at' => $createdAt]);
    }

    /**
     * Seed pengaduan dengan variasi status Menunggu, Diproses, dan Selesai.
     *
     * @param  list<Transaction>  $transactions
     */
    private function seedComplaints(array $transactions, User $staff): void
    {
        $complaints = [
            [$transactions[3], 'Kemeja putih ada noda kuning', 'Setelah diambil, kemeja putih masih ada noda kuning di bagian kerah.', 'Selesai', 'Kami sudah mencuci ulang kemeja tersebut tanpa biaya tambahan.'],
            [$transactions[1], 'Pakaian belum wangi', 'Sebagian pakaian masih tercium apek saat saya cek di outlet.', 'Diproses', null],
            [$transactions[5], 'Kaos kaki hilang satu pasang', 'Jumlah kaos kaki yang kembali kurang satu pasang dari yang saya serahkan.', 'Menunggu', null],
        ];

        foreach ($complaints as [$transaction, $subject, $description, $status, $response]) {
            Complaint::create([
                'complaint_code' => ComplaintCodeGenerator::generate(),
                'transaction_id' => $transaction->id,
                'subject' => $subject,
                'description' => $description,
                'status' => $status,
                'response' => $response,
                'deadline_at' => now()->addDays(2),
                'handled_by' => $status === 'Menunggu' ? null : $staff->id,
                'handled_at' => $status === 'Selesai' ? now() : null,
            ]);
        }
    }

    /**
     * Tulis gambar PNG sederhana sebagai bukti pembayaran contoh (tanpa data rekening asli).
     */
    public static function writeSampleProof(string $path, string $method, float $amount): void
    {
        $disk = Storage::disk(Payment::PROOF_DISK);

        if ($disk->exists($path)) {
            return;
        }

        if (! extension_loaded('gd')) {
            // PNG 1x1 transparan sebagai cadangan jika ekstensi GD tidak aktif.
            $disk->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

            return;
        }

        $image = imagecreatetruecolor(480, 640);
        $background = imagecolorallocate($image, 248, 250, 252);
        $ink = imagecolorallocate($image, 15, 23, 42);
        $accent = imagecolorallocate($image, 14, 116, 144);

        imagefilledrectangle($image, 0, 0, 480, 640, $background);
        imagefilledrectangle($image, 0, 0, 480, 80, $accent);
        imagestring($image, 5, 24, 30, 'BUKTI '.strtoupper($method).' (DATA DEMO)', $background);
        imagestring($image, 5, 24, 140, 'Nominal: Rp '.number_format($amount, 0, ',', '.'), $ink);
        imagestring($image, 4, 24, 180, 'Tanggal: '.now()->format('d-m-Y H:i'), $ink);
        imagestring($image, 4, 24, 220, 'Status : BERHASIL', $ink);
        imagestring($image, 3, 24, 580, 'Gambar contoh untuk pengujian CUCI.IN', $accent);

        ob_start();
        imagepng($image);
        $disk->put($path, (string) ob_get_clean());
        imagedestroy($image);
    }
}
