<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Models\Payment;
use App\Services\TransactionCodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'created_by',
        'transaction_code',
        'total_amount',
        'status',
        'estimated_completed_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => TransactionStatus::Diterima,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'estimated_completed_at' => 'datetime',
            'status' => TransactionStatus::class,
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction): void {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = TransactionCodeGenerator::generate();
            }
        });
    }

    /**
     * Relasi ke Customer (many-to-one).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke User pembuat transaksi (many-to-one).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias relasi ke creator (User).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke TransactionDetail (one-to-many).
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    /**
     * Alias relasi ke transaction details.
     */
    public function transactionDetails(): HasMany
    {
        return $this->details();
    }

    /**
     * Relasi ke Payment (one-to-many).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LaundryStatusHistory::class, 'transaction_id');
    }

    /**
     * Relasi ke Complaint (one-to-many).
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'transaction_id');
    }

    /**
     * Hitung total pembayaran yang valid (tidak ditolak).
     */
    public function getTotalPaidAttribute(): float
    {
        $validPayments = $this->relationLoaded('payments')
            ? $this->payments->filter(fn (Payment $p): bool => in_array($p->verification_status, Payment::PAID_STATUSES, true))
            : $this->payments()->paid()->get();

        return (float) $validPayments->sum('amount');
    }

    /**
     * Hitung sisa tagihan yang belum dibayar.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->total_amount - $this->total_paid);
    }

    /**
     * Status pembayaran dinamis: 'Lunas', 'DP', atau 'Belum Bayar'.
     */
    public function getPaymentStatusAttribute(): string
    {
        $totalPaid = $this->total_paid;
        $totalAmount = (float) $this->total_amount;

        if ($totalPaid >= $totalAmount && $totalAmount > 0) {
            return 'Lunas';
        }

        if ($totalPaid > 0) {
            return 'DP';
        }

        return 'Belum Bayar';
    }

    /**
     * Cek apakah transaksi sudah lunas 100%.
     */
    public function isPaidOff(): bool
    {
        return $this->payment_status === 'Lunas';
    }

    /**
     * Scope untuk menyaring cucian yang selesai namun belum diambil melebihi ambang hari.
     */
    public function scopeUnclaimedStorage($query, int $days = 14)
    {
        return $query->where('status', TransactionStatus::Selesai)
            ->where('updated_at', '<=', now()->subDays($days));
    }

    /**
     * Cek apakah cucian selesai disimpan melebihi ambang batas hari.
     */
    public function isStorageOverdue(int $days = 14): bool
    {
        return $this->status === TransactionStatus::Selesai
            && $this->updated_at !== null
            && $this->updated_at <= now()->subDays($days);
    }
}
