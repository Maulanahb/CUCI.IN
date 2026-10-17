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

    /**
     * Kelas Tailwind CSS untuk badge status berbasis semantik visual UX laundry.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Diterima => 'bg-sky-50 text-sky-700 border-sky-200',
            self::Diproses => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Selesai => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Diambil => 'bg-slate-100 text-slate-700 border-slate-300',
            self::Dibatalkan => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    /**
     * Kelas Tailwind CSS untuk dot indikator visual.
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::Diterima => 'bg-sky-500',
            self::Diproses => 'bg-amber-500',
            self::Selesai => 'bg-emerald-500',
            self::Diambil => 'bg-slate-400',
            self::Dibatalkan => 'bg-rose-500',
        };
    }

    /**
     * Mendapatkan pasangan style badge, dot, dan label untuk status (baik instance enum maupun string).
     *
     * @return array{badge: string, dot: string, label: string}
     */
    public static function visualStyle(self|string|null $status): array
    {
        $enum = is_string($status) ? self::tryFrom($status) : $status;

        if ($enum instanceof self) {
            return [
                'badge' => $enum->badgeClasses(),
                'dot' => $enum->dotClasses(),
                'label' => $enum->value,
            ];
        }

        return [
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400',
            'label' => (string) ($status ?? '-'),
        ];
    }
}
