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

    /**
     * Hirarki/urutan alur operasional status cucian.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Diterima => 1,
            self::Diproses => 2,
            self::Selesai => 3,
            self::Diambil => 4,
            self::Dibatalkan => 0,
        };
    }

    /**
     * Menandakan apakah status sudah final dan tidak dapat diubah lagi.
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Diambil, self::Dibatalkan], true);
    }

    /**
     * Memeriksa apakah perpindahan ke status target adalah rollback (mundur ke belakang).
     */
    public function isRollbackTo(self|string $target): bool
    {
        $targetEnum = is_string($target) ? self::tryFrom($target) : $target;

        if (! $targetEnum || $targetEnum === self::Dibatalkan || $this->isTerminal()) {
            return false;
        }

        return $targetEnum->rank() < $this->rank();
    }
}
