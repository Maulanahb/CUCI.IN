<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Diterima = 'Diterima';
    case Diproses = 'Diproses';
    case Selesai = 'Selesai';
    case Diambil = 'Diambil';
    case Dibatalkan = 'Dibatalkan';

    /**
     * Get all enum values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
