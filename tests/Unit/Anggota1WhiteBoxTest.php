<?php

namespace Tests\Unit;

use App\Support\ShopRules;
use PHPUnit\Framework\TestCase;

class Anggota1WhiteBoxTest extends TestCase
{
    /**
     * Anggota 1: Daysyani Sophi Masayu
     * Kasus TC10-01: Diskon Member Transaksi Laundry di atas ambang batas (Rp150.000, true)
     * Jalur Keputusan: D1=False, D2=True, D3=– (Return 10%)
     */
    public function test_anggota_1_white_box(): void
    {
        $subtotal = 150000;
        $isMember = true;

        $hasilDiskon = ShopRules::discount($subtotal, $isMember);

        $this->assertSame(15000, $hasilDiskon);
    }
}
