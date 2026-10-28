<?php

namespace App\Services\Reports;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pembatas kueri pembayaran valid sesuai PRD BR-04 dan FR-13.
 *
 * Aturan validitas pembayaran:
 * - Tunai: selalu valid (verification_status = 'Tidak Diperlukan').
 * - Transfer / QRIS: hanya jika verification_status = 'Terverifikasi'.
 * - 'Menunggu Verifikasi' dan 'Ditolak' TIDAK dihitung sebagai pembayaran valid.
 *
 * Kelas ini mendelegasikan ke scope Payment::scopePaid() yang sudah ada
 * di model Payment (whereIn verification_status PAID_STATUSES).
 * Model Payment tidak diubah.
 */
final class ValidPayments
{
    /**
     * Kembalikan query builder pembayaran valid dalam rentang tanggal tertentu.
     *
     * Kolom yang digunakan untuk filter tanggal adalah `paid_at`,
     * yaitu waktu pembayaran benar-benar diterima.
     */
    public static function query(DateRange $range): Builder
    {
        return Payment::query()
            ->paid()
            ->whereBetween('paid_at', $range->toArray());
    }

    /**
     * Terapkan filter pembayaran valid ke query builder yang sudah ada.
     *
     * Berguna saat ingin meng-chain filter tambahan sebelum memanggil.
     */
    public static function applyTo(Builder $query): Builder
    {
        return $query->whereIn('verification_status', Payment::PAID_STATUSES);
    }
}
