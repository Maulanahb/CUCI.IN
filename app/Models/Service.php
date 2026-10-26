<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'unit',
        'service_type',
        'is_express',
        'minimum_order',
        'estimated_duration',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'unit' => 'string',
            'service_type' => 'string',
            'is_express' => 'boolean',
            'minimum_order' => 'decimal:2',
            'estimated_duration' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Label representasi unit/satuan (Kg, Pcs, M²).
     */
    public function getUnitLabelAttribute(): string
    {
        return match ($this->unit) {
            'pcs' => 'Pcs',
            'meter' => 'M²',
            default => 'Kg',
        };
    }

    /**
     * Label tipe paket layanan (Reguler, Express, Khusus).
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'express' => 'Paket Kilat / Express',
            'khusus' => 'Perawatan Khusus',
            default => 'Reguler',
        };
    }

    /**
     * Label durasi ramah pengguna (cth: "4 Jam (Kilat)", "24 Jam (1 Hari)").
     */
    public function getDurationLabelAttribute(): string
    {
        $hours = (int) $this->estimated_duration;
        if ($hours < 24) {
            return "{$hours} Jam".($this->is_express ? ' (Kilat)' : '');
        }

        $days = (int) round($hours / 24);

        return "{$hours} Jam ({$days} Hari)";
    }

    /**
     * Relasi ke TransactionDetail (one-to-many).
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'service_id');
    }

    /**
     * Scope untuk layanan aktif saja.
     *
     * @param  Builder<Service>  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk paket kilat/express.
     *
     * @param  Builder<Service>  $query
     */
    public function scopeExpress($query)
    {
        return $query->where('is_express', true);
    }
}
