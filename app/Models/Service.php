<?php

namespace App\Models;

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
            'estimated_duration' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke TransactionDetail (one-to-many).
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'service_id');
    }
}
