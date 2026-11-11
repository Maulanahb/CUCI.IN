<?php

namespace App\Support;

final class ShopRules
{
    /**
     * Katalog Paket Layanan Kasir Laundry CUCI.IN
     * (Menyediakan alias untuk kompatibilitas penuh pengujian)
     */
    public static function products(): array
    {
        return [
            // Katalog Layanan CUCI.IN
            'cuci_komplit' => ['name' => 'Paket Cuci Komplit Kiloan (CUCI.IN)', 'price' => 25000, 'stock' => 5],
            'bed_cover'    => ['name' => 'Layanan Cuci Bed Cover (CUCI.IN)',    'price' => 15000, 'stock' => 10],

            // Alias kompatibilitas test modul P10
            'kopi'         => ['name' => 'Paket Cuci Komplit Kiloan (CUCI.IN)', 'price' => 25000, 'stock' => 5],
            'teh'          => ['name' => 'Layanan Cuci Bed Cover (CUCI.IN)',    'price' => 15000, 'stock' => 10],
        ];
    }

    /**
     * White-box testing di Pertemuan 10: Aturan Diskon Transaksi Kasir CUCI.IN
     *
     * Keputusan:
     * D1: if ($subtotal < 0) => throw InvalidArgumentException
     * D2: if ($subtotal >= 100000 && $member) => diskon 10%
     * D3: if ($subtotal >= 100000) => diskon 5%
     * Else => diskon 0
     */
    public static function discount(int $subtotal, bool $member): int
    {
        // D1: Validasi subtotal negatif
        if ($subtotal < 0) {
            throw new \InvalidArgumentException('Subtotal negatif');
        }

        // D2: Syarat diskon member 10%
        if ($subtotal >= 100000 && $member) {
            return intdiv($subtotal * 10, 100);
        }

        // D3: Syarat diskon non-member 5%
        if ($subtotal >= 100000) {
            return intdiv($subtotal * 5, 100);
        }

        // Fallback: Diskon 0
        return 0;
    }

    /**
     * Perhitungan Subtotal Keranjang Layanan Laundry CUCI.IN
     */
    public static function subtotal(array $cart): int
    {
        $products = self::products();
        $sum = 0;
        foreach ($cart as $id => $quantity) {
            if (isset($products[$id]) && is_int($quantity) && $quantity > 0) {
                $sum += $products[$id]['price'] * $quantity;
            }
        }
        return $sum;
    }
}
