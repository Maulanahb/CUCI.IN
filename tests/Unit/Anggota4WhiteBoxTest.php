<?php

namespace Tests\Unit;

use App\Support\ShopRules;
use PHPUnit\Framework\TestCase;

class Anggota4WhiteBoxTest extends TestCase
{
    /**
     * Anggota 4: Maulana Ahmad Bukhori
     * Kasus TC10-04: Subtotal Transaksi Laundry Negatif (-1, false) harus melempar Exception
     * Jalur Keputusan: D1=True, D2=–, D3=– (Throw InvalidArgumentException)
     */
    public function test_anggota_4_white_box(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subtotal negatif');

        ShopRules::discount(-1, false);
    }
}
