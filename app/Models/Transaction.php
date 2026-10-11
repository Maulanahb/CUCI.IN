<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Services\TransactionCodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * TODO: Relasi ke Customer (many-to-one).
     * Model Customer dikelola oleh rekan tim (Daysyani).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * TODO: Relasi ke User pembuat transaksi (many-to-one).
     * Model User dikelola oleh rekan tim (Maulana).
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
     * TODO: Relasi ke TransactionDetail (one-to-many).
     * Model TransactionDetail dikelola oleh rekan tim (Gargarina).
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    /**
     * TODO: Relasi ke Payment (one-to-many).
     * Dikelola oleh rekan tim (Nayla).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }

    /**
     * TODO: Relasi ke LaundryStatusHistory (one-to-many).
     * Dikelola oleh rekan tim (Nayla).
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(LaundryStatusHistory::class, 'transaction_id');
    }

    /**
     * TODO: Relasi ke Complaint (one-to-many).
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'transaction_id');
    }
}
