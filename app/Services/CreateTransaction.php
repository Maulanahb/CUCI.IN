<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateTransaction
{
    /**
     * Jalankan proses pembuatan transaksi laundry secara atomik.
     *
     * @param  array<string, mixed>|int  $customerOrData  ID customer atau array data payload
     * @param  int|string|null  $createdBy  ID user pembuat transaksi (Staff) jika parameter dipisah
     * @param  array<int, array<string, mixed>>  $items  Daftar item jika parameter dipisah
     *
     * @throws InvalidArgumentException
     */
    public static function execute(
        int|array $customerOrData,
        int|string|null $createdBy = null,
        array $items = []
    ): Transaction {
        $payNow = false;
        $paymentMethod = 'Tunai';
        $paymentAmount = null;
        $paymentChoice = null;

        if (is_array($customerOrData)) {
            $customerId = $customerOrData['customer_id'] ?? null;
            $createdBy = $customerOrData['created_by'] ?? $createdBy;
            $items = $customerOrData['items'] ?? $customerOrData['details'] ?? $items;
            $payNow = (bool) ($customerOrData['pay_now'] ?? false);
            $paymentMethod = (string) ($customerOrData['payment_method'] ?? 'Tunai');
            $paymentAmount = isset($customerOrData['payment_amount']) && $customerOrData['payment_amount'] !== null && $customerOrData['payment_amount'] !== ''
                ? (float) $customerOrData['payment_amount']
                : null;
            $paymentChoice = $customerOrData['payment_choice'] ?? null;
        } else {
            $customerId = $customerOrData;
        }

        if ($customerId === null) {
            throw new InvalidArgumentException('Customer ID harus diisi.');
        }

        if (empty($createdBy)) {
            throw new InvalidArgumentException('Created by (user ID Staff) harus diisi.');
        }

        return DB::transaction(function () use ($customerId, $createdBy, $items, $payNow, $paymentMethod, $paymentAmount, $paymentChoice): Transaction {
            // 1. Pastikan customer ada di database
            $customer = Customer::find($customerId);
            if ($customer === null) {
                throw new InvalidArgumentException("Customer dengan ID {$customerId} tidak ditemukan.");
            }

            // 2. Hitung harga, subtotal, total, dan durasi menggunakan TransactionCalculator
            // (Harga dan durasi diambil resmi dari model Service di database, input luar diabaikan)
            $calculation = TransactionCalculator::calculate($items);

            // 3. Buat transaction_code lewat TransactionCodeGenerator di dalam transaksi yang sama
            $transactionCode = TransactionCodeGenerator::generate();

            // 4. Simpan Transaction: status default 'Diterima', total_amount & estimated_completed_at dari kalkulator
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'created_by' => $createdBy,
                'transaction_code' => $transactionCode,
                'total_amount' => $calculation['total_amount'],
                'status' => TransactionStatus::Diterima,
                'estimated_completed_at' => $calculation['estimated_completed_at'],
            ]);

            // 5. Simpan TransactionDetail per item
            foreach ($calculation['items'] as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'service_id' => $item['service_id'],
                    'weight' => $item['weight'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 6. Catat log riwayat status pertama kali (Diterima)
            LaundryStatusHistory::create([
                'transaction_id' => $transaction->id,
                'status' => TransactionStatus::Diterima,
                'changed_by' => $createdBy,
                'changed_at' => now(),
            ]);

            // 7. Jika kasir memilih untuk bayar sekarang (Lunas atau DP)
            if ($payNow) {
                $total = (float) $transaction->total_amount;
                $choice = $paymentChoice ?? ($paymentAmount !== null && $paymentAmount > 0 && $paymentAmount < $total ? 'dp' : 'full');

                if ($choice === 'dp') {
                    if ($paymentAmount === null || $paymentAmount <= 0 || $paymentAmount >= $total) {
                        throw new InvalidArgumentException('Nominal DP harus lebih dari 0 dan kurang dari total tagihan.');
                    }

                    $payAmount = $paymentAmount;
                } else {
                    $payAmount = $total;
                }

                RecordPayment::execute(
                    transaction: $transaction,
                    amount: $payAmount,
                    method: $paymentMethod,
                    userId: (int) $createdBy
                );
            }

            // 8. Muat relasi details, customer, statusHistories & payments dan kembalikan model Transaction
            $transaction->load(['details', 'customer', 'statusHistories', 'payments']);

            return $transaction;
        });
    }

    /**
     * Alias execute() untuk pemanggilan berbasis instance.
     *
     * @param  array<string, mixed>|int  $customerOrData
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(
        int|array $customerOrData,
        int|string|null $createdBy = null,
        array $items = []
    ): Transaction {
        return self::execute($customerOrData, $createdBy, $items);
    }

    /**
     * Memungkinkan action dipanggil sebagai invokable class.
     *
     * @param  array<string, mixed>|int  $customerOrData
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __invoke(
        int|array $customerOrData,
        int|string|null $createdBy = null,
        array $items = []
    ): Transaction {
        return self::execute($customerOrData, $createdBy, $items);
    }
}
