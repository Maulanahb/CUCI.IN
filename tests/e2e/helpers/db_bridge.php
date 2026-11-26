<?php

declare(strict_types=1);

use App\Enums\TransactionStatus;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

require __DIR__.'/../../../vendor/autoload.php';

$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

/**
 * Decode JSON payload passed as base64 CLI argument (or raw JSON fallback).
 *
 * @return array<string, mixed>
 */
function parsePayload(?string $raw): array
{
    if ($raw === null || $raw === '') {
        return [];
    }

    $decodedBase64 = base64_decode($raw, true);
    $candidate = $decodedBase64 !== false ? $decodedBase64 : $raw;
    $parsed = json_decode($candidate, true);

    return is_array($parsed) ? $parsed : [];
}

/**
 * Generate a minimal valid 1x1 PNG binary payload.
 */
function minimalPngBinary(): string
{
    return base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    );
}

/**
 * Generate an Indonesian-style unique mobile number for E2E customers.
 */
function uniqueE2ePhone(): string
{
    do {
        $phone = '0899'.str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    } while (Customer::query()->where('phone', $phone)->exists());

    return $phone;
}

/**
 * Ensure baseline accounts and at least two active services exist.
 *
 * @return array<string, mixed>
 */
function handleEnsureSeed(): array
{
    if (! User::query()->where('email', 'admin@cuci.in')->exists()
        || ! User::query()->where('email', 'staff@cuci.in')->exists()
        || ! User::query()->where('email', 'nonaktif@cuci.in')->exists()) {
        (new UserSeeder)->run();
    }

    if (! User::query()->where('email', 'customer@cuci.in')->exists()) {
        (new CustomerSeeder)->run();
    }

    if (Service::query()->where('is_active', true)->count() < 2) {
        (new ServiceSeeder)->run();
    }

    $services = Service::query()
        ->where('is_active', true)
        ->orderBy('id')
        ->get()
        ->map(fn (Service $service): array => [
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'unit' => $service->unit ?? 'kg',
            'minimum_order' => (float) ($service->minimum_order ?? 1),
            'estimated_duration' => (int) $service->estimated_duration,
            'is_express' => (bool) $service->is_express,
        ])
        ->values()
        ->all();

    return [
        'ok' => true,
        'services' => $services,
    ];
}

