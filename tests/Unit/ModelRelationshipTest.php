<?php

namespace Tests\Unit;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    public function test_user_relationships(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasOne::class, $user->customer());
        $this->assertInstanceOf(Customer::class, $user->customer()->getRelated());
        $this->assertEquals('user_id', $user->customer()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $user->createdPayments());
        $this->assertInstanceOf(Payment::class, $user->createdPayments()->getRelated());
        $this->assertEquals('created_by', $user->createdPayments()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $user->verifiedPayments());
        $this->assertInstanceOf(Payment::class, $user->verifiedPayments()->getRelated());
        $this->assertEquals('verified_by', $user->verifiedPayments()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $user->laundryStatusHistories());
        $this->assertInstanceOf(LaundryStatusHistory::class, $user->laundryStatusHistories()->getRelated());
        $this->assertEquals('changed_by', $user->laundryStatusHistories()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $user->handledComplaints());
        $this->assertInstanceOf(Complaint::class, $user->handledComplaints()->getRelated());
        $this->assertEquals('handled_by', $user->handledComplaints()->getForeignKeyName());
    }

    public function test_customer_relationships(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(BelongsTo::class, $customer->user());
        $this->assertInstanceOf(User::class, $customer->user()->getRelated());
        $this->assertEquals('user_id', $customer->user()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $customer->transactions());
        $this->assertInstanceOf(Transaction::class, $customer->transactions()->getRelated());
        $this->assertEquals('customer_id', $customer->transactions()->getForeignKeyName());
    }

    public function test_service_relationships(): void
    {
        $service = new Service;

        $this->assertInstanceOf(HasMany::class, $service->transactionDetails());
        $this->assertInstanceOf(TransactionDetail::class, $service->transactionDetails()->getRelated());
        $this->assertEquals('service_id', $service->transactionDetails()->getForeignKeyName());
    }

    public function test_transaction_relationships(): void
    {
        $transaction = new Transaction;

        $this->assertInstanceOf(BelongsTo::class, $transaction->customer());
        $this->assertInstanceOf(Customer::class, $transaction->customer()->getRelated());
        $this->assertEquals('customer_id', $transaction->customer()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $transaction->details());
        $this->assertInstanceOf(TransactionDetail::class, $transaction->details()->getRelated());
        $this->assertEquals('transaction_id', $transaction->details()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $transaction->payments());
        $this->assertInstanceOf(Payment::class, $transaction->payments()->getRelated());
        $this->assertEquals('transaction_id', $transaction->payments()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $transaction->statusHistories());
        $this->assertInstanceOf(LaundryStatusHistory::class, $transaction->statusHistories()->getRelated());
        $this->assertEquals('transaction_id', $transaction->statusHistories()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $transaction->complaints());
        $this->assertInstanceOf(Complaint::class, $transaction->complaints()->getRelated());
        $this->assertEquals('transaction_id', $transaction->complaints()->getForeignKeyName());
    }

    public function test_transaction_detail_relationships(): void
    {
        $detail = new TransactionDetail;

        $this->assertFalse($detail->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $detail->transaction());
        $this->assertInstanceOf(Transaction::class, $detail->transaction()->getRelated());
        $this->assertEquals('transaction_id', $detail->transaction()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $detail->service());
        $this->assertInstanceOf(Service::class, $detail->service()->getRelated());
        $this->assertEquals('service_id', $detail->service()->getForeignKeyName());
    }

    public function test_payment_relationships(): void
    {
        $payment = new Payment;

        $this->assertInstanceOf(BelongsTo::class, $payment->transaction());
        $this->assertInstanceOf(Transaction::class, $payment->transaction()->getRelated());
        $this->assertEquals('transaction_id', $payment->transaction()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $payment->creator());
        $this->assertInstanceOf(User::class, $payment->creator()->getRelated());
        $this->assertEquals('created_by', $payment->creator()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $payment->verifier());
        $this->assertInstanceOf(User::class, $payment->verifier()->getRelated());
        $this->assertEquals('verified_by', $payment->verifier()->getForeignKeyName());
    }

    public function test_laundry_status_history_relationships(): void
    {
        $history = new LaundryStatusHistory;

        $this->assertFalse($history->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $history->transaction());
        $this->assertInstanceOf(Transaction::class, $history->transaction()->getRelated());
        $this->assertEquals('transaction_id', $history->transaction()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $history->changedBy());
        $this->assertInstanceOf(User::class, $history->changedBy()->getRelated());
        $this->assertEquals('changed_by', $history->changedBy()->getForeignKeyName());
    }

    public function test_complaint_relationships(): void
    {
        $complaint = new Complaint;

        $this->assertInstanceOf(BelongsTo::class, $complaint->transaction());
        $this->assertInstanceOf(Transaction::class, $complaint->transaction()->getRelated());
        $this->assertEquals('transaction_id', $complaint->transaction()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $complaint->handler());
        $this->assertInstanceOf(User::class, $complaint->handler()->getRelated());
        $this->assertEquals('handled_by', $complaint->handler()->getForeignKeyName());
    }
}
