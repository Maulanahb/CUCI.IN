<?php

namespace Tests\Unit;

use App\Support\ShopRules;
use PHPUnit\Framework\TestCase;

class Anggota2WhiteBoxTest extends TestCase
{
    /**
     * Anggota 2: Gargarina Nanda Iswati (Ayin)
     * Kasus TC10-02: Diskon Non-Member Transaksi Laundry di atas ambang batas (Rp150.000, false)
     * Jalur Keputusan: D1=False, D2=False, D3=True (Return 5%)
     */
    public function test_anggota_2_white_box(): void
    {
        $subtotal = 150000;
        $isMember = false;

        $hasilDiskon = ShopRules::discount($subtotal, $isMember);

        $this->assertSame(7500, $hasilDiskon);
    }
}
