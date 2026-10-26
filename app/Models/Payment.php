<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Status verifikasi yang dihitung sebagai uang masuk.
     *
     * @var list<string>
     */
    public const PAID_STATUSES = ['Tidak Diperlukan', 'Terverifikasi'];

    /**
     * Status bukti pembayaran yang masih menunggu keputusan kasir.
     */
    public const PENDING_STATUS = 'Menunggu Verifikasi';

    /**
     * Metode pembayaran yang dapat disertai bukti unggahan.
     *
     * @var list<string>
     */
    public const PROOF_METHODS = ['Transfer', 'QRIS'];

    /**
     * Disk privat penyimpanan bukti pembayaran (tidak dapat diakses langsung lewat URL publik).
     */
    public const PROOF_DISK = 'local';

    /**
     * Folder penyimpanan bukti pembayaran di dalam disk.
     */
    public const PROOF_DIRECTORY = 'payments/proofs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'transaction_id',
        'amount',
        'payment_type',
        'method',
        'cash_received',
        'change_amount',
        'proof_file',
        'verification_status',
        'verification_notes',
        'paid_at',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cash_received' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Batasi kueri ke pembayaran yang sah sebagai uang masuk.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->whereIn('verification_status', self::PAID_STATUSES);
    }

    /**
     * Batasi kueri ke bukti pembayaran yang menunggu verifikasi.
     */
    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('verification_status', self::PENDING_STATUS);
    }

    /**
     * Cek apakah pembayaran masih menunggu verifikasi kasir.
     */
    public function isPendingVerification(): bool
    {
        return $this->verification_status === self::PENDING_STATUS;
    }

    /**
     * Relasi ke Transaction (many-to-one).
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Relasi ke User pencatat pembayaran (many-to-one).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User verifikator pembayaran (many-to-one opsional).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
