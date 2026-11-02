<?php

namespace App\Models;

use Database\Factories\IdempotencyKeyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdempotencyKey extends Model
{
    /** @use HasFactory<IdempotencyKeyFactory> */
    use HasFactory, Prunable;

    /**
     * Status siklus hidup idempotensi.
     */
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'idempotency_keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'user_id',
        'route',
        'request_hash',
        'status',
        'response_code',
        'response_body',
        'response_headers',
        'expires_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_PROCESSING,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_code' => 'integer',
            'response_headers' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Dapatkan kueri untuk pembersihan data kedaluwarsa secara otomatis (model:prune).
     */
    public function prunable(): Builder
    {
        return static::whereNotNull('expires_at')->where('expires_at', '<', now());
    }

    /**
     * Relasi ke User pembuat atau inisiator request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Cek apakah request sedang dalam status diproses.
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Cek apakah request telah selesai diproses dengan sukses.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Cek apakah eksekusi request mengalami kegagalan.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Cek apakah masa berlaku kunci idempotensi telah kedaluwarsa.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
