<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Payment;
use App\Models\Transaction;
use Database\Seeders\LoadTestTransactionSeeder;
use Database\Seeders\PaymentVerificationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTestingSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Payment::PROOF_DISK);
    }

    public function test_demo_seeder_creates_every_verification_scenario_with_proof_files(): void
    {
        $this->seed(PaymentVerificationDemoSeeder::class);

        $this->assertSame(3, Payment::query()->pendingVerification()->count());
        $this->assertSame(1, Payment::query()->where('verification_status', 'Ditolak')->whereNotNull('verification_notes')->count());
        $this->assertSame(1, Payment::query()->where('verification_status', 'Terverifikasi')->count());
        $this->assertEqualsCanonicalizing(['Menunggu', 'Diproses', 'Selesai'], Complaint::query()->pluck('status')->all());

        Payment::query()->whereNotNull('proof_file')->each(function (Payment $payment): void {
            Storage::disk(Payment::PROOF_DISK)->assertExists($payment->proof_file);
        });

        Transaction::query()->with('payments')->each(function (Transaction $transaction): void {
            $this->assertLessThanOrEqual((float) $transaction->total_amount, $transaction->total_paid + $transaction->pending_verification_amount);
        });
    }

    public function test_demo_seeder_is_not_duplicated_when_run_twice(): void
    {
        $this->seed(PaymentVerificationDemoSeeder::class);
        $transactionCount = Transaction::count();

        $this->seed(PaymentVerificationDemoSeeder::class);

        $this->assertSame($transactionCount, Transaction::count());
    }

    public function test_load_seeder_inserts_requested_volume_with_consistent_payments(): void
    {
        $seeder = $this->app->make(LoadTestTransactionSeeder::class);
        $seeder->setContainer($this->app);
        $seeder(['count' => 1200]);

        $this->assertSame(1200, Transaction::count());
        $this->assertSame(1200, Transaction::query()->distinct()->count('transaction_code'));
        $this->assertSame(0, Transaction::query()->doesntHave('details')->count());
        $this->assertSame(0, Transaction::query()->doesntHave('statusHistories')->count());

        $overpaid = Transaction::query()
            ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.transaction_id = transactions.id AND payments.verification_status != ?) > transactions.total_amount + 0.01', ['Ditolak'])
            ->count();
        $this->assertSame(0, $overpaid);

        $unpaidPickups = Transaction::query()->where('status', 'Diambil')->with('payments')->get()
            ->reject(fn (Transaction $transaction): bool => $transaction->isPaidOff());
        $this->assertCount(0, $unpaidPickups);
    }
}
