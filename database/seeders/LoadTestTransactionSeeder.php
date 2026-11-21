<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoadTestTransactionSeeder extends Seeder
{
    /**
     * Jumlah baris per batch insert agar memori & ukuran query tetap stabil.
     */
    private const CHUNK_SIZE = 500;

    /**
     * Path gambar bukti bersama untuk seluruh pembayaran non-tunai hasil uji beban.
     */
    private const SAMPLE_PROOF_PATH = Payment::PROOF_DIRECTORY.'/load-test-sample.png';

    /**
     * Seed transaksi dalam jumlah besar (default 5.000) untuk uji beban laporan, daftar, dan panel verifikasi.
     *
     * Jalankan: php artisan db:seed --class=LoadTestTransactionSeeder
     * Data dimasukkan dengan bulk insert sehingga event model sengaja dilewati.
     */
    public function run(int $count = 5000): void
    {
        $this->call([UserSeeder::class, ServiceSeeder::class, CustomerSeeder::class]);

        $staffIds = User::query()->whereIn('role', ['admin', 'staff'])->where('is_active', true)->pluck('id')->all();
        $services = Service::query()->where('is_active', true)->get(['id', 'price', 'estimated_duration'])->all();

        if (Customer::query()->count() < 100) {
            Customer::factory()->count(100)->create();
        }

        $customerIds = Customer::query()->pluck('id')->all();

        PaymentVerificationDemoSeeder::writeSampleProof(self::SAMPLE_PROOF_PATH, 'Transfer', 0);

        $nextNumber = $this->nextTransactionNumber();
        $remaining = $count;

        while ($remaining > 0) {
            $batchSize = min(self::CHUNK_SIZE, $remaining);

            DB::transaction(function () use ($batchSize, $nextNumber, $staffIds, $services, $customerIds): void {
                $this->insertBatch($batchSize, $nextNumber, $staffIds, $services, $customerIds);
            });

            $nextNumber += $batchSize;
            $remaining -= $batchSize;
        }

        $this->command?->info("{$count} transaksi uji beban berhasil dibuat.");
    }

    /**
     * Ambil nomor urut kode transaksi berikutnya (lanjutan dari TRX-XXXX terakhir).
     */
    private function nextTransactionNumber(): int
    {
        $lastCode = Transaction::query()
            ->where('transaction_code', 'like', 'TRX-%')
            ->orderByDesc('id')
            ->value('transaction_code');

        return $lastCode !== null && preg_match('/^TRX-(\d+)$/', $lastCode, $matches) === 1
            ? ((int) $matches[1]) + 1
            : 1;
    }

    /**
     * Masukkan satu batch transaksi beserta detail, riwayat status, dan pembayaran.
     *
     * @param  list<int>  $staffIds
     * @param  list<Service>  $services
     * @param  list<int>  $customerIds
     */
    private function insertBatch(int $batchSize, int $startNumber, array $staffIds, array $services, array $customerIds): void
    {
        $transactionRows = [];
        $plans = [];

        for ($i = 0; $i < $batchSize; $i++) {
            $code = sprintf('TRX-%04d', $startNumber + $i);
            $createdAt = Carbon::now()->subMinutes(random_int(0, 180 * 24 * 60));
            $staffId = $staffIds[array_rand($staffIds)];

            $details = [];
            $total = 0.0;
            $maxDuration = 0;

            foreach ((array) array_rand($services, random_int(1, min(2, count($services)))) as $serviceIndex) {
                $service = $services[$serviceIndex];
                $weight = random_int(10, 80) / 10;
                $subtotal = round($weight * (float) $service->price, 2);
                $total += $subtotal;
                $maxDuration = max($maxDuration, (int) $service->estimated_duration);
                $details[] = ['service_id' => $service->id, 'weight' => $weight, 'unit_price' => $service->price, 'subtotal' => $subtotal];
            }

            [$status, $paymentPlan] = $this->pickScenario();

            $transactionRows[] = [
                'customer_id' => $customerIds[array_rand($customerIds)],
                'created_by' => $staffId,
                'transaction_code' => $code,
                'total_amount' => $total,
                'status' => $status,
                'estimated_completed_at' => $createdAt->copy()->addHours($maxDuration),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            $plans[$code] = compact('details', 'total', 'status', 'paymentPlan', 'createdAt', 'staffId');
        }

        DB::table('transactions')->insert($transactionRows);

        $transactionIds = DB::table('transactions')
            ->whereIn('transaction_code', array_keys($plans))
            ->pluck('id', 'transaction_code');

        $detailRows = [];
        $historyRows = [];
        $paymentRows = [];

        foreach ($plans as $code => $plan) {
            $transactionId = $transactionIds[$code];

            foreach ($plan['details'] as $detail) {
                $detailRows[] = ['transaction_id' => $transactionId, ...$detail];
            }

            foreach ($this->statusPath($plan['status']) as $step => $status) {
                $historyRows[] = [
                    'transaction_id' => $transactionId,
                    'status' => $status,
                    'notes' => $status === 'Dibatalkan' ? 'Dibatalkan pelanggan (data uji beban).' : null,
                    'changed_by' => $plan['staffId'],
                    'changed_at' => $plan['createdAt']->copy()->addHours($step * 12),
                ];
            }

            foreach ($this->paymentRows($plan) as $payment) {
                $paymentRows[] = ['transaction_id' => $transactionId, ...$payment];
            }
        }

        DB::table('transaction_details')->insert($detailRows);
        DB::table('laundry_status_histories')->insert($historyRows);

        if ($paymentRows !== []) {
            DB::table('payments')->insert($paymentRows);
        }
    }

    /**
     * Pilih kombinasi status cucian & pola pembayaran. Status "Diambil" selalu lunas.
     *
     * @return array{0: string, 1: string}
     */
    private function pickScenario(): array
    {
        $roll = random_int(1, 100);

        return match (true) {
            $roll <= 35 => ['Diambil', 'full'],
            $roll <= 50 => ['Selesai', 'full'],
            $roll <= 62 => ['Selesai', 'dp'],
            $roll <= 72 => ['Diproses', 'dp'],
            $roll <= 82 => ['Diproses', 'pending_proof'],
            $roll <= 87 => ['Diterima', 'rejected_proof'],
            $roll <= 95 => ['Diterima', 'unpaid'],
            default => ['Dibatalkan', 'unpaid'],
        };
    }

    /**
     * Urutan status yang dilalui hingga status akhir.
     *
     * @return list<string>
     */
    private function statusPath(string $finalStatus): array
    {
        return match ($finalStatus) {
            'Diproses' => ['Diterima', 'Diproses'],
            'Selesai' => ['Diterima', 'Diproses', 'Selesai'],
            'Diambil' => ['Diterima', 'Diproses', 'Selesai', 'Diambil'],
            'Dibatalkan' => ['Diterima', 'Dibatalkan'],
            default => ['Diterima'],
        };
    }

    /**
     * Bangun baris pembayaran sesuai pola. Nominal valid tidak pernah melebihi total tagihan.
     *
     * @param  array{total: float, paymentPlan: string, createdAt: Carbon, staffId: int}  $plan
     * @return list<array<string, mixed>>
     */
    private function paymentRows(array $plan): array
    {
        $total = $plan['total'];
        $paidAt = $plan['createdAt']->copy()->addMinutes(5);
        $dpAmount = round($total * 0.5, 2);
        $nonCashMethod = random_int(0, 1) === 1 ? 'Transfer' : 'QRIS';

        $row = fn (float $amount, string $type, string $method, string $verification, ?string $notes = null): array => [
            'amount' => $amount,
            'payment_type' => $type,
            'method' => $method,
            'cash_received' => $method === 'Tunai' ? $amount : null,
            'change_amount' => $method === 'Tunai' ? 0 : null,
            'proof_file' => $method === 'Tunai' ? null : self::SAMPLE_PROOF_PATH,
            'verification_status' => $verification,
            'verification_notes' => $notes,
            'paid_at' => $paidAt,
            'created_by' => $plan['staffId'],
            'verified_by' => in_array($verification, ['Terverifikasi', 'Ditolak'], true) ? $plan['staffId'] : null,
            'verified_at' => in_array($verification, ['Terverifikasi', 'Ditolak'], true) ? $paidAt : null,
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ];

        return match ($plan['paymentPlan']) {
            'full' => random_int(0, 2) === 0
                ? [$row($total, 'Pembayaran Penuh', $nonCashMethod, 'Terverifikasi')]
                : [$row($total, 'Pembayaran Penuh', 'Tunai', 'Tidak Diperlukan')],
            'dp' => [$row($dpAmount, 'DP', 'Tunai', 'Tidak Diperlukan')],
            'pending_proof' => [
                $row($dpAmount, 'DP', 'Tunai', 'Tidak Diperlukan'),
                $row(round($total - $dpAmount, 2), 'Pelunasan', $nonCashMethod, Payment::PENDING_STATUS),
            ],
            'rejected_proof' => [$row($total, 'Pembayaran Penuh', $nonCashMethod, 'Ditolak', 'Bukti tidak terbaca (data uji beban).')],
            default => [],
        };
    }
}
