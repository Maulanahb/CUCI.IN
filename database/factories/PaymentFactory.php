<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'amount' => fake()->randomFloat(2, 10000, 100000),
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Tunai',
            'proof_file' => null,
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Bukti transfer/QRIS unggahan pelanggan yang menunggu verifikasi kasir.
     */
    public function pendingProof(string $method = 'Transfer'): static
    {
        return $this->state(fn (): array => [
            'method' => $method,
            'proof_file' => 'payments/proofs/'.fake()->uuid().'.jpg',
            'verification_status' => 'Menunggu Verifikasi',
        ]);
    }

    /**
     * Pembayaran non-tunai yang sudah disetujui kasir.
     */
    public function verified(string $method = 'Transfer'): static
    {
        return $this->state(fn (): array => [
            'method' => $method,
            'verification_status' => 'Terverifikasi',
            'verified_at' => now(),
        ]);
    }

    /**
     * Pembayaran non-tunai yang ditolak kasir.
     */
    public function rejected(string $method = 'Transfer'): static
    {
        return $this->state(fn (): array => [
            'method' => $method,
            'proof_file' => 'payments/proofs/'.fake()->uuid().'.jpg',
            'verification_status' => 'Ditolak',
            'verified_at' => now(),
        ]);
    }
}
