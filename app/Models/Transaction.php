<?php

namespace App\Models;

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
        'transaction_code',
        'total_amount',
        'status',
        'estimated_completed_at',
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
        ];
    }

    /**
     * Relasi ke Customer (many-to-one).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke TransactionDetail (one-to-many).
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    /**
     * Relasi ke Payment (one-to-many).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }

    /**
     * Relasi ke LaundryStatusHistory (one-to-many).
     */
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
}