/**
 * Create a disposable E2E user (and optional Customer record).
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function handleCreateUser(array $payload): array
{
    $role = (string) ($payload['role'] ?? 'customer');
    $suffix = strtolower(Str::random(8));
    $email = (string) ($payload['email'] ?? "e2e.{$role}.{$suffix}@cuci.in");
    $name = (string) ($payload['name'] ?? 'E2E '.ucfirst($role).' '.strtoupper(substr($suffix, 0, 4)));
    $password = (string) ($payload['password'] ?? 'password');
    $isActive = array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : true;
    $withCustomer = array_key_exists('with_customer', $payload) ? (bool) $payload['with_customer'] : ($role === 'customer');

    return DB::transaction(function () use ($role, $email, $name, $password, $isActive, $withCustomer, $payload): array {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => $isActive,
        ]);

        $customer = null;
        if ($withCustomer) {
            $customer = Customer::query()->create([
                'user_id' => $user->id,
                'name' => $name,
                'phone' => (string) ($payload['phone'] ?? uniqueE2ePhone()),
                'address' => (string) ($payload['address'] ?? 'Jl. Uji E2E No. 1'),
            ]);
        }

        return [
            'ok' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'customer_id' => $customer?->id,
            'phone' => $customer?->phone,
        ];
    });
}

/**
 * Create a disposable E2E customer (with optional User login account).
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function handleCreateCustomer(array $payload): array
{
    $suffix = strtolower(Str::random(8));
    $name = (string) ($payload['name'] ?? 'E2E Pelanggan '.strtoupper(substr($suffix, 0, 4)));
    $phone = (string) ($payload['phone'] ?? uniqueE2ePhone());
    $address = (string) ($payload['address'] ?? 'Jl. E2E Testing No. 99');
    $withUser = (bool) ($payload['with_user'] ?? false);

    return DB::transaction(function () use ($suffix, $name, $phone, $address, $withUser, $payload): array {
        $user = null;
        if ($withUser) {
            $user = User::query()->create([
                'name' => $name,
                'email' => (string) ($payload['email'] ?? "e2e.cust.{$suffix}@cuci.in"),
                'password' => Hash::make((string) ($payload['password'] ?? 'password')),
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        $customer = Customer::query()->create([
            'user_id' => $user?->id,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
        ]);

        return [
            'ok' => true,
            'customer_id' => $customer->id,
            'user_id' => $user?->id,
            'email' => $user?->email,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'phone_last_4' => substr($customer->phone, -4),
        ];
    });
}

/**
 * Create a disposable E2E service.
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function handleCreateService(array $payload): array
{
    $suffix = strtoupper(Str::random(6));
    $service = Service::query()->create([
        'name' => (string) ($payload['name'] ?? "E2E Layanan {$suffix}"),
        'price' => (float) ($payload['price'] ?? 10000),
        'unit' => (string) ($payload['unit'] ?? 'kg'),
        'service_type' => (string) ($payload['service_type'] ?? 'reguler'),
        'is_express' => (bool) ($payload['is_express'] ?? false),
        'minimum_order' => (float) ($payload['minimum_order'] ?? 1.00),
        'estimated_duration' => (int) ($payload['estimated_duration'] ?? 24),
        'description' => (string) ($payload['description'] ?? 'Layanan sementara pengujian E2E'),
        'is_active' => array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : true,
    ]);

    return [
        'ok' => true,
        'service_id' => $service->id,
        'name' => $service->name,
        'price' => (float) $service->price,
        'minimum_order' => (float) $service->minimum_order,
    ];
}

/**
 * Create a real transaction with configurable status & payment state.
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function handleCreateTransaction(array $payload): array
{
    $customerId = isset($payload['customer_id']) ? (int) $payload['customer_id'] : null;
    if (! $customerId) {
        $created = handleCreateCustomer(['name' => 'E2E Pelanggan Trx']);
        $customerId = (int) $created['customer_id'];
    }

    $customer = Customer::query()->findOrFail($customerId);
    $creatorId = isset($payload['created_by_id'])
        ? (int) $payload['created_by_id']
        : (int) User::query()->where('email', 'staff@cuci.in')->value('id');

    $service = isset($payload['service_id'])
        ? Service::query()->findOrFail((int) $payload['service_id'])
        : Service::query()->where('is_active', true)->orderBy('id')->firstOrFail();

    $weight = (float) ($payload['weight'] ?? 5.0);
    $unitPrice = (float) $service->price;
    $totalAmount = isset($payload['total_amount'])
        ? (float) $payload['total_amount']
        : round($unitPrice * $weight, 2);

    $statusValue = (string) ($payload['status'] ?? 'Diterima');
    $statusEnum = TransactionStatus::from($statusValue);
    $paymentMode = (string) ($payload['payment_mode'] ?? 'unpaid');
    $transactionCode = (string) ($payload['transaction_code'] ?? ('TRX-E2E-'.strtoupper(Str::random(6))));

    return DB::transaction(function () use (
        $customer,
        $creatorId,
        $service,
        $weight,
        $unitPrice,
        $totalAmount,
        $statusEnum,
        $paymentMode,
        $transactionCode,
        $payload
    ): array {
        $transaction = Transaction::query()->create([
            'customer_id' => $customer->id,
            'created_by' => $creatorId,
            'transaction_code' => $transactionCode,
            'total_amount' => $totalAmount,
            'status' => $statusEnum,
            'estimated_completed_at' => now()->addHours((int) $service->estimated_duration),
        ]);

        TransactionDetail::query()->create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => $weight,
            'unit_price' => $unitPrice,
            'subtotal' => $totalAmount,
        ]);

        LaundryStatusHistory::query()->create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diterima,
            'changed_by' => $creatorId,
            'changed_at' => now()->subMinutes(10),
        ]);

        if ($statusEnum !== TransactionStatus::Diterima) {
            LaundryStatusHistory::query()->create([
                'transaction_id' => $transaction->id,
                'status' => $statusEnum,
                'notes' => $statusEnum === TransactionStatus::Dibatalkan ? 'Dibatalkan fixture E2E' : null,
                'changed_by' => $creatorId,
                'changed_at' => now()->subMinutes(2),
            ]);
        }

        $payment = null;
        $proofFile = null;

        if ($paymentMode === 'paid') {
            $payment = Payment::query()->create([
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount,
                'payment_type' => 'Pembayaran Penuh',
                'method' => 'Tunai',
                'cash_received' => $totalAmount,
                'change_amount' => 0,
                'verification_status' => 'Tidak Diperlukan',
                'paid_at' => now()->subMinutes(5),
                'created_by' => $creatorId,
            ]);
        } elseif ($paymentMode === 'dp') {
            $dpAmount = isset($payload['dp_amount'])
                ? (float) $payload['dp_amount']
                : max(1000.0, round($totalAmount * 0.4, -2));

            $payment = Payment::query()->create([
                'transaction_id' => $transaction->id,
                'amount' => min($dpAmount, max(100.0, $totalAmount - 100.0)),
                'payment_type' => 'DP',
                'method' => 'Tunai',
                'cash_received' => min($dpAmount, max(100.0, $totalAmount - 100.0)),
                'change_amount' => 0,
                'verification_status' => 'Tidak Diperlukan',
                'paid_at' => now()->subMinutes(5),
                'created_by' => $creatorId,
            ]);
        } elseif ($paymentMode === 'pending_proof') {
            $proofFile = Payment::PROOF_DIRECTORY.'/e2e_'.strtolower(Str::random(10)).'.png';
            Storage::disk(Payment::PROOF_DISK)->put($proofFile, minimalPngBinary());

            $proofAmount = isset($payload['dp_amount'])
                ? (float) $payload['dp_amount']
                : $totalAmount;

            $payment = Payment::query()->create([
                'transaction_id' => $transaction->id,
                'amount' => $proofAmount,
                'payment_type' => $proofAmount >= $totalAmount ? 'Pembayaran Penuh' : 'DP',
                'method' => (string) ($payload['proof_method'] ?? 'Transfer'),
                'proof_file' => $proofFile,
                'verification_status' => Payment::PENDING_STATUS,
                'paid_at' => now()->subMinutes(3),
                'created_by' => $customer->user_id ?? $creatorId,
            ]);
        }

        $transaction->refresh();

        return [
            'ok' => true,
            'transaction_id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_phone_last_4' => substr($customer->phone, -4),
            'service_id' => $service->id,
            'service_name' => $service->name,
            'total_amount' => (float) $transaction->total_amount,
            'total_paid' => (float) $transaction->total_paid,
            'remaining_amount' => (float) $transaction->remaining_amount,
            'status' => $transaction->status->value,
            'payment_id' => $payment?->id,
            'proof_file' => $proofFile,
        ];
    });
}

/**
 * Create a disposable complaint for an existing transaction.
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function handleCreateComplaint(array $payload): array
{
    $transaction = Transaction::query()->findOrFail((int) $payload['transaction_id']);
    $complaint = Complaint::query()->create([
        'complaint_code' => 'CMP-E2E-'.strtoupper(Str::random(6)),
        'transaction_id' => $transaction->id,
        'subject' => (string) ($payload['subject'] ?? 'E2E Kendala Pakaian'),
        'description' => (string) ($payload['description'] ?? 'Deskripsi kendala otomatis pengujian E2E.'),
        'status' => (string) ($payload['status'] ?? 'Menunggu'),
        'deadline_at' => now()->addDays(2),
    ]);

    return [
        'ok' => true,
        'complaint_id' => $complaint->id,
        'complaint_code' => $complaint->complaint_code,
        'transaction_id' => $transaction->id,
        'status' => $complaint->status,
    ];
}

/**
 * Clean up all E2E-created database records and uploaded temporary files.
 *
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
function handleCleanup(array $payload): array
{
    $transactionIds = array_values(array_unique(array_map('intval', (array) ($payload['transaction_ids'] ?? []))));
    $transactionCodes = array_values(array_filter(array_map('strval', (array) ($payload['transaction_codes'] ?? []))));
    $customerIds = array_values(array_unique(array_map('intval', (array) ($payload['customer_ids'] ?? []))));
    $userIds = array_values(array_unique(array_map('intval', (array) ($payload['user_ids'] ?? []))));
    $userEmails = array_values(array_filter(array_map('strval', (array) ($payload['user_emails'] ?? []))));
    $serviceIds = array_values(array_unique(array_map('intval', (array) ($payload['service_ids'] ?? []))));
    $serviceNames = array_values(array_filter(array_map('strval', (array) ($payload['service_names'] ?? []))));
    $sweepPrefix = (bool) ($payload['sweep_e2e_prefix'] ?? true);
    $sweepSessionActors = (bool) ($payload['sweep_session_actors'] ?? false);

    return DB::transaction(function () use (
        $transactionIds,
        $transactionCodes,
        $customerIds,
        $userIds,
        $userEmails,
        $serviceIds,
        $serviceNames,
        $sweepPrefix,
        $sweepSessionActors
    ): array {
        $allUserIds = User::query()
            ->where(function ($query) use ($userIds, $userEmails, $sweepPrefix, $sweepSessionActors): void {
                if ($userIds !== []) {
                    $query->orWhereIn('id', $userIds);
                }
                if ($userEmails !== []) {
                    $query->orWhereIn('email', $userEmails);
                }
                if ($sweepPrefix) {
                    $query->orWhere('email', 'like', 'e2e.%@cuci.in');
                }
                if ($sweepSessionActors) {
                    $query->orWhere('email', 'like', 'e2e-session.%@cuci.in');
                }
            })
            ->when(! $sweepSessionActors, function ($query): void {
                $query->where('email', 'not like', 'e2e-session.%@cuci.in');
            })
            ->pluck('id')
            ->all();

        $allCustomerIds = Customer::query()
            ->where(function ($query) use ($customerIds, $allUserIds, $sweepPrefix, $sweepSessionActors): void {
                if ($customerIds !== []) {
                    $query->orWhereIn('id', $customerIds);
                }
                if ($allUserIds !== []) {
                    $query->orWhereIn('user_id', $allUserIds);
                }
                if ($sweepPrefix) {
                    $query->orWhere('name', 'like', 'E2E %');
                }
                if ($sweepSessionActors) {
                    $query->orWhere('name', 'like', 'SessionE2E %');
                }
            })
            ->when(! $sweepSessionActors, function ($query): void {
                $query->where('name', 'not like', 'SessionE2E %');
            })
            ->pluck('id')
            ->all();

        $allTransactionIds = Transaction::query()
            ->where(function ($query) use ($transactionIds, $transactionCodes, $allCustomerIds, $allUserIds, $sweepPrefix): void {
                if ($transactionIds !== []) {
                    $query->orWhereIn('id', $transactionIds);
                }
                if ($transactionCodes !== []) {
                    $query->orWhereIn('transaction_code', $transactionCodes);
                }
                if ($allCustomerIds !== []) {
                    $query->orWhereIn('customer_id', $allCustomerIds);
                }
                if ($allUserIds !== []) {
                    $query->orWhereIn('created_by', $allUserIds);
                }
                if ($sweepPrefix) {
                    $query->orWhere('transaction_code', 'like', 'TRX-E2E-%');
                }
            })
            ->pluck('id')
            ->all();

        $complaintQuery = Complaint::query()
            ->where(function ($query) use ($allTransactionIds, $sweepPrefix): void {
                if ($allTransactionIds !== []) {
                    $query->orWhereIn('transaction_id', $allTransactionIds);
                }
                if ($sweepPrefix) {
                    $query->orWhere('complaint_code', 'like', 'CMP-E2E-%');
                }
            });

        $complaintPhotoPaths = (clone $complaintQuery)
            ->whereNotNull('photo_path')
            ->pluck('photo_path')
            ->all();

        foreach ($complaintPhotoPaths as $photoPath) {
            Storage::disk('public')->delete($photoPath);
            Storage::disk('local')->delete($photoPath);
        }

        $deletedComplaints = (clone $complaintQuery)->delete();

        $proofFiles = $allTransactionIds !== []
            ? Payment::query()->whereIn('transaction_id', $allTransactionIds)->whereNotNull('proof_file')->pluck('proof_file')->all()
            : [];

        foreach ($proofFiles as $proofFile) {
            Storage::disk(Payment::PROOF_DISK)->delete($proofFile);
        }

        $deletedPayments = $allTransactionIds !== []
            ? Payment::query()->whereIn('transaction_id', $allTransactionIds)->delete()
            : 0;

        $deletedHistories = $allTransactionIds !== []
            ? LaundryStatusHistory::query()->whereIn('transaction_id', $allTransactionIds)->delete()
            : 0;

        $deletedDetails = $allTransactionIds !== []
            ? TransactionDetail::query()->whereIn('transaction_id', $allTransactionIds)->delete()
            : 0;

        $deletedTransactions = $allTransactionIds !== []
            ? Transaction::query()->whereIn('id', $allTransactionIds)->delete()
            : 0;

        $deletedServices = Service::query()
            ->where(function ($query) use ($serviceIds, $serviceNames, $sweepPrefix): void {
                if ($serviceIds !== []) {
                    $query->orWhereIn('id', $serviceIds);
                }
                if ($serviceNames !== []) {
                    $query->orWhereIn('name', $serviceNames);
                }
                if ($sweepPrefix) {
                    $query->orWhere('name', 'like', 'E2E %');
                }
            })
            ->whereDoesntHave('transactionDetails')
            ->delete();

        if ($allUserIds !== []) {
            DB::table('idempotency_keys')->whereIn('user_id', $allUserIds)->delete();
            DB::table('sessions')->whereIn('user_id', $allUserIds)->delete();
        }

        $deletedCustomers = $allCustomerIds !== []
            ? Customer::query()->whereIn('id', $allCustomerIds)->delete()
            : 0;

        $deletedUsers = $allUserIds !== []
            ? User::query()->whereIn('id', $allUserIds)->delete()
            : 0;

        return [
            'ok' => true,
            'deleted' => [
                'complaints' => $deletedComplaints,
                'payments' => $deletedPayments,
                'histories' => $deletedHistories,
                'details' => $deletedDetails,
                'transactions' => $deletedTransactions,
                'services' => $deletedServices,
                'customers' => $deletedCustomers,
                'users' => $deletedUsers,
                'files' => count($proofFiles) + count($complaintPhotoPaths),
            ],
        ];
    });
}

$action = $argv[1] ?? '';
$payload = parsePayload($argv[2] ?? null);

try {
    $result = match ($action) {
        'ensure-seed' => handleEnsureSeed(),
        'create-user' => handleCreateUser($payload),
        'create-customer' => handleCreateCustomer($payload),
        'create-service' => handleCreateService($payload),
        'create-transaction' => handleCreateTransaction($payload),
        'create-complaint' => handleCreateComplaint($payload),
        'cleanup' => handleCleanup($payload),
        default => throw new InvalidArgumentException("Aksi tidak dikenal: {$action}"),
    };

    fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR).PHP_EOL);
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'exception' => $e::class,
    ], JSON_THROW_ON_ERROR).PHP_EOL);
    exit(1);
}
